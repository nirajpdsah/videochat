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
    <link rel="icon" type="image/png" href="uploads/logo.png">
    <link rel="stylesheet" href="css/style.css?v=1.0">
    <style>
        body {
            /* Background handled by global style.css */
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
            margin-bottom: 80px;
            position: relative;
        }

        /* Decorative background glow */
        .landing-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.3) 0%, transparent 70%);
            z-index: -1;
            filter: blur(60px);
        }

        .landing-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 24px;
        }

        .landing-header img {
            width: 64px;
            height: 64px;
            filter: drop-shadow(0 0 20px rgba(139, 92, 246, 0.5));
        }

        .landing-container h1 {
            font-size: 64px;
            font-weight: 800;
            margin-bottom: 24px;
            letter-spacing: -2px;
            background: linear-gradient(135deg, white 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .landing-container p {
            font-size: 20px;
            margin-bottom: 48px;
            color: var(--text-muted);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }

        .landing-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 80px;
        }

        .landing-buttons a {
            padding: 18px 48px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .landing-buttons .btn-primary {
            background: white;
            color: var(--primary-dark);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        .landing-buttons .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.4);
        }

        .landing-buttons .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            backdrop-filter: blur(10px);
        }

        .landing-buttons .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-4px);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 32px;
            margin-top: 40px;
        }

        .feature {
            background: rgba(255, 255, 255, 0.03);
            padding: 40px;
            border-radius: 24px;
            backdrop-filter: blur(20px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        .feature::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .feature:hover {
            transform: translateY(-8px);
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .feature:hover::before {
            opacity: 1;
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: rgba(139, 92, 246, 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: #a78bfa;
            border: 1px solid rgba(139, 92, 246, 0.2);
            transition: all 0.3s ease;
        }

        .feature:hover .feature-icon {
            background: var(--primary);
            color: white;
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.4);
        }

        .feature-icon svg {
            width: 32px;
            height: 32px;
        }

        .feature h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 16px;
            color: white;
        }

        .feature p {
            font-size: 16px;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-top: 80px;
            text-align: center;
        }

        .stat {
            background: rgba(255, 255, 255, 0.03);
            padding: 32px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s;
        }

        .stat:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.05);
        }

        .stat-number {
            font-size: 48px;
            font-weight: 800;
            display: block;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        @media (max-width: 768px) {
            .landing-container h1 {
                font-size: 42px;
            }

            .landing-container p {
                font-size: 18px;
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
                <h1><span class="hindi-stylized">वार्ता</span>Laap</h1>
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