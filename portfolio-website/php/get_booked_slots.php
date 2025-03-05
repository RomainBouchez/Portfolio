<?php
require_once 'config.php';
header('Content-Type: application/json');

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit();
}

// Récupérer tous les créneaux réservés
$stmt = $conn->prepare("SELECT appointment_date, appointment_time FROM appointments");
$stmt->execute();
$bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Renvoyer les créneaux réservés au format JSON
echo json_encode(['bookedSlots' => $bookedSlots]);
?>