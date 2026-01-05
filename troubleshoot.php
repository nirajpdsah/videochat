<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Troubleshooting - Wartalaap</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            line-height: 1.6;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            color: #f8fafc;
        }
        h1 { color: #a78bfa; }
        h2 { color: #c4b5fd; margin-top: 30px; }
        .issue { 
            background: rgba(255, 255, 255, 0.05);
            border-left: 4px solid #8b5cf6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .solution { 
            background: rgba(16, 185, 129, 0.1);
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
        }
        .error { 
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
            padding: 10px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 10px 20px 0;
        }
        code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        ul { margin-left: 20px; }
        li { margin: 8px 0; }
    </style>
</head>
<body>
    <h1>🔧 Troubleshooting Guide</h1>
    <p>Having issues with video calls? This guide will help you fix common problems.</p>
    
    <a href="dashboard.php" class="btn">← Back to Dashboard</a>
    <button onclick="clearCache()" class="btn" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">Clear Cache Now</button>
    
    <h2>Common Issues & Solutions</h2>
    
    <div class="issue">
        <h3>❌ "Cannot read properties of undefined (reading 'getUserMedia')"</h3>
        <div class="error">Could not access media devices: Cannot read properties of undefined</div>
        
        <div class="solution">
            <strong>Causes:</strong>
            <ul>
                <li>Browser cache serving old code</li>
                <li>Page not loaded over HTTPS</li>
                <li>Browser doesn't support WebRTC</li>
            </ul>
            
            <strong>Solutions:</strong>
            <ol>
                <li><strong>Clear your browser cache:</strong>
                    <ul>
                        <li>Chrome: Press <code>Ctrl+Shift+Delete</code> → Select "Cached images and files" → Click "Clear data"</li>
                        <li>Firefox: Press <code>Ctrl+Shift+Delete</code> → Select "Cache" → Click "Clear Now"</li>
                        <li>Or click the "Clear Cache Now" button above</li>
                    </ul>
                </li>
                <li><strong>Hard refresh the page:</strong>
                    <ul>
                        <li>Windows: <code>Ctrl+F5</code> or <code>Ctrl+Shift+R</code></li>
                        <li>Mac: <code>Cmd+Shift+R</code></li>
                    </ul>
                </li>
                <li><strong>Check your URL:</strong> Make sure it starts with <code>https://</code> not <code>http://</code></li>
                <li><strong>Update your browser</strong> to the latest version</li>
            </ol>
        </div>
    </div>
    
    <div class="issue">
        <h3>🎥 Camera/Microphone Permission Denied</h3>
        
        <div class="solution">
            <strong>Solutions:</strong>
            <ol>
                <li><strong>Check browser permissions:</strong>
                    <ul>
                        <li>Look for camera icon in the address bar</li>
                        <li>Click it and select "Allow"</li>
                    </ul>
                </li>
                <li><strong>Chrome:</strong> Go to <code>chrome://settings/content/camera</code> and <code>chrome://settings/content/microphone</code></li>
                <li><strong>Firefox:</strong> Click the lock icon in address bar → Click arrow next to "Blocked" → Select "Allow"</li>
                <li><strong>Check if another app is using camera/mic</strong> (Zoom, Teams, etc.)</li>
            </ol>
        </div>
    </div>
    
    <div class="issue">
        <h3>🌐 Weak Connection / Call Keeps Dropping</h3>
        
        <div class="solution">
            <strong>Solutions:</strong>
            <ol>
                <li><strong>Check your internet speed:</strong> You need at least 1 Mbps for voice, 2-4 Mbps for video</li>
                <li><strong>Switch to audio-only call</strong> if video keeps failing</li>
                <li><strong>Close other tabs/apps</strong> using internet (YouTube, downloads, etc.)</li>
                <li><strong>Move closer to Wi-Fi router</strong> or use Ethernet cable</li>
                <li><strong>Disable VPN</strong> if you're using one (it can slow connection)</li>
            </ol>
        </div>
    </div>
    
    <div class="issue">
        <h3>🔒 HTTPS Required Error</h3>
        
        <div class="solution">
            <strong>Why:</strong> Browsers require HTTPS for security when accessing camera/microphone.
            
            <strong>Solutions:</strong>
            <ol>
                <li>Contact your server administrator to install SSL certificate</li>
                <li>For development: Use <code>localhost</code> (works over HTTP)</li>
                <li>Free SSL certificates available from Let's Encrypt</li>
            </ol>
        </div>
    </div>
    
    <div class="issue">
        <h3>🔇 No Audio/Video During Call</h3>
        
        <div class="solution">
            <strong>Solutions:</strong>
            <ol>
                <li><strong>Check if muted:</strong> Look for red mic/camera icons</li>
                <li><strong>Test in another app</strong> to confirm devices work</li>
                <li><strong>Restart your browser</strong></li>
                <li><strong>Check system audio settings:</strong>
                    <ul>
                        <li>Windows: Right-click speaker icon → Sound settings</li>
                        <li>Mac: System Preferences → Sound</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
    
    <h2>Browser Compatibility</h2>
    <p><strong>Supported Browsers (with latest versions):</strong></p>
    <ul>
        <li>✅ Google Chrome 90+</li>
        <li>✅ Mozilla Firefox 88+</li>
        <li>✅ Microsoft Edge 90+</li>
        <li>✅ Safari 14+</li>
        <li>❌ Internet Explorer (not supported)</li>
    </ul>
    
    <h2>Quick Diagnostics</h2>
    <div id="diagnostics" style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 8px; margin: 20px 0;">
        <p><strong>Running system check...</strong></p>
    </div>
    
    <script>
        // Run diagnostics
        function runDiagnostics() {
            const results = [];
            
            // Check HTTPS
            if (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
                results.push('✅ HTTPS: OK');
            } else {
                results.push('❌ HTTPS: Required for camera/microphone access');
            }
            
            // Check getUserMedia
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                results.push('✅ getUserMedia API: Supported');
            } else {
                results.push('❌ getUserMedia API: Not supported');
            }
            
            // Check RTCPeerConnection
            if (window.RTCPeerConnection) {
                results.push('✅ WebRTC: Supported');
            } else {
                results.push('❌ WebRTC: Not supported');
            }
            
            // Check browser
            const ua = navigator.userAgent;
            let browser = 'Unknown';
            if (ua.includes('Chrome')) browser = 'Chrome';
            else if (ua.includes('Firefox')) browser = 'Firefox';
            else if (ua.includes('Safari')) browser = 'Safari';
            else if (ua.includes('Edge')) browser = 'Edge';
            results.push(`ℹ️ Browser: ${browser}`);
            
            // Check connection
            if (navigator.onLine) {
                results.push('✅ Internet: Connected');
            } else {
                results.push('❌ Internet: Offline');
            }
            
            document.getElementById('diagnostics').innerHTML = '<p><strong>System Check Results:</strong></p>' + 
                results.map(r => `<p style="margin: 5px 0;">${r}</p>`).join('');
        }
        
        // Clear cache function
        function clearCache() {
            // Clear localStorage
            localStorage.clear();
            
            // Clear sessionStorage
            sessionStorage.clear();
            
            // Unregister service workers
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    for (let registration of registrations) {
                        registration.unregister();
                    }
                });
            }
            
            // Clear cache storage
            if ('caches' in window) {
                caches.keys().then(function(names) {
                    for (let name of names) {
                        caches.delete(name);
                    }
                });
            }
            
            alert('Cache cleared! Please press Ctrl+F5 (Windows) or Cmd+Shift+R (Mac) to hard refresh the page.');
        }
        
        // Run diagnostics on load
        window.addEventListener('load', runDiagnostics);
    </script>
    
    <h2>Still Having Issues?</h2>
    <p>If none of these solutions work:</p>
    <ul>
        <li>Try a different browser</li>
        <li>Restart your computer</li>
        <li>Check firewall/antivirus settings</li>
        <li>Contact your system administrator</li>
    </ul>
    
    <a href="dashboard.php" class="btn">← Back to Dashboard</a>
</body>
</html>
