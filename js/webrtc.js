/**
 * WebRTC Video/Audio Call Implementation
 * Handles peer-to-peer connection between two users
 */

// WebRTC Configuration
const config = {
    iceServers: [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' }
    ]
};

let localStream = null;
let remoteStream = null;
let peerConnection = null;
let signalingInterval = null;
let isAudioEnabled = true;
let isVideoEnabled = true;
let pendingIceCandidates = []; // Queue ICE candidates until offer/answer is set

// Debug logger: logs to console only
function logEvent(event, payload = {}) {
    console.log('[LOG]', event, payload);
}

/**
 * Initialize call on page load
 */
document.addEventListener('DOMContentLoaded', async function () {
    try {
        console.log('Initializing call...');
        console.log('Call Type:', callType);
        console.log('Is Initiator:', isInitiator);

        // Only clear call-ended signals from old calls
        await fetch('api/get_signals.php').then(r => r.json()).then(data => {
            if (data.success && data.signals) {
                data.signals.forEach(s => {
                    if (s.from_user_id == remoteUserId && s.signal_type === 'call-ended') {
                        fetch('api/delete_signal.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ from_user_id: remoteUserId, signal_type: 'call-ended' })
                        });
                    }
                });
            }
        });

        logEvent('init');

        // If receiver, setup media and wait for offer
        if (!isInitiator) {
            logEvent('receiver_setup');
            await setupMediaOnly();
            updateCallStatus('Preparing...');
            await sendSignal('receiver-ready', { ready: true });
            updateCallStatus('Ready, waiting for connection...');
            signalingInterval = setInterval(checkForSignals, 200);
            checkForSignals();
        } else {
            // If initiator, setup media and start the call immediately
            logEvent('initiator_setup');
            await setupMediaOnly();
            updateCallStatus('Connecting...');
            await startCall();
            signalingInterval = setInterval(checkForSignals, 200);
            checkForSignals();
        }
    } catch (error) {
        console.error('[INIT ERROR]', error);
        alert('Initialization error: ' + error.message);
    }
});

/**
 * Setup media (camera/microphone) without creating peer connection yet
 */
async function setupMediaOnly() {
    try {
        logEvent('media_request_start');
        // Get user media (camera/microphone)
        const constraints = {
            audio: true,
            video: callType === 'video' ? { width: 1280, height: 720 } : false
        };

        localStream = await navigator.mediaDevices.getUserMedia(constraints);
        logEvent('media_granted');

        // Display local video
        const localVideo = document.getElementById('localVideo');
        localVideo.srcObject = localStream;

        // Hide video info overlay once stream starts
        if (callType === 'video') {
            localStream.getVideoTracks()[0].onended = function () {
                console.log('Video track ended');
            };
        }

        logEvent('media_setup_complete');
    } catch (error) {
        logEvent('media_error', { name: error?.name });
        // Log detailed media error info to help diagnose (e.g., NotAllowedError, NotFoundError, NotReadableError)
        console.error('Error accessing media:', {
            name: error?.name,
            message: error?.message,
            constraints
        });
        alert(`Could not access camera/microphone (${error?.name || 'unknown'}): ${error?.message || ''}`);
        endCall();
    }
}

/**
 * Start the call
 */
async function startCall() {
    try {
        // Prevent double-start
        callStarted = true;
        logEvent('start_call', { isInitiator });

        // If we don't have media yet, get it
        if (!localStream) {
            await setupMediaOnly();
        }

        // Create peer connection
        createPeerConnection();

        // If initiator, create offer
        if (isInitiator) {
            updateCallStatus('Connecting...');
            await createOffer();
        }

        // Don't set "Connected" here - let the connection state handler do it

    } catch (error) {
        console.error('Error starting call:', error);
        alert('Could not access camera/microphone. Please check permissions.');
        endCall();
    }
}

/**
 * Create WebRTC peer connection
 */
function createPeerConnection() {
    peerConnection = new RTCPeerConnection(config);

    // Add local tracks to peer connection
    localStream.getTracks().forEach(track => {
        peerConnection.addTrack(track, localStream);
    });

    // Handle incoming tracks
    peerConnection.ontrack = (event) => {
        console.log('Received remote track');
        logEvent('remote_track', { kind: event.track?.kind });
        if (!remoteStream) {
            remoteStream = new MediaStream();
            const remoteVideo = document.getElementById('remoteVideo');
            remoteVideo.srcObject = remoteStream;
        }
        remoteStream.addTrack(event.track);

        // Hide video info overlay when remote video actually starts playing
        const remoteVideo = document.getElementById('remoteVideo');
        remoteVideo.onloadedmetadata = () => {
            console.log('Remote video metadata loaded');
            const videoInfo = document.querySelector('.video-info');
            if (videoInfo) {
                videoInfo.style.display = 'none';
            }
        };
    };

    // Handle ICE candidates
    peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
            console.log('Sending ICE candidate');
            sendSignal('ice-candidate', event.candidate);
            logEvent('ice_sent', { candidate: event.candidate });
        }
    };

    // Handle connection state changes
    peerConnection.onconnectionstatechange = () => {
        console.log('Connection state:', peerConnection.connectionState);
        updateCallStatus(peerConnection.connectionState);

        if (peerConnection.connectionState === 'disconnected' ||
            peerConnection.connectionState === 'failed') {
            endCall();
        }
    };
}

/**
 * Create and send offer (initiator only)
 */
async function createOffer() {
    try {
        logEvent('creating_offer');
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        await sendSignal('offer', offer);
        logEvent('offer_sent');
    } catch (error) {
        console.error('Error creating offer:', error);
    }
}

/**
 * Handle incoming offer (receiver only)
 */
async function handleOffer(offer) {
    try {
        console.log('Handling offer...');
        await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));

        // Add any queued ICE candidates now that we have remote description
        console.log('Processing', pendingIceCandidates.length, 'queued ICE candidates...');
        for (const candidate of pendingIceCandidates) {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (e) {
                console.error('Error adding queued ICE candidate:', e);
            }
        }
        pendingIceCandidates = [];

        // Create answer
        const answer = await peerConnection.createAnswer();
        await peerConnection.setLocalDescription(answer);

        // Send answer back
        sendSignal('answer', answer);
    } catch (error) {
        console.error('Error handling offer:', error);
    }
}

/**
 * Handle incoming answer (initiator only)
 */
async function handleAnswer(answer) {
    try {
        console.log('Handling answer...');
        await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));

        // Add any queued ICE candidates now that we have remote description
        console.log('Processing', pendingIceCandidates.length, 'queued ICE candidates...');
        for (const candidate of pendingIceCandidates) {
            try {
                await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
            } catch (e) {
                console.error('Error adding queued ICE candidate:', e);
            }
        }
        pendingIceCandidates = [];
    } catch (error) {
        console.error('Error handling answer:', error);
    }
}

/**
 * Handle incoming ICE candidate
 */
async function handleIceCandidate(candidate) {
    try {
        if (!peerConnection || !peerConnection.remoteDescription) {
            console.log('Queuing ICE candidate (no remote description yet)...');
            pendingIceCandidates.push(candidate);
            return;
        }
        console.log('Adding ICE candidate...');
        await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
    } catch (error) {
        console.error('Error adding ICE candidate:', error);
    }
}

/**
 * Send signaling data to server
 */
async function sendSignal(signalType, signalData) {
    try {
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
            logEvent('sendSignal_failed', { signalType, to: remoteUserId, message: data.message });
        }
        logEvent('sendSignal_ok', { signalType, to: remoteUserId, callType, signalData });
    } catch (error) {
        console.error('Error sending signal:', error);
        logEvent('sendSignal_error', { signalType, to: remoteUserId, error: error?.message });
    }
}

/**
 * Check for incoming signals from server
 */
let callStarted = false;

async function checkForSignals() {
    try {
        const response = await fetch('api/get_signals.php');

        // Check if response is OK
        if (!response.ok) {
            console.error('API error:', response.status, response.statusText);
            return;
        }

        // Get response as text first to check if it's JSON
        const responseText = await response.text();

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response was:', responseText.substring(0, 200));
            return;
        }

        if (data.success && data.signals && data.signals.length > 0) {
            logEvent('signals_received', { count: data.signals.length });
            for (const signal of data.signals) {
                // Only process signals from the remote user we're calling
                if (signal.from_user_id !== remoteUserId) continue;

                logEvent('signal_' + signal.signal_type);

                // CHECK FOR CALL-ENDED FIRST for immediate disconnect detection
                if (signal.signal_type === 'call-ended') {
                    console.log('Remote user ended the call!');
                    logEvent('call_ended_received', {});

                    // Stop listening
                    if (signalingInterval) {
                        clearInterval(signalingInterval);
                        signalingInterval = null;
                    }

                    // Delete the signal
                    fetch('api/delete_signal.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            from_user_id: remoteUserId,
                            signal_type: 'call-ended'
                        })
                    }).catch(e => console.error('Error deleting call-ended signal:', e));

                    // Close the call immediately
                    endCallImmediately();
                    return;
                }

                // If initiator receives "call-accepted", start the WebRTC connection
                if (isInitiator && signal.signal_type === 'call-accepted' && !callStarted) {
                    console.log('Call accepted by receiver! Starting WebRTC connection...');
                    logEvent('call_accepted', {});
                    callStarted = true;
                    updateCallStatus('Connecting...');
                    await startCall(); // Now create peer connection and offer
                    continue;
                }

                // Receiver should IGNORE call-accepted signals (they're for the caller)
                if (!isInitiator && signal.signal_type === 'call-accepted') {
                    console.log('Ignoring call-accepted signal (receiver side)');
                    continue;
                }

                // If receiver gets an offer, make sure we have peer connection ready
                if (!isInitiator && signal.signal_type === 'offer' && !peerConnection) {
                    logEvent('offer_received');
                    if (!localStream) await setupMediaOnly();
                    createPeerConnection();
                }

                // Process the signal
                switch (signal.signal_type) {
                    case 'offer':
                        if (peerConnection) await handleOffer(signal.signal_data);
                        break;
                    case 'answer':
                        if (peerConnection) await handleAnswer(signal.signal_data);
                        break;
                    case 'ice-candidate':
                        if (peerConnection) await handleIceCandidate(signal.signal_data);
                        break;
                }
            }
        }
    } catch (error) {
        console.error('Error checking signals:', error);
    }
}

/**
 * Toggle audio on/off
 */
function toggleAudio() {
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            isAudioEnabled = !isAudioEnabled;
            audioTrack.enabled = isAudioEnabled;

            const audioIcon = document.getElementById('audioIcon');
            audioIcon.innerHTML = isAudioEnabled ? `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                        <line x1="12" y1="19" x2="12" y2="23"></line>
                        <line x1="8" y1="23" x2="16" y2="23"></line>
                    </svg>
                ` : `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                        <path d="M9 9v3a3 3 0 0 0 5.12 2.12M15 9.34V4a3 3 0 0 0-5.94-.6"></path>
                        <path d="M17 16.95A7 7 0 0 1 5 12v-2m14 0v2a7 7 0 0 1-.11 1.23"></path>
                        <line x1="12" y1="19" x2="12" y2="23"></line>
                        <line x1="8" y1="23" x2="16" y2="23"></line>
                    </svg>
                `;

            const btn = document.getElementById('toggleAudioBtn');
            btn.classList.toggle('active', !isAudioEnabled);
        }
    }
}

/**
 * Toggle video on/off
 */
function toggleVideo() {
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            isVideoEnabled = !isVideoEnabled;
            videoTrack.enabled = isVideoEnabled;

            const videoIcon = document.getElementById('videoIcon');
            videoIcon.innerHTML = isVideoEnabled ? `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 7l-7 5 7 5V7z"></path>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                ` : `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                        <path d="M16 16v1a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h2m5.66 0H14a2 2 0 0 1 2 2v3.34l1 1L23 7v10"></path>
                    </svg>
                `;

            const btn = document.getElementById('toggleVideoBtn');
            btn.classList.toggle('active', !isVideoEnabled);
        }
    }
}

/**
 * End the call
 */
function endCall() {
    console.log('Ending call...');

    // Send "call-ended" signal to remote user for real-time disconnect
    sendEndCallSignal();

    // Stop signaling
    if (signalingInterval) {
        clearInterval(signalingInterval);
    }

    // Stop call-ended listener if running
    if (window.callEndedInterval) {
        clearInterval(window.callEndedInterval);
        window.callEndedInterval = null;
    }

    // Stop all tracks
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }

    // Close peer connection
    if (peerConnection) {
        peerConnection.close();
    }

    // Update status to online
    fetch('api/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: 'online' })
    }).then(() => {
        // Redirect back to dashboard
        window.location.href = 'dashboard.php';
    });
}

/**
 * Send call-ended signal to remote user
 */
async function sendEndCallSignal() {
    try {
        const response = await fetch('api/send_signal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                to_user_id: remoteUserId,
                signal_type: 'call-ended',
                signal_data: { ended: true },
                call_type: callType
            })
        });
        const result = await response.json();
        console.log('Call-ended signal sent:', result.success);
    } catch (error) {
        console.error('Error sending call-ended signal:', error);
    }
}

/**
 * Close call immediately without sending signal (for when we receive call-ended)
 */
function endCallImmediately() {
    console.log('Closing call immediately...');

    // Stop signaling
    if (signalingInterval) {
        clearInterval(signalingInterval);
        signalingInterval = null;
    }

    // Stop all tracks
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }

    // Close peer connection
    if (peerConnection) {
        peerConnection.close();
    }

    // Update status to online
    fetch('api/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: 'online' })
    }).then(() => {
        // Redirect back to dashboard
        window.location.href = 'dashboard.php';
    });
}

/**
 * Update call status text
 */
function updateCallStatus(status) {
    const statusEl = document.getElementById('callStatus');
    if (statusEl) {
        let statusText = status;

        switch (status.toLowerCase()) {
            case 'new':
            case 'connecting':
            case 'connecting...':
                statusText = 'Connecting...';
                break;
            case 'connected':
                statusText = 'Connected';
                // Hide the profile picture overlay when actually connected
                const videoInfo = document.querySelector('.video-info');
                if (videoInfo && remoteStream && remoteStream.getTracks().length > 0) {
                    videoInfo.style.display = 'none';
                }
                break;
            case 'calling':
            case 'calling...':
                statusText = 'Calling...';
                break;
            case 'waiting':
            case 'preparing':
            case 'preparing...':
                statusText = status.includes('.') ? status : status + '...';
                break;
            case 'disconnected':
                statusText = 'Disconnected';
                break;
            case 'failed':
                statusText = 'Connection Failed';
                break;
            default:
                statusText = status;
        }

        statusEl.textContent = statusText;
    }
}

/**
 * Handle page unload
 */
/**
 * Handle page unload
 */
window.addEventListener('beforeunload', function () {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    if (peerConnection) {
        peerConnection.close();
    }
});

/**
 * Make an element draggable
 */
function makeDraggable(element) {
    let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

    element.onmousedown = dragMouseDown;

    function dragMouseDown(e) {
        e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;

        // set the element's new position:
        let newTop = (element.offsetTop - pos2);
        let newLeft = (element.offsetLeft - pos1);

        // Boundary checks
        const maxTop = window.innerHeight - element.offsetHeight;
        const maxLeft = window.innerWidth - element.offsetWidth;

        newTop = Math.max(0, Math.min(newTop, maxTop));
        newLeft = Math.max(0, Math.min(newLeft, maxLeft));

        element.style.top = newTop + "px";
        element.style.left = newLeft + "px";
        // Clear right property (since we use left/top for positioning)
        element.style.right = 'auto';
        element.style.bottom = 'auto';
    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

// Initialize things
const localVideoContainer = document.getElementById('localVideoContainer');
if (localVideoContainer) {
    makeDraggable(localVideoContainer);
}