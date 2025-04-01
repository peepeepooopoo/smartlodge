<?php
session_start();
include 'db_connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        $error = "Error: Missing email or password.";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password, role, name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];
                
                header("Location: " . ($row['role'] == 'guest' ? 'index.php' : 'admin_dashboard.php'));
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "User not found. Please register.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartLodge</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poiret+One&display=swap');

        body {
            font-family: Arial, sans-serif;
            background: url("register1.jpg");
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            background-attachment: fixed;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.3));
            z-index: -1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            margin: 0;
        }

        .login-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-family: "Poiret One", sans-serif;
            font-size: 2.5rem;
        }

        form {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }

        input {
            padding: 12px;
            margin-top: 5px;
            border: 2px solid #ddd;
            border-radius: 8px;
            width: 100%;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #d9534f;
            outline: none;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #c9302c;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 1.1rem;
        }

        .register-link a {
            color: #d9534f;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Welcome Back</h1>
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['login_error'];
                unset($_SESSION['login_error']);
                ?>
            </div>
        <?php endif; ?>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</body>
</html>