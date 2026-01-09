# API Endpoints Documentation

## Overview
All backend API endpoints are in the `/api` folder. They handle:
- User authentication & registration
- WebRTC signal exchange
- User status management
- Message delivery

## Authentication Flow

### 1. register.php - User Registration
**Endpoint:** `POST /api/register.php`

```javascript
// Request
{
    "username": "john_doe",
    "email": "john@example.com",
    "password": "secure_password"
}

// Response (Success)
{
    "success": true,
    "message": "User registered successfully",
    "user": {
        "id": 5,
        "username": "john_doe",
        "email": "john@example.com"
    }
}

// Response (Error)
{
    "success": false,
    "message": "Username already exists"
}
```

**Validations:**
- Username: 3-50 characters, unique
- Email: Valid format, unique
- Password: Hashed with password_hash()
- XSS Prevention: htmlspecialchars()

**Database:**
```sql
INSERT INTO users (username, email, password, status)
VALUES (?, ?, password_hash(?), 'offline');
```

---

### 2. authenticate.php - User Login
**Endpoint:** `POST /api/authenticate.php`

```javascript
// Request
{
    "username": "john_doe",
    "password": "secure_password"
}

// Response (Success)
{
    "success": true,
    "message": "Logged in successfully",
    "user": {
        "id": 5,
        "username": "john_doe",
        "email": "john@example.com"
    }
}

// Response (Error)
{
    "success": false,
    "message": "Invalid credentials"
}
```

**Process:**
1. Find user by username
2. Verify password with password_verify()
3. Create PHP session: `$_SESSION['user_id']`
4. Set user status to 'online'

---

## Signaling Endpoints

### 3. send_signal.php - Send WebRTC Signal
**Endpoint:** `POST /api/send_signal.php`

```javascript
// Request
{
    "to_user_id": 3,
    "signal_type": "offer",  // See signal types table below
    "signal_data": { /* depends on signal type */ },
    "call_type": "video"
}

// Response (Success)
{
    "success": true,
    "message": "Signal sent",
    "signal_id": 142,
    "debug": {
        "sent_signal_type": "offer",
        "db_signal_type": "offer"
    }
}

// Response (Error)
{
    "success": false,
    "message": "Invalid signal type: unknown"
}
```

**Valid Signal Types:**
| Type | Data Structure | Direction | Frequency |
|------|---|---|---|
| `offer` | `{type: "offer", sdp: "..."}` | Caller → Receiver | Once |
| `answer` | `{type: "answer", sdp: "..."}` | Receiver → Caller | Once |
| `ice-candidate` | `{candidate: "...", ...}` | Both directions | Multiple |
| `call-request` | `{request: true}` | Caller → Receiver | Once |
| `call-accepted` | `{accepted: true}` | Receiver → Caller | Once |
| `call-rejected` | `{reason: "busy"}` | Receiver → Caller | Once |
| `call-ended` | `{ended: true}` | Both directions | Once |
| `receiver-ready` | `{ready: true}` | Receiver → Caller | Once |
| `video-status` | `{enabled: true/false}` | Both directions | Per toggle |
| `audio-status` | `{enabled: true/false}` | Both directions | Per toggle |

**Database Storage:**
```sql
INSERT INTO signals (from_user_id, to_user_id, signal_type, signal_data, call_type)
VALUES ($from_user_id, $to_user_id, '$signal_type', '$signal_data_json', '$call_type');
```

---

### 4. get_signals.php - Retrieve Pending Signals
**Endpoint:** `GET /api/get_signals.php`

```javascript
// Request (no body, uses session)

// Response (Success)
{
    "success": true,
    "signals": [
        {
            "id": 142,
            "from_user_id": 3,
            "from_username": "alice",
            "from_profile_picture": "alice.jpg",
            "signal_type": "offer",
            "signal_data": {
                "type": "offer",
                "sdp": "v=0\r\no=- ..."
            },
            "call_type": "video"
        },
        {
            "id": 143,
            "from_user_id": 3,
            "from_username": "alice",
            "signal_type": "video-status",
            "signal_data": {
                "enabled": false
            },
            "call_type": "video"
        }
    ],
    "has_is_read_column": true
}

// Response (No signals)
{
    "success": true,
    "signals": [],
    "has_is_read_column": true
}
```

**Process:**
1. Get unread signals for current user
2. Parse signal_data from JSON
3. Mark signals as read (except call-request, call-accepted)
4. Return with sender info

**Database:**
```sql
SELECT s.id, s.from_user_id, s.signal_type, s.signal_data, u.username
FROM signals s
JOIN users u ON s.from_user_id = u.id
WHERE s.to_user_id = ? AND s.is_read = 0
ORDER BY s.created_at ASC;
```

---

### 5. delete_signal.php - Delete Signal
**Endpoint:** `POST /api/delete_signal.php`

```javascript
// Request
{
    "from_user_id": 3,
    "signal_type": "call-ended"
}

// Response
{
    "success": true,
    "message": "Signal deleted",
    "deleted_count": 1
}
```

**Purpose:** Remove old signals (especially call-ended)

---

## User Management Endpoints

### 6. get_users.php - List Online Users
**Endpoint:** `GET /api/get_users.php`

```javascript
// Request (no body)

// Response
{
    "success": true,
    "users": [
        {
            "id": 2,
            "username": "bob",
            "profile_picture": "bob.jpg",
            "status": "online",
            "last_seen": "2026-01-07 14:25:30"
        },
        {
            "id": 3,
            "username": "alice",
            "profile_picture": "alice.jpg",
            "status": "online",
            "last_seen": "2026-01-07 14:30:45"
        },
        {
            "id": 5,
            "username": "charlie",
            "profile_picture": "charlie.jpg",
            "status": "on_call",
            "last_seen": "2026-01-07 14:31:00"
        }
    ]
}
```

**Excludes:** Current logged-in user

**Database:**
```sql
SELECT id, username, profile_picture, status, last_seen
FROM users
WHERE id != ? AND status IN ('online', 'on_call')
ORDER BY username ASC;
```

---

### 7. update_status.php - Update User Status
**Endpoint:** `POST /api/update_status.php`

```javascript
// Request
{
    "status": "online"  // or "offline", "on_call"
}

// Response
{
    "success": true,
    "message": "Status updated",
    "new_status": "online"
}
```

**Status Values:**
| Status | Meaning | When Set |
|--------|---------|----------|
| `online` | Available for calls | Login, call end |
| `offline` | Not available | Logout |
| `on_call` | Currently in a call | Call start |

**Database:**
```sql
UPDATE users SET status = ?, last_seen = NOW() WHERE id = ?;
```

---

### 8. send_call_request.php - Initiate Call
**Endpoint:** `POST /api/send_call_request.php`

```javascript
// Request
{
    "to_user_id": 3,
    "call_type": "video"  // or "audio"
}

// Response
{
    "success": true,
    "message": "Call request sent",
    "call_url": "call.php?user_id=3&type=video&initiator=true"
}
```

**Flow:**
1. Send 'call-request' signal to recipient
2. Redirect caller to call.php as initiator

---

## Message Endpoints (Future)

### 9. messages.php - Send Message
**Endpoint:** `POST /api/messages.php`

```javascript
// Request
{
    "to_user_id": 3,
    "message": "Hello! How are you?"
}

// Response
{
    "success": true,
    "message_id": 256,
    "created_at": "2026-01-07 14:35:00"
}
```

---

## Error Handling

### Standard Error Response
```javascript
{
    "success": false,
    "message": "Unauthorized",
    "error_code": 401
}
```

**HTTP Status Codes:**
| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Not logged in |
| 500 | Server Error - Database or PHP error |

### Common Errors
```javascript
// Not logged in
{
    "success": false,
    "message": "Unauthorized"
}

// Invalid signal type
{
    "success": false,
    "message": "Invalid signal type: unknown"
}

// Database connection error
{
    "success": false,
    "message": "Database error: connection failed"
}

// JSON decode error
{
    "success": false,
    "message": "Invalid JSON",
    "error_code": 400
}
```

---

## Request/Response Examples

### Example 1: Complete Call Flow

```
STEP 1: User A calls User B
├─ POST /api/send_signal.php
│  └─ Type: call-request
│  └─ To: User B ID
│
STEP 2: User B receives notification
├─ GET /api/get_signals.php (polling)
│  └─ Returns: call-request signal
│
STEP 3: User B accepts
├─ POST /api/send_signal.php
│  └─ Type: call-accepted
│  └─ To: User A ID
│
STEP 4: User A receives acceptance
├─ GET /api/get_signals.php (polling)
│  └─ Returns: call-accepted signal
│
STEP 5: WebRTC Negotiation
├─ POST /api/send_signal.php (User A)
│  └─ Type: offer (SDP)
├─ GET /api/get_signals.php (User B)
│  └─ Receives: offer
├─ POST /api/send_signal.php (User B)
│  └─ Type: answer (SDP)
├─ GET /api/get_signals.php (User A)
│  └─ Receives: answer
├─ Multiple ICE candidate exchanges
│  └─ Types: ice-candidate
│
STEP 6: Connection Established
├─ Video/Audio flows P2P (no server)
├─ Control signals still via server:
│  ├─ video-status (when toggling camera)
│  ├─ audio-status (when toggling mic)
│
STEP 7: End Call
├─ POST /api/send_signal.php
│  └─ Type: call-ended
├─ POST /api/update_status.php
│  └─ Status: online
```

---

## Security Measures

✅ **Session Verification**: All endpoints check `isLoggedIn()`
✅ **Input Validation**: Type checking and intval() for IDs
✅ **SQL Injection**: Prepared statements with bind_param()
✅ **XSS Prevention**: htmlspecialchars() on usernames
✅ **CORS**: Same-origin only (no cross-domain requests)
✅ **Password Hashing**: password_hash() and password_verify()

---

## Rate Limiting

Currently **no rate limiting** implemented. For production:
- Limit polls to 1 per second per user
- Limit signals to 10 per minute per user
- Implement CAPTCHA for registration

---

## Database Schema Reference

### signals table
```sql
CREATE TABLE signals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_user_id INT NOT NULL,
    to_user_id INT NOT NULL,
    signal_type ENUM(...) NOT NULL,
    signal_data TEXT NOT NULL,
    call_type ENUM('video', 'audio') DEFAULT 'video',
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### users table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default-avatar.png',
    status ENUM('online', 'offline', 'on_call') DEFAULT 'offline',
    last_seen DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Testing with cURL

```bash
# Register user
curl -X POST http://localhost/videochat/api/register.php \
  -H "Content-Type: application/json" \
  -d '{"username":"test","email":"test@test.com","password":"pass123"}'

# Login
curl -X POST http://localhost/videochat/api/authenticate.php \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"pass123"}'

# Get online users
curl http://localhost/videochat/api/get_users.php \
  -b "PHPSESSID=your_session_id"

# Send signal
curl -X POST http://localhost/videochat/api/send_signal.php \
  -H "Content-Type: application/json" \
  -b "PHPSESSID=your_session_id" \
  -d '{"to_user_id":2,"signal_type":"call-request","signal_data":{},"call_type":"video"}'
```

---

**Last Updated**: January 7, 2026
**Status**: Fully Functional ✅
