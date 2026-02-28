<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../db.php'; // Assuming db.php is in parent directory
require_once '../config.php'; // Assuming config.php is in parent directory

$db = new Database();

// Fetch all distinct teams
$stmt = $db->getConnection()->query("SELECT DISTINCT team FROM registrations ORDER BY team ASC");
$teams = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get team to display (from GET parameter or default to first team)
$selectedTeam = isset($_GET['team']) ? $_GET['team'] : ($teams[0] ?? null);

// Fetch registrations for the selected team
$registrations = [];
if ($selectedTeam) {
    $stmt = $db->getConnection()->prepare("
        SELECT id, name, roll_no, department, email, team, created_at 
        FROM registrations 
        WHERE team = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$selectedTeam]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle verification toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_verification'])) {
    $regId = (int)$_POST['reg_id'];
    $currentStatus = (bool)$_POST['current_status'];
    
    $updateStmt = $db->getConnection()->prepare("
        INSERT INTO verification_status (registration_id, verified) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE verified = ?
    ");
    $updateStmt->execute([$regId, !$currentStatus, !$currentStatus]);
    
    // Redirect back to avoid re-submission on refresh
    $redirectUrl = $_SERVER['PHP_SELF'] . '?team=' . urlencode($selectedTeam);
    header("Location: $redirectUrl");
    exit;
}

// Function to get verification status for a registration
function isVerified($regId, $db) {
    $stmt = $db->getConnection()->prepare("
        SELECT verified 
        FROM verification_status 
        WHERE registration_id = ?
    ");
    $stmt->execute([$regId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['verified'] : false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SGC Workshop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <button class="close-btn" id="closeSidebar">&times;</button>
            </div>
            <ul class="nav-list">
                <li><a href="index.php">Dashboard Home</a></li>
                <?php foreach ($teams as $team): ?>
                    <li>
                        <a href="?team=<?= urlencode($team) ?>" 
                           class="<?= $selectedTeam === $team ? 'active' : '' ?>">
                            <?= htmlspecialchars($team) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation Bar -->
            <header class="top-nav">
                <button class="menu-toggle" id="openSidebar">&#9776;</button>
                <h1>Registration Verification Dashboard</h1>
                <div class="current-team-display">
                    Viewing Team: <strong><?= $selectedTeam ? htmlspecialchars($selectedTeam) : 'None' ?></strong>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="team-info">
                    <h2>Team: <?= $selectedTeam ? htmlspecialchars($selectedTeam) : 'Select a Team' ?></h2>
                    <p>Total Registrations: <?= count($registrations) ?></p>
                </div>

                <?php if ($selectedTeam): ?>
                    <div class="registrations-table-container">
                        <table class="registrations-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Roll No</th>
                                    <th>Department</th>
                                    <th>Email</th>
                                    <th>Registered At</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($registrations)): ?>
                                    <?php foreach ($registrations as $reg): ?>
                                        <?php $isVerified = isVerified($reg['id'], $db); ?>
                                        <tr class="<?= $isVerified ? 'verified-row' : 'pending-row' ?>">
                                            <td><?= htmlspecialchars($reg['id']) ?></td>
                                            <td><?= htmlspecialchars($reg['name']) ?></td>
                                            <td><?= htmlspecialchars($reg['roll_no']) ?></td>
                                            <td><?= htmlspecialchars($reg['department']) ?></td>
                                            <td><?= htmlspecialchars($reg['email']) ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($reg['created_at'])) ?></td>
                                            <td>
                                                <span class="status-badge <?= $isVerified ? 'verified' : 'pending' ?>">
                                                    <?= $isVerified ? 'Verified' : 'Pending' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to change verification status for <?= addslashes(htmlspecialchars($reg['name'])) ?>?');">
                                                    <input type="hidden" name="reg_id" value="<?= $reg['id'] ?>">
                                                    <input type="hidden" name="current_status" value="<?= $isVerified ? '1' : '0' ?>">
                                                    <input type="hidden" name="toggle_verification" value="1">
                                                    <button type="submit" class="verify-btn <?= $isVerified ? 'unverify' : 'verify' ?>">
                                                        <?= $isVerified ? 'Unverify' : 'Verify' ?>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8">No registrations found for this team.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-team-selected">
                        <p>Please select a team from the sidebar to view registrations.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>