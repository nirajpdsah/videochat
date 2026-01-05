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
let isRemoteVideoEnabled = true; // Track remote peer's video state via signaling
let isRemoteAudioEnabled = true; // Track remote peer's audio state via signaling
let isCallConnected = false; // Track if WebRTC connection is established
let pendingIceCandidates = []; // Queue ICE candidates until offer/answer is set
let connectionRetryCount = 0;
let maxConnectionRetries = 3;
let signalingRetryDelay = 1000; // Start with 1 second
let connectionTimeout = null;
let hasMediaPermissions = false;
let audioOnlyMode = false; // Fallback when video permission denied

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

    // Setup auto-hide controls and cursor (YouTube-style)
    setupAutoHideControls();
});

/**
 * Setup auto-hide for controls and cursor after inactivity
 */
function setupAutoHideControls() {
    const callContainer = document.querySelector('.call-container');
    if (!callContainer) return;

    let inactivityTimer;
    const INACTIVITY_DELAY = 3000; // 3 seconds

    // Show controls and cursor
    function showControls() {
        callContainer.classList.add('active');
        callContainer.classList.remove('inactive');
    }

    // Hide controls and cursor
    function hideControls() {
        callContainer.classList.remove('active');
        callContainer.classList.add('inactive');
    }

    // Reset timer on mouse movement
    function resetTimer() {
        showControls();
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(hideControls, INACTIVITY_DELAY);
    }

    // Listen for mouse movement
    callContainer.addEventListener('mousemove', resetTimer);
    callContainer.addEventListener('mouseenter', resetTimer);

    // Start with controls visible, then hide after delay
    showControls();
    inactivityTimer = setTimeout(hideControls, INACTIVITY_DELAY);
}

/**
 * Setup media (camera/microphone) without creating peer connection yet
 */
async function setupMediaOnly() {
    try {
        logEvent('media_request_start');
        
        // Check if mediaDevices API is available (critical safety check)
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('Camera/microphone access is not supported in your browser or requires HTTPS. Please ensure you are using a modern browser over HTTPS.');
        }
        
        // Try full video/audio first for video calls
        if (callType === 'video') {
            try {
                const constraints = {
                    audio: true,
                    video: { width: 1280, height: 720 }
                };
                
                localStream = await navigator.mediaDevices.getUserMedia(constraints);
                logEvent('media_granted', { type: 'video_audio' });
                hasMediaPermissions = true;
                
            } catch (videoError) {
                console.warn('Video permission denied or failed:', videoError.name);
                
                // If video permission denied, try audio-only fallback
                if (videoError.name === 'NotAllowedError' || videoError.name === 'NotFoundError' || videoError.name === 'NotReadableError') {
                    console.log('Falling back to audio-only mode...');
                    updateCallStatus('Camera unavailable - Audio only');
                    
                    try {
                        localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                        audioOnlyMode = true;
                        hasMediaPermissions = true;
                        logEvent('media_granted', { type: 'audio_only', reason: videoError.name });
                        
                        // Show user notification
                        showNotification('Camera unavailable. Continuing with audio only.', 'warning');
                        
                        // Hide local video overlay since we have no video
                        const localVideoContainer = document.getElementById('localVideoContainer');
                        if (localVideoContainer) {
                            localVideoContainer.style.display = 'none';
                        }
                    } catch (audioError) {
                        throw audioError; // Re-throw if audio also fails
                    }
                } else {
                    throw videoError; // Re-throw for other errors
                }
            }
        } else {
            // Audio-only call
            const constraints = { audio: true, video: false };
            localStream = await navigator.mediaDevices.getUserMedia(constraints);
            logEvent('media_granted', { type: 'audio_only' });
            hasMediaPermissions = true;
        }

        // Display local video (if we have video stream)
        const localVideo = document.getElementById('localVideo');
        if (localVideo && localStream) {
            localVideo.srcObject = localStream;
        }

        // Monitor track ended events
        if (localStream) {
            localStream.getTracks().forEach(track => {
                track.onended = function () {
                    console.log(`${track.kind} track ended`);
                    showNotification(`${track.kind === 'video' ? 'Camera' : 'Microphone'} disconnected`, 'error');
                };
            });
        }

        logEvent('media_setup_complete');
        
    } catch (error) {
        logEvent('media_error', { name: error?.name });
        console.error('Error accessing media:', {
            name: error?.name,
            message: error?.message
        });
        
        hasMediaPermissions = false;
        
        // Handle different error types with appropriate messages
        let errorMessage = '';
        let showTroubleshootLink = false;
        
        if (error.name === 'NotAllowedError') {
            errorMessage = 'Camera and microphone permissions were denied. Please allow access and try again.';
        } else if (error.name === 'NotFoundError') {
            errorMessage = 'No camera or microphone found. Please connect a device and try again.';
        } else if (error.name === 'NotReadableError') {
            errorMessage = 'Camera or microphone is already in use by another application.';
        } else if (error.name === 'OverconstrainedError') {
            errorMessage = 'Camera settings are not supported. Please check your camera.';
        } else if (error.message && error.message.includes('getUserMedia')) {
            errorMessage = `Could not access media devices: ${error.message}`;
            showTroubleshootLink = true;
        } else {
            errorMessage = `Could not access media devices: ${error.message || error.name}`;
            showTroubleshootLink = true;
        }
        
        // Add troubleshooting link for technical errors
        if (showTroubleshootLink) {
            errorMessage += '\n\nThis might be a caching issue. Try:\n1. Press Ctrl+F5 to hard refresh\n2. Clear your browser cache\n3. Visit troubleshoot.php for more help';
        }
        
        alert(errorMessage);
        
        // Redirect to troubleshoot page for getUserMedia errors
        if (showTroubleshootLink && confirm('Would you like to see detailed troubleshooting steps?')) {
            window.location.href = 'troubleshoot.php';
        } else {
            endCall();
        }
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

        // Monitor video track state for avatar display
        if (event.track.kind === 'video') {
            monitorRemoteVideoTrack(event.track);
        }

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
            sendSignalWithRetry('ice-candidate', event.candidate);
            logEvent('ice_sent', { candidate: event.candidate });
        }
    };

    // Handle connection state changes
    peerConnection.onconnectionstatechange = () => {
        console.log('Connection state:', peerConnection.connectionState);
        updateCallStatus(peerConnection.connectionState);

        if (peerConnection.connectionState === 'connected') {
            isCallConnected = true;
            connectionRetryCount = 0; // Reset retry count on success
            if (connectionTimeout) {
                clearTimeout(connectionTimeout);
                connectionTimeout = null;
            }
            console.log('Call fully connected - video overlays now active');
            showNotification('Call connected', 'success');
        }

        if (peerConnection.connectionState === 'disconnected') {
            console.warn('Connection disconnected, attempting to reconnect...');
            updateCallStatus('Connection lost, reconnecting...');
            showNotification('Connection lost, trying to reconnect...', 'warning');
            attemptReconnection();
        }
        
        if (peerConnection.connectionState === 'failed') {
            console.error('Connection failed');
            if (connectionRetryCount < maxConnectionRetries) {
                updateCallStatus('Connection failed, retrying...');
                showNotification(`Connection failed, retrying (${connectionRetryCount + 1}/${maxConnectionRetries})...`, 'warning');
                attemptReconnection();
            } else {
                updateCallStatus('Connection failed');
                showNotification('Connection failed. Please check your internet connection.', 'error');
                setTimeout(() => endCall(), 3000);
            }
        }
    };
    
    // Set connection timeout (30 seconds)
    connectionTimeout = setTimeout(() => {
        if (!isCallConnected && peerConnection && peerConnection.connectionState !== 'connected') {
            console.error('Connection timeout');
            showNotification('Connection timeout. Please check your internet connection.', 'error');
            endCall();
        }
    }, 30000);
}

/**
 * Monitor remote video track to show/hide avatar when video is off
 * Now relies ONLY on explicit signaling for accuracy
 * Only shows overlay after call is fully connected
 */
function monitorRemoteVideoTrack(track) {
    const overlay = document.getElementById('remoteVideoOffOverlay');

    if (!overlay) {
        console.error('Video off overlay not found!');
        return;
    }

    console.log('Monitoring remote video track - using explicit signaling only');
    
    // Keep overlay hidden during connection phase
    overlay.classList.remove('visible');
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
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        const response = await fetch('api/send_signal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                to_user_id: remoteUserId,
                signal_type: signalType,
                signal_data: signalData,
                call_type: callType
            }),
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);

        const data = await response.json();
        if (!data.success) {
            console.error('Failed to send signal:', data.message);
            logEvent('sendSignal_failed', { signalType, to: remoteUserId, message: data.message });
        }
        logEvent('sendSignal_ok', { signalType, to: remoteUserId, callType, signalData });
    } catch (error) {
        console.error('Error sending signal:', error);
        logEvent('sendSignal_error', { signalType, to: remoteUserId, error: error?.message });
        
        if (error.name === 'AbortError') {
            console.warn('Signal request timeout');
        }
    }
}

/**
 * Send signal with retry logic for critical signals
 */
async function sendSignalWithRetry(signalType, signalData, maxRetries = 3) {
    let retries = 0;
    let delay = signalingRetryDelay;
    
    while (retries < maxRetries) {
        try {
            await sendSignal(signalType, signalData);
            return; // Success
        } catch (error) {
            retries++;
            console.warn(`Signal send failed (attempt ${retries}/${maxRetries}), retrying in ${delay}ms...`);
            
            if (retries < maxRetries) {
                await new Promise(resolve => setTimeout(resolve, delay));
                delay *= 2; // Exponential backoff
            } else {
                console.error('Signal send failed after all retries');
                throw error;
            }
        }
    }
}

/**
 * Check for incoming signals from server
 */
let callStarted = false;

async function checkForSignals() {
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000); // 5 second timeout
        
        const response = await fetch('api/get_signals.php', {
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);

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
                    case 'video-status':
                        console.log('Received video-status signal:', signal.signal_data);
                        isRemoteVideoEnabled = signal.signal_data.enabled;
                        const remoteOverlay = document.getElementById('remoteVideoOffOverlay');
                        console.log('Remote overlay element:', remoteOverlay);
                        console.log('Call connected status:', isCallConnected);
                        
                        // Only show/hide overlay if call is fully connected
                        if (remoteOverlay && isCallConnected) {
                            if (!isRemoteVideoEnabled) {
                                console.log('Showing remote video-off overlay');
                                remoteOverlay.classList.add('visible');
                            } else {
                                console.log('Hiding remote video-off overlay');
                                remoteOverlay.classList.remove('visible');
                            }
                        } else if (!isCallConnected) {
                            console.log('Call not connected yet, ignoring video-status signal');
                        } else {
                            console.error('Remote overlay element not found!');
                        }
                        break;
                    case 'audio-status':
                        console.log('Received audio-status signal:', signal.signal_data);
                        isRemoteAudioEnabled = signal.signal_data.enabled;
                        const remoteMicIndicator = document.getElementById('remoteMicOffIndicator');
                        console.log('Remote mic indicator element:', remoteMicIndicator);
                        console.log('Call connected status:', isCallConnected);
                        
                        // Only show/hide indicator if call is fully connected
                        if (remoteMicIndicator && isCallConnected) {
                            if (!isRemoteAudioEnabled) {
                                console.log('Showing remote mic-off indicator');
                                remoteMicIndicator.classList.add('visible');
                            } else {
                                console.log('Hiding remote mic-off indicator');
                                remoteMicIndicator.classList.remove('visible');
                            }
                        } else if (!isCallConnected) {
                            console.log('Call not connected yet, ignoring audio-status signal');
                        } else {
                            console.error('Remote mic indicator element not found!');
                        }
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
    console.log('toggleAudio() called');
    if (localStream) {
        const audioTrack = localStream.getAudioTracks()[0];
        if (audioTrack) {
            isAudioEnabled = !isAudioEnabled;
            audioTrack.enabled = isAudioEnabled;
            console.log('Audio toggled. New state:', isAudioEnabled);

            // Update local mic indicator visibility
            const localMicIndicator = document.getElementById('localMicOffIndicator');
            if (localMicIndicator) {
                if (!isAudioEnabled) {
                    localMicIndicator.classList.add('visible');
                    console.log('Local mic indicator shown');
                } else {
                    localMicIndicator.classList.remove('visible');
                    console.log('Local mic indicator hidden');
                }
            }

            // Send signal to remote peer
            console.log('Sending audio-status signal to remote peer:', { enabled: isAudioEnabled, to: remoteUserId });
            sendSignal('audio-status', { enabled: isAudioEnabled });

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
    console.log('toggleVideo() called');
    if (localStream) {
        const videoTrack = localStream.getVideoTracks()[0];
        if (videoTrack) {
            isVideoEnabled = !isVideoEnabled;
            videoTrack.enabled = isVideoEnabled;
            console.log('Video toggled. New state:', isVideoEnabled);

            // Update local overlay visibility
            const localOverlay = document.getElementById('localVideoOffOverlay');
            if (localOverlay) {
                if (!isVideoEnabled) {
                    localOverlay.classList.add('visible');
                    console.log('Local overlay shown');
                } else {
                    localOverlay.classList.remove('visible');
                    console.log('Local overlay hidden');
                }
            }

            // Send signal to remote peer
            console.log('Sending video-status signal to remote peer:', { enabled: isVideoEnabled, to: remoteUserId });
            sendSignal('video-status', { enabled: isVideoEnabled });

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
 * Attempt to reconnect after connection loss
 */
async function attemptReconnection() {
    if (connectionRetryCount >= maxConnectionRetries) {
        console.error('Max reconnection attempts reached');
        return;
    }
    
    connectionRetryCount++;
    console.log(`Reconnection attempt ${connectionRetryCount}/${maxConnectionRetries}`);
    
    // Close existing peer connection
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
    }
    
    // Wait before retrying (exponential backoff)
    const delay = Math.min(1000 * Math.pow(2, connectionRetryCount - 1), 10000);
    await new Promise(resolve => setTimeout(resolve, delay));
    
    // Try to reconnect
    try {
        if (isInitiator) {
            await startCall();
        } else {
            createPeerConnection();
        }
    } catch (error) {
        console.error('Reconnection failed:', error);
        if (connectionRetryCount < maxConnectionRetries) {
            attemptReconnection();
        }
    }
}

/**
 * Show notification to user
 */
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotification = document.querySelector('.call-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `call-notification call-notification-${type}`;
    notification.textContent = message;
    
    // Add to DOM
    const callContainer = document.querySelector('.call-container');
    if (callContainer) {
        callContainer.appendChild(notification);
        
        // Auto-remove after 5 seconds (unless it's an error)
        if (type !== 'error') {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }
    }
}

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