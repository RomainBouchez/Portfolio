<?php
header('Content-Type: application/json');

// Make sure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'portfolio');
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => "Connection Failed: " . $conn->connect_error]);
    exit;
}

// Check which action is requested
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'login':
        handleLogin($conn);
        break;
    case 'delete':
        handleDelete($conn);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}

function handleLogin($conn) {
    // Get user credentials
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        return;
    }
    
    // Prepare SQL statement to prevent SQL injection - Now targeting appointments table
    $stmt = $conn->prepare("SELECT a.id, u.first_name, u.last_name, u.email, u.password_hash, u.phone_number, a.appointment_date, a.appointment_time 
                           FROM users u 
                           JOIN appointments a ON u.id = a.user_id 
                           WHERE u.email = ?");
    
    $stmt->bind_param('s', $email);
    
    // Execute the statement
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        $stmt->close();
        return;
    }
    
    // Get the result
    $result = $stmt->get_result();
    
    // Check if user exists and has appointments
    if ($result->num_rows === 0) {
        // Try to find user without appointments
        $stmt->close();
        $userStmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, phone_number FROM users WHERE email = ?");
        $userStmt->bind_param('s', $email);
        
        if (!$userStmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $userStmt->error]);
            $userStmt->close();
            return;
        }
        
        $userResult = $userStmt->get_result();
        
        if ($userResult->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'User not found. Please check your email or register a new account.']);
            $userStmt->close();
            return;
        }
        
        // User exists but has no appointments
        $user = $userResult->fetch_assoc();
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password. Please try again.']);
            $userStmt->close();
            return;
        }
        
        // Return success with user data but no appointments
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful, but you don\'t have any appointments yet.',
            'data' => [
                'id' => $user['id'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'email' => $user['email'],
                'phone_number' => $user['phone_number'],
                'has_appointments' => false
            ]
        ]);
        
        $userStmt->close();
        return;
    }
    
    // Get user data - with appointments
    $appointments = [];
    $userData = null;
    
    while ($row = $result->fetch_assoc()) {
        if ($userData === null) {
            $userData = [
                'id' => $row['id'],
                'first_name' => $row['first_name'],
                'last_name' => $row['last_name'],
                'email' => $row['email'],
                'phone_number' => $row['phone_number'],
                'password_hash' => $row['password_hash']
            ];
        }
        
        // Format the date for display
        $appointmentDate = new DateTime($row['appointment_date']);
        $formattedDate = $appointmentDate->format('l, F j, Y');
        
        // Format the time for display
        $appointmentTime = new DateTime($row['appointment_time']);
        $formattedTime = $appointmentTime->format('g:i A');
        
        $appointments[] = [
            'id' => $row['id'],
            'date' => $formattedDate,
            'time' => $formattedTime,
            'raw_date' => $row['appointment_date'],
            'raw_time' => $row['appointment_time']
        ];
    }
    
    // Verify password
    if (!password_verify($password, $userData['password_hash'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password. Please try again.']);
        $stmt->close();
        return;
    }
    
    // Return success with user data and appointments
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'data' => [
            'id' => $userData['id'],
            'first_name' => $userData['first_name'],
            'last_name' => $userData['last_name'],
            'email' => $userData['email'],
            'phone_number' => $userData['phone_number'],
            'has_appointments' => true,
            'appointments' => $appointments
        ]
    ]);
    
    $stmt->close();
}

function handleDelete($conn) {
    // Get appointment ID
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid appointment ID']);
        return;
    }
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    // Execute the statement
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        $stmt->close();
        return;
    }
    
    // Check if any row was affected
    if ($stmt->affected_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found or already deleted']);
        $stmt->close();
        return;
    }
    
    // Return success
    echo json_encode(['status' => 'success', 'message' => 'Appointment deleted successfully']);
    
    $stmt->close();
}

// Close the database connection
$conn->close();
?>