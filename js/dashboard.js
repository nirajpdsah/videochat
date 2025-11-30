/**
 * Dashboard JavaScript
 * Handles user list, chat functionality, and call initiation
 */

let users = [];
let pollInterval = null;
let messagesInterval = null;

// Load users on page load
document.addEventListener('DOMContentLoaded', function() {
    loadUsers();
    updateUserStatus('online');
    
    // Poll for users every 3 seconds
    pollInterval = setInterval(loadUsers, 3000);
    
    // Poll for incoming calls every 2 seconds
    setInterval(checkForIncomingCalls, 2000);
    
    // Update status before leaving page
    window.addEventListener('beforeunload', function() {
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
        const statusText = user.status === 'online' ? 'Online' : 
                          user.status === 'on_call' ? 'On a call' : 'Offline';
        
        html += `
            <div class="user-item" data-user-id="${user.id}">
                <img src="uploads/${user.profile_picture}" alt="${user.username}">
                <div class="user-item-info">
                    <h4>${user.username}</h4>
                    <p>
                        <span class="status-indicator ${statusClass}"></span>
                        ${statusText}
                    </p>
                </div>
                <div class="user-actions">
                    <button class="action-btn video" onclick="initiateCall(${user.id}, '${user.username}', 'video')" 
                            ${user.status === 'on_call' ? 'disabled' : ''} title="Video Call">
                        📹
                    </button>
                    <button class="action-btn audio" onclick="initiateCall(${user.id}, '${user.username}', 'audio')" 
                            ${user.status === 'on_call' ? 'disabled' : ''} title="Audio Call">
                        📞
                    </button>
                    <button class="action-btn chat" onclick="openChat(${user.id}, '${user.username}', '${user.profile_picture}')" 
                            title="Chat">
                        💬
                    </button>
                </div>
            </div>
        `;
    });
    
    usersList.innerHTML = html;
}

/**
 * Update current user's status
 */
async function updateUserStatus(status) {
    try {
        await fetch('api/update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({status})
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
            headers: {'Content-Type': 'application/json'},
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
    
    // Show calling modal
    showCallingModal(username, user.profile_picture);
    
    // Update own status
    updateUserStatus('on_call');
    
    // Redirect to call page as initiator
    setTimeout(() => {
        window.location.href = `call.php?user_id=${userId}&type=${callType}&initiator=true`;
    }, 1000);
}

/**
 * Show calling modal
 */
function showCallingModal(username, profilePic) {
    const modal = document.getElementById('callingModal');
    document.getElementById('callingModalName').textContent = username;
    document.getElementById('callingModalAvatar').src = 'uploads/' + profilePic;
    modal.classList.add('active');
}

/**
 * Cancel call
 */
function cancelCall() {
    document.getElementById('callingModal').classList.remove('active');
    updateUserStatus('online');
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
 * Open chat with a user
 */
function openChat(userId, username, profilePic) {
    selectedUserId = userId;
    selectedUsername = username;
    
    // Update chat header
    document.getElementById('chatHeader').innerHTML = `
        <img src="uploads/${profilePic}" alt="${username}" class="profile-pic-small">
        <span>${username}</span>
    `;
    
    // Show chat input
    document.getElementById('chatInput').style.display = 'flex';
    
    // Load messages
    loadMessages();
    
    // Start polling for new messages
    if (messagesInterval) {
        clearInterval(messagesInterval);
    }
    messagesInterval = setInterval(loadMessages, 2000);
    
    // Highlight selected user
    document.querySelectorAll('.user-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`[data-user-id="${userId}"]`).classList.add('active');
}

/**
 * Load messages with selected user
 */
async function loadMessages() {
    if (!selectedUserId) return;
    
    try {
        const response = await fetch(`api/messages.php?user_id=${selectedUserId}`);
        const data = await response.json();
        
        if (data.success) {
            displayMessages(data.messages);
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

/**
 * Display messages in chat
 */
function displayMessages(messages) {
    const chatMessages = document.getElementById('chatMessages');
    
    if (messages.length === 0) {
        chatMessages.innerHTML = '<div class="loading">No messages yet. Start chatting!</div>';
        return;
    }
    
    let html = '';
    messages.forEach(msg => {
        const isSent = msg.from_user_id === currentUserId;
        const messageClass = isSent ? 'sent' : '';
        
        html += `
            <div class="message ${messageClass}">
                <img src="uploads/${msg.profile_picture}" alt="${msg.username}">
                <div class="message-content">
                    <p>${msg.message}</p>
                    <span>${formatTime(msg.created_at)}</span>
                </div>
            </div>
        `;
    });
    
    chatMessages.innerHTML = html;
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

/**
 * Send a message
 */
async function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message || !selectedUserId) return;
    
    try {
        const response = await fetch('api/messages.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                to_user_id: selectedUserId,
                message: message
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            input.value = '';
            loadMessages();
        }
    } catch (error) {
        console.error('Error sending message:', error);
    }
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
        return date.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
    }
    
    // Otherwise
    return date.toLocaleDateString('en-US', {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
}

/**
 * Check for incoming call requests
 */
let incomingCallData = null;

async function checkForIncomingCalls() {
    try {
        const response = await fetch('api/get_signals.php');
        const data = await response.json();
        
        if (data.success && data.signals.length > 0) {
            for (const signal of data.signals) {
                // Check for call request
                if (signal.signal_type === 'call-request') {
                    // Show incoming call modal
                    incomingCallData = {
                        from_user_id: signal.from_user_id,
                        from_username: signal.from_username,
                        from_profile_picture: signal.from_profile_picture,
                        call_type: signal.call_type
                    };
                    showIncomingCallModal();
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
    document.getElementById('callModalTitle').textContent = 
        incomingCallData.call_type === 'video' ? 'Incoming Video Call' : 'Incoming Audio Call';
    document.getElementById('callModalName').textContent = incomingCallData.from_username;
    document.getElementById('callModalAvatar').src = 'uploads/' + incomingCallData.from_profile_picture;
    modal.classList.add('active');
    
    // Play notification sound (optional)
    // You can add a sound file and play it here
}

/**
 * Accept incoming call
 */
function acceptCall() {
    if (!incomingCallData) return;
    
    // Update status
    updateUserStatus('on_call');
    
    // Redirect to call page as receiver (not initiator)
    window.location.href = `call.php?user_id=${incomingCallData.from_user_id}&type=${incomingCallData.call_type}&initiator=false`;
}

/**
 * Reject incoming call
 */
async function rejectCall() {
    if (!incomingCallData) return;
    
    // Close modal
    document.getElementById('callModal').classList.remove('active');
    
    // Send rejection signal (optional - you can implement this)
    // For now, just clear the call data
    incomingCallData = null;
}

// Allow Enter key to send message
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('messageInput');
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
});