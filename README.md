# 🎥 VideoChat - Real-Time Video Calling Application

A complete video calling web application where users can make video/audio calls and chat with each other in real-time. Built with PHP, MySQL, and WebRTC technology.

---

## 📋 Table of Contents

1. [What is This Project?](#what-is-this-project)
2. [Features](#features)
3. [How It Works](#how-it-works)
4. [Requirements](#requirements)
5. [Installation Guide](#installation-guide)
6. [Project Structure](#project-structure)
7. [How to Use](#how-to-use)
8. [Troubleshooting](#troubleshooting)
9. [For Developers](#for-developers)

---

## 🤔 What is This Project?

VideoChat is a web application that allows users to:
- **Register and login** to their account
- **See who's online** in real-time
- **Make video calls** to other users (like Zoom, but simpler)
- **Make audio calls** without video
- **Chat with messages** (coming soon)

Think of it like a simplified version of Skype or WhatsApp Web, but for video calling.

---

## ✨ Features

### Current Features
- ✅ User registration and login
- ✅ Profile pictures for users
- ✅ Online/offline status indicator
- ✅ Real-time call requests (calling someone notifies them instantly)
- ✅ Accept or reject incoming calls
- ✅ Video and audio calls using WebRTC
- ✅ Hang up / end call functionality
- ✅ Works on different devices (desktop, mobile, tablets)

### Coming Soon
- 💬 Text messaging
- 📞 Call history
- 🔔 Notifications

---

## 🔧 How It Works

### Simple Explanation (Non-Technical)
1. Users create an account (signup)
2. Users login to see a dashboard with all other users
3. Click on someone's name to call them
4. The other person gets a notification and can accept or reject
5. If accepted, both users can see and hear each other through their cameras/microphones
6. Either person can hang up to end the call

### Technical Explanation
- **Frontend**: HTML, CSS, JavaScript (WebRTC for video/audio)
- **Backend**: PHP (handles user authentication, signaling)
- **Database**: MySQL (stores users, messages, call signals)
- **Communication**: AJAX polling (frontend checks server every 200ms for new signals)
- **WebRTC**: Peer-to-peer connection for video/audio (direct connection between browsers)

**How Calls Work:**
1. User A clicks "Call" on User B
2. Server stores a "call-request" signal in database
3. User B's browser polls server and sees the request
4. User B accepts → browser sends "call-accepted" signal
5. Both browsers exchange WebRTC offers/answers/ICE candidates
6. Direct peer-to-peer video/audio connection established
7. Video feeds display on both screens

---

## 💻 Requirements

Before you start, make sure you have:

### For Windows Users (Easiest):
- **XAMPP** (includes PHP + MySQL + Apache)
  - Download from: https://www.apachefriends.org/
  - Version 7.4 or higher

### For Manual Setup:
- **PHP** version 7.4 or higher
- **MySQL** version 5.7 or higher
- **Web Server** (Apache or Nginx)

### Other Requirements:
- Modern web browser (Chrome, Firefox, Edge, Safari)
- Webcam and microphone (for video calls)
- HTTPS connection (required for WebRTC - we'll show you how)

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

1. **Copy Configuration Template**
   - In your project folder, find `config.example.php`
   - Make a copy and rename it to `config.php`

2. **Edit config.php**
   - Open `config.php` in any text editor (Notepad, VS Code, etc.)
   - Update these lines:
   
   ```php
   $db_host = 'localhost';       // Keep as is
   $db_user = 'root';            // Your MySQL username (default: root)
   $db_pass = '';                // Your MySQL password (default: empty)
   $db_name = 'videochat';       // The database name you created
   ```

3. **Save the file**

### Step 5: Test the Installation

1. **Open Your Browser**
   - Go to: `http://localhost/videochat`
   - You should see the VideoChat homepage

2. **Create Test Accounts**
   - Click "Sign Up"
   - Create first user (e.g., username: `alice`, email: `alice@test.com`)
   - Logout
   - Create second user (e.g., username: `bob`, email: `bob@test.com`)

3. **Test a Call**
   - Login as `alice` in one browser/tab
   - Login as `bob` in another browser/tab (or incognito mode)
   - Click on Bob's name in Alice's dashboard
   - Accept the call in Bob's browser
   - Grant camera/microphone permissions when asked
   - You should see video on both screens!

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

1. **Register an Account**
   - Click "Sign Up" on homepage
   - Fill in username, email, password
   - (Optional) Upload a profile picture
   - Click "Create Account"

2. **Login**
   - Enter your username/email and password
   - Click "Login"

3. **Dashboard**
   - You'll see a list of all users
   - Green dot = online
   - Red dot = offline
   - Blue dot = on a call

4. **Make a Call**
   - Click on any online user's name
   - Click "Call" button
   - Wait for them to accept

5. **Receive a Call**
   - You'll see a popup notification
   - Click "Accept" to answer
   - Click "Reject" to decline

6. **During a Call**
   - You'll see your video (small) and their video (large)
   - Click "Hang Up" button to end call
   - You can mute/unmute (if you add those buttons later)

---

## 🐛 Troubleshooting

### Problem: "Connection Failed" error on homepage

**Solution:**
- Check if MySQL is running in XAMPP
- Make sure you created `config.php` (not just config.example.php)
- Verify database credentials in `config.php`
- Check if database exists in phpMyAdmin

---

### Problem: Black screen during video call

**Solution:**
- Grant camera/microphone permissions in browser
- Check if another app is using your camera
- Try a different browser (Chrome works best)
- Make sure both users accepted permissions

---

### Problem: Can't hear the other person

**Solution:**
- Check your computer's volume
- Check browser permissions for microphone
- Test your microphone in browser settings
- Try refreshing the page

---

### Problem: "Not secure" warning in browser

**Solution:**
- For localhost, ignore warning and proceed
- For production, you need HTTPS certificate
- WebRTC requires HTTPS for security

---

### Problem: Call request doesn't show up

**Solution:**
- Refresh the receiving user's page
- Check if both users are marked as "online"
- Clear browser cache (Ctrl+Shift+Delete)
- Check browser console for JavaScript errors

---

### Problem: Page shows old code after changes

**Solution:**
- We added cache-busting, so clear your cache once
- Hard refresh: `Ctrl + F5` (Windows) or `Cmd + Shift + R` (Mac)
- Close all browser tabs and reopen

---

## 👨‍💻 For Developers

### Database Tables

**users**
- `id` - User ID (primary key)
- `username` - Unique username
- `email` - Email address
- `password` - Hashed password
- `profile_picture` - Filename in uploads/
- `status` - online, offline, on_call
- `created_at` - Registration timestamp

**signals**
- `id` - Signal ID (primary key)
- `from_user_id` - Who sent this signal
- `to_user_id` - Who should receive it
- `signal_type` - Type: offer, answer, ice-candidate, call-request, call-accepted, call-rejected, call-ended, receiver-ready
- `signal_data` - JSON payload
- `created_at` - Timestamp

**messages** (not fully implemented)
- `id` - Message ID
- `from_user_id` - Sender
- `to_user_id` - Receiver
- `message` - Text content
- `is_read` - Read status
- `created_at` - Timestamp

### WebRTC Flow

```
Caller                          Server                      Receiver
  |                               |                              |
  |---[call-request]------------->|                              |
  |                               |---[call-request]------------>|
  |                               |                              |
  |                               |<--[call-accepted]------------|
  |<--[call-accepted]-------------|                              |
  |                               |                              |
  |---[offer]-------------------->|                              |
  |                               |---[offer]------------------->|
  |                               |                              |
  |                               |<--[answer]-------------------|
  |<--[answer]---------------------|                              |
  |                               |                              |
  |<--[ICE candidates]----------->|<--[ICE candidates]---------->|
  |                               |                              |
  |<============= Direct P2P Connection ========================>|
```

### Key Functions in webrtc.js

- `initWebRTC()` - Initialize WebRTC configuration
- `createOffer()` - Create SDP offer (caller side)
- `handleOffer()` - Process incoming offer (receiver side)
- `handleAnswer()` - Process answer (caller side)
- `handleIceCandidate()` - Process ICE candidates (both sides)
- `endCall()` - Clean up and close connection
- `pollForSignals()` - Check server every 200ms for new signals

### API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/authenticate.php` | POST | Login user |
| `/api/register.php` | POST | Register new user |
| `/api/get_users.php` | GET | Get all users with status |
| `/api/send_signal.php` | POST | Send WebRTC signal |
| `/api/get_signals.php` | GET | Get pending signals |
| `/api/delete_signal.php` | POST | Remove processed signal |
| `/api/send_call_request.php` | POST | Initiate call |
| `/api/update_status.php` | POST | Update online/offline status |

### Making Changes

1. **Frontend Changes** (HTML/CSS/JS)
   - Edit the respective files
   - Clear browser cache or hard refresh
   - Cache-busting is enabled (files auto-reload with `?v=timestamp`)

2. **Backend Changes** (PHP)
   - Edit PHP files
   - Changes take effect immediately (no restart needed)
   - Check `error_log` for PHP errors

3. **Database Changes**
   - Always backup database first
   - Run SQL in phpMyAdmin
   - Update `database.sql` for new installations

### Git Workflow

```bash
# Check status
git status

# Add changes
git add .

# Commit
git commit -m "Description of changes"

# Push to remote
git push origin main
```

**Files NOT in Git (see .gitignore):**
- `config.php` (contains passwords)
- `uploads/*.jpg/png` (user uploads)
- `.env` files
- Test and debug files

### Security Notes

- ✅ Passwords are hashed with `password_hash()`
- ✅ SQL injection protected with prepared statements
- ✅ XSS protection with `htmlspecialchars()`
- ✅ Session-based authentication
- ⚠️ No CSRF protection yet (TODO)
- ⚠️ No rate limiting (TODO)
- ⚠️ Need HTTPS for production

---

## 🚀 Deployment (Production)

### Requirements for Production:
1. Web hosting with PHP 7.4+ and MySQL
2. SSL certificate (HTTPS) - required for WebRTC
3. Domain name

### Steps:
1. Upload all files via FTP/cPanel
2. Create database and import `database.sql`
3. Create `config.php` with production database credentials
4. Set `display_errors = 0` in production
5. Test thoroughly

### Recommended Hosts:
- Shared: SiteGround, Bluehost, HostGator
- VPS: DigitalOcean, Linode, Vultr
- Free (testing only): InfinityFree, 000webhost

---

## 📞 Support

### Need Help?

1. **Check the Troubleshooting section above**
2. **Check browser console** (F12) for JavaScript errors
3. **Check PHP errors** in XAMPP logs or `error_log`
4. **Contact the team** - [Add your contact info]

---

## 📝 License

This project is for educational purposes. Feel free to modify and use for learning.

---

## 🙏 Credits

**Built with:**
- WebRTC (Web Real-Time Communication)
- PHP (Backend)
- MySQL (Database)
- JavaScript (Frontend)
- XAMPP (Development environment)

**Team:**
- [Add your team members here]

---

## 📚 Additional Resources

**Learn More:**
- WebRTC: https://webrtc.org/
- PHP: https://www.php.net/
- MySQL: https://dev.mysql.com/doc/
- JavaScript: https://developer.mozilla.org/

---

**Last Updated:** December 28, 2025

**Version:** 1.0.0

---

Made with ❤️ by Your Team
