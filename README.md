# 🗣️ Wartalaap - Where Conversations Come Alive

**वार्तालाप (Wartalaap)** means "conversation" or "dialogue" in Hindi/Urdu - a beautiful word that captures the essence of meaningful human connection.

A modern, professional video calling web application that brings people together through crystal-clear conversations. Built with PHP, MySQL, and WebRTC technology with a focus on simplicity and elegance.

---

## 📋 Table of Contents

1. [What is This Project?](#what-is-this-project)
2. [Features](#features)
3. [Design & UI](#design--ui)
4. [How It Works](#how-it-works)
5. [Requirements](#requirements)
6. [Installation Guide](#installation-guide)
7. [Project Structure](#project-structure)
8. [How to Use](#how-to-use)
9. [Troubleshooting](#troubleshooting)
10. [For Developers](#for-developers)

---

## 🤔 What is This Project?

Wartalaap (वार्तालाप - "conversation") is a modern web application that enables meaningful dialogues through technology. Users can:
- **Register and create profiles** with personalized avatars
- **See who's available** for conversation in real-time
- **Initiate video dialogues** with HD quality
- **Have voice-only conversations** when preferred
- **Connect instantly** with end-to-end encrypted calls
- **Experience seamless communication** across all devices

Inspired by the timeless value of human conversation, Wartalaap combines traditional communication values with modern technology - creating a space where every dialogue matters.

---

## ✨ Features

### Current Features ✅
- **User Management**
  - User registration and login with secure password hashing
  - Profile pictures for all users
  - Email verification (basic implementation)
  - Session-based authentication

- **Real-Time Presence**
  - Live online/offline status with 15-second heartbeat
  - Last seen timestamp for offline users
  - Real-time user list updates (2-second polling)
  - Status indicators with color-coded indicators

- **Video & Audio Calls**
  - HD quality video calls (720p)
  - High-quality audio calls
  - Instant call notifications (1-second polling)
  - Accept/reject incoming calls
  - Mute/unmute audio and video during calls
  - Hang up / end call functionality
  - Works on desktop, tablet, and mobile

- **Modern UI/UX**
  - Professional gradient design with glassmorphism effects
  - Clean, intuitive interface
  - Smooth animations and transitions
  - SVG icons instead of emojis for modern look
  - Fully responsive design
  - Dark and light mode compatible

### Technical Features
- Peer-to-peer (P2P) WebRTC connections
- STUN server for NAT traversal
- ICE candidate handling for connection optimization
- Cache-busting for always-fresh assets
- AJAX polling for real-time signaling

---

## 🎨 Design & UI

### Modern Design System
- **Color Palette**: Indigo (#6366f1) and Purple (#8b5cf6) gradients
- **Typography**: System font stack with proper sizing and weights
- **Components**: 
  - Glassmorphic cards with backdrop blur
  - Rounded buttons with smooth hover effects
  - SVG icons with smooth transitions
  - Status indicators with pulsing animations
  - Modern modals with fade/slide animations

### Pages
- **Landing Page**: Professional hero section with feature cards and stats
- **Login/Signup**: Modern auth forms with clear CTAs
- **Dashboard**: User list with status, online indicators, action buttons
- **Call Page**: Full-screen video call interface with minimal controls

### Icons
All UI uses clean SVG icons:
- Video camera for video calls
- Phone receiver for audio calls
- Microphone for audio controls
- Shield for security
- Check for verified features

---

## 🔧 How It Works

### Simple Explanation (Non-Technical)
1. Users create an account with an optional profile picture
2. Login to see a dashboard with all other users and their status
3. Click video or audio icon to call someone
4. The other person gets an instant notification
5. If they accept, both users connect and can see/hear each other
6. Either person can hang up to end the call

### Technical Explanation
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (WebRTC API)
- **Backend**: PHP 7.4+, MySQL 5.7+
- **Real-Time Communication**: AJAX polling with optimized intervals
- **Video/Audio**: WebRTC Peer Connection with STUN servers
- **Database**: Relational tables for users, signals, and messages

**How Calls Work (Detailed):**

```
Timeline of a Video Call
│
├─ User A clicks "Call User B"
│  └─ Browser stores "call-request" signal in database
│
├─ User B's browser polls every 1 second
│  └─ Finds the call-request and shows notification
│
├─ User B clicks "Accept"
│  └─ Browser sends "call-accepted" signal
│
├─ User A receives "call-accepted"
│  └─ Creates WebRTC PeerConnection
│  └─ Generates SDP offer
│  └─ Sends offer via database signal
│
├─ User B receives offer
│  └─ Creates PeerConnection
│  └─ Generates SDP answer
│  └─ Sends answer back
│
├─ Both exchange ICE candidates
│  └─ Candidates help find best network path
│  └─ Usually takes 2-5 seconds total
│
├─ Connection established ✅
│  └─ Both users see video/hear audio
│  └─ Direct peer-to-peer (end-to-end encrypted)
│
└─ Either user can hang up
   └─ Sends "call-ended" signal
   └─ Other user automatically ends call
```

---

## 💻 Requirements

Before you start, make sure you have:

### For Windows Users (Easiest):
- **XAMPP** (includes PHP + MySQL + Apache)
  - Download from: https://www.apachefriends.org/
  - Version 7.4 or higher recommended
  - All-in-one solution for beginners

### For Manual Setup (Mac/Linux):
- **PHP** version 7.4 or higher
- **MySQL** version 5.7 or higher  
- **Web Server** (Apache or Nginx)
- **Composer** (optional, for package management)

### Other Requirements:
- Modern web browser (Chrome, Firefox, Edge, Safari)
  - Must support WebRTC API
  - Chrome/Chromium works best
- Webcam and microphone (for video/audio calls)
- HTTPS connection (required for WebRTC in production)
- Stable internet connection for optimal call quality

### Browser Compatibility
| Browser | Status | Notes |
|---------|--------|-------|
| Chrome/Chromium | ✅ Full support | Best performance |
| Firefox | ✅ Full support | Reliable |
| Safari | ✅ Full support | iOS 11+ required |
| Edge | ✅ Full support | Chromium-based |
| Opera | ✅ Full support | Alternative |

---

## 📥 Installation Guide

### Step 1: Download the Project

```bash
# Using Git
git clone https://github.com/YOUR_USERNAME/videochat.git
cd videochat
```

Or download ZIP file from GitHub and extract it.

### Step 2: Set Up XAMPP (Windows)

1. **Download and Install XAMPP**
   - Go to https://www.apachefriends.org/
   - Download XAMPP for Windows (PHP 7.4+)
   - Install it (default location: `C:\xampp`)

2. **Start XAMPP**
   - Open XAMPP Control Panel
   - Click "Start" for **Apache** (web server)
   - Click "Start" for **MySQL** (database)

3. **Copy Project Files**
   - Copy your `videochat` folder to: `C:\xampp\htdocs\videochat`

### Step 3: Create Database

1. **Open phpMyAdmin**
   - In browser, go to: `http://localhost/phpmyadmin`
   - Default username: `root`
   - Default password: (leave empty)

2. **Create Database**
   - Click "New" on left sidebar
   - Database name: `videochat` (or any name you prefer)
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import Database Structure**
   - Click on your new `videochat` database
   - Click "Import" tab at the top
   - Click "Choose File"
   - Select `database.sql` from your project folder
   - Click "Go" at bottom
   - You should see success message

### Step 4: Configure the Application

1. **Copy Configuration Template** (skip if already done)
   - In your project folder, find `config.example.php`
   - Make a copy and rename it to `config.php`

2. **Edit config.php**
   - Open `config.php` in any text editor (Notepad, VS Code, Sublime, etc.)
   - Update these lines:
   
   ```php
   $db_host = 'localhost';       // Database host (usually localhost)
   $db_user = 'root';            // MySQL username (default: root)
   $db_pass = '';                // MySQL password (default: empty)
   $db_name = 'videochat';       // Database name you created
   ```

3. **Save the file**
   - Ctrl+S or File → Save

### Step 5: Test the Installation ✅

1. **Open Your Browser**
   - Go to: `http://localhost/videochat`
   - You should see Wartalaap's beautiful landing page
   - If you see an error, check troubleshooting section below

2. **Create Test Accounts**
   - Click "Create Account" button
   - Create first user:
     - Username: `alice`
     - Email: `alice@test.com`
     - Password: `Test123!`
     - Upload a profile picture (optional)
   
   - Click "Sign In" after account created
   - Create second user:
     - Username: `bob`
     - Email: `bob@test.com`
     - Password: `Test123!`

3. **Test a Conversation** 🗣️
   - Open two browser windows/tabs
   - Login as `alice` in first window
   - Login as `bob` in second window (or use incognito mode)
   - In Alice's dashboard, find Bob's user card
   - Click the blue video camera icon to start a video conversation
   - Bob should see a notification popup
   - Bob clicks "Accept"
   - Grant camera/microphone permissions when prompted
   - You should see video and hear audio - your first Wartalaap conversation! 🎉

### Troubleshooting Installation

**Issue: "Connection refused" error**
- Make sure MySQL is running in XAMPP
- Go to XAMPP Control Panel → Click "Start" next to MySQL

**Issue: "No database selected" error**
- You forgot to create the database
- Go back to Step 3 and create the `videochat` database

**Issue: Login page shows blank or error**
- Check config.php has correct database credentials
- Try deleting config.php and creating it again carefully

**Issue: "Not secure" warning**
- This is normal for localhost
- Click "Advanced" → "Proceed to localhost" or similar button

**Issue: Logo not showing**
- Make sure `uploads/logo.png` exists
- Check file permissions on uploads folder

---

## 📁 Project Structure

```
videochat/
│
├── api/                          # Backend API files
│   ├── authenticate.php          # Login API
│   ├── register.php              # Signup API
│   ├── get_users.php             # Get list of users
│   ├── send_signal.php           # Send WebRTC signals
│   ├── get_signals.php           # Receive WebRTC signals
│   ├── delete_signal.php         # Clean up signals
│   ├── send_call_request.php    # Initiate call
│   └── update_status.php         # Update user online status
│
├── css/
│   └── style.css                 # All styling
│
├── js/
│   ├── dashboard.js              # Dashboard logic (user list, incoming calls)
│   └── webrtc.js                 # Video call logic (WebRTC implementation)
│
├── uploads/                      # User profile pictures
│   └── .gitkeep
│
├── index.php                     # Landing/home page
├── login.php                     # Login page
├── signup.php                    # Registration page
├── dashboard.php                 # Main dashboard (after login)
├── call.php                      # Video call page
├── logout.php                    # Logout handler
├── config.example.php            # Configuration template
├── config.php                    # Your actual config (not in git)
├── database.sql                  # Database structure
├── .gitignore                    # Files to ignore in git
└── README.md                     # This file
```

### Important Files Explained:

| File | What It Does |
|------|--------------|
| `index.php` | Homepage, redirects to dashboard if logged in |
| `login.php` | Login form and authentication |
| `signup.php` | Registration form with profile picture upload |
| `dashboard.php` | Shows list of users, handle incoming calls |
| `call.php` | The actual video call interface |
| `js/webrtc.js` | All WebRTC magic happens here (600+ lines) |
| `js/dashboard.js` | User list updates, call request handling |
| `config.php` | Database credentials (you create this) |
| `database.sql` | Database tables structure |

---

## 🎯 How to Use

### For End Users:

#### 1. Register an Account 📝
- Click "Create Account" on homepage
- Fill in details:
  - **Username**: Choose a unique username (3+ characters)
  - **Email**: Valid email address
  - **Password**: At least 6 characters
  - **Profile Picture**: (Optional) Upload JPG or PNG
- Click "Create Account" button
- You'll be redirected to login page

#### 2. Login 🔐
- Enter your username/email and password
- Click "Sign In" button
- You'll go to the dashboard

#### 3. Dashboard Overview 👥
The dashboard shows:
- **Your Profile**: Top-right corner with your picture
- **User List**: All registered users on left side
- **Online Status**: 
  - 🟢 Green dot = Online now
  - 🟡 Yellow dot = On a call
  - ⚫ Gray dot = Offline (with last seen time)

#### 4. Make a Call ☎️

**Video Call:**
- Find the person you want to call
- Click the blue **video camera icon** on their card
- Status changes to "Connecting..."
- They get a notification popup
- When they accept, video call starts
- You'll see their video and they'll see yours

**Audio Call:**
- Click the green **phone icon** instead
- Similar process, but audio only
- Useful for quick calls or saving bandwidth

#### 5. Receive a Call 🔔
- You'll see a popup with caller's picture
- Shows "Someone is calling you..."
- Click **"Accept"** to answer
- Click **"Reject"** to decline
- If you don't respond for 30 seconds, call times out

#### 6. During a Call 🎬
- **Large video**: The other person
- **Small video** (bottom-right): Your video
- **Controls** (bottom):
  - 🎤 **Microphone button**: Click to mute/unmute
  - 📹 **Camera button** (video calls only): Click to turn video off/on
  - 📞 **End Call button** (red): Click to hang up

#### 7. End a Call 🛑
- Click the red **End Call** button at bottom
- Call disconnects immediately
- Status returns to "Online"
- Both users are disconnected

#### 8. Logout 🚪
- Click your profile picture in top-right
- Select "Logout" (if available)
- Or close the browser tab
- Your status will show "Offline" after 15 seconds

---

## 🐛 Troubleshooting

### General Issues

#### Problem: "Connection Failed" on homepage
**Solution:**
1. Check if Apache is running in XAMPP (green light)
2. Check if MySQL is running in XAMPP (green light)
3. Make sure you created `config.php` (not just config.example.php)
4. Verify database credentials in `config.php` match your MySQL
5. Check if database named `videochat` exists in phpMyAdmin
6. Restart Apache and MySQL in XAMPP

#### Problem: MySQL won't start
**Solution:**
1. Make sure MySQL port 3306 is not in use
2. Check for conflicts with other MySQL installations
3. Try clicking "Config" → "MySQL" → change port if needed
4. Reinstall XAMPP if problem persists

#### Problem: "MySQL has gone away" after login
**Solution:**
1. Your database connection was lost
2. Make sure MySQL is still running
3. Check if query took too long
4. Increase `max_allowed_packet` in MySQL config

---

### Login/Account Issues

#### Problem: Can't login - "Invalid credentials"
**Solution:**
1. Check if you spelled username/email correctly (case-sensitive)
2. Verify password is correct
3. Make sure account exists in database
4. Clear browser cache and cookies
5. Try incognito/private mode

#### Problem: Can't upload profile picture
**Solution:**
1. Make sure `uploads/` folder exists and is writable
2. Check file size (must be under 5MB)
3. Use JPG or PNG format only
4. Filename shouldn't have special characters
5. Check folder permissions: should be 755 or 777

#### Problem: Profile picture not showing
**Solution:**
1. Hard refresh page (Ctrl+F5)
2. Check `uploads/` folder - file should be there
3. Check image file name in database
4. Try re-uploading the picture

---

### Video/Audio Call Issues

#### Problem: Black screen during video call
**Solution:**
1. **Grant permissions**: Allow camera/microphone when prompted
2. **Check camera**: Make sure camera isn't in use by another app
3. **Restart browser**: Close all tabs and reopen
4. **Try different browser**: Chrome works best
5. **Check camera in OS**: Windows Settings → Privacy → Camera → make sure apps can access it
6. **Unplug/replug**: Disconnect and reconnect camera

#### Problem: Can't hear the other person
**Solution:**
1. Check your computer's volume (should not be muted)
2. Check browser volume (websites tab in volume mixer)
3. Check microphone in browser settings
4. Test microphone: Go to chrome://settings/content/microphone
5. Try different microphone if you have one
6. Refresh the page

#### Problem: Other person can't hear you
**Solution:**
1. Make sure your microphone is enabled (not muted)
2. Check if microphone is working: Use system test
3. Click unmute button if available
4. Try closing other apps using microphone
5. Check browser permissions for microphone

#### Problem: Call request doesn't appear
**Solution:**
1. Refresh the receiver's page (F5)
2. Check if both users are marked as "Online"
3. Check if database is still connected
4. Clear browser cache (Ctrl+Shift+Delete)
5. Check browser console for errors (F12 → Console tab)
6. Both users need to be on the page actively

#### Problem: "Connection Failed" during call
**Solution:**
1. Check internet connection stability
2. Check if remote user is still online
3. Try calling again
4. Try different browser
5. Restart both browsers
6. Check firewall settings (may be blocking WebRTC)

#### Problem: Video is laggy or choppy
**Solution:**
1. Close other bandwidth-heavy apps
2. Move closer to WiFi router
3. Use 5GHz WiFi instead of 2.4GHz if available
4. Close other browser tabs
5. Reduce screen resolution if possible
6. Try audio-only call instead

#### Problem: "Not secure" warning in browser
**Solution:**
- For **localhost**: This is normal, proceed with warning
- For **production**: Get SSL certificate (HTTPS)
- WebRTC requires HTTPS for security

---

### Page/Display Issues

#### Problem: Page shows old code after I made changes
**Solution:**
1. Hard refresh (Ctrl+Shift+Delete)
2. Clear browser cache completely
3. Close browser entirely and reopen
4. Cache-busting is enabled, but browser cache can override it

#### Problem: Dashboard looks broken or misaligned
**Solution:**
1. Hard refresh (Ctrl+F5)
2. Check browser zoom (should be 100%)
3. Make window bigger if on mobile
4. Try different browser
5. Check browser console for CSS errors

#### Problem: Buttons don't work
**Solution:**
1. Check browser console (F12) for JavaScript errors
2. Make sure JavaScript is enabled
3. Hard refresh page
4. Try different browser
5. Check if database is still connected

---

### Server/Database Issues

#### Problem: "Too many connections" error
**Solution:**
1. Check how many queries are running
2. Close some browser tabs/windows
3. Restart MySQL in XAMPP
4. Reduce polling frequency in JavaScript if needed

#### Problem: "Table doesn't exist"
**Solution:**
1. Database wasn't imported correctly
2. Go to phpMyAdmin
3. Click your database → Import tab
4. Select database.sql file
5. Click Go
6. All tables should appear

#### Problem: Page loading is very slow
**Solution:**
1. Check if MySQL is running slow
2. Close other bandwidth-heavy apps
3. Check disk space (might be full)
4. Reduce polling frequency in dashboard.js if needed

---

### Advanced Troubleshooting

#### Check PHP Errors
1. Look in `C:\xampp\apache\logs\error.log`
2. Or `C:\xampp\mysql\data\` for MySQL errors
3. Errors usually start with date/time

#### Check Browser Console
1. Press F12 to open Developer Tools
2. Click "Console" tab
3. Look for red error messages
4. JavaScript errors appear here

#### Test Database Connection
1. Create a test file: `test.php`
2. Put this code:
```php
<?php
$conn = new mysqli('localhost', 'root', '', 'videochat');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully!";
?>
```
3. Visit: `http://localhost/videochat/test.php`
4. Should show "Connected successfully!"

---

## 👨‍💻 For Developers

### Architecture Overview

```
┌─────────────────────────────────────────────────┐
│            Browser (Frontend)                    │
├─────────────────────────────────────────────────┤
│  HTML/CSS/JS → WebRTC API → Camera/Microphone  │
│                                                  │
│  • dashboard.js (user list, incoming calls)    │
│  • webrtc.js (peer connection, media)          │
│  • style.css (modern UI with SVG icons)        │
└──────────────┬──────────────────────────────────┘
               │ HTTPS/WebSocket
               │ (AJAX Polling every 200-2000ms)
               ↓
┌─────────────────────────────────────────────────┐
│         PHP Backend + MySQL Database             │
├─────────────────────────────────────────────────┤
│  • Authentication (login/signup)                │
│  • User management (profile, status)            │
│  • Signal relay (offer/answer/ICE)             │
│  • Message storage (future feature)            │
└─────────────────────────────────────────────────┘
               ↓
┌─────────────────────────────────────────────────┐
│         Direct P2P Connection                    │
├─────────────────────────────────────────────────┤
│  WebRTC Peer Connection (encrypted)            │
│  • Video stream                                 │
│  • Audio stream                                 │
│  • Data channel (future)                       │
└─────────────────────────────────────────────────┘
```

### Database Schema

**users table**
```sql
- id (INT, primary key, auto-increment)
- username (VARCHAR 255, unique)
- email (VARCHAR 255, unique)
- password (VARCHAR 255, hashed)
- profile_picture (VARCHAR 255, filename)
- status (ENUM: online, offline, on_call)
- last_seen (TIMESTAMP, NULL)
- created_at (TIMESTAMP)
```

**signals table**
```sql
- id (INT, primary key, auto-increment)
- from_user_id (INT, foreign key)
- to_user_id (INT, foreign key)
- signal_type (VARCHAR 255)
  Options: offer, answer, ice-candidate, call-request,
           call-accepted, call-rejected, call-ended,
           receiver-ready
- signal_data (LONGTEXT, JSON)
- created_at (TIMESTAMP)
```

**messages table** (future use)
```sql
- id (INT, primary key, auto-increment)
- from_user_id (INT, foreign key)
- to_user_id (INT, foreign key)
- message (TEXT)
- is_read (BOOLEAN)
- created_at (TIMESTAMP)
```

### API Endpoints

| Endpoint | Method | Purpose | Parameters |
|----------|--------|---------|------------|
| `/api/authenticate.php` | POST | Login | username, password |
| `/api/register.php` | POST | Register user | username, email, password |
| `/api/get_users.php` | GET | Get all users | none |
| `/api/update_status.php` | POST | Set user status | status |
| `/api/send_signal.php` | POST | Send WebRTC signal | to_user_id, signal_type, signal_data |
| `/api/get_signals.php` | GET | Get pending signals | none |
| `/api/delete_signal.php` | POST | Remove signal | from_user_id, signal_type |

### Key JavaScript Functions

**dashboard.js** (User list, call handling)
```javascript
loadUsers()              // Fetch all users from database
displayUsers()           // Render user list with status
initiateCall()          // Start a call to user
acceptCall()            // Accept incoming call
rejectCall()            // Reject incoming call
updateUserStatus()      // Send heartbeat to keep online
checkForIncomingCalls() // Poll for incoming call notifications
formatLastSeen()        // Format last_seen as relative time
```

**webrtc.js** (Video/audio, peer connection)
```javascript
setupMediaOnly()              // Get camera/microphone access
startCall()                   // Initiate WebRTC connection
createPeerConnection()        // Create RTCPeerConnection
createOffer()                 // Generate SDP offer
handleOffer()                 // Process incoming offer
handleAnswer()                // Process incoming answer
handleIceCandidate()          // Process ICE candidates
toggleAudio()                 // Mute/unmute microphone
toggleVideo()                 // Turn camera on/off
endCall()                     // Close peer connection
checkForSignals()             // Poll for WebRTC signals
```

### Polling Intervals

| Action | Interval | Purpose |
|--------|----------|---------|
| Check for signals | 200ms | WebRTC signaling (fast) |
| Check for calls | 1000ms | Incoming call notifications |
| Get user list | 2000ms | User status updates |
| Heartbeat ping | 10000ms | Keep user marked online |

### Making Code Changes

#### Frontend Changes (HTML/CSS/JS)
```bash
# Edit files
vim js/dashboard.js
vim css/style.css
vim index.php

# Changes take effect immediately on next page load
# Cache-busting enabled: ?v=timestamp added to assets
```

#### Backend Changes (PHP)
```bash
# Edit files
vim api/authenticate.php
vim api/get_users.php

# Changes take effect immediately (no restart needed)
# Check logs: C:\xampp\apache\logs\error.log
```

#### Database Changes
```bash
# Backup first!
# In phpMyAdmin, run SQL queries or use database.sql
# Update database.sql for new installations
```

### Git Workflow (Team Development)

```bash
# See what changed
git status

# Stage changes
git add .

# Commit with clear message
git commit -m "Fix: users stay online after logout

- Add 15-second inactivity timeout
- Update last_seen on all status changes
- Auto-mark users offline in get_users.php"

# Push to remote
git push origin main

# Pull latest from team
git pull origin main
```

**Files NOT in git (see .gitignore):**
- `config.php` (database passwords)
- `uploads/*.jpg`, `uploads/*.png` (user files)
- `.env` files (environment variables)
- Test files (test_*.php, debug_*.php)

### Security Best Practices

**Implemented ✅**
- Passwords hashed with `password_hash()` (bcrypt)
- SQL injection prevented with prepared statements
- XSS protection with `htmlspecialchars()`
- Session-based authentication
- HTTPS recommended for production

**Should Add 🔔**
- CSRF protection tokens on forms
- Rate limiting on login attempts
- Email verification for signups
- Refresh token rotation
- Activity logging and monitoring
- Input validation on all forms

### Performance Optimization Tips

1. **Reduce Polling Frequency**
   - Change `2000` to `5000` in dashboard.js for slower updates
   - Change `200` to `500` in webrtc.js if network is slow

2. **Database Optimization**
   - Add indexes on frequently queried columns
   - Clean up old signals regularly (add cleanup script)
   - Archive old messages to separate table

3. **Caching**
   - Cache user list for 1-2 seconds on server
   - Cache profile pictures with longer expiry
   - Use Redis if you scale to many users

4. **Network**
   - Use CDN for static assets (CSS, JS, images)
   - Enable gzip compression on server
   - Consider WebSocket instead of polling

---

## 🚀 Deployment (Production)

### Requirements
- Web hosting with:
  - PHP 7.4+ support
  - MySQL 5.7+ support
  - HTTPS/SSL certificate (required for WebRTC)
  - SSH access (recommended)
  
- Domain name
- Email for SSL certificate

### Popular Hosting Options

| Provider | Type | Cost | WebRTC | Ease |
|----------|------|------|--------|------|
| Vercel + Firebase | Serverless | $$ | ✅ | Easy |
| Railway | PaaS | $5/mo | ✅ | Easy |
| DigitalOcean | VPS | $5/mo | ✅ | Medium |
| Heroku | PaaS | $7/mo | ✅ | Easy |
| Linode | VPS | $5/mo | ✅ | Medium |
| AWS Lightsail | VPS | $3.50/mo | ✅ | Medium |

### Deployment Steps

1. **Get SSL Certificate** (free from Let's Encrypt)
   - Most hosting auto-installs
   - Or use Certbot

2. **Upload Files**
   - Via FTP, SFTP, or Git
   - Set correct permissions (755 for dirs, 644 for files)

3. **Configure Database**
   - Create MySQL database
   - Import database.sql
   - Create config.php with credentials

4. **Configure PHP**
   - Set `display_errors = 0`
   - Set `error_reporting = E_ALL`
   - Logs go to `/var/log/php_errors.log`

5. **Test Thoroughly**
   - Test account creation
   - Test login
   - Test video call
   - Test audio call
   - Test in different browsers
   - Test on mobile

6. **Monitor**
   - Check error logs regularly
   - Monitor database performance
   - Watch for security issues

---

## 📞 Support & Contact

### Need Help?

1. **Check Troubleshooting Guide** (above)
2. **Check Browser Console** (F12 → Console tab)
3. **Check Server Logs** (error.log in XAMPP)
4. **Search closed issues** on GitHub
5. **Create new issue** on GitHub if needed

### Reporting Issues

Include:
- Error message (exact text)
- Steps to reproduce
- Browser and OS
- Console errors (F12)
- Server error logs

---

## 📝 License

This project is open source and available under the MIT License.

You are free to:
- Use for learning and educational purposes
- Modify and customize for your needs
- Deploy and use personally
- Share improvements

---

## 🙏 Credits

**Technologies Used:**
- [WebRTC](https://webrtc.org/) - Real-time communication
- [PHP](https://www.php.net/) - Backend language
- [MySQL](https://www.mysql.com/) - Database
- [JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript) - Frontend logic

**Design Inspiration:**
- Zoom, Microsoft Teams, Discord
- Modern UI principles
- Glassmorphism design trend

**Contributors:**
- Initial development: January 2026
- UI/UX modernization: January 2, 2026
- Community improvements: Welcome!

---

## 📚 Learning Resources

### WebRTC
- [WebRTC.org Official Docs](https://webrtc.org/)
- [MDN WebRTC Guide](https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API)
- [WebRTC In 100 Seconds](https://www.youtube.com/watch?v=WmR9IMQQ3mE)

### PHP & Backend
- [PHP Official Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)

### Frontend Development
- [MDN Web Docs](https://developer.mozilla.org/)
- [JavaScript.info](https://javascript.info/)
- [CSS Tricks](https://css-tricks.com/)

### Video Calling Concepts
- [Understanding WebRTC](https://www.html5rocks.com/en/tutorials/webrtc/basics/)
- [How STUN/TURN Works](https://www.youtube.com/watch?v=TY6DqDr16rQ)
- [SDP Explained](https://en.wikipedia.org/wiki/Session_Description_Protocol)

---

## 🔄 Version History

**v1.2.0** (January 2, 2026)
- ✨ Complete UI redesign with modern design system
- 🎨 Replace emoji icons with clean SVG icons
- 📱 Improved responsive design for mobile
- 🎯 Modernized landing page with feature cards
- 🚀 Better call controls with smooth animations
- 📝 Comprehensive README documentation

**v1.1.0** (December 28, 2025)
- 🐛 Fixed online status bug with heartbeat system
- ⏰ Add last seen timestamp feature
- ✨ Better status indicators
- 🧹 Remove chat functionality
- 📚 Detailed README for teammates

**v1.0.0** (December 2025)
- 🎉 Initial release
- ✅ User registration and login
- 📞 Video and audio calls
- 👥 User presence and status
- 🔄 Real-time signaling with AJAX polling

---

**Last Updated:** January 2, 2026

**Maintained by:** Development Team

**Status:** Active Development

---

Made with ❤️ for real-time communication
