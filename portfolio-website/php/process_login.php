<?php
session_start();
require_once 'config.php';

// Verify CSRF token (keep your existing code)
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: login.php?error=Session expirée, veuillez réessayer');
    exit();
}

// Get form data (keep your existing code)
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Validate data (keep your existing code)
if (empty($email) || empty($password)) {
    header('Location: login.php?error=Veuillez remplir tous les champs');
    exit();
}

// Database connection (keep your existing code)
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Location: login.php?error=Erreur de connexion à la base de données');
    exit();
}

// Check credentials
// MODIFY THIS QUERY to include is_active in the SELECT 
$stmt = $conn->prepare("SELECT id, first_name, last_name, password_hash, is_active FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header('Location: login.php?error=Email ou mot de passe incorrect');
    exit();
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify password
if (!password_verify($password, $user['password_hash'])) {
    header('Location: login.php?error=Email ou mot de passe incorrect');
    exit();
}

// ADD THIS NEW CHECK for account activation
if ($user['is_active'] == 0) {
    header('Location: login.php?error=Votre compte n\'est pas encore activé. Veuillez vérifier votre email et cliquer sur le lien d\'activation.');
    exit();
}

// Create session (keep your existing code)
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_email'] = $email;

// Redirect to dashboard
header('Location: dashboard.php');
exit();
?>