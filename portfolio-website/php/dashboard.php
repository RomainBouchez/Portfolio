<?php
session_start();
require_once 'config.php';

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?error=Veuillez vous connecter pour accéder à cette page');
    exit();
}

// Génération d'un token CSRF pour les formulaires
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

// Récupération des rendez-vous de l'utilisateur
$stmt = $conn->prepare("SELECT id, appointment_date, appointment_time, description FROM appointments WHERE user_id = :userId ORDER BY appointment_date ASC, appointment_time ASC");
$stmt->bindParam(':userId', $_SESSION['user_id']);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des informations de l'utilisateur
$stmt = $conn->prepare("SELECT first_name, last_name, birth_date, address, phone_number, email FROM users WHERE id = :userId");
$stmt->bindParam(':userId', $_SESSION['user_id']);
$stmt->execute();
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/project.css">
    <link rel="stylesheet" href="../css/registration-styles.css">
    <link rel="stylesheet" href="../css/calendar-style.css">

    <script src="../js/calendar.js"></script>
</head>
<body>
    <div class="navbar">
        <div class="navbar-container">
            <a class="logo">SystemRDV</a>
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
        <div class="dashboard-container">
            <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
            
            <?php
            // Affichage des messages
            if (isset($_GET['success'])) {
                echo '<div class="success-message show"><i class="fas fa-check-circle success-icon"></i><span>' . htmlspecialchars($_GET['success']) . '</span></div>';
            }
            if (isset($_GET['error'])) {
                echo '<div class="error-message show"><i class="fas fa-exclamation-circle error-icon"></i><span>' . htmlspecialchars($_GET['error']) . '</span></div>';
            }
            ?>
            
            <div class="dashboard-section">
                <h2>Prendre un rendez-vous</h2>
                <form action="process_appointment.php" method="post" id="appointmentForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group">
                        <label for="appointmentDisplay">Date et heure du rendez-vous</label>
                        <input type="text" class="form-control" id="appointmentDisplay" placeholder="Cliquez pour sélectionner" readonly required />
                        <input type="hidden" id="appointmentDateTime" name="appointmentDateTime" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Prendre rendez-vous</button>
                </form>
            </div>
            
            <div class="dashboard-section">
                <h2>Mes rendez-vous</h2>
                <?php if (count($appointments) > 0): ?>
                    <div class="appointments-list">
                        <?php foreach($appointments as $appointment): ?>
                            <div class="appointment-card">
                                <div class="appointment-date">
                                    <?php 
                                    $date = new DateTime($appointment['appointment_date']);
                                    echo $date->format('d/m/Y'); 
                                    ?>
                                </div>
                                <div class="appointment-time">
                                    <?php 
                                    $time = new DateTime($appointment['appointment_time']);
                                    echo $time->format('H:i'); 
                                    ?>
                                </div>
                                <div class="appointment-description">
                                    <?php echo htmlspecialchars($appointment['description'] ?? 'Aucune description'); ?>
                                </div>
                                <div class="appointment-actions">
                                    <form action="delete_appointment.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Annuler</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Vous n'avez pas encore de rendez-vous.</p>
                <?php endif; ?>
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
            
            // Initialisation du calendrier
            const appointmentDateInput = document.getElementById('appointmentDateTime');
            const appointmentPicker = new Calendar(appointmentDateInput, {
                displayElementId: 'appointmentDisplay',
                minDate: new Date(),
                maxDate: new Date(new Date().setFullYear(new Date().getFullYear() + 1)),
                minTime: '09:00',
                maxTime: '17:00',
                timeSlotInterval: 30,
                onChange: function(datetime) {
                    if (datetime) {
                        const dateStr = datetime.toISOString().split('T')[0];
                        const timeStr = datetime.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
                        
                        // Suppression des inputs cachés existants
                        const existingInputs = document.querySelectorAll('input[name="appointmentDate"], input[name="appointmentTime"]');
                        existingInputs.forEach(input => input.remove());
                        
                        // Ajout des nouveaux inputs
                        const dateTimeInputs = `
                        <input type="hidden" name="appointmentDate" value="${dateStr}" />
                        <input type="hidden" name="appointmentTime" value="${timeStr}" />
                        `;
                        
                        appointmentDateInput.insertAdjacentHTML('afterend', dateTimeInputs);
                    }
                }
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