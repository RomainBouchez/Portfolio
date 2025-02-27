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

$userId = $_SESSION['user_id'];

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    header('Location: profile.php?error=Erreur de connexion à la base de données');
    exit();
}

// Suppression du compte et de toutes les données associées
try {
    // Commencer une transaction
    $conn->beginTransaction();
    
    // Supprimer les rendez-vous
    $stmt = $conn->prepare("DELETE FROM appointments WHERE user_id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    
    // Supprimer l'utilisateur
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :userId");
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    
    // Valider la transaction
    $conn->commit();
    
    // Détruire la session
    session_unset();
    session_destroy();
    
    // Rediriger vers la page d'accueil
    header('Location: index.php?success=Votre compte a été supprimé avec succès');
    exit();
    
} catch(PDOException $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollBack();
    header('Location: profile.php?error=Erreur lors de la suppression du compte: ' . $e->getMessage());
    exit();
}
?>