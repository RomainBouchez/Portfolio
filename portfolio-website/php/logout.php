<?php
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Redirection vers la page de connexion
header("Location: login.php?success=Vous avez été déconnecté avec succès");
exit();
?>