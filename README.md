# Wartalaap - Peer-to-Peer Video Calling Application

🎥 **Wartalaap** (वार्तालाप - "Conversation" in Hindi/Urdu) is a modern, real-time video calling application built with WebRTC, PHP, and MySQL.

![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)
![Version](https://img.shields.io/badge/Version-2.0-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

- ✅ **Peer-to-Peer Video Calling** - Direct video streams using WebRTC
- ✅ **Audio Calling** - Voice-only calls for low-bandwidth scenarios
- ✅ **Real-time Status** - Camera & microphone on/off indicators
- ✅ **User Authentication** - Secure login and registration
- ✅ **Online User Directory** - See who's available for calls
- ✅ **Profile Pictures** - User avatars and custom profile images
- ✅ **Dark Modern UI** - Glassmorphism design with smooth animations
- ✅ **Responsive Layout** - Works on desktop and mobile devices
- ✅ **WhatsApp-Style Interface** - Familiar and intuitive UX

## 🚀 Quick Start

### Installation
```bash
# 1. Extract to htdocs (XAMPP)
cd C:\xampp\htdocs
git clone <repo-url> videochat
cd videochat

# 2. Import database
# Open http://localhost/phpmyadmin → Import database.sql

# 3. Start XAMPP (Apache + MySQL)

# 4. Access application
# Open http://localhost/videochat
```

## 📚 Documentation

### 🔴 **START HERE** → [Documentation Index](docs/INDEX.md)

Complete guide to all files and components. **All teammates must read this first!**

### Quick Links

| Topic | Location | For |
|-------|----------|-----|
| **System Design** | [Architecture Overview](docs/ARCHITECTURE.md) | Everyone |
| **Video Call UI** | [call.php](docs/CALL.md) | Frontend devs |
| **WebRTC Logic** ⭐ | [webrtc.js](docs/WEBRTC.md) | All devs (MUST READ) |
| **Backend API** | [API Endpoints](docs/API.md) | Backend devs |
| **Styling** | [style.css](docs/STYLE.md) | Frontend devs |
| **Database** | [Database Schema](docs/DATABASE.md) | Backend devs |
| **Configuration** | [config.php](docs/CONFIG.md) | DevOps |

## 📖 For Different Roles

**👨‍💻 Frontend Developer?**
1. Read [Architecture](docs/ARCHITECTURE.md) first
2. Study [call.php](docs/CALL.md) (HTML structure)
3. Reference [style.css](docs/STYLE.md) (Styling)
4. Learn [webrtc.js](docs/WEBRTC.md) (JavaScript)

**🔧 Backend Developer?**
1. Read [Architecture](docs/ARCHITECTURE.md) first
2. Study [API Endpoints](docs/API.md) (REST)
3. Reference [Database](docs/DATABASE.md) (Schema)
4. Learn [webrtc.js](docs/WEBRTC.md) (Signaling)

**🚀 DevOps/Deployment?**
1. [INFINITYFREE_DEPLOYMENT.md](INFINITYFREE_DEPLOYMENT.md)
2. [RAILWAY_SETUP.md](RAILWAY_SETUP.md)
3. [Database Setup](DATABASE_SETUP_GUIDE.md)

---

### 2. The Brains (JavaScript)

These files handle all the interactivity.

-   **`js/dashboard.js`**
    -   **Role:** The "Manager" of the Dashboard.
    -   **Key Jobs:**
        -   **Polling:** Every few seconds, it asks the server "Who is online?" and "Is anyone calling me?".
        -   **UI Updates:** It builds the list of users you see on the sidebar.
        -   **Call Logic:** When you click "Video Call", this file tells the server "Tell User B that User A is calling".
        -   **Sound:** It controls the ringing sounds (`sounds/ringtone.mp3`).

-   **`js/webrtc.js`**
    -   **Role:** The "Engineer" of the Video Call.
    -   **Key Jobs:**
        -   **Camera/Mic Access:** It asks the browser for permission to use the webcam.
        -   **Peer Connection:** It uses specific internet protocols (ICE Candidates, SDP) to find a path through the internet to connect User A's computer to User B's computer.
        -   **Stream Handling:** It takes the video coming from the other person and puts it into the `<video>` tag on `call.php`.
        -   **Draggable Video:** It contains the code that lets you drag your own video window around the screen.

---

### 3. The Look & Feel (CSS)

-   **`css/style.css`**
    -   **What it is:** The one file to rule them all.
    -   **Style:** We use a "Glassmorphism" + "Neon" aesthetic.
        -   **Glassmorphism:** Semi-transparent backgrounds with blur filters (`backdrop-filter: blur(10px)`), making elements look like frosted glass.
        -   **Neon:** Bright gradients (Purples, Pinks, Violets) against a deep dark background.
    -   **Responsiveness:** It ensures the app looks good on Phones, Tablets, and Laptops.

---

### 4. The Server (Backend API)

These files live in the `api/` folder. They are like waiters—the JavaScript (customer) asks them for something, and they fetch it from the Database (kitchen).

-   **`api/get_users.php`**
    -   Fetches a list of all registered users so `dashboard.js` can display them.

-   **`api/send_signal.php`**
    -   **Concept:** "Signaling". Since User A and User B don't know each other's IP addresses yet, they send messages to this file.
    -   **Job:** Takes a message (like "Here is my IP") and saves it in the database for the other person to read.

-   **`api/get_signals.php`**
    -   **Job:** The receiver (User B) basically asks this file every second: "Do I have any new messages?"
    -   If User A sent a signal, this file delivers it to User B.

-   **`api/update_status.php`**
    -   Updates whether a user is "Online", "Offline", or "On a Call" so the green/red dots on the dashboard are accurate.

-   **`api/delete_signal.php`**
    -   Cleans up old messages so the database doesn't get clogged with old call requests.

---

### 5. Configuration & Database

-   **`config.php`**
    -   Contains the secret codes to connect to the MySQL database. **Never share this file publicly.**

-   **`database.sql`**
    -   This is a blueprint. If you are setting up this project on a new computer, running this file in your database tool (like phpMyAdmin) will create all the necessary tables (`users`, `signals`, etc.).

---

## � Browser Compatibility

| Browser | Desktop | Mobile | Notes |
|---------|---------|--------|-------|
| **Chrome** | ✅ 76+ | ✅ 76+ | Best support, recommended |
| **Edge** | ✅ 79+ | ✅ 79+ | Chromium-based, works great |
| **Firefox** | ⚠️ 68+ | ⚠️ 68+ | Partial WebRTC support |
| **Safari** | ✅ 11+ | ✅ 11+ | Works, may need iOS 14.5+ |
| **Opera** | ✅ 63+ | ✅ 63+ | Chromium-based, full support |
| **IE 11** | ❌ No | ❌ No | Not supported, WebRTC missing |

**What to test if video doesn't work:**
1. Check browser version: Open DevTools → Help → About
2. Ensure HTTPS on production (browsers block camera/mic on HTTP)
3. Grant camera/microphone permissions
4. Check browser console for errors (F12 → Console)

---

## 📊 System Requirements

### Server
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher (MariaDB 10.3+ works too)
- **Server:** Apache with mod_rewrite enabled
- **Storage:** ~50 MB for initial setup
- **RAM:** 512 MB minimum (1 GB recommended)

### Client (User's Computer)
- **Processor:** Any modern CPU (Intel/AMD)
- **RAM:** 2 GB minimum
- **Bandwidth:** 2.5 Mbps for HD video calling
- **Camera/Mic:** Built-in or USB connected
- **Internet:** Stable connection (WiFi or Ethernet)

### Mobile Devices
- **iOS:** iPhone 6S+ (Safari 11+)
- **Android:** Most devices from 2017+ (Chrome 76+)
- **Network:** 3G/4G minimum, 5G recommended

---

## 🚀 Deployment

### Option 1: InfinityFree (Free, Easy)
Completely free hosting. See [INFINITYFREE_DEPLOYMENT.md](INFINITYFREE_DEPLOYMENT.md) for setup.

**Pros:** Free, no credit card needed
**Cons:** Limited resources, slower performance

### Option 2: Railway (Modern, Recommended)
Cloud platform with MySQL support. See [RAILWAY_SETUP.md](RAILWAY_SETUP.md) for setup.

**Pros:** Easy to scale, good performance, integrated MySQL
**Cons:** Paid after free tier ($5-50/month depending on usage)

### Option 3: Local XAMPP/WAMP
Run on your computer. Good for development/testing.

**Pros:** Completely local, no internet needed
**Cons:** Only accessible from your computer

### Option 4: Self-Hosted VPS
Full control with AWS, DigitalOcean, Linode, etc.

**Pros:** Complete control, good performance
**Cons:** More technical setup, need to manage everything

---

## ⚡ Performance & Optimization

### Current Performance
- **Page Load:** ~500ms on local, ~2s on cloud
- **Call Connection:** ~3-5 seconds (P2P)
- **Video Quality:** 1280x720 @ 30fps typical
- **Bandwidth:** ~2.5 Mbps for HD video

### Tips to Improve
1. **Enable Compression:** Use gzip in Apache config
2. **Cache CSS/JS:** Add cache headers to static files
3. **Database Indexes:** Already optimized, but monitor with `EXPLAIN` queries
4. **CDN:** Use CloudFlare for static files (CSS, JS, images)
5. **Lazy Loading:** Load user list only when scrolling

### Monitor Performance
```bash
# Check server load
top

# Check database performance
mysql> EXPLAIN SELECT * FROM users WHERE status = 'online';

# Monitor web server logs
tail -f /var/log/apache2/error.log
```

---

## 🐛 Troubleshooting Guide

### Problem: "Cannot connect to database"
**Solution:** Check `config.php` - verify DB_HOST, DB_USER, DB_PASS, DB_NAME

### Problem: "Camera not working"
**Solution:**
1. Check browser permissions (click lock icon in address bar)
2. Grant camera access
3. Restart browser
4. Check DevTools console for errors

### Problem: "Call won't connect"
**Solution:**
1. Check both users are in same network or have internet
2. Try Chrome instead of other browsers
3. Check firewall isn't blocking WebRTC
4. Try with same user on different device to test

### Problem: "Avatar shows but no video"
**Solution:** This is the camera-off feature working! Check if sender disabled camera

### Problem: "Black screen instead of avatar"
**Solution:** This was a bug we fixed - upgrade to latest code

### Problem: "Slow/Laggy Video"
**Solution:**
1. Check internet bandwidth (run speed test)
2. Reduce video resolution (will add setting soon)
3. Close other apps using internet
4. Try moving closer to WiFi router

For more help, see [docs/TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) (detailed debugging guide).

---

## 🎵 Sounds Directory

-   **`sounds/` folder** (You need to add these!)
    -   **`ringtone.mp3`**: Plays when YOU receive a call
    -   **`ringback.mp3`**: Plays when YOU are calling someone else
    -   Don't have audio files? Download from [Freesound.org](https://freesound.org) or use [Zapsplat](https://www.zapsplat.com)

---

## 🚀 How a Call Happens (Step-by-Step)

If you're debugging a "Call Failed" issue, follow this flow:

1.  **Initiation:** User A clicks "Call". `dashboard.js` sends a signal via `api/send_signal.php`.
2.  **Notification:** User B's `dashboard.js` (polling `api/get_signals.php`) sees the signal and rings.
3.  **Acceptance:** User B clicks "Accept". Both are redirected to `call.php`.
4.  **Connection:** Both load `webrtc.js`. Connection sequence starts.
5.  **Handshake:** `webrtc.js` exchanges ICE Candidates through API (~100 signals)
6.  **Media Flow:** Once handshake complete, API no longer needed. Video streams P2P.
7.  **Active Call:** User can toggle video/audio, showing overlays in real-time.
8.  **Hangup:** Either user clicks "End Call" → sends `call-ended` signal → both close.

**Debugging:** Check browser console (F12) for errors during each step.

---

## 🔐 Security Checklist

Before deploying to production:

- [ ] Use HTTPS (SSL certificate)
- [ ] Enable password hashing (using password_hash)
- [ ] Use environment variables for database credentials (.env file)
- [ ] Add rate limiting for login attempts
- [ ] Enable CORS headers if using separate frontend
- [ ] Regular database backups
- [ ] Keep PHP, MySQL, and libraries updated
- [ ] Implement session timeout (30 min inactivity = logout)
- [ ] Use secure session cookies (httponly, secure, samesite)
- [ ] Validate all user input on backend

See [docs/CONFIG.md](docs/CONFIG.md) for detailed security practices.

---

## 🛠️ Development Tips

### Useful Developer Tools

1. **Chrome DevTools** (F12)
   - Console: See JavaScript errors and logs
   - Network: Monitor API calls and bandwidth
   - Performance: Check page load times
   - Sources: Debug JavaScript step-by-step

2. **phpMyAdmin** (http://localhost/phpmyadmin)
   - Browse database tables
   - Run custom SQL queries
   - Import/export data
   - Monitor table sizes

3. **VS Code Extensions**
   - Prettier: Auto-format code
   - PHP Intelephense: PHP code hints
   - MySQL: Manage database from editor
   - Live Server: Local development server

### Common Tasks

**Add a new feature:**
1. Update database schema (DATABASE_SETUP_GUIDE.md)
2. Create API endpoint (api/your_endpoint.php)
3. Update JavaScript to call endpoint
4. Update HTML/CSS for UI

**Debug WebRTC connection:**
```javascript
// In console (F12)
console.log(peerConnection.connectionState);  // See connection status
console.log(webRTC.isVideoEnabled);            // Check if video on
console.log(webRTC.isRemoteVideoEnabled);      // Check if other's video on
```

**Check active signals:**
```sql
-- In phpMyAdmin → SQL
SELECT * FROM signals WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE);
```

---

## 🎓 Learning Resources

### WebRTC
- [MDN WebRTC Guide](https://developer.mozilla.org/en-US/docs/Web/API/WebRTC_API)
- [WebRTC for Beginners](https://www.html5rocks.com/en/tutorials/webrtc/basics/)

### PHP/MySQL
- [PHP Official Docs](https://www.php.net/manual/)
- [MySQL Queries Explained](https://www.w3schools.com/sql/)

### Web Development
- [MDN Web Docs](https://developer.mozilla.org/)
- [CSS-Tricks](https://css-tricks.com/)

---

## 📈 Future Enhancements

Potential features for next versions:

- [ ] **Screen Sharing** - Share desktop during calls
- [ ] **Chat Messages** - Text chat during calls
- [ ] **Call Recording** - Record calls (with permission)
- [ ] **Group Calling** - 3+ people on same call
- [ ] **Phone Call API** - Dial actual phone numbers
- [ ] **Call History** - See past calls and duration
- [ ] **Better Audio** - Noise cancellation, echo reduction
- [ ] **Mobile App** - iOS/Android native apps
- [ ] **End-to-End Encryption** - SRTP for secure calls
- [ ] **Reactions** - Emoji reactions during calls

---

## 📄 License

This project is licensed under the MIT License - see LICENSE file for details.

**What this means:**
- ✅ Use in personal projects
- ✅ Use in commercial applications
- ✅ Modify and distribute
- ❌ Remove license from copies
- ❌ Hold original author liable

---

## 👥 Contributing

Found a bug or have an idea? [Open an issue](../../issues) or submit a pull request!

**Before submitting code:**
1. Test thoroughly
2. Follow existing code style
3. Add comments for complex logic
4. Update documentation

---

## 📞 Support

- 📧 Email: [your-email@example.com]
- 💬 Discord: [your-discord-link]
- 🐛 Bug Reports: GitHub Issues
- 💡 Feature Requests: GitHub Discussions

---

## 🙏 Acknowledgments

Built with:
- **WebRTC** - For peer-to-peer connections
- **PHP/MySQL** - For backend and database
- **Modern CSS** - Glassmorphism design inspiration

Special thanks to all contributors and users testing the platform!

---

## 📝 Changelog

### v2.0 (Current)
- ✨ Complete Wartalaap rebranding
- 🎨 Glassmorphism UI design
- 🐛 Fixed video-off overlay bug
- 🎤 Added mic-off indicator
- 📚 Comprehensive documentation

### v1.0
- Initial VideoChat release
- Basic P2P calling
- User authentication

---

**Happy Coding! 🚀**

*Last Updated: January 7, 2026*
