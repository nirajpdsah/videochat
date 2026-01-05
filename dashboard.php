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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard - Wartalaap</title>
    <link rel="icon" type="image/png" href="uploads/logo.png">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <script src="js/cache-buster.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="uploads/logo.png" alt="Wartalaap">
                <h1><span class="hindi-stylized">वार्ता</span>Laap</h1>
            </div>
            <div class="user-info">
                <img src="uploads/<?php echo !empty($current_user['profile_picture']) ? $current_user['profile_picture'] : 'default-avatar.png'; ?>" alt="Profile" class="profile-pic-small">
                <h3><?php echo htmlspecialchars($current_user['username']); ?></h3>
            </div>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </header>

        <!-- Main Content -->
        <div class="dashboard-content">
            <!-- Users List -->
            <div class="users-panel">
                <h3>Start a Conversation</h3>
                <div id="usersList" class="users-list">
                    <!-- Users will be loaded here via JavaScript -->
                    <div class="loading">Loading users...</div>
                </div>
            </div>
            
            <!-- Right Side: User Profile / Call Actions -->
            <div class="chat-area" id="chatArea">
                <div class="empty-state">
                    <img src="uploads/logo.png" alt="Wartalaap" style="opacity: 0.5;">
                    <h2>Wartalaap Web</h2>
                    <p>Select a contact to view profile and start a call.</p>
                </div>
                
                <!-- Active User View (Hidden by default) -->
                <div class="user-profile-view" id="userProfileView" style="display: none;">
                    <img id="selectedUserAvatar" src="" alt="Profile" class="profile-pic-jumbo">
                    <h2 id="selectedUserName"></h2>
                    <p id="selectedUserStatus" class="status-badge"></p>
                    
                    <div class="profile-actions">
                        <button onclick="startVideoCall()" class="action-card video">
                            <div class="icon-circle">
                                <svg viewBox="0 0 24 24" fill="none" class="feather"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            </div>
                            <span>Video Call</span>
                        </button>
                        
                        <button onclick="startAudioCall()" class="action-card audio">
                            <div class="icon-circle">
                                <svg viewBox="0 0 24 24" fill="none" class="feather"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <span>Audio Call</span>
                        </button>
                    </div>
                    

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

    <!-- Audio Elements for Call Sounds -->
    <audio id="incomingSound" src="sounds/ringtone.mp3" loop preload="auto"></audio>
    <audio id="outgoingSound" src="sounds/ringback.mp3" loop preload="auto"></audio>

    <script>
        const currentUserId = <?php echo $current_user['id']; ?>;
        let selectedUserId = null;
        let selectedUsername = null;
    </script>
    <script src="js/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>