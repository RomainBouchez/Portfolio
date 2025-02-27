<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '\..\vendor\autoload.php'; // Chemin vers l'autoload de Composer

session_start();
require_once 'config.php';

// Génération d'un token de vérification unique
$verificationToken = bin2hex(random_bytes(32));

// Vos informations de configuration Gmail
$gmailUsername = 'romainbouchez44230@gmail.com';
$gmailPassword = 'jymp vuzw boys zenu';

// Vérification du token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: register.php?error=Invalid CSRF token');
    exit();
}

// Récupération des données du formulaire
$firstName = htmlspecialchars(trim($_POST['firstName'] ?? ''));
$lastName = htmlspecialchars(trim($_POST['lastName'] ?? ''));
$birthDate = htmlspecialchars(trim($_POST['birthDate'] ?? ''));
$address = htmlspecialchars(trim($_POST['address'] ?? ''));
$phoneNumber = htmlspecialchars(trim($_POST['phoneNumber'] ?? ''));
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Validation des données
if (empty($firstName) || empty($lastName) || empty($birthDate) || 
    empty($address) || empty($phoneNumber) || empty($email) || 
    empty($password) || empty($confirmPassword)) {
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

// Préparation de l'insertion
try {
    $stmt = $conn->prepare("INSERT INTO users 
        (first_name, last_name, birth_date, address, phone_number, 
        email, password_hash, verification_token, is_active) 
        VALUES 
        (:firstName, :lastName, :birthDate, :address, :phoneNumber, 
        :email, :passwordHash, :verificationToken, 0)");
    
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':birthDate', $birthDate);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phoneNumber', $phoneNumber);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':passwordHash', $passwordHash);
    $stmt->bindParam(':verificationToken', $verificationToken);
    
    $stmt->execute();
    
    // Envoi de l'email de vérification
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $gmailUsername;
        $mail->Password   = $gmailPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Configuration de l'email
        $mail->setFrom($gmailUsername, 'SystemRDV');
        $mail->addAddress($email, $firstName . ' ' . $lastName);
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de votre compte SystemRDV';
        
        // Générer le lien de vérification
        $verificationLink = 'http://' . $_SERVER['HTTP_HOST'] . '/Perso/portfolio-website/php/verify.php?token=' . $verificationToken;
        
        $mail->Body    = "
            <h2>Bienvenue sur SystemRDV, $firstName!</h2>
            <p>Merci de vous être inscrit. Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :</p>
            <p><a href='$verificationLink'>Confirmer mon compte</a></p>
            <p>Si vous n'avez pas créé ce compte, ignorez cet email.</p>verificationLink 
        ";
        $mail->AltBody = "Bienvenue sur SystemRDV, $firstName! 
        Pour activer votre compte, copiez ce lien dans votre navigateur : $verificationLink";

        $mail->send();
        
        // Redirection avec message de succès
        header('Location: register.php?success=Inscription réussie. Veuillez vérifier votre email pour activer votre compte.');
        exit();
        
    } catch (Exception $e) {
        // En cas d'erreur d'envoi d'email, supprimer l'utilisateur
        $stmt = $conn->prepare("DELETE FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        header('Location: register.php?error=Erreur lors de l\'envoi de l\'email de confirmation: ' . $mail->ErrorInfo);
        exit();
    }
    
} catch(PDOException $e) {
    header('Location: register.php?error=Erreur lors de l\'inscription: ' . $e->getMessage());
    exit();
}
?>