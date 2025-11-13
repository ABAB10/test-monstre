<?php
class Monster {

// Connexion à la base de donnée monsters.db et création de table si nécessaire

    private static function connect() {
        $dbFile = __DIR__ . '/../Database/monsters.db';
        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("PRAGMA foreign_keys = OFF;");

            $sql = "CREATE TABLE IF NOT EXISTS monsters (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                owner TEXT NOT NULL,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                heads INTEGER NOT NULL,
                image_blob BLOB,
                description TEXT,
                health INTEGER,
                attack INTEGER,
                defense INTEGER,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
            $pdo->exec($sql);

            return $pdo;
        } catch (PDOException $e) {
            die("Erreur de connexion ou de création de la base de données : " . $e->getMessage());
        }
    }

    // Générer les données d'image depuis une API externe en binaire
    public static function generateImageData($prompt) {
        $url = 'https://image.pollinations.ai/prompt/' . rawurlencode($prompt);
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: PHP"
            ]
        ];
        $context = stream_context_create($opts);
        // Utilisez file_get_contents en mode binaire explicitement
        $imageData = @file_get_contents($url, false, $context);
        if ($imageData === false || strlen($imageData) < 100) {
            return null;
        }
        return $imageData;
    }

    // Générer description et stats (external API ou fallback)
    public static function generateDescription($name, $type, $heads) {
        $prompt = "Describe a fantasy monster named '{$name}', of type '{$type}', with {$heads} heads. 
        Return a JSON object with fields:
        { \"description\": string, \"health\": number(0-100), \"attack\": number(0-100), \"defense\": number(0-100) }";

        $ch = curl_init("https://text.pollinations.ai/");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["model" => "gpt-4o-mini", "input" => $prompt]));
        $response = curl_exec($ch);
        curl_close($ch);

        $jsonStart = strpos($response, "{");
        $jsonEnd = strrpos($response, "}");
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonStr = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $parsed = json_decode($jsonStr, true);
            if (is_array($parsed)) {
                return [
                    "description" => $parsed["description"] ?? "Créature mystérieuse...",
                    "health" => $parsed["health"] ?? rand(50, 100),
                    "attack" => $parsed["attack"] ?? rand(50, 100),
                    "defense" => $parsed["defense"] ?? rand(50, 100),
                ];
            }
        }
        // Fallback si l'API échoue
        return [
            "description" => "Créature mystérieuse...",
            "health" => rand(50, 100),
            "attack" => rand(50, 100),
            "defense" => rand(50, 100)
        ];
    }

    // Enregistrement d'un nouveau monstre
    public static function register($name, $type, $heads, $userId, $ownerPseudo) {
        $pdo = self::connect();

        // Vérification des champs obligatoires
        if (empty($name) || empty($type) || empty($heads) || empty($userId) || empty($ownerPseudo)) {
            return ['success' => false, 'message' => "Données insuffisantes pour créer un monstre."];
        }

        // Génération de l'image et des stats du monstre
        $prompt = "fantasy monster named {$name}, type {$type}, with {$heads} heads, detailed illustration";
        $imageBlob = self::generateImageData($prompt);
        if ($imageBlob === null) {
            return ['success' => false, 'message' => "Erreur lors de la génération d'image."];
        }
        $stats = self::generateDescription($name, $type, $heads);

        try {
            $sql = "INSERT INTO monsters (user_id, owner, name, type, heads, image_blob, description, health, attack, defense) VALUES (:user_id, :owner, :name, :type, :heads, :image_blob, :description, :health, :attack, :defense)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                ':user_id' => $userId,
                ':owner' => $ownerPseudo,
                ':name' => $name,
                ':type' => $type,
                ':heads' => $heads,
                ':image_blob' => $imageBlob,
                ':description' => $stats['description'],
                ':health' => $stats['health'],
                ':attack' => $stats['attack'],
                ':defense' => $stats['defense']
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Monstre enregistré avec succès !'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la création du monstre.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur SQL: ' . $e->getMessage()];
        }
    }

    // Obtenir tous les monstres d'un utilisateur
    public static function getByUserId($userId) {
        $pdo = self::connect();
        $sql = "SELECT * FROM monsters WHERE user_id = :user_id ORDER BY createdAt DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $monsters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($monsters as &$monster) {
            if (!empty($monster['image_blob'])) {
                $mime = "image/png";
                if (substr($monster['image_blob'], 1, 3) === 'PNG') {
                    $mime = "image/png";
                } elseif (substr($monster['image_blob'], 0, 2) === "\xFF\xD8") {
                    $mime = "image/jpeg";
                }
                $monster['image_data_uri'] = "data:{$mime};base64," . base64_encode($monster['image_blob']);
            } else {
                $monster['image_data_uri'] = null;
            }
        }
        return $monsters;
    }
}
?>