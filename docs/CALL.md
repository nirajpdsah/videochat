# call.php - Video Call Interface

## Overview
`call.php` is the main video calling interface where users conduct peer-to-peer video and audio calls. It provides the UI for both the remote and local video streams, control buttons, and status indicators.

## File Structure

### 1. PHP Backend (Lines 1-44)
```php
// Authentication check
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get call parameters from URL
$remote_user_id = $_GET['user_id'];    // Who are we calling?
$call_type = $_GET['type'];             // 'video' or 'audio'?
$is_initiator = $_GET['initiator'];     // Are we initiating?
```

**Key Parameters:**
| Parameter | Example | Purpose |
|-----------|---------|---------|
| `user_id` | `3` | Remote user's database ID |
| `type` | `video` or `audio` | Call type determines UI |
| `initiator` | `true` or `false` | Caller vs. receiver |

### 2. HTML Structure (Lines 50-140)

#### Remote Video Container
```html
<div id="remoteVideoContainer" class="video-container remote-video">
    <!-- Video stream from remote peer -->
    <video id="remoteVideo" autoplay playsinline></video>
    
    <!-- Camera-off overlay (shown when remote peer disables camera) -->
    <div class="video-off-overlay" id="remoteVideoOffOverlay">
        <img class="video-off-avatar">      <!-- Remote user's avatar -->
        <p class="video-off-text">         <!-- Remote user's name -->
        <p class="video-off-subtext">       <!-- "Camera is off" text -->
    </div>
    
    <!-- Mic-off indicator (red badge when remote peer mutes) -->
    <div class="mic-off-indicator" id="remoteMicOffIndicator">
        <svg><!-- Mic-off icon --></svg>
    </div>
    
    <!-- Connection status overlay (shown while "Connecting...") -->
    <div class="video-info">
        <img class="call-avatar">           <!-- Remote user's avatar -->
        <h3><!-- Remote username --></h3>
        <p id="callStatus">Connecting...</p> <!-- Real-time status -->
    </div>
</div>
```

**Remote Video Layers (Z-Index Order):**
1. `remoteVideo` (z-index: auto) - Video stream
2. `video-info` (z-index: above video) - Connection status
3. `remoteVideoOffOverlay` (z-index: 100) - Camera-off avatar
4. `remoteMicOffIndicator` (z-index: 150) - Mic-off badge

#### Local Video Container
```html
<div id="localVideoContainer" class="video-container local-video">
    <!-- Your video stream (mirror/PiP) -->
    <video id="localVideo" autoplay muted playsinline></video>
    
    <!-- Camera-off overlay (your perspective) -->
    <div class="video-off-overlay" id="localVideoOffOverlay">
        <img class="video-off-avatar local">  <!-- Your avatar -->
    </div>
    
    <!-- Mic-off indicator (your perspective) -->
    <div class="mic-off-indicator" id="localMicOffIndicator">
        <svg><!-- Mic-off icon --></svg>
    </div>
    
    <!-- Label -->
    <p>You</p>
</div>
```

### 3. Control Buttons
```html
<div class="call-controls">
    <!-- Audio Toggle -->
    <button id="toggleAudioBtn" onclick="toggleAudio()">
        <span id="audioIcon"><!-- Mic icon --></span>
    </button>
    
    <!-- Video Toggle (only shown for video calls) -->
    <?php if ($call_type == 'video'): ?>
    <button id="toggleVideoBtn" onclick="toggleVideo()">
        <span id="videoIcon"><!-- Camera icon --></span>
    </button>
    <?php endif; ?>
    
    <!-- End Call -->
    <button class="end-call-btn" onclick="endCall()">
        <span><!-- Phone-down icon --></span>
    </button>
</div>
```

**Button Behaviors:**
| Button | Function | Called | Effect |
|--------|----------|--------|--------|
| Audio | `toggleAudio()` | webrtc.js | Mute/unmute mic, send `audio-status` signal |
| Video | `toggleVideo()` | webrtc.js | Turn camera on/off, send `video-status` signal |
| End Call | `endCall()` | webrtc.js | Close connection, send `call-ended` signal |

### 4. JavaScript Initialization (Lines 142-167)
```php
<script>
    const currentUserId = <?php echo $current_user['id']; ?>;
    const remoteUserId = <?php echo $remote_user_id; ?>;
    const callType = '<?php echo $call_type; ?>';
    const isInitiator = <?php echo $is_initiator ? 'true' : 'false'; ?>;
</script>
<script src="js/webrtc.js?v=<?php echo time(); ?>"></script>
```

**Why time() caching?**
- `?v=<?php echo time(); ?>` forces browser to reload JS every page refresh
- Prevents stale JavaScript from breaking calls
- Important during development

## UI States

### State 1: Connecting
```
┌────────────────────────────┐
│   Remote User Name         │
│   [Avatar]                 │
│   Connecting...            │
└────────────────────────────┘
```
- **Shown**: Before WebRTC connection established
- **Duration**: 2-5 seconds
- **Overlay**: `video-info` visible, video stream loading
- **Action**: Click controls disabled until connected

### State 2: Connected (Normal)
```
┌────────────────────────────┐
│   [Remote Video Stream]    │
│   ┌──────────────────────┐ │
│   │ [Your PiP Video]     │ │
│   │ You                  │ │
│   └──────────────────────┘ │
└────────────────────────────┘
```
- **Shown**: After WebRTC connection established
- **Overlays**: All hidden
- **Actions**: Full control (audio/video/end call)

### State 3: Camera Off
```
┌────────────────────────────┐
│   [Avatar]                 │
│   Remote User Name         │
│   Camera is off            │
│   🔴 (Mic-off badge)       │
└────────────────────────────┘
```
- **Shown**: When remote peer disables camera
- **Overlay**: `video-off-overlay` visible
- **Badge**: `mic-off-indicator` if also muted
- **Status**: Call continues, audio only visible

### State 4: Mic Off
```
┌────────────────────────────┐
│   [Remote Video]           │
│   🔴 Mic-off badge         │
└────────────────────────────┘
```
- **Shown**: When remote peer mutes mic
- **Badge**: Red circular indicator (bottom-left)
- **Styling**: Pulsing animation
- **Size**: 40px on remote, 28px on local PiP

## CSS Classes Reference

| Class | Purpose | Applied To |
|-------|---------|-----------|
| `.video-container` | Base container styling | Both video containers |
| `.remote-video` | Remote video layout | Remote container |
| `.local-video` | Local video PiP layout | Local container |
| `.call-controls` | Control button container | Buttons div |
| `.control-btn` | Individual button styling | Each button |
| `.end-call-btn` | End call button special styling | End call button |
| `.video-off-overlay` | Camera-off overlay base | Both overlays |
| `.video-off-overlay.visible` | Show camera-off overlay | When activated |
| `.video-off-avatar` | Avatar in overlay | Avatar images |
| `.video-off-text` | Username in overlay | Username p tag |
| `.video-off-subtext` | "Camera is off" text | Subtext p tag |
| `.mic-off-indicator` | Mic-off badge base | Both indicators |
| `.mic-off-indicator.visible` | Show mic-off badge | When activated |
| `.video-info` | Connection status overlay | Status div |
| `.call-avatar` | Avatar in status overlay | Avatar in status |
| `.call-container` | Main call interface | Parent div |
| `.call-body` | Body styling | Body tag |

## Event Flow

```
Page Load (DOMContentLoaded in webrtc.js)
    ↓
    ├─→ Check if logged in (PHP redirect)
    ├─→ Get remote user details from DB (PHP)
    ├─→ Set user status to "on_call" (PHP)
    ├─→ Load HTML with video containers
    ├─→ Load webrtc.js with global variables
    ├─→ webrtc.js DOMContentLoaded fires
    │
    ├─→ [If Receiver]
    │   ├─ setupMediaOnly() - Request camera/mic permissions
    │   ├─ Send "receiver-ready" signal
    │   ├─ Start polling for "offer" signal
    │
    └─→ [If Initiator]
        ├─ setupMediaOnly() - Request camera/mic permissions
        ├─ createPeerConnection()
        ├─ createOffer() and send it
        ├─ Start polling for "answer" signal

User clicks "Camera Off"
    ↓
    ├─→ toggleVideo() fires
    ├─→ Disable video track
    ├─→ Show localVideoOffOverlay (add 'visible' class)
    ├─→ Send "video-status" signal with {enabled: false}
    │
    └─→ Remote peer receives signal
        ├─ Update isRemoteVideoEnabled flag
        ├─ Show remoteVideoOffOverlay (add 'visible' class)
        ├─ Remote user sees avatar + "Camera is off"

User clicks "Mic Off"
    ↓
    ├─→ toggleAudio() fires
    ├─→ Disable audio track
    ├─→ Show localMicOffIndicator (add 'visible' class)
    ├─→ Send "audio-status" signal with {enabled: false}
    │
    └─→ Remote peer receives signal
        ├─ Update isRemoteAudioEnabled flag
        ├─ Show remoteMicOffIndicator (add 'visible' class)
        ├─ Remote user sees red badge
```

## Important Notes

### Video Attributes
```html
<video id="remoteVideo" autoplay playsinline></video>
```
- `autoplay` - Start playing immediately when stream available
- `playsinline` - Play inline on mobile (not fullscreen)
- `muted` on local video prevents echo

### Avatar Display
```php
src="uploads/<?php echo !empty($remote_user['profile_picture']) 
    ? $remote_user['profile_picture'] 
    : 'default-avatar.png'; ?>"
```
- Falls back to `default-avatar.png` if user has no custom picture
- Upload directory: `/uploads/`
- Supported formats: PNG, JPG, GIF

### Cache Busting
```php
<link href="css/style.css?v=<?php echo time(); ?>">
<script src="js/webrtc.js?v=<?php echo time(); ?>">
```
- `time()` ensures fresh load on every page refresh
- Prevents browser caching issues during development

## Security Measures

✅ **Authentication**: `isLoggedIn()` check prevents unauthorized access
✅ **Input Validation**: `intval($_GET['user_id'])` prevents SQL injection
✅ **XSS Prevention**: `htmlspecialchars()` on usernames
✅ **Session-based**: Uses PHP sessions, not URL tokens
✅ **HTTPS ready**: Supports both HTTP (dev) and HTTPS (prod)

## Common Issues & Solutions

### Problem: Video doesn't appear
**Solution**: Check browser console (F12) for errors in webrtc.js

### Problem: "Connecting..." stays forever
**Solution**: WebRTC ICE candidates failing. Check STUN server connectivity

### Problem: Mic/Camera permission denied
**Solution**: Browser permissions must be granted. Clear and re-grant access

### Problem: Avatar overlays visible during call
**Solution**: Ensure `isCallConnected` flag is true before showing overlays

## Related Files
- [webrtc.js](WEBRTC.md) - Connection logic (must read!)
- [style.css](STYLE.md) - All styling
- [API Endpoints](API.md) - Backend signals
- [dashboard.php](DASHBOARD.md) - Call initiation

---

**Last Updated**: January 7, 2026
**Status**: Fully Functional ✅
