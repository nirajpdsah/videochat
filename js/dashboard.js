/**
 * Dashboard JavaScript
 * Handles user list and call initiation
 */

let users = [];
let pollInterval = null;
let heartbeatInterval = null;

// Load users on page load
document.addEventListener('DOMContentLoaded', function () {
    loadUsers();
    updateUserStatus('online');

    // Poll for users every 2 seconds (faster updates)
    pollInterval = setInterval(loadUsers, 2000);

    // Poll for incoming calls every 1 second (faster notification)
    setInterval(checkForIncomingCalls, 1000);

    // Send heartbeat every 10 seconds to keep user marked as online
    heartbeatInterval = setInterval(() => {
        updateUserStatus('online');
    }, 10000);

    // Update status before leaving page
    window.addEventListener('beforeunload', function () {
        updateUserStatus('offline');
    });
});

/**
 * Load all users from database
 */
async function loadUsers() {
    try {
        const response = await fetch('api/get_users.php');
        const data = await response.json();

        if (data.success) {
            users = data.users;
            displayUsers();

            // Debug: Log user statuses
            console.log('Users loaded:', users.map(u => `${u.username}: ${u.status}`));
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

/**
 * Display users in the sidebar
 */
function displayUsers() {
    const usersList = document.getElementById('usersList');

    if (users.length === 0) {
        usersList.innerHTML = '<div class="loading">No other users found</div>';
        return;
    }

    let html = '';
    users.forEach(user => {
        const statusClass = `status-${user.status}`;
        let statusText;

        if (user.status === 'online') {
            statusText = 'Online';
        } else if (user.status === 'on_call') {
            statusText = 'On a call';
        } else {
            // Show last seen for offline users
            statusText = `Last seen ${formatLastSeen(user.last_seen)}`;
        }

        html += `
            <div class="user-item" onclick="selectUser(${user.id})" data-user-id="${user.id}">
                <img src="uploads/${user.profile_picture ? user.profile_picture : 'default-avatar.png'}" alt="${user.username}">
                <div class="user-item-info">
                    <h4>${user.username}</h4>
                    <p>
                        <span class="status-indicator ${statusClass}"></span>
                        ${statusText}
                    </p>
                </div>
            </div>
        `;
    });

    usersList.innerHTML = html;

    // Re-highlight selected user if exists
    if (selectedUserId) {
        const selectedEl = document.querySelector(`.user-item[data-user-id="${selectedUserId}"]`);
        if (selectedEl) selectedEl.classList.add('active');

        // Also update status in the main view if it changed
        const selectedUser = users.find(u => u.id === selectedUserId);
        if (selectedUser) {
            updateSelectedUserView(selectedUser);
        }
    }
}

function selectUser(userId) {
    selectedUserId = userId;

    // Update active class in list
    document.querySelectorAll('.user-item').forEach(el => el.classList.remove('active'));
    document.querySelector(`.user-item[data-user-id="${userId}"]`)?.classList.add('active');

    // Find user data
    const user = users.find(u => u.id === userId);
    if (user) {
        selectedUsername = user.username;
        updateSelectedUserView(user);
    }
}

function updateSelectedUserView(user) {
    const emptyState = document.querySelector('.empty-state');
    const profileView = document.getElementById('userProfileView');

    if (emptyState) emptyState.style.display = 'none';
    if (profileView) {
        profileView.style.display = 'block';

        // Update content
        document.getElementById('selectedUserName').textContent = user.username;
        document.getElementById('selectedUserAvatar').src = 'uploads/' + (user.profile_picture ? user.profile_picture : 'default-avatar.png');

        // Status TEXT
        let statusText = user.status === 'online' ? 'Online' :
            (user.status === 'on_call' ? 'On a call' : 'Offline');
        const statusBadge = document.getElementById('selectedUserStatus');
        statusBadge.textContent = statusText;
        statusBadge.style.color = user.status === 'online' ? '#34d399' :
            (user.status === 'on_call' ? '#ef4444' : 'var(--text-muted)');
        statusBadge.style.borderColor = user.status === 'online' ? 'rgba(52, 211, 153, 0.2)' : 'rgba(255,255,255,0.1)';

        // Disable buttons if user is on call
        const btns = document.querySelectorAll('.action-card');
        btns.forEach(btn => {
            if (user.status === 'on_call') {
                btn.style.opacity = '0.5';
                btn.style.pointerEvents = 'none';
                btn.title = 'User is currently on a call';
            } else {
                btn.style.opacity = '1';
                btn.style.pointerEvents = 'auto';
                btn.title = '';
            }
        });
    }
}

function startVideoCall() {
    if (selectedUserId && selectedUsername) {
        initiateCall(selectedUserId, selectedUsername, 'video');
    }
}

function startAudioCall() {
    if (selectedUserId && selectedUsername) {
        initiateCall(selectedUserId, selectedUsername, 'audio');
    }
}

/**
 * Update current user's status
 */
async function updateUserStatus(status) {
    try {
        await fetch('api/update_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status })
        });
    } catch (error) {
        console.error('Error updating status:', error);
    }
}

/**
 * Initiate a call
 */
async function initiateCall(userId, username, callType) {
    // Check if user is busy
    const user = users.find(u => u.id === userId);
    if (user && user.status === 'on_call') {
        showBusyModal();
        return;
    }

    // Send call request to the other user
    try {
        const response = await fetch('api/send_call_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                to_user_id: userId,
                call_type: callType
            })
        });

        const data = await response.json();

        if (!data.success) {
            if (data.message === 'User is busy') {
                showBusyModal();
                return;
            }
            console.error('Call request failed:', data.message);
            alert('Failed to initiate call: ' + (data.message || 'Unknown error'));
            return;
        }
    } catch (error) {
        console.error('Error sending call request:', error);
        console.error('Error details:', error.message);
        alert('Failed to send call request. Please check console for details and ensure the database is updated.');
        return;
    }

    // Show calling modal and wait for acceptance
    showCallingModal(username, user.profile_picture);

    // Update own status
    updateUserStatus('on_call');

    // Store call info for later when receiver accepts
    window.pendingCall = {
        userId: userId,
        callType: callType
    };

    console.log('Pending call set:', window.pendingCall);

    // Start listening for call acceptance
    if (!window.callAcceptanceInterval) {
        window.callAcceptanceInterval = setInterval(checkForCallAcceptance, 500);
        console.log('Started checking for call acceptance every 500ms');
    }
}

/**
 * Show calling modal
 */
function showCallingModal(username, profilePic) {
    const modal = document.getElementById('callingModal');
    document.getElementById('callingModalName').textContent = username;
    document.getElementById('callingModalAvatar').src = 'uploads/' + (profilePic ? profilePic : 'default-avatar.png');

    // Clear any previous status message (like rejection messages)
    document.getElementById('callingModalStatus').textContent = '';
    document.getElementById('callingModalStatus').style.color = '#95a5a6';

    modal.classList.add('active');

    // Play outgoing ringback tone
    const outgoingSound = document.getElementById('outgoingSound');
    if (outgoingSound) {
        outgoingSound.currentTime = 0;
        const playPromise = outgoingSound.play();
        if (playPromise !== undefined) {
            playPromise.catch(e => {
                console.error('Audio play failed:', e);
            });
        }
    }
}

/**
 * Cancel call
 */
function cancelCall() {
    // Stop listening for acceptance
    if (window.callAcceptanceInterval) {
        clearInterval(window.callAcceptanceInterval);
        window.callAcceptanceInterval = null;
    }

    // Clear pending call
    window.pendingCall = null;

    document.getElementById('callingModal').classList.remove('active');
    document.getElementById('callingModal').classList.remove('active');
    updateUserStatus('online');

    // Stop sound
    stopRingTone();
}

/**
 * Check if receiver has accepted the call
 */
async function checkForCallAcceptance() {
    if (!window.pendingCall) return;

    try {
        const response = await fetch('api/get_signals.php');
        if (!response.ok) {
            console.log('Failed to fetch signals for call acceptance, status:', response.status);
            return;
        }

        const responseText = await response.text();
        let data;

        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error in checkForCallAcceptance:', parseError);
            console.error('Raw response:', responseText);
            return;
        }

        console.log('Checking for call acceptance, signals:', data.signals?.length || 0);
        console.log('Full response:', data);

        if (data.success && data.signals && data.signals.length > 0) {
            for (const signal of data.signals) {
                console.log('Signal:', signal.signal_type, 'from:', signal.from_user_id, 'expecting from:', window.pendingCall.userId);

                // Check if receiver sent call-accepted signal
                if (signal.signal_type === 'call-accepted' &&
                    signal.from_user_id === window.pendingCall.userId) {

                    console.log('Call accepted! Redirecting to call page...');

                    // Stop checking immediately
                    clearInterval(window.callAcceptanceInterval);
                    window.callAcceptanceInterval = null;

                    // Delete the acceptance signal so receiver doesn't process it
                    fetch('api/delete_signal.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            from_user_id: signal.from_user_id,
                            signal_type: 'call-accepted'
                        })
                    }).catch(e => console.error('Error deleting acceptance signal:', e));

                    // Close modal
                    document.getElementById('callingModal').classList.remove('active');

                    // Redirect to call page
                    window.location.href = `call.php?user_id=${window.pendingCall.userId}&type=${window.pendingCall.callType}&initiator=true`;
                    return;
                }

                // Check if receiver rejected the call
                if (signal.signal_type === 'call-rejected' &&
                    signal.from_user_id === window.pendingCall.userId) {

                    console.log('Call rejected by receiver!');

                    // Stop checking immediately
                    clearInterval(window.callAcceptanceInterval);
                    window.callAcceptanceInterval = null;

                    // Delete the rejection signal
                    fetch('api/delete_signal.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            from_user_id: signal.from_user_id,
                            signal_type: 'call-rejected'
                        })
                    }).catch(e => console.error('Error deleting rejection signal:', e));

                    // Show rejection message in modal
                    document.getElementById('callingModalStatus').textContent = 'Call Rejected from Other Side';
                    document.getElementById('callingModalStatus').style.color = '#e74c3c';

                    // Close modal after 2 seconds
                    setTimeout(() => {
                        document.getElementById('callingModal').classList.remove('active');
                        updateUserStatus('online');
                        window.pendingCall = null;
                        updateUserStatus('online');
                        window.pendingCall = null;
                    }, 2000);

                    // Stop sound
                    stopRingTone();
                    return;
                }
            }
        } else {
            console.log('No signals found or data.success is false. Success:', data.success, 'Signals:', data.signals);
        }
    } catch (error) {
        console.error('Error checking for call acceptance:', error);
    }
}

/**
 * Show busy modal
 */
function showBusyModal() {
    document.getElementById('busyModal').classList.add('active');
}

/**
 * Close busy modal
 */
function closeBusyModal() {
    document.getElementById('busyModal').classList.remove('active');
}

/**
 * Format timestamp for last seen
 */
function formatLastSeen(timestamp) {
    if (!timestamp) return 'Long time ago';

    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

/**
 * Format timestamp
 */
function formatTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now - date;

    // If less than 1 minute
    if (diff < 60000) {
        return 'Just now';
    }

    // If today
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    // Otherwise
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

/**
 * Check for incoming call requests
 */
let incomingCallData = null;
let callModalShown = false;

async function checkForIncomingCalls() {
    // Don't check if modal is already shown (user is handling the call)
    if (callModalShown) return;

    try {
        const response = await fetch('api/get_signals.php');

        if (!response.ok) {
            console.error('Failed to fetch signals:', response.status);
            return;
        }

        const responseText = await response.text();
        let data;

        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error in checkForIncomingCalls:', parseError);
            console.error('Response:', responseText.substring(0, 200));
            return;
        }

        if (data.success && data.signals && data.signals.length > 0) {
            for (const signal of data.signals) {
                // Check for call request
                if (signal.signal_type === 'call-request') {
                    // Only show if not already showing
                    if (!callModalShown) {
                        // Show incoming call modal
                        incomingCallData = {
                            from_user_id: signal.from_user_id,
                            from_username: signal.from_username,
                            from_profile_picture: signal.from_profile_picture,
                            call_type: signal.call_type || 'video'
                        };
                        showIncomingCallModal();
                        callModalShown = true;
                        console.log('Incoming call detected and modal shown');
                    }
                    break; // Only show one call at a time
                }
            }
        }
    } catch (error) {
        console.error('Error checking for incoming calls:', error);
    }
}

/**
 * Show incoming call modal
 */
function showIncomingCallModal() {
    if (!incomingCallData) return;

    const modal = document.getElementById('callModal');
    if (!modal) {
        console.error('Call modal element not found!');
        return;
    }

    document.getElementById('callModalTitle').textContent =
        incomingCallData.call_type === 'video' ? 'Incoming Video Call' : 'Incoming Audio Call';
    document.getElementById('callModalName').textContent = incomingCallData.from_username;
    document.getElementById('callModalAvatar').src = 'uploads/' + (incomingCallData.from_profile_picture ? incomingCallData.from_profile_picture : 'default-avatar.png');
    modal.classList.add('active');

    console.log('Incoming call modal shown for:', incomingCallData.from_username);

    // Play incoming ringtone
    const incomingSound = document.getElementById('incomingSound');
    if (incomingSound) {
        incomingSound.currentTime = 0;
        const playPromise = incomingSound.play();
        if (playPromise !== undefined) {
            playPromise.catch(e => {
                console.error('Audio play failed:', e);
            });
        }
    }
}

/**
 * Accept incoming call
 */
async function acceptCall() {
    if (!incomingCallData) return;

    console.log('Accepting call from user:', incomingCallData.from_user_id);
    console.log('Sending call-accepted signal...');

    // STOP checking for incoming calls immediately to prevent consuming signals
    callModalShown = false; // This will stop checkForIncomingCalls from running

    // Send "ready" signal to caller to let them know we accepted
    try {
        const signalPayload = {
            to_user_id: incomingCallData.from_user_id,
            signal_type: 'call-accepted',
            signal_data: { accepted: true },
            call_type: incomingCallData.call_type
        };

        console.log('Signal payload:', signalPayload);

        const response = await fetch('api/send_signal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(signalPayload)
        });

        const result = await response.json();
        console.log('Call acceptance signal sent, response:', result);

        if (!result.success) {
            console.error('Failed to send acceptance signal:', result.message);
            alert('Failed to send acceptance signal: ' + result.message);
            callModalShown = true; // Re-enable if failed
            return;
        }
    } catch (error) {
        console.error('Error sending acceptance signal:', error);
        alert('Error sending acceptance signal: ' + error.message);
        callModalShown = true; // Re-enable if failed
        return;
    }

    // Delete the call-request signal
    try {
        await fetch('api/delete_signal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                from_user_id: incomingCallData.from_user_id
            })
        });
    } catch (error) {
        console.error('Error deleting signal:', error);
    }

    // Update status
    await updateUserStatus('on_call');

    // Close modal
    document.getElementById('callModal').classList.remove('active');

    // Stop sound
    stopRingTone();

    console.log('=== ACCEPTANCE COMPLETE, REDIRECTING IMMEDIATELY ===');

    // Redirect to call page as receiver (not initiator) - NO DELAY
    window.location.href = `call.php?user_id=${incomingCallData.from_user_id}&type=${incomingCallData.call_type}&initiator=false`;
}

/**
 * Reject incoming call
 */
async function rejectCall() {
    if (!incomingCallData) return;

    console.log('Rejecting call from user:', incomingCallData.from_user_id);

    // Send "call-rejected" signal to the caller
    try {
        const signalPayload = {
            to_user_id: incomingCallData.from_user_id,
            signal_type: 'call-rejected',
            signal_data: { rejected: true },
            call_type: incomingCallData.call_type
        };

        console.log('Sending rejection signal:', signalPayload);

        const response = await fetch('api/send_signal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(signalPayload)
        });

        const result = await response.json();
        console.log('Call rejection signal sent, response:', result);
    } catch (error) {
        console.error('Error sending rejection signal:', error);
    }

    // Delete the call-request signal
    try {
        await fetch('api/delete_signal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                from_user_id: incomingCallData.from_user_id
            })
        });
    } catch (error) {
        console.error('Error deleting signal:', error);
    }

    // Close modal
    callModalShown = false;
    document.getElementById('callModal').classList.remove('active');

    // Clear the call data
    // Clear the call data
    incomingCallData = null;

    // Stop sound
    stopRingTone();
}

/**
 * Stop all ringtones
 */
function stopRingTone() {
    const incomingSound = document.getElementById('incomingSound');
    const outgoingSound = document.getElementById('outgoingSound');

    if (incomingSound) {
        incomingSound.pause();
        incomingSound.currentTime = 0;
    }

    if (outgoingSound) {
        outgoingSound.pause();
        outgoingSound.currentTime = 0;
    }
}


