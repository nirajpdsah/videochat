# Wartalaap - System Architecture

## Overview
Wartalaap (वार्तालाप - "Conversation") is a peer-to-peer video/audio calling application built with WebRTC, PHP, and MySQL.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER (Browser)                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Frontend (HTML/CSS)                                      │   │
│  │ - index.php (Landing)        - call.php (Call Interface)│   │
│  │ - dashboard.php (User List)  - login/signup.php (Auth)  │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ JavaScript (WebRTC & UI Logic)                           │   │
│  │ - webrtc.js (P2P Connection) - dashboard.js (UI Events) │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              ↓ HTTP/AJAX ↓
┌─────────────────────────────────────────────────────────────────┐
│                     API LAYER (REST Endpoints)                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Authentication        │ Signaling       │ User Management │   │
│  │ - register.php       │ - send_signal   │ - get_users     │   │
│  │ - authenticate.php   │ - get_signals   │ - update_status │   │
│  │                      │ - delete_signal │                 │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                              ↓ MySQL ↓
┌─────────────────────────────────────────────────────────────────┐
│                       DATABASE LAYER (MySQL)                     │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │ Tables:                                                   │   │
│  │ - users (id, username, email, password, profile_picture) │   │
│  │ - signals (id, from_user_id, to_user_id, signal_type,   │   │
│  │           signal_data, call_type, created_at, is_read)   │   │
│  │ - messages (id, from_user_id, to_user_id, message,      │   │
│  │            created_at, is_read)                           │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow

### Call Initiation
```
User A (Caller)                Database              User B (Receiver)
      │                            │                        │
      ├─ Send call-request signal ─→ Stored in DB           │
      │                            │                        │
      │                            ← Polling (get_signals)──┤
      │                            │                        │
      │                            │ Receives call-request  │
      │                            │                        │
      │                            ← Sends call-accepted ──┤
      │                            │                        │
      ├─ Receives accepted ────────│                        │
      │                            │                        │
      ├─ Creates WebRTC Offer ──→ send_signal.php           │
      │                            │ Stores as 'offer'      │
      │                            │                        │
      │                            ← Polling (get_signals)──┤
      │                            │                        │
      │                            │ Receives offer         │
      │                            │ Creates answer         │
      │                            │                        │
      │                            ← answer signal ─────────┤
      │                            │                        │
      ├─ Receives answer ──────────│                        │
      │                            │                        │
      ├─ Exchange ICE candidates ──→← via get/send_signal   │
      │                            │                        │
      ╔═══════════════════════════════════════════════════════╗
      ║    WebRTC Connection Established (P2P)               ║
      ║    Video/Audio streams flow directly peer-to-peer    ║
      ╚═══════════════════════════════════════════════════════╝
```

### Real-time Communication
After WebRTC connection:
- **Video/Audio**: Flows directly P2P (not through server)
- **Control signals**: Still use server (video-status, audio-status, call-ended)
- **Polling**: Every 200ms for new signals

## Key Technologies

| Component | Technology | Purpose |
|-----------|-----------|---------|
| **Frontend** | HTML5, CSS3 | UI Structure & Styling |
| **Video/Audio** | WebRTC API | Peer-to-peer media streaming |
| **Signaling** | AJAX/JSON | WebRTC connection setup |
| **Backend** | PHP 7.4+ | Server logic & API endpoints |
| **Database** | MySQL 5.7+ | User data & signaling storage |
| **Real-time** | Polling (200ms) | Signal retrieval from DB |
| **Authentication** | PHP Sessions | User login persistence |

## Signal Types

| Signal Type | Direction | Purpose | Frequency |
|------------|-----------|---------|-----------|
| `call-request` | Caller → Receiver | Initiate call | Once |
| `call-accepted` | Receiver → Caller | Accept incoming call | Once |
| `call-rejected` | Receiver → Caller | Reject call | Once |
| `call-ended` | Both directions | Terminate call | Once |
| `receiver-ready` | Receiver → Caller | Indicate ready state | Once |
| `offer` | Caller → Receiver | WebRTC offer | Once |
| `answer` | Receiver → Caller | WebRTC answer | Once |
| `ice-candidate` | Both directions | NAT traversal info | Multiple |
| `video-status` | Both directions | Camera on/off | Every toggle |
| `audio-status` | Both directions | Mic on/off | Every toggle |

## File Organization

```
videochat/
├── index.php              ← Landing page
├── login.php              ← Login form
├── signup.php             ← Registration form
├── dashboard.php          ← User list & call initiation
├── call.php               ← Video call interface
├── config.php             ← Database & auth config
├── database.sql           ← Schema
│
├── api/
│   ├── register.php              ← User registration
│   ├── authenticate.php          ← User login
│   ├── send_signal.php           ← Send WebRTC signals
│   ├── get_signals.php           ← Retrieve pending signals
│   ├── delete_signal.php         ← Mark signals as read
│   ├── get_users.php             ← List online users
│   ├── update_status.php         ← Update user status
│   ├── messages.php              ← Chat (future)
│   └── send_call_request.php     ← Initiate call
│
├── css/
│   └── style.css          ← Complete application styling
│
├── js/
│   ├── webrtc.js          ← WebRTC connection logic
│   ├── dashboard.js       ← Dashboard interactions
│   └── cache-buster.js    ← Force refresh mechanism
│
├── uploads/
│   ├── logo.png           ← Wartalaap logo
│   ├── default-avatar.png ← Default user avatar
│   └── [user avatars]     ← User profile pictures
│
└── docs/
    ├── ARCHITECTURE.md    ← This file
    ├── CALL.md            ← call.php documentation
    ├── WEBRTC.md          ← webrtc.js documentation
    ├── STYLE.md           ← style.css documentation
    ├── API.md             ← API endpoints documentation
    └── [other docs]       ← Individual file docs
```

## Key Features

✅ **Video Calling** - Peer-to-peer video with WebRTC
✅ **Audio Calling** - Voice calls with audio-only mode
✅ **Real-time Status** - Camera & mic on/off indicators
✅ **User Authentication** - Secure login/registration
✅ **Online User List** - See who's available
✅ **Avatar Support** - User profile pictures
✅ **Responsive Design** - Works on desktop & mobile
✅ **WhatsApp-style UI** - Modern, familiar interface

## Security Considerations

1. **Authentication**: PHP Sessions with database user verification
2. **Input Validation**: All user inputs sanitized and validated
3. **SQL Injection**: Prepared statements for all DB queries
4. **HTTPS**: Recommended for production (required for camera access)
5. **CORS**: Not applicable (same-origin)
6. **WebRTC**: Uses STUN servers for NAT traversal

## Performance Metrics

- **Signal Polling Interval**: 200ms
- **User List Refresh**: 2000ms
- **Heartbeat Interval**: 10000ms
- **Media Bitrate**: Auto-adaptive
- **Database Indexes**: On user ID, signals, timestamps

## Deployment Platforms

✅ Local Development (XAMPP)
✅ InfinityFree (Free hosting)
✅ Railway (Premium hosting)
✅ Any PHP 7.4+ with MySQL 5.7+ host

## Future Enhancements

- [ ] Text messaging during calls
- [ ] Screen sharing
- [ ] Call recording
- [ ] Group calls
- [ ] WhatsApp integration
- [ ] Mobile app (React Native)
- [ ] WebSocket instead of polling
- [ ] End-to-end encryption

---

**Next**: Read specific documentation for individual components:
- [call.php](CALL.md) - Video call interface
- [webrtc.js](WEBRTC.md) - WebRTC connection logic
- [style.css](STYLE.md) - UI styling
- [API Endpoints](API.md) - Backend REST API
