<?php
class User {
    // Connexion à la base de données SQLite
    private static function connect() {
        $dbFile = 'database.db';
        try {
            return new PDO('sqlite:' . $dbFile);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
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

        // Vérification du mot de passe
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