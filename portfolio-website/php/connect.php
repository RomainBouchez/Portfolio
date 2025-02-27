<?php
header('Content-Type: application/json');

$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$gender = $_POST['gender'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$number = $_POST['number'];
$appointmentDate = $_POST['appointmentDate'];
$appointmentTime = $_POST['appointmentTime'];

// Database connection
$conn = new mysqli('localhost','root','','portfolio');
if($conn->connect_error){
    echo json_encode(['status' => 'error', 'message' => "Connection Failed: " . $conn->connect_error]);
    die();
}

// Check if email exists
$checkEmail = $conn->prepare("SELECT email, password_hash, appointment_date, appointment_time FROM form WHERE email = ?");
$checkEmail->bind_param("s", $email);
$checkEmail->execute();
$result = $checkEmail->get_result();

if($result->num_rows > 0) {
    // Email exists, check password
    $userData = $result->fetch_assoc();
    if($userData['password_hash'] !== $password) {
        echo json_encode([
            'status' => 'error',
            'message' => 'This email is already registered with a different password. Please use the correct password.'
        ]);
        $checkEmail->close();
        $conn->close();
        exit();
    }
    
    // Password matches, update the appointment
    $oldDate = $userData['appointment_date'];
    $oldTime = $userData['appointment_time'];
    
    $updateStmt = $conn->prepare("UPDATE form SET first_name=?, last_name=?, gender=?, phone_number=?, appointment_date=?, appointment_time=? WHERE email=?");
    $updateStmt->bind_param("sssisss", $firstName, $lastName, $gender, $number, $appointmentDate, $appointmentTime, $email);
    
    if($updateStmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => "Appointment updated successfully!",
            'isUpdate' => true,
            'oldDate' => $oldDate,
            'oldTime' => $oldTime,
            'newDate' => $appointmentDate,
            'newTime' => $appointmentTime
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error updating appointment: ' . $updateStmt->error]);
    }
    $updateStmt->close();
} else {
    // New registration
    $stmt = $conn->prepare("INSERT INTO form(first_name, last_name, gender, email, password_hash, phone_number, appointment_date, appointment_time) VALUES(?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiss", $firstName, $lastName, $gender, $email, $password, $number, $appointmentDate, $appointmentTime);
    
    if($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful!',
            'isUpdate' => false
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error during registration: ' . $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>