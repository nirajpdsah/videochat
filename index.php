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
    <title>Wartalaap - Where Conversations Come Alive</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .landing-wrapper {
            width: 100%;
            max-width: 1200px;
            padding: 40px 20px;
        }

        .landing-container {
            text-align: center;
            color: white;
            margin-bottom: 60px;
        }

        .landing-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .landing-header img {
            width: 56px;
            height: 56px;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.2));
        }

        .landing-container h1 {
            font-size: 52px;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -1px;
        }

        .landing-container p {
            font-size: 18px;
            margin-bottom: 48px;
            opacity: 0.95;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .landing-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 60px;
        }

        .landing-buttons a {
            padding: 16px 40px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .landing-buttons .btn-primary {
            background: white;
            color: #6366f1;
            font-weight: 700;
        }

        .landing-buttons .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .landing-buttons .btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .landing-buttons .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-top: 40px;
        }

        .feature {
            background: rgba(255, 255, 255, 0.95);
            padding: 32px;
            border-radius: 16px;
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
        }

        .feature-icon svg {
            width: 32px;
            height: 32px;
        }

        .feature h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1f2937;
        }

        .feature p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-top: 60px;
            text-align: center;
        }

        .stat {
            background: rgba(255, 255, 255, 0.1);
            padding: 24px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            display: block;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .landing-container h1 {
                font-size: 36px;
            }

            .landing-container p {
                font-size: 16px;
            }

            .landing-buttons {
                flex-direction: column;
            }

            .landing-buttons a {
                width: 100%;
            }

            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="landing-wrapper">
        <div class="landing-container">
            <div class="landing-header">
                <img src="uploads/logo.png" alt="Wartalaap Logo">
                <h1>Wartalaap</h1>
            </div>
            <p>Where meaningful conversations happen. Connect through crystal clear video and audio calls, bringing people closer no matter the distance.</p>
            
            <div class="landing-buttons">
                <a href="login.php" class="btn btn-primary">Sign In</a>
                <a href="signup.php" class="btn btn-secondary">Create Account</a>
            </div>

            <div class="stats">
                <div class="stat">
                    <span class="stat-number">Real-Time</span>
                    <span class="stat-label">Conversations</span>
                </div>
                <div class="stat">
                    <span class="stat-number">720p</span>
                    <span class="stat-label">HD Quality</span>
                </div>
                <div class="stat">
                    <span class="stat-number">Secure</span>
                    <span class="stat-label">End-to-End</span>
                </div>
            </div>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 7l-7 5 7 5V7z"></path>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                </div>
                <h3>Face-to-Face Dialogue</h3>
                <p>Experience crystal clear HD video calls that make you feel like you're in the same room.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <h3>Voice Conversations</h3>
                <p>Have meaningful voice-only conversations with pristine audio quality, perfect for on-the-go discussions.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="1"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m5.08-5.08l4.24-4.24"></path>
                    </svg>
                </div>
                <h3>Instant Connection</h3>
                <p>No downloads needed. Sign up and start your conversations within seconds.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3>Private Conversations</h3>
                <p>Your dialogues stay between you. Peer-to-peer encryption ensures your conversations remain private.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                    </svg>
                </div>
                <h3>Cross-Platform</h3>
                <p>Works seamlessly on desktop, tablet, and mobile devices using any modern web browser.</p>
            </div>

            <div class="feature">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1v22m11-11H1"></path>
                        <circle cx="12" cy="12" r="11"></circle>
                    </svg>
                </div>
                <h3>Easy to Use</h3>
                <p>Intuitive interface designed for everyone. Start making calls in just a few clicks.</p>
            </div>
        </div>
    </div>
</body>
</html>