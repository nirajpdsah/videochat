# 📱 WhatsApp-Like Features Implementation Plan

## Current Status
✅ Basic call notifications working
✅ Signal cleanup working
⚠️ Need to add WhatsApp-like UX features

## 🎯 WhatsApp-Like Features To Implement

### Phase 1: Essential Call Flow Features (HIGH PRIORITY)

#### 1. **Ringing Sound** 🔔
- [ ] Add ringtone when receiving call
- [ ] Add calling tone for initiator
- [ ] Stop sound when call is answered/rejected

#### 2. **Call Rejection Notification** ❌
- [ ] Send "call-rejected" signal to caller
- [ ] Show "Call Declined" message to caller
- [ ] Auto-redirect caller back to dashboard

#### 3. **Call Timeout** ⏱️
- [ ] Auto-cancel call after 30 seconds if not answered
- [ ] Show "No Answer" status to caller
- [ ] Send "call-timeout" signal
- [ ] Clean up signals on timeout

#### 4. **Call Duration Timer** ⏰
- [ ] Show call duration during active call
- [ ] Format as MM:SS or HH:MM:SS
- [ ] Display prominently in call UI

#### 5. **Proper End Call Signaling** 📞
- [ ] Send "call-ended" signal when either user hangs up
- [ ] Show "Call Ended" message
- [ ] Redirect both users to dashboard
- [ ] Clean up all call signals

### Phase 2: Call History & Notifications (MEDIUM PRIORITY)

#### 6. **Call History/Logs** 📊
- [ ] Database table for call logs
- [ ] Track: caller, receiver, type, duration, status, timestamp
- [ ] Status: completed, missed, rejected, no-answer
- [ ] Display recent calls on dashboard

#### 7. **Missed Call Badges** 🔴
- [ ] Show red badge for missed calls
- [ ] Display count of missed calls per user
- [ ] Clear badge when call history is viewed

#### 8. **Better Call Status Messages** 💬
- [ ] "Ringing..."
- [ ] "Connecting..."
- [ ] "Connected"
- [ ] "Call Ended"
- [ ] "Call Declined"
- [ ] "No Answer"
- [ ] "User Busy"

### Phase 3: Enhanced UX (MEDIUM PRIORITY)

#### 9. **Connection Quality Indicator** 📶
- [ ] Monitor WebRTC connection stats
- [ ] Show signal strength bars
- [ ] Display "Poor Connection" warning

#### 10. **Reconnection Handling** 🔄
- [ ] Auto-reconnect on network drop
- [ ] Show "Reconnecting..." status
- [ ] Timeout after 15 seconds of reconnection attempts

#### 11. **Picture-in-Picture Mode** 📺
- [ ] Minimize video to corner
- [ ] Continue call while browsing
- [ ] Bring back to full screen on click

### Phase 4: Advanced Features (LOW PRIORITY)

#### 12. **Screen Sharing** 🖥️
- [ ] Share screen button
- [ ] Switch between camera and screen
- [ ] Show "Sharing Screen" indicator

#### 13. **Group Calls** 👥
- [ ] Support 3+ participants
- [ ] Grid view for multiple videos
- [ ] Individual mute controls

#### 14. **Chat During Call** 💬
- [ ] Text chat overlay during video call
- [ ] Show new message indicator
- [ ] Persist messages

#### 15. **Call Recording** 🎥
- [ ] Record video/audio with permission
- [ ] Save to server
- [ ] Download option

## 🚀 Implementation Order

### Week 1: Core Call Flow
1. Ringing sounds
2. Call rejection notification
3. Call timeout
4. Call duration timer
5. Proper end call signaling

### Week 2: History & UI
6. Call history database
7. Missed call badges
8. Better status messages
9. Connection quality indicator

### Week 3: Advanced Features
10. Reconnection handling
11. Picture-in-Picture
12. Screen sharing (if needed)

## 📋 Technical Implementation Details

### New Database Table: `call_logs`
```sql
CREATE TABLE call_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    caller_id INT NOT NULL,
    receiver_id INT NOT NULL,
    call_type ENUM('video', 'audio') NOT NULL,
    status ENUM('completed', 'missed', 'rejected', 'no_answer', 'busy'),
    duration INT DEFAULT 0, -- seconds
    started_at DATETIME,
    ended_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (caller_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);
```

### New Signal Types
- `call-rejected` - User rejected the call
- `call-timeout` - No answer after 30 seconds
- `call-ended` - Either user ended the call
- `call-answered` - Receiver accepted (for caller confirmation)

### New API Endpoints
1. `api/save_call_log.php` - Save call history
2. `api/get_call_history.php` - Retrieve call logs
3. `api/mark_calls_seen.php` - Clear missed call badges

### JavaScript Changes
1. Add audio files for ringtones
2. Implement timeout timers
3. Add call duration counter
4. Improve signal handling
5. Add call state management

### UI Components
1. Ringing screen with animation
2. Call status overlay
3. Call history list
4. Missed call badges
5. Call duration display

## 🎨 UI/UX Improvements

### Calling Screen
- Animated ringing indicator
- User avatar (large)
- "Calling..." text
- Cancel button (prominent)

### Incoming Call Screen
- Full-screen modal
- Ringing animation
- Large avatar
- Accept (green) and Reject (red) buttons
- Swipe to answer/reject (mobile)

### Active Call Screen
- Full video layout
- Small self-view (picture-in-picture)
- Call duration at top
- Connection quality indicator
- Control buttons at bottom:
  - Mute/Unmute
  - Video On/Off
  - End Call (red)
  - Switch Camera
  - Speaker/Earpiece toggle

### Call History
- List grouped by date
- Icons for call type (video/audio)
- Status indicators (missed, completed, rejected)
- Duration display
- Timestamp
- Quick call-back button

## 📁 Files to Create/Modify

### New Files
- `sounds/ringtone.mp3` - Incoming call sound
- `sounds/calling.mp3` - Outgoing call tone
- `sounds/call_end.mp3` - Call ended sound
- `api/save_call_log.php`
- `api/get_call_history.php`
- `api/mark_calls_seen.php`
- `call_history.php` - Call history page
- `js/call_manager.js` - Call state management

### Files to Modify
- `js/dashboard.js` - Add timeout, rejection, sounds
- `js/webrtc.js` - Add duration timer, end call signaling
- `dashboard.php` - Add call history link, missed call badges
- `call.php` - Add duration display, better UI
- `database.sql` - Add call_logs table

## 🎯 Quick Wins (Implement First)

1. **Ringing Sound** (15 min)
2. **Call Rejection** (30 min)
3. **Call Timeout** (30 min)
4. **Duration Timer** (20 min)
5. **End Call Signal** (30 min)

Total: ~2 hours for core WhatsApp-like experience!

## 📝 Next Steps

Would you like me to implement:
1. **Phase 1 features first** (Core call flow - 2 hours)
2. **All features at once** (Complete implementation - 1 day)
3. **Specific features only** (Tell me which ones)

Let me know and I'll start coding! 🚀
