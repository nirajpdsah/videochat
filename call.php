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
    <title><?php echo $call_type == 'video' ? 'Video' : 'Audio'; ?> Call - VideoChat</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body class="call-body">
    <div class="call-container">
        <!-- Remote Video (other person) -->
        <div id="remoteVideoContainer" class="video-container remote-video">
            <video id="remoteVideo" autoplay playsinline></video>
            <div class="video-info">
                <img src="uploads/<?php echo $remote_user['profile_picture']; ?>" alt="Avatar" class="call-avatar">
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
                <span id="audioIcon">🎤</span>
            </button>
            
            <?php if ($call_type == 'video'): ?>
            <button id="toggleVideoBtn" class="control-btn" onclick="toggleVideo()" title="Video On/Off">
                <span id="videoIcon">📹</span>
            </button>
            <?php endif; ?>
            
            <button class="control-btn end-call-btn" onclick="endCall()" title="End Call">
                <span>📞</span>
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