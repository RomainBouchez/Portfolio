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
        /* Styles additionnels spécifiques au tableau de bord */
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .dashboard-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .dashboard-header h1 {
            font-size: 2.5rem;
            background: linear-gradient(45deg, #fff, #666);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient 8s ease infinite;
            margin-bottom: 1rem;
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-section {
            background-color: rgba(26, 26, 26, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .dashboard-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }
        
        .dashboard-section h2 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 0.8rem;
            display: flex;
            align-items: center;
        }
        
        .dashboard-section h2 i {
            margin-right: 0.8rem;
            color: #007AFF;
        }
        
        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .appointment-card {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        
        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.2);
            background-color: rgba(255, 255, 255, 0.08);
        }
        
        .appointment-date {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007AFF;
            margin-bottom: 0.5rem;
        }
        
        .appointment-time {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1rem;
        }
        
        .appointment-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .appointment-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            border: none;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background-color: #007AFF;
            color: #fff;
        }
        
        .btn-primary:hover {
            background-color: #0066cc;
        }
        
        .btn-danger {
            background-color: #FF3B30;
            color: #fff;
        }
        
        .btn-danger:hover {
            background-color: #e6352b;
        }
        
        .no-appointments {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-style: italic;
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem;
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: #fff;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: rgba(0, 122, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.08);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .user-welcome {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #007AFF;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        /* Messages de succès et d'erreur - version simplifiée */
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
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
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
            background-color: rgba(40, 167, 69, 0.9);
            border-left-color: #1d8036;
            color: white;
        }
        
        .error-message {
            background-color: rgba(220, 53, 69, 0.9);
            border-left-color: #a71d2a;
            color: white;
        }
        
        .success-icon, .error-icon {
            margin-right: 1rem;
            font-size: 1.3rem;
        }
        
        .success-message .success-icon {
            color: #8cffaa;
        }
        
        .error-message .error-icon {
            color: #ffb3b8;
        }
        
        @media (max-width: 768px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
            
            .appointments-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-container {
                padding: 1rem;
            }
            
            .dashboard-section {
                padding: 1.5rem;
            }
        }
        
        /* Animation keyframes */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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