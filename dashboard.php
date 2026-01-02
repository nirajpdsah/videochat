<?php
require_once 'config.php';

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$current_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VideoChat</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="user-info">
                <img src="uploads/<?php echo $current_user['profile_picture']; ?>" alt="Profile" class="profile-pic-small">
                <span><?php echo htmlspecialchars($current_user['username']); ?></span>
            </div>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </header>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Users List -->
            <div class="users-panel">
                <h3>Users</h3>
                <div id="usersList" class="users-list">
                    <!-- Users will be loaded here via JavaScript -->
                    <div class="loading">Loading users...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call Modal -->
    <div id="callModal" class="modal">
        <div class="modal-content">
            <h3 id="callModalTitle">Incoming Call</h3>
            <img id="callModalAvatar" src="" alt="Avatar" class="profile-pic-large">
            <p id="callModalName"></p>
            <div class="call-buttons">
                <button onclick="acceptCall()" class="btn btn-success">Accept</button>
                <button onclick="rejectCall()" class="btn btn-danger">Reject</button>
            </div>
        </div>
    </div>

    <!-- Calling Modal -->
    <div id="callingModal" class="modal">
        <div class="modal-content">
            <h3>Calling...</h3>
            <img id="callingModalAvatar" src="" alt="Avatar" class="profile-pic-large">
            <p id="callingModalName"></p>
            <p id="callingModalStatus" style="color: #95a5a6; font-size: 0.9em; margin-top: 10px;"></p>
            <button onclick="cancelCall()" class="btn btn-danger">Cancel</button>
        </div>
    </div>

    <!-- User Busy Modal -->
    <div id="busyModal" class="modal">
        <div class="modal-content">
            <h3>User Busy</h3>
            <p>This user is currently on another call</p>
            <button onclick="closeBusyModal()" class="btn btn-primary">OK</button>
        </div>
    </div>

    <script>
        const currentUserId = <?php echo $current_user['id']; ?>;
        let selectedUserId = null;
        let selectedUsername = null;
    </script>
    <script src="js/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>