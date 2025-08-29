<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $identity = $input['identity'] ?? '';
    $password = $input['password'] ?? '';

    if (strpos($identity, '@') !== false && !filter_var($identity, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']);
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identity, $identity);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo json_encode(['success' => true, 'message' => 'Login berhasil!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Password salah.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Username atau email tidak ditemukan.']);
    }
    
    $stmt->close();
    $conn->close();
}
?>