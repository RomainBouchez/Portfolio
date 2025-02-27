<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/project.css">
    <link rel="stylesheet" href="../css/registration-styles.css">
    <link rel="stylesheet" href="../css/form-login.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="logo">SystemRDV</a>
            <button class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="index.php">Accueil</a>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <?php
            session_start();
            // Génération d'un token CSRF
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // Affichage des messages d'erreur ou de succès
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
                    <h2>Connexion</h2>
                    
                    

                    <form action="process_login.php" method="post" id="loginForm">
                        <!-- Token CSRF pour la sécurité -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required />
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                    </form>

                    <div class="form-group">
                        <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                    </div>
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