<?php
// login.php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration and class
require_once '../config.php'; // Adjust path if config.php is elsewhere
require_once '../db.php';    // Adjust path if db.php is elsewhere

// Initialize error message variable
$error_message = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_username = trim($_POST['username'] ?? '');
    $submitted_password = $_POST['password'] ?? ''; // Raw password entered by user

    if (!empty($submitted_username) && !empty($submitted_password)) {
        try {
            $db = new Database(); // Create a new database connection instance

            // Prepare a statement to fetch admin data based on username
            $stmt = $db->getConnection()->prepare("SELECT id, username, password_hash FROM admin WHERE username = ?");
            $stmt->execute([$submitted_username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                // Hash the submitted password using SHA-256
                $hashed_submitted_password = hash('sha256', $submitted_password);

                // Compare the hashed submitted password with the stored hash
                if (hash_equals($admin['password_hash'], $hashed_submitted_password)) {
                    // Login successful
                    // Store relevant admin info in session (avoid storing password hash)
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];

                    // Redirect to the admin dashboard (adjust path as needed)
                    header('Location: index.php'); // Or wherever your dashboard is
                    exit; // Important: Stop execution after redirect
                } else {
                    // Invalid password
                    $error_message = 'Invalid username or password.';
                }
            } else {
                // Username not found
                $error_message = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            // Handle potential database errors
            error_log("Database error during login: " . $e->getMessage()); // Log error
            $error_message = 'An error occurred during login. Please try again later.'; // Generic message for user
        }
    } else {
        // Username or password not provided
        $error_message = 'Please enter both username and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        /* Basic styling for the login form */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Include padding/border in width */
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>