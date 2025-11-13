<?php
// Nom du fichier de la base de données
 $dbFile = 'database.db';

try {
    // Connexion à la base de données (le fichier sera créé s'il n'existe pas)
    $pdo = new PDO('sqlite:' . $dbFile);
    // Activer les erreurs PDO pour le débogage
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL pour créer la table des utilisateurs
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pseudo TEXT NOT NULL UNIQUE,
        mail TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    // Exécuter la requête
    $pdo->exec($sql);

    echo "Table 'users' créée avec succès dans le fichier '$dbFile'.";

} catch (PDOException $e) {
    // En cas d'erreur, l'afficher
    die("Erreur lors de la création de la base de données : " . $e->getMessage());
}
?>