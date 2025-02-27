<?php
session_start();
require_once 'config.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Veuillez vous connecter pour accéder à cette page');
    exit();
}

// Vérification du token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: profile.php?error=Token CSRF invalide');
    exit();
}

// Récupération des données du formulaire
$userId = $_SESSION['user_id'];
$firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
$lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
$birthDate = htmlspecialchars(trim($_POST['birthDate'] ?? ''));
$address = htmlspecialchars(trim($_POST['address'] ?? '')); // Cette ligne était manquante
$phoneNumber = htmlspecialchars(trim($_POST['phoneNumber'] ?? ''));
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL); // Cette variable n'était pas correctement définie
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validation des données
if (empty($firstName) || empty($lastName) || empty($birthDate) || empty($address) || empty($phoneNumber) || empty($email)) {
    header('Location: profile.php?error=Tous les champs du profil sont obligatoires');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: profile.php?error=Email invalide');
    exit();
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Location: profile.php?error=Erreur de connexion à la base de données');
    exit();
}

// Vérification de l'unicité de l'email s'il a été modifié
$stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :userId");
$stmt->bindParam(':email', $email);
$stmt->bindParam(':userId', $userId);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    header('Location: profile.php?error=Cet email est déjà utilisé par un autre compte');
    exit();
}

// Mise à jour du profil
try {
    // Si un nouveau mot de passe est fourni
    if (!empty($password)) {
        if ($password !== $confirmPassword) {
            header('Location: profile.php?error=Les mots de passe ne correspondent pas');
            exit();
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET first_name = :firstName, last_name = :lastName, birth_date = :birthDate,
                              address = :address, phone_number = :phoneNumber, email = :email, password_hash = :passwordHash
                              WHERE id = :userId");
        $stmt->bindParam(':passwordHash', $passwordHash);
    } else {
        $stmt = $conn->prepare("UPDATE users SET first_name = :firstName, last_name = :lastName, birth_date = :birthDate,
                              address = :address, phone_number = :phoneNumber, email = :email
                              WHERE id = :userId");
    }
    
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':birthDate', $birthDate);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phoneNumber', $phoneNumber);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':userId', $userId);
    
    $stmt->execute();
    
    // Mise à jour du nom dans la session
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
    $_SESSION['user_email'] = $email;
    
    header('Location: profile.php?success=Profil mis à jour avec succès');
    exit();
    
} catch(PDOException $e) {
    header('Location: profile.php?error=Erreur lors de la mise à jour du profil: ' . $e->getMessage());
    exit();
}
?>