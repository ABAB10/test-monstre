<?php
// Nom du fichier de la base de données
 $dbFile = 'database.db';

try {
    // Connexion à la base de données
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête SQL pour créer la table des monstres
    // user_id est une clé étrangère qui lie le monstre à un utilisateur
    $sql = "CREATE TABLE IF NOT EXISTS monsters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        owner TEXT NOT NULL,
        name TEXT NOT NULL,
        type TEXT NOT NULL,
        heads INTEGER NOT NULL,
        image TEXT NOT NULL,
        description TEXT,
        health INTEGER,
        attack INTEGER,
        defense INTEGER,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    )";

    // Exécuter la requête
    $pdo->exec($sql);

    echo "Table 'monsters' créée avec succès dans le fichier '$dbFile'.";

} catch (PDOException $e) {
    die("Erreur lors de la création de la base de données : " . $e->getMessage());
}
?>