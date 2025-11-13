<?php
class User {

// Connexion à la base de données et création de la table si nécessaire

    private static function connect() {

        $dbFile = __DIR__ . '/../Database/database.db';
        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            // Activer les contraintes de clés étrangères pour la cohérence
            $pdo->exec("PRAGMA foreign_keys = ON;");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Vérifier si la table 'users' existe
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
            if ($stmt->fetch() === false) {
                // La table n'existe pas, on la crée
                echo "La table 'users' n'existe pas. Création en cours...<br>";
                $sql = "CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    pseudo TEXT NOT NULL UNIQUE,
                    mail TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
                )";
                $pdo->exec($sql);
                echo "Table 'users' créée avec succès !<br>";
            }
            return $pdo;
        } catch (PDOException $e) {
            die("Erreur de connexion ou de création de la base de données : " . $e->getMessage());
        }
    }

    // Inscription d'un nouvel utilisateur
    public static function signup($pseudo, $mail, $password) {
        $pdo = self::connect();
        // Hachage du mot de passe pour la sécurité
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (pseudo, mail, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$pseudo, $mail, $hashedPassword]);
    }

    // Connexion d'un utilisateur
    public static function login($mail, $password) {
        $pdo = self::connect();
        $sql = "SELECT * FROM users WHERE mail = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$mail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe avec le hash
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['pseudo'] = $user['pseudo'];
            return true;
        }
        return false;
    }

    // Déconnexion
    public static function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['pseudo']);
    }

    // Vérifier si l'utilisateur est connecté
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Obtenir le pseudo de l'utilisateur connecté
    public static function getPseudo() {
        return $_SESSION['pseudo'] ?? 'Invité';
    }

    // Obtenir l'ID de l'utilisateur connecté
    public static function getId() {
        return $_SESSION['user_id'] ?? null;
    }
}
?>