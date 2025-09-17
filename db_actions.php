<?php
require_once 'config.php';

function registerUser($username, $password) {
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, added_on) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $username, $hashed_password);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

function loginUser($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    } else {
        $stmt->close();
    }
    return false;
}

function addStudent($name, $age, $grade, $username) {
    global $conn;
    $name = htmlspecialchars($conn->real_escape_string($name));
    $age = intval($age);
    $grade = htmlspecialchars($conn->real_escape_string($grade));
    $stmt = $conn->prepare("INSERT INTO students (name, age, grade, added_on) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sis", $name, $age, $grade);
    $stmt->execute();
    $stmt->close();
    logActivity($username, "Added student: $name");
}

function updateStudent($id, $name, $age, $grade, $username) {
    global $conn;
    $id = intval($id);
    $name = htmlspecialchars($conn->real_escape_string($name));
    $age = intval($age);
    $grade = htmlspecialchars($conn->real_escape_string($grade));
    $stmt = $conn->prepare("UPDATE students SET name=?, age=?, grade=? WHERE id=?");
    $stmt->bind_param("sisi", $name, $age, $grade, $id);
    $stmt->execute();
    $stmt->close();
    logActivity($username, "Updated student ID $id: $name");
}

function deleteStudent($id, $username) {
    global $conn;
    $id = intval($id);
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    logActivity($username, "Deleted student ID $id");
}

function getStudents() {
    global $conn;
    $sql = "SELECT * FROM students ORDER BY id DESC";
    $result = $conn->query($sql);
    $students = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    return $students;
}

function logActivity($username, $message) {
    $logFile = __DIR__ . '/logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] User $username: $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

function getLastLogs($username, $count = 5) {
    $logFile = __DIR__ . '/logs.txt';
    $logs = [];
    if (!file_exists($logFile)) {
        return $logs;
    }
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // Filter lines for the given username
    $userLogs = array_filter($lines, function($line) use ($username) {
        return strpos($line, "User $username:") !== false;
    });
    // Get last $count logs
    $userLogs = array_slice($userLogs, -$count);
    // Reverse to show latest first
    $userLogs = array_reverse($userLogs);
    return $userLogs;
}

function exportStudentsToJson() {
    $students = getStudents();
    header('Content-Type: application/json');
    echo json_encode($students);
    exit;
}
?>
