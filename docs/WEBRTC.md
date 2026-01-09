# webrtc.js - WebRTC Connection & Call Logic

## Overview
`webrtc.js` (835 lines) is the core of Wartalaap's calling system. It handles:
- WebRTC peer-to-peer connections
- Signal polling and processing
- Media stream management
- Audio/video toggle functionality
- Call lifecycle management

## Global Variables

```javascript
const config = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' }
    ]
};

// Media states
let localStream = null;              // Your camera/mic stream
let remoteStream = null;             // Other person's stream
let peerConnection = null;           // WebRTC connection object

// Signaling states
let signalingInterval = null;        // Polling interval ID
let isAudioEnabled = true;           // Mic on/off
let isVideoEnabled = true;           // Camera on/off
let isRemoteVideoEnabled = true;     // Remote camera on/off (from signal)
let isRemoteAudioEnabled = true;     // Remote mic on/off (from signal)
let isCallConnected = false;         // Connection established?
let pendingIceCandidates = [];       // Queue for early ICE candidates
let callStarted = false;             // Prevent double-start
```

## Initialization Flow

### DOMContentLoaded Event (Lines 30-75)
Fires when HTML is fully loaded.

```javascript
document.addEventListener('DOMContentLoaded', async function () {
    // Clear old signals
    await fetch('api/get_signals.php').then(...);
    
    if (!isInitiator) {
        // Receiver side
        await setupMediaOnly();
        await sendSignal('receiver-ready', { ready: true });
        signalingInterval = setInterval(checkForSignals, 200);
    } else {
        // Caller side
        await setupMediaOnly();
        await startCall();
        signalingInterval = setInterval(checkForSignals, 200);
    }
});
```

**Execution Timeline:**
```
0ms    → Page loaded, DOMContentLoaded fires
↓
100ms  → Get previous signals from DB
↓
200ms  → Request camera/mic permissions
↓
300ms  → Permission granted (or denied)
↓
400ms  → Start polling signals every 200ms
↓
500ms+ → WebRTC negotiation begins
```

## Key Functions

### 1. setupMediaOnly() (Lines 133-160)
Requests camera and microphone permissions.

```javascript
async function setupMediaOnly() {
    const constraints = {
        audio: true,
        video: callType === 'video' 
            ? { width: 1280, height: 720 } 
            : false
    };
    
    localStream = await navigator.mediaDevices.getUserMedia(constraints);
    const localVideo = document.getElementById('localVideo');
    localVideo.srcObject = localStream;
}
```

**What it does:**
- Gets user's camera/mic stream via `getUserMedia()`
- Sets video resolution to 1280x720 for video calls
- Audio-only calls don't request video
- Handles permission errors gracefully

**Browser Permissions:**
```
User sees: "videochat.ct.ws wants access to your camera"
├─ Allow  → localStream is populated
└─ Block  → Error alert, call ends
```

### 2. startCall() (Lines 162-185)
Initiates the WebRTC connection (caller side only).

```javascript
async function startCall() {
    callStarted = true;
    
    if (!localStream) {
        await setupMediaOnly();
    }
    
    createPeerConnection();
    
    if (isInitiator) {
        await createOffer();
    }
}
```

**Flow:**
1. **Ensure media exists** - Call setupMediaOnly if needed
2. **Create RTCPeerConnection** - Establish P2P connection object
3. **Send offer** (caller only) - WebRTC negotiation begins

### 3. createPeerConnection() (Lines 187-248)
Creates the WebRTC connection with all handlers.

```javascript
function createPeerConnection() {
    peerConnection = new RTCPeerConnection(config);
    
    // Add your tracks to connection
    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });
    
    // Handler: When remote stream arrives
    peerConnection.ontrack = (event) => {
        remoteStream.addTrack(event.track);
        if (event.track.kind === 'video') {
            monitorRemoteVideoTrack(event.track);
        }
    };
    
    // Handler: ICE candidates for NAT traversal
    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            sendSignal('ice-candidate', event.candidate);
        }
    };
    
    // Handler: Connection state changes
    peerConnection.onconnectionstatechange = () => {
        if (peerConnection.connectionState === 'connected') {
            isCallConnected = true;  // ← Enables overlay signals
        }
    };
}
```

**Important Flags:**
- `isCallConnected = true` only AFTER connection established
- This prevents video-off overlays from showing during "Connecting..."

### 4. checkForSignals() (Lines 423-510)
Polls server every 200ms for incoming signals.

```javascript
async function checkForSignals() {
    const response = await fetch('api/get_signals.php');
    const data = await response.json();
    
    for (const signal of data.signals) {
        if (signal.from_user_id !== remoteUserId) continue;
        
        switch (signal.signal_type) {
            case 'offer':
                await handleOffer(signal.signal_data);
                break;
            case 'answer':
                await handleAnswer(signal.signal_data);
                break;
            case 'ice-candidate':
                await handleIceCandidate(signal.signal_data);
                break;
            case 'video-status':
                isRemoteVideoEnabled = signal.signal_data.enabled;
                // Show/hide remote camera-off overlay
                break;
            case 'audio-status':
                isRemoteAudioEnabled = signal.signal_data.enabled;
                // Show/hide remote mic-off indicator
                break;
            case 'call-ended':
                endCallImmediately();
                return;
        }
    }
}
```

**Polling Mechanism:**
```
Every 200ms:
┌─────────────────────┐
│ fetch get_signals   │
└──────────┬──────────┘
           ↓
    ┌──────────────┐
    │ Parse JSON   │
    └──────┬───────┘
           ↓
    ┌──────────────────────────┐
    │ For each signal:         │
    │ - Check if from remote   │
    │ - Handle by type         │
    │ - Delete from DB         │
    └──────────────────────────┘
           ↓
    ┌──────────────┐
    │ Wait 200ms   │
    └──────┬───────┘
           ↓
        (Repeat)
```

### 5. toggleAudio() (Lines 517-551)
Mutes/unmutes microphone.

```javascript
function toggleAudio() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            isAudioEnabled = !isAudioEnabled;
            audioTrack.enabled = isAudioEnabled;
            
            // Show/hide local mic-off indicator
            const localMicIndicator = document.getElementById('localMicOffIndicator');
            if (localMicIndicator) {
                if (!isAudioEnabled) {
                    localMicIndicator.classList.add('visible');
                } else {
                    localMicIndicator.classList.remove('visible');
                }
            }
            
            // Send to remote peer
            sendSignal('audio-status', { enabled: isAudioEnabled });
            
            // Update button icon
            updateAudioIcon();
        }
    }
}
```

**State Changes:**
```
Mic ON (isAudioEnabled = true)
├─ audioTrack.enabled = true
├─ localMicIndicator: hide
├─ Send: audio-status { enabled: true }
└─ Icon: microphone symbol

Mic OFF (isAudioEnabled = false)
├─ audioTrack.enabled = false
├─ localMicIndicator: show (red badge)
├─ Send: audio-status { enabled: false }
└─ Icon: microphone-off symbol
```

### 6. toggleVideo() (Lines 553-596)
Turns camera on/off.

```javascript
function toggleVideo() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            isVideoEnabled = !isVideoEnabled;
            videoTrack.enabled = isVideoEnabled;
            
            // Show/hide local camera-off overlay
            const localOverlay = document.getElementById('localVideoOffOverlay');
            if (localOverlay) {
                if (!isVideoEnabled) {
                    localOverlay.classList.add('visible');
                } else {
                    localOverlay.classList.remove('visible');
                }
            }
            
            // Send to remote peer
            sendSignal('video-status', { enabled: isVideoEnabled });
            
            // Update button icon
            updateVideoIcon();
        }
    }
}
```

**Remote Side Effect:**
```
Local User: Clicks video OFF
    ↓ sends: video-status { enabled: false }
    ↓
Remote checkForSignals():
    - Updates: isRemoteVideoEnabled = false
    - Shows: remoteVideoOffOverlay
    - Remote sees: User's avatar + "Camera is off"
```

### 7. sendSignal() (Lines 398-421)
Sends signal to remote peer via server.

```javascript
async function sendSignal(signalType, signalData) {
    const response = await fetch('api/send_signal.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            to_user_id: remoteUserId,
            signal_type: signalType,
            signal_data: signalData,
            call_type: callType
        })
    });
    
    const data = await response.json();
    if (!data.success) {
        console.error('Failed to send signal:', data.message);
    }
}
```

**Signal Types Sent:**
| Type | Data | When |
|------|------|------|
| `offer` | RTCSessionDescription | After creating peer connection |
| `answer` | RTCSessionDescription | After receiving offer |
| `ice-candidate` | RTCIceCandidate | During connection setup |
| `video-status` | {enabled: bool} | When toggling camera |
| `audio-status` | {enabled: bool} | When toggling mic |
| `call-ended` | {ended: true} | When ending call |

### 8. endCall() (Lines 598-635)
Properly closes the call.

```javascript
function endCall() {
    // Send disconnect signal to remote
    sendEndCallSignal();
    
    // Stop polling
    if (signalingInterval) {
        clearInterval(signalingInterval);
    }
    
    // Stop media tracks
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    
    // Close P2P connection
    if (peerConnection) {
        peerConnection.close();
    }
    
    // Update status in DB
    fetch('api/update_status.php', {
        method: 'POST',
        body: JSON.stringify({ status: 'online' })
    }).then(() => {
        window.location.href = 'dashboard.php';
    });
}
```

**Cleanup Steps:**
1. Signal remote peer (so they know we left)
2. Stop polling for signals (save server load)
3. Stop all media tracks (release camera/mic)
4. Close WebRTC connection
5. Update status back to "online" in DB
6. Redirect to dashboard

## Signal State Machine

```
                    [Initial State]
                          |
                 (isInitiator check)
                    /           \
                [Caller]      [Receiver]
                   |              |
         Create peer connection   Wait for offer
            Send offer             |
               |                   |
          [Offer Sent]        [Offer Received]
               |                   |
          Wait for answer    Create peer connection
               |              Send answer
               |                   |
         [Answer Received]    [Answer Sent]
               \                  /
                \                /
                 Exchange ICE candidates
                      |
                      ↓
           [Connection Established]
                      |
        (isCallConnected = true)
                      |
            Ready for signals:
          - video-status
          - audio-status
          - call-ended
```

## Important Notes

### ICE Candidate Queuing
```javascript
let pendingIceCandidates = [];

async function handleIceCandidate(candidate) {
    if (!peerConnection.remoteDescription) {
        // Queue until we have remote description
        pendingIceCandidates.push(candidate);
        return;
    }
    // Add immediately if ready
    await peerConnection.addIceCandidate(...);
}
```

**Why?** WebRTC sometimes gets ICE candidates before offer/answer are fully negotiated. Queueing prevents errors.

### Overlay Display Logic
```javascript
// Only show overlays AFTER connection, not during "Connecting..."
if (remoteOverlay && isCallConnected) {
    if (!isRemoteVideoEnabled) {
        remoteOverlay.classList.add('visible');
    }
}
```

**This prevents:**
- Video-off overlay during initial connection
- Flickering as overlays toggle
- Confusing UX during "Connecting..." phase

### Auto-Hide Controls
```javascript
function setupAutoHideControls() {
    // Hide controls after 3 seconds of inactivity
    // Move mouse to show again (YouTube-style)
}
```

## Debugging Tips

### Enable Console Logging
```javascript
function logEvent(event, payload = {}) {
    console.log('[LOG]', event, payload);
}

// Look for: [LOG] signal_offer, [LOG] signal_answer, etc.
```

### Check Signal Flow
```javascript
// In browser console:
// Search for: "Received video-status signal"
// This shows if signals are being received from remote
```

### Monitor Connection State
```javascript
// In browser console:
// Look for: "Connection state: connecting"
// Then: "Connection state: connected"
// Indicates successful WebRTC negotiation
```

## Common Errors & Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| `getUserMedia permission denied` | User blocked camera/mic | Re-grant browser permissions |
| `Cannot add track to closed peer connection` | P2P closed too early | Check `peerConnection.connectionState` |
| `ICE candidate from unknown peer` | Stale signal queue | Clear browser cache & reload |
| `Connecting... forever` | STUN server unreachable | Check internet, try different STUN server |
| `Black video screen` | Remote camera off but no overlay | Check `isCallConnected` flag |

## Performance Metrics

- **Signal Polling**: 200ms interval (5 polls/second)
- **Media Constraints**: 1280x720 video, auto audio
- **ICE Gathering**: ~3-5 seconds typical
- **Connection Time**: 2-10 seconds typical
- **Bandwidth**: 500kbps - 2.5mbps depending on quality

## Related Files
- [call.php](CALL.md) - HTML structure
- [style.css](STYLE.md) - UI styling for overlays
- [API: send_signal.php](API.md) - Stores signals in DB
- [API: get_signals.php](API.md) - Retrieves signals

---

**Last Updated**: January 7, 2026
**Status**: Fully Functional ✅
**Lines of Code**: 835
