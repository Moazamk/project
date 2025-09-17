<?php
session_start();
require_once 'db_actions.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'register') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($username) || empty($password) || empty($confirm_password)) {
            $message = 'All fields are required.';
        } elseif ($password !== $confirm_password) {
            $message = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
        } else {
            if (registerUser($username, $password)) {
                $message = 'Registration successful. Please login.';
            } else {
                $message = 'Registration failed. Username may already exist.';
            }
        }
    } elseif ($action === 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            $message = 'Username and password are required.';
        } else {
            $user = loginUser($username, $password);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    setcookie('last_login', date('Y-m-d H:i:s'), time() + (86400 * 30), "/"); // 30 days
                    logActivity($user['username'], "User $username logged in");
                    header("Location: dashboard.php");
                    exit;
                } else {
                $message = 'Invalid username or password.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Management System - Login/Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">Student Management System</h1>

                        <?php if ($message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <ul class="nav nav-tabs" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="true">Login</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="false">Register</button>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="authTabsContent">
                            <div class="tab-pane fade show active" id="login" role="tabpanel" aria-labelledby="login-tab">
                                <form method="POST">
                                    <input type="hidden" name="action" value="login" />
                                    <div class="mb-3">
                                        <label for="login-username" class="form-label">Username</label>
                                        <input type="text" name="username" id="login-username" class="form-control" placeholder="Username" required />
                                    </div>
                                    <div class="mb-3">
                                        <label for="login-password" class="form-label">Password</label>
                                        <input type="password" name="password" id="login-password" class="form-control" placeholder="Password" required autocomplete="current-password" />
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Login</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
                                <form method="POST">
                                    <input type="hidden" name="action" value="register" />
                                    <div class="mb-3">
                                        <label for="register-username" class="form-label">Username</label>
                                        <input type="text" name="username" id="register-username" class="form-control" placeholder="Username" required />
                                    </div>
                                    <div class="mb-3">
                                        <label for="register-password" class="form-label">Password</label>
                                        <input type="password" name="password" id="register-password" class="form-control" placeholder="Password" required autocomplete="new-password" />
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm-password" class="form-label">Confirm Password</label>
                                        <input type="password" name="confirm_password" id="confirm-password" class="form-control" placeholder="Confirm Password" required autocomplete="new-password" />
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Register</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
