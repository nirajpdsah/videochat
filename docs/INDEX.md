# Wartalaap Documentation Index

Welcome to the Wartalaap technical documentation! This guide will help you understand and work with our video calling application.

## 📚 Quick Links

### System Design
- **[Architecture Overview](ARCHITECTURE.md)** - System architecture, data flow, and technologies
  - Understanding the overall system design
  - Data flow diagrams
  - Technology stack
  - Feature list

### Core Components

#### Frontend
- **[call.php](CALL.md)** - Video call interface
  - HTML structure
  - Video containers and overlays
  - Control buttons
  - UI states and styling
  - Event flow
  
- **[webrtc.js](WEBRTC.md)** - WebRTC connection logic ⭐ MUST READ
  - Global variables
  - Initialization flow
  - Key functions (setupMediaOnly, startCall, createPeerConnection, etc.)
  - Signal handling
  - Audio/video toggle functionality
  - Debugging tips

- **[dashboard.js](DASHBOARD.md)** - User interface interactions
  - User list display
  - Call initiation
  - Real-time updates
  - Error handling

#### Backend
- **[API Endpoints](API.md)** - REST API documentation
  - Authentication (register, login)
  - Signaling (send_signal, get_signals)
  - User management
  - Complete request/response examples
  - Error handling

#### Styling
- **[style.css](STYLE.md)** - Complete UI styling
  - CSS variables and color palette
  - Dark theme with glassmorphism effect
  - Z-index hierarchy for overlays
  - Responsive design (mobile, tablet, desktop)
  - Animation effects (pulse, fade, slide)
  - Layout systems (flexbox, grid)

### Configuration & Database
- **[config.php](CONFIG.md)** - Database and environment setup
  - Environment variable loading (.env files)
  - Database connection (MySQLi)
  - Authentication functions (isLoggedIn, getCurrentUser)
  - Input sanitization (XSS prevention)
  - Multi-host support (InfinityFree, Railway, local)
  - Security best practices

- **[database.sql](DATABASE.md)** - Complete database schema
  - Users table structure
  - Signals table with 10 signal types
  - Messages table for chat
  - Foreign keys and relationships
  - Indexes for performance
  - Common SQL queries
  - Maintenance and cleanup scripts

---

## 🚀 Getting Started

### For New Developers

1. **Start here**: [Architecture Overview](ARCHITECTURE.md)
   - Understand the system design
   - Learn about signal types
   - See the data flow

2. **Then read**: [webrtc.js Documentation](WEBRTC.md)
   - Most critical component
   - Understand P2P connections
   - Learn signal handling

3. **Study**: [API Endpoints](API.md)
   - Backend communication
   - Request/response formats
   - Error handling

4. **Explore**: [call.php](CALL.md)
   - UI structure
   - HTML elements
   - CSS classes

5. **Reference**: [style.css](STYLE.md)
   - Look up styling
   - Understand themes
   - Animations

### For Frontend Work
1. [call.php](CALL.md) - HTML/PHP structure
2. [style.css](STYLE.md) - Styling
3. [webrtc.js](WEBRTC.md) - JavaScript logic
4. [dashboard.js](DASHBOARD.md) - User interactions

### For Backend Work
1. [API Endpoints](API.md) - Endpoint specifications
2. [config.php](CONFIG.md) - Configuration
3. [database.sql](DATABASE.md) - Schema
4. [webrtc.js](WEBRTC.md) - Signal handling

### For Debugging
1. [webrtc.js](WEBRTC.md#debugging-tips) - Debugging tips
2. [API.md](API.md#error-handling) - Error responses
3. [ARCHITECTURE.md](ARCHITECTURE.md) - Data flow diagrams

---

## 📋 File Documentation Map

### Root Level Files
| File | Purpose | Type |
|------|---------|------|
| `index.php` | Landing page | HTML/PHP |
| `login.php` | User login | HTML/PHP |
| `signup.php` | User registration | HTML/PHP |
| `dashboard.php` | User list & calls | HTML/PHP |
| `call.php` | Video call UI | HTML/PHP |
| `config.php` | Database config | PHP |

### API Endpoints (`/api`)
| File | Purpose | Docs |
|------|---------|------|
| `register.php` | User registration | [API.md](API.md#2-registerphp) |
| `authenticate.php` | User login | [API.md](API.md#2-authenticatephp) |
| `send_signal.php` | Send WebRTC signal | [API.md](API.md#3-send_signalphp) |
| `get_signals.php` | Receive WebRTC signal | [API.md](API.md#4-get_signalsphp) |
| `delete_signal.php` | Clean up signals | [API.md](API.md#5-delete_signalphp) |
| `get_users.php` | List online users | [API.md](API.md#6-get_usersphp) |
| `update_status.php` | Update user status | [API.md](API.md#7-update_statusphp) |
| `send_call_request.php` | Initiate call | [API.md](API.md#8-send_call_requestphp) |

### Static Assets (`/css` & `/js`)
| File | Purpose | Docs |
|------|---------|------|
| `css/style.css` | All styling | [STYLE.md](STYLE.md) |
| `js/webrtc.js` | WebRTC logic | [WEBRTC.md](WEBRTC.md) |
| `js/dashboard.js` | UI interactions | [DASHBOARD.md](DASHBOARD.md) |

---

## 🔑 Key Concepts

### WebRTC Connection Flow
```
1. Caller sends call-request signal
2. Receiver accepts with call-accepted
3. Caller creates WebRTC offer
4. Receiver creates WebRTC answer
5. Exchange ICE candidates for NAT traversal
6. Connection established → Video/audio flows P2P
```

### Signal Types (10 Total)
- **Call Control**: call-request, call-accepted, call-rejected, call-ended
- **WebRTC Negotiation**: offer, answer, ice-candidate, receiver-ready
- **Real-time Control**: video-status, audio-status

### Important Flags
- `isInitiator` - Are we the caller or receiver?
- `isCallConnected` - Has P2P connection been established?
- `isVideoEnabled` - Is our camera on?
- `isAudioEnabled` - Is our mic on?
- `isRemoteVideoEnabled` - Is remote peer's camera on?
- `isRemoteAudioEnabled` - Is remote peer's mic on?

### UI Overlays
1. **video-info** - Shows during "Connecting..." (connection status)
2. **video-off-overlay** - Shows when camera is disabled (avatar + text)
3. **mic-off-indicator** - Shows when mic is disabled (red badge)

---

## 🐛 Common Issues

### "Connecting..." never completes
- Check STUN server connectivity
- Look for ICE candidate errors in console
- Verify both users have media permissions granted

### Camera-off overlay shows during initial connection
- Check if `isCallConnected` flag is properly set to false initially
- Ensure overlays only show when `isCallConnected === true`

### Mic-off badge not appearing
- Verify `audio-status` signal type is in database ENUM
- Check if signal is being received (F12 console)
- Ensure `isCallConnected` is true

### Video appears but audio doesn't work
- Check microphone permissions
- Verify audio track is being added to peer connection
- Look for audio errors in console

---

## 📞 API Usage Examples

### Making a Call
```javascript
// 1. Send call request
POST /api/send_signal.php
{
    "to_user_id": 3,
    "signal_type": "call-request",
    "signal_data": {},
    "call_type": "video"
}

// 2. Receiver polls for request
GET /api/get_signals.php
// Returns: [{ signal_type: "call-request", ... }]

// 3. Receiver accepts
POST /api/send_signal.php
{
    "to_user_id": 2,
    "signal_type": "call-accepted",
    "signal_data": {},
    "call_type": "video"
}

// 4. Caller receives acceptance
// webrtc.js polls and starts WebRTC negotiation
```

### Toggling Camera
```javascript
// In webrtc.js toggleVideo():
sendSignal('video-status', { enabled: isVideoEnabled });

// checkForSignals() on receiver:
case 'video-status':
    isRemoteVideoEnabled = signal.signal_data.enabled;
    // Show/hide remoteVideoOffOverlay
```

---

## 🔒 Security Notes

- All inputs validated and sanitized
- SQL injection prevention via prepared statements
- XSS prevention via htmlspecialchars()
- Passwords hashed with password_hash()
- Session-based authentication
- HTTPS recommended for production

---

## 📈 Performance

- Signal polling: 200ms interval
- Media: 1280x720 video, auto-adaptive audio
- Connection time: 2-10 seconds typical
- Bandwidth: 500kbps - 2.5mbps

---

## 🛠️ Troubleshooting Checklist

- [ ] Database connection working?
- [ ] PHP version >= 7.4?
- [ ] HTTPS or localhost (for camera access)?
- [ ] Browser permissions granted?
- [ ] WebRTC STUN servers reachable?
- [ ] All database migrations applied?
- [ ] JavaScript console errors present?
- [ ] Network inspection shows signals in DB?

---

## 📝 Contributing

When adding new features:
1. Update relevant documentation file
2. Add to this index
3. Include code examples
4. Document error cases
5. Add to "Future Enhancements" if applicable

---

## 📞 Support Resources

**Browser DevTools (F12):**
- Check Network tab for API calls
- Look for JavaScript errors in Console
- Monitor WebRTC connection in Application → Storage

**Database Inspection:**
- Open phpMyAdmin
- Check `signals` table for pending signals
- Verify `users` table status

**Log Files:**
- PHP error logs: `/var/log/php-errors.log`
- Browser console: F12 → Console tab

---

**Last Updated**: January 7, 2026
**Version**: 2.0 (Complete Documentation)
**Status**: Ready for Team Review ✅

---

**Want to contribute? Check individual documentation files for TODOs and enhancement notes!**
