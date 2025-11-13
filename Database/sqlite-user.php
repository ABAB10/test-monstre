<?php

// Nom du fichier de la base de données

$dbFile = 'database.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pseudo TEXT NOT NULL UNIQUE,
        mail TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);

    echo "Table 'users' créée avec succès dans le fichier '$dbFile'.";

} catch (PDOException $e) {
    die("Erreur lors de la création de la base de données : " . $e->getMessage());
}
?>