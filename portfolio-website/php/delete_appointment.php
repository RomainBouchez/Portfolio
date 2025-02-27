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
    header('Location: dashboard.php?error=Token CSRF invalide');
    exit();
}

// Récupération de l'ID du rendez-vous
$appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);

if (!$appointmentId) {
    header('Location: dashboard.php?error=ID de rendez-vous invalide');
    exit();
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Location: dashboard.php?error=Erreur de connexion à la base de données');
    exit();
}

// Vérification que le rendez-vous appartient bien à l'utilisateur
$stmt = $conn->prepare("SELECT id FROM appointments WHERE id = :id AND user_id = :userId");
$stmt->bindParam(':id', $appointmentId);
$stmt->bindParam(':userId', $_SESSION['user_id']);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header('Location: dashboard.php?error=Ce rendez-vous n\'existe pas ou ne vous appartient pas');
    exit();
}

// Suppression du rendez-vous
try {
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = :id AND user_id = :userId");
    $stmt->bindParam(':id', $appointmentId);
    $stmt->bindParam(':userId', $_SESSION['user_id']);
    $stmt->execute();
    
    header('Location: dashboard.php?success=Rendez-vous annulé avec succès');
    exit();
    
} catch(PDOException $e) {
    header('Location: dashboard.php?error=Erreur lors de la suppression du rendez-vous: ' . $e->getMessage());
    exit();
}
?>