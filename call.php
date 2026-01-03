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

// Get call parameters from URL
$remote_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$call_type = isset($_GET['type']) ? $_GET['type'] : 'video'; // 'audio' or 'video'
$is_initiator = isset($_GET['initiator']) ? $_GET['initiator'] == 'true' : false;

if ($remote_user_id == 0) {
    header('Location: dashboard.php');
    exit();
}

// Get remote user details
$stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $remote_user_id);
$stmt->execute();
$result = $stmt->get_result();
$remote_user = $result->fetch_assoc();

if (!$remote_user) {
    header('Location: dashboard.php');
    exit();
}

// Update current user status to on_call
$update_stmt = $conn->prepare("UPDATE users SET status = 'on_call' WHERE id = ?");
$update_stmt->bind_param("i", $current_user['id']);
$update_stmt->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $call_type == 'video' ? 'Video' : 'Voice'; ?> Call - Wartalaap</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="call-body">
    <div class="call-container">
        <!-- Remote Video (other person) -->
        <div id="remoteVideoContainer" class="video-container remote-video">
            <video id="remoteVideo" autoplay playsinline></video>
            <div class="video-info">
                <img src="uploads/<?php echo !empty($remote_user['profile_picture']) ? $remote_user['profile_picture'] : 'default-avatar.png'; ?>" alt="Avatar" class="call-avatar">
                <h3><?php echo htmlspecialchars($remote_user['username']); ?></h3>
                <p id="callStatus">Connecting...</p>
            </div>
        </div>

        <!-- Local Video (you) -->
        <div id="localVideoContainer" class="video-container local-video">
            <video id="localVideo" autoplay muted playsinline></video>
            <p>You</p>
        </div>

        <!-- Call Controls -->
        <div class="call-controls">
            <button id="toggleAudioBtn" class="control-btn" onclick="toggleAudio()" title="Mute/Unmute">
                <span id="audioIcon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                        <line x1="12" y1="19" x2="12" y2="23"></line>
                        <line x1="8" y1="23" x2="16" y2="23"></line>
                    </svg>
                </span>
            </button>
            
            <?php if ($call_type == 'video'): ?>
            <button id="toggleVideoBtn" class="control-btn" onclick="toggleVideo()" title="Video On/Off">
                <span id="videoIcon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 7l-7 5 7 5V7z"></path>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                </span>
            </button>
            <?php endif; ?>
            
            <button class="control-btn end-call-btn" onclick="endCall()" title="End Call">
                <span>
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 9c-1.6 0-3.15.25-4.6.72v3.1c0 .39-.23.74-.56.9-.98.49-1.87 1.12-2.66 1.85-.18.18-.43.28-.7.28-.28 0-.53-.11-.71-.29L.29 13.08c-.18-.17-.29-.42-.29-.7 0-.28.11-.53.29-.71C3.34 8.78 7.46 7 12 7s8.66 1.78 11.71 4.67c.18.18.29.43.29.71 0 .28-.11.53-.29.71l-2.48 2.48c-.18.18-.43.29-.71.29-.27 0-.52-.11-.7-.28-.79-.74-1.68-1.36-2.66-1.85-.33-.16-.56-.5-.56-.9v-3.1C15.15 9.25 13.6 9 12 9z"/>
                    </svg>
                </span>
            </button>
        </div>
    </div>

    <script>
        const currentUserId = <?php echo $current_user['id']; ?>;
        const remoteUserId = <?php echo $remote_user_id; ?>;
        const callType = '<?php echo $call_type; ?>';
        const isInitiator = <?php echo $is_initiator ? 'true' : 'false'; ?>;
    </script>
    <script src="js/webrtc.js?v=<?php echo time(); ?>"></script>
</body>
</html>