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
    <title>Tableau de bord - SystemRDV</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/project.css">
    <link rel="stylesheet" href="../css/registration-styles.css">
    <link rel="stylesheet" href="../css/calendar-style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="../js/calendar.js"></script>
    <style>
        /* Enhanced Dashboard Styles */

        /* Main Layout and Container Improvements */
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Dashboard Header Improvements */
        .dashboard-header {
            margin-bottom: 3rem;
            position: relative;
        }

        .dashboard-header h1 {
            font-size: 2.8rem;
            background: linear-gradient(to right, #007AFF, #5AC8FA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            background: rgba(0, 122, 255, 0.08);
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #007AFF;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #007AFF, #5856D6);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 5px 15px rgba(0, 122, 255, 0.3);
        }

        /* Dashboard Sections Improvements */
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        .dashboard-section {
            background: rgba(26, 26, 26, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        /* Border animation instead of movement */
        .dashboard-section {
            position: relative;
        }

        .dashboard-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #007AFF, #5AC8FA);
            opacity: 0.8;
        }

        .dashboard-section:hover::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 4px;
            background: linear-gradient(to right, #5AC8FA, #007AFF);
            opacity: 0.8;
            animation: borderExpand 0.5s forwards;
        }

        @keyframes borderExpand {
            0% {
                width: 0;
            }
            100% {
                width: 100%;
            }
        }

        .dashboard-section h2 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 1.8rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .dashboard-section h2 i {
            margin-right: 1.2rem; /* Increased space between icon and text */
            color: #007AFF;
            font-size: 1.6rem;
            background: rgba(0, 122, 255, 0.1);
            padding: 0.7rem;
            border-radius: 10px;
        }

        /* Form Elements Improvements */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.8rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1.2rem;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(0, 122, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }

        /* Button Improvements */
        .btn {
            padding: 0.9rem 1.8rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            line-height: 1.5;
            position: relative;
            overflow: hidden;
        }

        /* Using the .nav-links a hover effect */
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: left 0.4s ease-in-out;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn i {
            margin-right: 1.2rem; /* Increased space between icon and text */
            font-size: 1.1rem;
        }

        .btn-primary {
            background: linear-gradient(to right, #007AFF, #5AC8FA);
            color: #fff;
        }

        .btn-primary:hover {
            background: linear-gradient(to right, #0062CC, #4BA8F5);
        }

        .btn-danger {
            background: linear-gradient(to right, #FF3B30, #FF9500);
            color: #fff;
        }

        .btn-danger:hover {
            background: linear-gradient(to right, #E0352B, #E68600);
        }

        /* Appointments Grid and Cards Improvements */
        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .appointment-card {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .appointment-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, rgba(0, 122, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .appointment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(0, 122, 255, 0.3);
        }

        .appointment-card:hover::after {
            opacity: 1;
        }

        .appointment-date {
            font-size: 1.3rem;
            font-weight: 600;
            color: #007AFF;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .appointment-date::before {
            content: '\f133';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 1.2rem; /* Increased space between icon and text */
            font-size: 1rem;
            opacity: 0.8;
        }

        .appointment-time {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .appointment-time::before {
            content: '\f017';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 1.2rem; /* Increased space between icon and text */
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .appointment-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            border-left: 3px solid rgba(0, 122, 255, 0.5);
        }

        .appointment-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        /* No Appointments Message Improvements */
        .no-appointments {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            padding: 3rem 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            margin: 1.5rem 0;
        }

        .no-appointments i {
            font-size: 3rem;
            color: rgba(0, 122, 255, 0.5);
            margin-bottom: 1.5rem;
            display: block;
        }

        .no-appointments p {
            font-size: 1.1rem;
            margin-bottom: 0.7rem;
        }

        /* Alert Messages Improvements */
        .message {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            z-index: 1000;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            font-weight: 500;
            border-left: 5px solid;
            animation: messageAnimation 5s forwards;
        }

        @keyframes messageAnimation {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            10% {
                opacity: 1;
                transform: translateY(0);
            }
            90% {
                opacity: 1;
                transform: translateY(0);
            }
            100% {
                opacity: 0;
                transform: translateY(-20px);
            }
        }

        .success-message {
            background: rgba(40, 167, 69, 0.9);
            border-left-color: #1d8036;
            color: white;
            backdrop-filter: blur(10px);
        }

        .error-message {
            background: rgba(220, 53, 69, 0.9);
            border-left-color: #a71d2a;
            color: white;
            backdrop-filter: blur(10px);
        }

        .success-icon, .error-icon {
            margin-right: 1.2rem; /* Increased space between icon and text */
            font-size: 1.3rem;
        }

        /* Responsive Design Improvements */
        @media (max-width: 992px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
            
            .dashboard-container {
                padding: 1.5rem;
            }
            
            .dashboard-header h1 {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-section {
                padding: 1.8rem;
            }
            
            .appointments-grid {
                grid-template-columns: 1fr;
            }
            
            .user-welcome {
                flex-direction: column;
                text-align: center;
                padding: 1.2rem;
            }
            
            .user-avatar {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .message {
                left: 15px;
                right: 15px;
                width: auto;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .dashboard-header h1 {
                font-size: 1.8rem;
            }
            
            .btn {
                width: 100%;
            }
            
            .appointment-card {
                padding: 1.2rem;
            }
        }

        /* Animation for gradient backgrounds */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Custom scrollbar for better UX */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(0, 122, 255, 0.5);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 122, 255, 0.7);
        }
    </style>
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
        <div class="dashboard-container">
            <div class="dashboard-header">
                <div class="user-welcome">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
                </div>
                
                <?php
                // Affichage des messages - approche simple
                if (isset($_GET['success'])) {
                    echo '<div class="message success-message"><i class="fas fa-check-circle success-icon"></i><span>' . htmlspecialchars($_GET['success']) . '</span></div>';
                }
                if (isset($_GET['error'])) {
                    echo '<div class="message error-message"><i class="fas fa-exclamation-circle error-icon"></i><span>' . htmlspecialchars($_GET['error']) . '</span></div>';
                }
                ?>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <h2><i class="fas fa-calendar-plus"></i> Prendre un rendez-vous</h2>
                    <form action="process_appointment.php" method="post" id="appointmentForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label for="appointmentDisplay">Date et heure du rendez-vous</label>
                            <input type="text" class="form-control" id="appointmentDisplay" placeholder="Cliquez pour sélectionner" readonly required />
                            <input type="hidden" id="appointmentDateTime" name="appointmentDateTime" required />
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Décrivez l'objet de votre rendez-vous"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Prendre rendez-vous</button>
                    </form>
                </div>
                
                <div class="dashboard-section">
                    <h2><i class="fas fa-calendar-alt"></i> Mes rendez-vous</h2>
                    <?php if (count($appointments) > 0): ?>
                        <div class="appointments-grid">
                            <?php foreach($appointments as $appointment): ?>
                                <div class="appointment-card">
                                    <div class="appointment-date">
                                        <?php 
                                        $date = new DateTime($appointment['appointment_date']);
                                        echo $date->format('l j F Y'); 
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
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Annuler</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-appointments">
                            <p><i class="fas fa-info-circle"></i> Vous n'avez pas encore de rendez-vous.</p>
                            <p>Utilisez le formulaire ci-dessus pour prendre votre premier rendez-vous.</p>
                        </div>
                    <?php endif; ?>
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
        });
        });
    </script>
</body>
</html>