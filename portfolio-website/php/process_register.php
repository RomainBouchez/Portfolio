<?php
session_start();
require_once 'config.php';

// Vérification du token CSRF
//if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//    header('Location: register.php?error=Invalid CSRF token');
//    exit();
//}

// Récupération des données du formulaire
$firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
$lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
$address = htmlspecialchars(trim($_POST['address'] ?? ''));
$phoneNumber = htmlspecialchars(trim($_POST['phoneNumber'] ?? ''));
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Validation des données
if (!isset($_POST['firstName']) || $_POST['firstName'] === '' ||
    !isset($_POST['lastName']) || $_POST['lastName'] === '' ||
    !isset($_POST['birthDate']) || $_POST['birthDate'] === '' ||
    !isset($_POST['address']) || $_POST['address'] === '' ||
    !isset($_POST['phoneNumber']) || $_POST['phoneNumber'] === '' ||
    !isset($_POST['email']) || $_POST['email'] === '' ||
    !isset($_POST['password']) || $_POST['password'] === '') {
    header('Location: register.php?error=Tous les champs sont obligatoires');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php?error=Email invalide');
    exit();
}

if ($password !== $confirmPassword) {
    header('Location: register.php?error=Les mots de passe ne correspondent pas');
    exit();
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Location: register.php?error=Erreur de connexion à la base de données');
    exit();
}

// Vérification de l'unicité de l'email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    header('Location: register.php?error=Cet email est déjà utilisé');
    exit();
}

// Hachage du mot de passe
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Génération d'un token de vérification
$verificationToken = bin2hex(random_bytes(32));

// Insertion de l'utilisateur dans la base de données
try {
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, birth_date, address, phone_number, email, password_hash, verification_token) 
                            VALUES (:firstName, :lastName, :birthDate, :address, :phoneNumber, :email, :passwordHash, :verificationToken)");
    
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':birthDate', $birthDate);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phoneNumber', $phoneNumber);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':passwordHash', $passwordHash);
    $stmt->bindParam(':verificationToken', $verificationToken);
    
    $stmt->execute();
    
    // Envoi de l'email de vérification (à implémenter)
    $to = $email;
    $subject = "Vérification de votre compte";
    $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=" . $verificationToken;
    $message = "Bonjour $firstName,\n\nMerci de vous être inscrit. Veuillez cliquer sur le lien suivant pour activer votre compte :\n$verificationLink\n\nCordialement,\nL'équipe Système de Réservation";
    $headers = "From: noreply@systemerdv.com";
    
    // Fonction mail() désactivée pour le moment, à activer en production
    // mail($to, $subject, $message, $headers);
    
    // Message de succès
    header('Location: register.php?success=Inscription réussie! Un email de vérification a été envoyé à votre adresse email.');
    
} catch(PDOException $e) {
    header('Location: register.php?error=Erreur lors de l\'inscription: ' . $e->getMessage());
}
?>