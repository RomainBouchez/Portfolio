<?php
require_once 'config.php';

// Récupération du token
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (empty($token)) {
    header('Location: login.php?error=Token de vérification invalide');
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

// Vérification du token
$stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = :token AND is_active = 0");
$stmt->bindParam(':token', $token);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header('Location: login.php?error=Token de vérification invalide ou compte déjà activé');
    exit();
}

// Activation du compte
$userId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

$stmt = $conn->prepare("UPDATE users SET is_active = 1, verification_token = NULL WHERE id = :userId");
$stmt->bindParam(':userId', $userId);
$stmt->execute();

// Redirection avec message de succès
header('Location: login.php?success=Votre compte a été activé avec succès. Vous pouvez maintenant vous connecter.');
exit();
?>