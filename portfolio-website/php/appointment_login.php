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
    
    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, phone_number, appointment_date, appointment_time FROM form WHERE email = ?");
    $stmt->bind_param('s', $email);
    
    // Execute the statement
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
        $stmt->close();
        return;
    }
    
    // Get the result
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User not found. Please check your email or register a new appointment.']);
        $stmt->close();
        return;
    }
    
    // Get user data
    $user = $result->fetch_assoc();
    
    // Verify password
    // Note: In your connect.php, you're storing the hashed password directly without verification
    // For login, we should verify the password using password_verify
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password. Please try again.']);
        $stmt->close();
        return;
    }
    
    // Format the date for display
    $appointmentDate = new DateTime($user['appointment_date']);
    $formattedDate = $appointmentDate->format('l, F j, Y');
    
    // Format the time for display (assuming it's stored in 24h format)
    $appointmentTime = new DateTime($user['appointment_time']);
    $formattedTime = $appointmentTime->format('g:i A');
    
    // Return success with user data
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'data' => [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone_number' => $user['phone_number'],
            'appointment_date' => $formattedDate,
            'appointment_time' => $formattedTime
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
    $stmt = $conn->prepare("DELETE FROM form WHERE id = ?");
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