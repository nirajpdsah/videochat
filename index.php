<?php
/**
 * Landing Page
 * Redirects to dashboard if logged in, otherwise shows welcome page
 */
require_once 'config.php';

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// If logged in, go to dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VideoChat - Connect with Anyone</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        .landing-container {
            text-align: center;
            color: white;
            padding: 50px 20px;
        }
        .landing-container h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .landing-container p {
            font-size: 20px;
            margin-bottom: 40px;
            opacity: 0.9;
        }
        .landing-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .landing-buttons .btn {
            padding: 15px 40px;
            font-size: 18px;
            text-decoration: none;
            display: inline-block;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 60px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }
        .feature {
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .feature h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .feature p {
            font-size: 16px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <h1>🎥 VideoChat</h1>
        <p>Connect face-to-face with anyone, anywhere in the world</p>
        
        <div class="landing-buttons">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="signup.php" class="btn btn-secondary">Sign Up</a>
        </div>
        
        <div class="features">
            <div class="feature">
                <h3>📹 Video Calls</h3>
                <p>High-quality video calls with crystal clear audio</p>
            </div>
            <div class="feature">
                <h3>📞 Audio Calls</h3>
                <p>Make voice calls when you're on the go</p>
            </div>
            <div class="feature">
                <h3>💬 Chat</h3>
                <p>Send instant messages to your contacts</p>
            </div>
            <div class="feature">
                <h3>🌐 Web-Based</h3>
                <p>No downloads needed. Works right in your browser</p>
            </div>
            <div class="feature">
                <h3>🔒 Secure</h3>
                <p>End-to-end encrypted calls for your privacy</p>
            </div>
            <div class="feature">
                <h3>✨ Easy to Use</h3>
                <p>Simple interface that anyone can use</p>
            </div>
        </div>
    </div>
</body>
</html>