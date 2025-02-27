<?php
session_start();
require_once 'config.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Veuillez vous connecter pour prendre un rendez-vous');
    exit();
}

// Vérification du token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: dashboard.php?error=Token CSRF invalide');
    exit();
}

// Récupération des données du formulaire
$userId = $_SESSION['user_id'];
$appointmentDate = $_POST['appointmentDate'] ?? null;
$appointmentTime = $_POST['appointmentTime'] ?? null;
$description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

// Validation des données
if (empty($appointmentDate) || empty($appointmentTime)) {
    header('Location: dashboard.php?error=Veuillez sélectionner une date et une heure');
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

// Vérification de la disponibilité du créneau
$stmt = $conn->prepare("SELECT id FROM appointments WHERE appointment_date = :date AND appointment_time = :time");
$stmt->bindParam(':date', $appointmentDate);
$stmt->bindParam(':time', $appointmentTime);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    header('Location: dashboard.php?error=Ce créneau horaire est déjà réservé');
    exit();
}

// Enregistrement du rendez-vous
try {
    $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date, appointment_time, description) 
                           VALUES (:userId, :date, :time, :description)");
    
    $stmt->bindParam(':userId', $userId);
    $stmt->bindParam(':date', $appointmentDate);
    $stmt->bindParam(':time', $appointmentTime);
    $stmt->bindParam(':description', $description);
    
    $stmt->execute();
    
    header('Location: dashboard.php?success=Rendez-vous enregistré avec succès');
    exit();
    
} catch(PDOException $e) {
    // Gestion des erreurs d'insertion
    if ($e->getCode() == 23000) { // Code d'erreur pour violation de contrainte d'unicité
        header('Location: dashboard.php?error=Vous avez déjà un rendez-vous à cette date et heure');
    } else {
        header('Location: dashboard.php?error=Erreur lors de l\'enregistrement du rendez-vous: ' . $e->getMessage());
    }
    exit();
}
?>