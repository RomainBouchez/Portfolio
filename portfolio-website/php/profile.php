<?php
session_start();
require_once 'config.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Veuillez vous connecter pour accéder à cette page');
    exit();
}

// Génération d'un token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Connexion à la base de données
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    exit('Erreur de connexion: ' . $e->getMessage());
}

// Récupération des informations de l'utilisateur
$stmt = $conn->prepare("SELECT first_name, last_name, birth_date, address, phone_number, email FROM users WHERE id = :userId");
$stmt->bindParam(':userId', $_SESSION['user_id']);
$stmt->execute();
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/project.css">
    <link rel="stylesheet" href="../css/registration-styles.css">
    <link rel="stylesheet" href="../css/form-profile.css">
</head>


<body>
    <div class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="logo">SystemRDV</a>
            <button class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="dashboard.php">Tableau de bord</a>
                <a href="profile.php">Mon profil</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <?php
            // Affichage des messages
            if (isset($_GET['success'])) {
                echo '<div class="message success-message"><i class="fas fa-check-circle success-icon"></i><span>' . htmlspecialchars($_GET['success']) . '</span></div>';
            }
            if (isset($_GET['error'])) {
                echo '<div class="message error-message"><i class="fas fa-exclamation-circle error-icon"></i><span>' . htmlspecialchars($_GET['error']) . '</span></div>';
            }
        ?>
        <div class="registration-container">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <h2>Mon Profil</h2>
                    
                    
                    
                    <form action="update_profile.php" method="post" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label for="firstName">Prénom</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userInfo['first_name']); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName">Nom</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userInfo['last_name']); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="birthDate">Date de naissance</label>
                            <input type="date" class="form-control" id="birthDate" name="birthDate" value="<?php echo htmlspecialchars($userInfo['birth_date']); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Adresse postale</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($userInfo['address']); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="phoneNumber">Numéro de téléphone</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($userInfo['phone_number']); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userInfo['email']); ?>" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Nouveau mot de passe (laisser vide pour ne pas modifier)</label>
                            <input type="password" class="form-control" id="password" name="password" />
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" />
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </form>
                    
                    <hr>
                    
                    <h3>Supprimer mon compte</h3>
                    <p>Attention : Cette action est irréversible et supprimera toutes vos données, y compris vos rendez-vous.</p>
                    <form action="delete_account.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Système de Réservation. Tous droits réservés.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menu hamburger
            const hamburger = document.getElementById('hamburger');
            const navLinks = document.getElementById('navLinks');
            
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                navLinks.classList.toggle('active');
            });
            
            // Validation du formulaire
            document.getElementById('profileForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (password !== '' && password !== confirmPassword) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                }
            });
            
            // Animation pour faire disparaître les messages après quelques secondes
            setTimeout(function() {
                document.querySelectorAll('.success-message, .error-message').forEach(function(el) {
                    el.classList.remove('show');
                });
            }, 5000);
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide messages after 5 seconds (as a fallback if CSS animation doesn't work)
            setTimeout(function() {
                const messages = document.querySelectorAll('.message');
                messages.forEach(function(message) {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-20px)';
                    
                    // Remove from DOM after fade out animation completes
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                });
            }, 5000);
        });
    </script>
</body>
</html>