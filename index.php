<?php
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
require_once 'config.php';

$message = '';
$team = '';
$result_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($roll_no) || empty($department) || empty($email)) {
        $response = [
            'success' => false,
            'message' => 'All fields are required!'
        ];
        echo json_encode($response);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'success' => false,
            'message' => 'Invalid email format!'
        ];
        echo json_encode($response);
        exit;
    }
    
    $db = new Database();
    
    // Check if roll number already exists
    $existing_user = $db->checkRollNoExists($roll_no);
    
    if ($existing_user) {
        // User already registered
        $response = [
            'success' => true,
            'type' => 'existing',
            'message' => "You are already registered under Team {$existing_user['team']}",
            'team' => $existing_user['team']
        ];
        echo json_encode($response);
        exit;
    } else {
        // New registration
        // Get total count to determine team assignment
        $total_registrations = $db->getTotalRegistrations();
        
        // Determine team based on modulo operation
        global $teams;
        $team_index = $total_registrations % 8;
        $assigned_team = $teams[$team_index];
        
        // Insert new registration
        $insert_success = $db->registerParticipant($name, $roll_no, $department, $email, $assigned_team);
        
        if ($insert_success) {
            $response = [
                'success' => true,
                'type' => 'new',
                'message' => "Registration successful! You are assigned to Team {$assigned_team}",
                'team' => $assigned_team
            ];
            echo json_encode($response);
            exit;
        } else {
            $response = [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
            echo json_encode($response);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGC Workshop Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>SGC Workshop</h1>
                <p>One Day College Event Registration</p>
            </div>
            
            <form id="registrationForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="roll_no">Roll Number</label>
                    <input type="text" id="roll_no" name="roll_no" placeholder="Enter your roll number" required>
                </div>
                
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department" required>
                        <option value="">Select your department</option>
                        <option value="AIDS">AIDS - Artificial Intelligence and Data Science</option>
                        <option value="AIML">AIML - Artificial Intelligence and Machine Learning</option>
                        <option value="CIVIL">CIVIL - Civil Engineering</option>
                        <option value="CSE">CSE - Computer Science and Engineering</option>
                        <option value="ECE">ECE - Electronics and Communication Engineering</option>
                        <option value="EEE">EEE - Electrical and Electronics Engineering</option>
                        <option value="IT">IT - Information Technology</option>
                        <option value="MECH">MECH - Mechanical Engineering</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">Register Now</button>
            </form>
            
            <div id="loading" class="loading">
                <div class="spinner"></div>
                <p>Processing your registration...</p>
            </div>
            
            <div id="resultContainer" class="result-container">
                <p id="message"></p>
                <div class="team-name" id="teamName"></div>
                <img id="teamImage" class="team-image" src="" alt="">
            </div>
            
            <div class="footer">
                <p>©2026 Student Guidance Cell. All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <!-- Confetti container -->
    <div class="confetti-container" id="confettiContainer"></div>
    
    <script src="script.js"></script>
</body>
</html>