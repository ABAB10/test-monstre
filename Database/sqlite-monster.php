<?php

$dbMonsterFile = __DIR__ . '/monsters.db';

try {
    $pdo = new PDO('sqlite:' . $dbMonsterFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA foreign_keys = OFF;");

    // On crée la table si elle n'existe pas déjà
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

    echo "Table 'monsters' créée (ou existe déjà) dans le fichier '" . basename($dbMonsterFile) . "'.";

} catch (PDOException $e) {
    die("Erreur de connexion ou de création de la base de données : " . $e->getMessage());
}
?>