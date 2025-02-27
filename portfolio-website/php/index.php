<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Système de Réservation</title>
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/project.css">
        <link rel="stylesheet" href="../css/registration-styles.css">
        <link rel="stylesheet" href="../css/home-styles.css">
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../index.html">Portfolio</a>
                    <a href="dashboard.php">Tableau de bord</a>
                    <a href="profile.php">Mon profil</a>
                    <a href="logout.php">Déconnexion</a>
                <?php else: ?>
                    <a href="../index.html">Portfolio</a>
                    <a href="index.php">Accueil</a>
                    <a href="login.php">Connexion</a>
                    <a href="register.php">Inscription</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="hero-section">
            <div class="hero-content">
                <h1>Prenez rendez-vous en quelques clics</h1>
                <p>Notre système de réservation en ligne vous permet de gérer vos rendez-vous facilement et efficacement.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn btn-primary">S'inscrire</a>
                        <a href="login.php" class="btn btn-secondary">Se connecter</a>
                    </div>
                <?php else: ?>
                    <div class="hero-buttons">
                        <a href="dashboard.php" class="btn btn-primary">Accéder à mon espace</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="hero-image">
                <!-- Image d'illustration (optionnelle) -->
            </div>
        </div>

        <div class="features-section">
            <h2>Nos fonctionnalités</h2>
            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Réservation en ligne</h3>
                    <p>Réservez vos créneaux horaires en quelques clics depuis votre ordinateur ou votre téléphone.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Gestion de profil</h3>
                    <p>Créez votre compte et gérez vos informations personnelles en toute sécurité.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Disponibilité en temps réel</h3>
                    <p>Consultez les créneaux disponibles et réservez immédiatement.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>Historique des rendez-vous</h3>
                    <p>Consultez et gérez l'ensemble de vos rendez-vous passés et à venir.</p>
                </div>
            </div>
        </div>

        <?php
        // Affichage des messages de succès ou d'erreur
        if (isset($_GET['success'])) {
            echo '<div class="success-message show"><i class="fas fa-check-circle success-icon"></i><span>' . htmlspecialchars($_GET['success']) . '</span></div>';
        }
        if (isset($_GET['error'])) {
            echo '<div class="error-message show"><i class="fas fa-exclamation-circle error-icon"></i><span>' . htmlspecialchars($_GET['error']) . '</span></div>';
        }
        ?>
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
            
            // Animation pour faire disparaître les messages après quelques secondes
            setTimeout(function() {
                document.querySelectorAll('.success-message, .error-message').forEach(function(el) {
                    el.classList.remove('show');
                });
            }, 5000);
        });
    </script>
</body>
</html>