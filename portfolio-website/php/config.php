<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'portfolio'); // Utilisation de la même base de données que dans connect.php
define('DB_USER', 'root');
define('DB_PASS', '');

// Initialisation de la session si elle n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Génération d'un token CSRF si nécessaire
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>