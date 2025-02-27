<?php
session_start();
require_once 'config.php';

// Vérification du token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: login.php?error=Session expirée, veuillez réessayer');
    exit();
}

// Récupération des données du formulaire
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Validation des données
if (empty($email) || empty($password)) {
    header('Location: login.php?error=Veuillez remplir tous les champs');
    exit();
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Location: login.php?error=Erreur de connexion à la base de données');
    exit();
}

// Vérification des identifiants
$stmt = $conn->prepare("SELECT id, first_name, last_name, password_hash FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header('Location: login.php?error=Email ou mot de passe incorrect');
    exit();
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérification du mot de passe
if (!password_verify($password, $user['password_hash'])) {
    header('Location: login.php?error=Email ou mot de passe incorrect');
    exit();
}

// Création de la session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_email'] = $email;

// Redirection vers le tableau de bord
header('Location: dashboard.php');
exit();
?>