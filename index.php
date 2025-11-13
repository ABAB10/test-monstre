<?php
session_start();

require_once __DIR__ . '/classes/User.class.php';
require_once __DIR__ . '/classes/Monster.class.php';

// Initialiser les variables pour éviter les "Undefined variable"
$errorMessage = '';
$successMessage = '';

// Gestion de la déconnexion
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    User::logout();
    header("Location: index.php");
    exit;
}

// Fonction utilitaire pour obtenir l'id utilisateur connecté
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'signup') {
        $pseudo = trim($_POST['pseudo']) ?? '';
        $mail = trim($_POST['mail']) ?? '';
        $password = trim($_POST['password']) ?? '';

        if ($pseudo && $mail && $password) {
            if (User::signup($pseudo, $mail, $password)) {
                $successMessage = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            } else {
                $errorMessage = "Ce mail ou ce pseudo est déjà utilisé(e).";
            }
        } else {
            $errorMessage = "Veuillez remplir tous les champs.";
        }
    } elseif ($_POST['action'] === 'login') {
        $mail = trim($_POST['mail']) ?? '';
        $password = trim($_POST['password']) ?? '';

        if (!User::login($mail, $password)) {
            $errorMessage = "Identifiant ou mot de passe incorrect.";
        }
    } elseif ($_POST['action'] === 'create_monster') {
        if (User::isLoggedIn()) {
            $name = trim($_POST['name'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $heads = intval($_POST['heads'] ?? 0);

            if ($name && $type && $heads > 0) {
                $userId = getUserId();
                $userPseudo = User::getPseudo();
                $result = Monster::register($name, $type, $heads, $userId, $userPseudo);
                if ($result['success']) {
                    $successMessage = $result['message'];
                } else {
                    $errorMessage = $result['message'];
                }
            } else {
                $errorMessage = "Tous les champs sont obligatoires.";
            }
        } else {
            $errorMessage = "Vous devez être connecté pour créer un monstre.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Monster Creator AI</title>
<style>
    body { font-family: Arial, sans-serif; background:#111; color:white; text-align:center; margin-top:40px; }
    .container { max-width: 800px; margin: auto; }
    form { margin: 15px auto; width: 340px; background: #222; padding: 20px; border-radius: 12px; display: inline-block; vertical-align: top; }
    input, button { width: 90%; padding: 8px; margin: 6px 0; border-radius: 6px; border:none; }
    button { background:#00cc88; color:white; cursor:pointer; }
    button:hover { background:#00ff99; }
    .error { color: red; font-weight: bold; }
    .success { color: green; font-weight: bold; }
    .monster-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; margin-top: 20px; }
    .monster { background:#222; padding:15px; border-radius:10px; width:280px; text-align: left; }
    .monster img { width:100%; border-radius:10px; }
    .logout-btn { background: #d9534f; }
    .logout-btn:hover { background: #c9302c; }
</style>
</head>
<body>

<div class="container">
    <h1>AI Monster Creator</h1>

    <?php if ($successMessage): ?>
        <p class="success"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <?php if (!User::isLoggedIn()): ?>
        <div>
            <form method="POST">
                <h3>Connexion</h3>
                <input type="hidden" name="action" value="login">
                <input type="email" name="mail" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Mot de passe" required><br>
                <button type="submit">Se connecter</button>
            </form>

            <form method="POST">
                <h3>Créer un compte</h3>
                <input type="hidden" name="action" value="signup">
                <input type="text" name="pseudo" placeholder="Pseudo" required><br>
                <input type="email" name="mail" placeholder="Email" required><br>
                <input type="password" name="password" placeholder="Mot de passe" required><br>
                <button type="submit">S'inscrire</button>
            </form>
        </div>
    <?php else: ?>
        <h2>Bienvenue, <?= htmlspecialchars(User::getPseudo()) ?> !</h2>
        <a href="?action=logout"><button class="logout-btn">Se déconnecter</button></a>
        
        <hr>

        <form method="POST">
            <h3>Créer un monstre</h3>
            <input type="hidden" name="action" value="create_monster">
            <input type="text" name="name" placeholder="Nom du monstre" required><br>
            <input type="text" name="type" placeholder="Type (feu, glace, etc.)" required><br>
            <input type="number" name="heads" placeholder="Nombre de têtes" required min="1"><br>
            <button type="submit">Créer</button>
        </form>

        <hr>
        <h2>Mes monstres</h2>
        <div class="monster-grid">
            <?php
            $userId = getUserId();
            $userMonsters = [];
            if ($userId) {
                $userMonsters = Monster::getByUserId($userId);
            }
            if (empty($userMonsters)) {
                echo "<p>Aucun monstre créé.</p>";
            } else {
                foreach ($userMonsters as $monster) {
                    echo "<div class='monster'>";
                    if (!empty($monster['image_data_uri'])) {
                        echo "<img src='" . htmlspecialchars($monster['image_data_uri']) . "' alt='Image de " . htmlspecialchars($monster['name']) . "'>";
                    } else {
zz                        echo "<img src='image.php?id=" . htmlspecialchars($monster['id']) . "' alt='Image de " . htmlspecialchars($monster['name']) . "'>";
                    }
                    echo "<h3>" . htmlspecialchars($monster['name']) . "</h3>";
                    echo "<p><strong>Type:</strong> " . htmlspecialchars($monster['type']) . "<br>";
                    echo "<strong>Têtes:</strong> " . htmlspecialchars($monster['heads']) . "<br>";
                    echo "<strong>Créé le:</strong> " . htmlspecialchars($monster['createdAt']) . "<br><br>";
                    echo "<em>" . htmlspecialchars($monster['description']) . "</em><br><br>";
                    echo "Santé: " . htmlspecialchars($monster['health']) . " | Attaque: " . htmlspecialchars($monster['attack']) . " | Défense: " . htmlspecialchars($monster['defense']) . "<br>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>