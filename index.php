<?php
session_start();

require_once 'User.class.php';
require_once 'Monster.class.php';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    User::logout();
    header("Location: index.php");
    exit;
}

 $errorMessage = '';
 $successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'signup') {
    }
    if ($_POST['action'] === 'login') {
    }
    if ($_POST['action'] === 'create_monster' && User::isLoggedIn()) {
    }
}


?>
<!DOCTYPE html>
<html lang="fr">
<head>
</head>
<body>
<div class="container">
    <h1>AI Monster Creator</h1>

    <?php if (User::isLoggedIn()): ?>
        <h2>Bienvenue, <?= htmlspecialchars(User::getPseudo()) ?> !</h2>

        <hr>
        <h2>Mes monstres</h2>
        <div class="monster-grid">
            <?php
            $userMonsters = Monster::getByUserId(User::getId());
            if (empty($userMonsters)) {
                echo "<p>Aucun monstre créé.</p>";
            } else {
                foreach ($userMonsters as $monster) {
                    echo "<div class='monster'>";
                    echo "<img src='image.php?id=" . htmlspecialchars($monster['id']) . "' alt='Image de " . htmlspecialchars($monster['name']) . "'>";
                    echo "<h3>" . htmlspecialchars($monster['name']) . "</h3>";
                    echo "<p><strong>Type:</strong> " . htmlspecialchars($monster['type']) . "<br>";
                    echo "<strong>Têtes:</strong> " . htmlspecialchars($monster['heads']) . "<br>";
                    echo "<strong>Créé le:</strong> " . htmlspecialchars($monster['createdAt']) . "<br><br>";
                    echo "<em>" . htmlspecialchars($monster['description']) . "</em><br><br>";
                    echo "Santé: {$monster['health']} | Attaque: {$monster['attack']} | Défense: {$monster['defense']}<br>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>