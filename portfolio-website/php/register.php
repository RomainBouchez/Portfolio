<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
        <link rel="stylesheet" href="../css/style.css">
        <link rel="stylesheet" href="../css/project.css">
        <link rel="stylesheet" href="../css/registration-styles.css">
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
                <a href="index.php">Accueil</a>
                <a href="login.php">Connexion</a>
                <a href="register.php">Inscription</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="registration-container">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <h2>Créer un compte</h2>
                    
                    <?php
                    // Affichage des messages d'erreur ou de succès
                    if (isset($_GET['error'])) {
                        echo '<div class="error-message show"><i class="fas fa-exclamation-circle error-icon"></i><span>' . htmlspecialchars($_GET['error']) . '</span></div>';
                    }
                    if (isset($_GET['success'])) {
                        echo '<div class="success-message show"><i class="fas fa-check-circle success-icon"></i><span>' . htmlspecialchars($_GET['success']) . '</span></div>';
                    }
                    ?>

                    <form action="process_register.php" method="post" id="registrationForm">
                        <!-- Ajout d'un token CSRF pour sécuriser le formulaire -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        
                        <div class="form-group">
                            <label for="firstName">Prénom</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName">Nom</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="birthDate">Date de naissance</label>
                            <input type="date" class="form-control" id="birthDate" name="birthDate" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Adresse postale</label>
                            <input type="text" class="form-control" id="address" name="address" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="phoneNumber">Numéro de téléphone</label>
                            <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmPassword">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required />
                        </div>
                        
                        <button type="submit" class="btn btn-primary">S'inscrire</button>
                    </form>

                    <div class="form-group">
                        <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
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
        // Script pour le menu hamburger mobile
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburger');
            const navLinks = document.getElementById('navLinks');
            
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                navLinks.classList.toggle('active');
            });
        });

        // Validation du formulaire côté client
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
            }
        });
    </script>
</body>
</html>