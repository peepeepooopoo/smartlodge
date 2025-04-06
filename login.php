<?php
session_start();
include 'db_connection.php';

// Check login status based on the role
$admin_logged_in = isset($_SESSION['admin_id']);
$guest_logged_in = isset($_SESSION['guest_user_id']);

// Redirect based on role if already logged in
if ($admin_logged_in && $_GET['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
} elseif ($guest_logged_in && $_GET['role'] === 'guest') {
    header("Location: index.php");
    exit();
}

$error = '';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartLodge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poiret+One&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Open Sans', sans-serif;
        }

        body {
            background-image: url('register1.jpg');
            background-size: 200% 200%;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.5) 100%);
            z-index: 1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            position: relative;
            z-index: 2;
        }

        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s ease;
        }

        .tab.active {
            color: #d9534f;
            border-bottom: 2px solid #d9534f;
            margin-bottom: -2px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-family: 'Poiret One', cursive;
            font-size: 2.5rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            border-color: #d9534f;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #c9302c;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #d9534f;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
        
        .status-indicator {
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 500;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>SmartLodge</h1>
        
        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['login_error'];
                unset($_SESSION['login_error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if ($admin_logged_in): ?>
            <div class="status-indicator status-active">
                Admin is currently logged in (<?php echo htmlspecialchars($_SESSION['admin_name']); ?>)
            </div>
        <?php endif; ?>
        
        <?php if ($guest_logged_in): ?>
            <div class="status-indicator status-active">
                Guest is currently logged in (<?php echo htmlspecialchars($_SESSION['guest_name']); ?>)
            </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab <?php echo (!isset($_GET['role']) || $_GET['role'] === 'guest') ? 'active' : ''; ?>" data-tab="guest">Guest Login</div>
            <div class="tab <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'active' : ''; ?>" data-tab="admin">Admin Login</div>
        </div>

        <div class="tab-content <?php echo (!isset($_GET['role']) || $_GET['role'] === 'guest') ? 'active' : ''; ?>" id="guest-login">
            <form action="login_process.php" method="POST">
                <input type="hidden" name="role" value="guest">
                <div class="form-group">
                    <label for="guest-email">Email</label>
                    <input type="email" id="guest-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="guest-password">Password</label>
                    <input type="password" id="guest-password" name="password" required>
                </div>
                <button type="submit">Login as Guest</button>
            </form>
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>

        <div class="tab-content <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'active' : ''; ?>" id="admin-login">
            <form action="login_process.php" method="POST">
                <input type="hidden" name="role" value="admin">
                <div class="form-group">
                    <label for="admin-email">Email</label>
                    <input type="email" id="admin-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="admin-password">Password</label>
                    <input type="password" id="admin-password" name="password" required>
                </div>
                <button type="submit">Login as Admin</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));

                    // Add active class to clicked tab and corresponding content
                    tab.classList.add('active');
                    document.getElementById(`${tab.dataset.tab}-login`).classList.add('active');
                    
                    // Update URL parameter without reloading the page
                    const url = new URL(window.location);
                    url.searchParams.set('role', tab.dataset.tab);
                    window.history.pushState({}, '', url);
                });
            });
        });
    </script>
</body>
</html>