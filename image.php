<?php
// Ce script sert à afficher une image stockée en BLOB dans la base de données

// Se connecter à la base de données
 $dbFile = 'database.db';
try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifier qu'un ID de monstre est fourni dans l'URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $monsterId = $_GET['id'];

    // Préparer et exécuter la requête pour récupérer les données de l'image
    $stmt = $pdo->prepare("SELECT image_blob FROM monsters WHERE id = ?");
    $stmt->execute([$monsterId]);
    $monster = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($monster && !empty($monster['image_blob'])) {
        header("Content-Type: image/png");
        echo $monster['image_blob'];
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "Image non trouvée.";
    }
} else {
    header("HTTP/1.0 400 Bad Request");
    echo "Requête invalide.";
}
?>