# वार्ताLaap (Wartalaap) - Developer Guide

Welcome to the **Wartalaap** project! This document is designed to help you understand exactly how this video calling application works. We've broken down every file and folder in simple terms so you can jump right in and start contributing or debugging.

---

## 🏗️ Project Overview

Wartalaap is a real-time video and audio calling application. It uses a technology called **WebRTC** to allow users to talk directly to each other (peer-to-peer) through the browser.

### How it works (The Big Picture):
1.  **User A** selects **User B** on the Dashboard.
2.  Our server acts as a "Signal System" to pass messages like "I want to call you" or "Here is my connection info" between them.
3.  Once they exchange this info, their browsers connect **directly** to transfer video and audio. The server steps back and just watches the status.

---

## 📂 File Documentation

Here is a detailed explanation of every important file in the codebase.

### 1. The Main Pages (Frontend)

These are the PHP files that users actually see in their browser.

-   **`index.php` (The Landing Page)**
    -   **What it is:** The very first page visitors see.
    -   **What it does:** Displays the "Wartalaap" branding, a cool neon glow effect, and buttons to Login or Sign Up. It's purely for making a good first impression.

-   **`login.php` & `signup.php`**
    -   **What they are:** Authentication pages.
    -   **What they do:** 
        -   `signup.php`: Takes a username, email, and password. It receives a photo, saves it, and creates a new database entry.
        -   `login.php`: Checks if the username and password match what's in the database. If yes, it saves the user's ID in a "Session" (a way for the server to remember who is logged in) and sends them to the Dashboard.

-   **`dashboard.php` (The Main Hub)**
    -   **What it is:** The screen users see after logging in.
    -   **What it does:** 
        -   Shows the standard user list on the left.
        -   Shows the "WhatsApp-style" profile view on the right.
        -   Contains hidden "Modals" (popups) that appear when you are calling someone or receiving a call.
        -   Plays the *ringtone* and *ringback* sounds.

-   **`call.php` (The Conversation Screen)**
    -   **What it is:** The page you are redirected to when a call starts.
    -   **What it does:** 
        -   This is where the magic happens. It shows the full-screen video of the person you are talking to.
        -   It shows your own face in a small, draggable window (Picture-in-Picture).
        -   It loads the heavy `webrtc.js` logic to handle the actual media connection.

---

### 2. The Brains (JavaScript)

These files handle all the interactivity.

-   **`js/dashboard.js`**
    -   **Role:** The "Manager" of the Dashboard.
    -   **Key Jobs:**
        -   **Polling:** Every few seconds, it asks the server "Who is online?" and "Is anyone calling me?".
        -   **UI Updates:** It builds the list of users you see on the sidebar.
        -   **Call Logic:** When you click "Video Call", this file tells the server "Tell User B that User A is calling".
        -   **Sound:** It controls the ringing sounds (`sounds/ringtone.mp3`).

-   **`js/webrtc.js`**
    -   **Role:** The "Engineer" of the Video Call.
    -   **Key Jobs:**
        -   **Camera/Mic Access:** It asks the browser for permission to use the webcam.
        -   **Peer Connection:** It uses specific internet protocols (ICE Candidates, SDP) to find a path through the internet to connect User A's computer to User B's computer.
        -   **Stream Handling:** It takes the video coming from the other person and puts it into the `<video>` tag on `call.php`.
        -   **Draggable Video:** It contains the code that lets you drag your own video window around the screen.

---

### 3. The Look & Feel (CSS)

-   **`css/style.css`**
    -   **What it is:** The one file to rule them all.
    -   **Style:** We use a "Glassmorphism" + "Neon" aesthetic.
        -   **Glassmorphism:** Semi-transparent backgrounds with blur filters (`backdrop-filter: blur(10px)`), making elements look like frosted glass.
        -   **Neon:** Bright gradients (Purples, Pinks, Violets) against a deep dark background.
    -   **Responsiveness:** It ensures the app looks good on Phones, Tablets, and Laptops.

---

### 4. The Server (Backend API)

These files live in the `api/` folder. They are like waiters—the JavaScript (customer) asks them for something, and they fetch it from the Database (kitchen).

-   **`api/get_users.php`**
    -   Fetches a list of all registered users so `dashboard.js` can display them.

-   **`api/send_signal.php`**
    -   **Concept:** "Signaling". Since User A and User B don't know each other's IP addresses yet, they send messages to this file.
    -   **Job:** Takes a message (like "Here is my IP") and saves it in the database for the other person to read.

-   **`api/get_signals.php`**
    -   **Job:** The receiver (User B) basically asks this file every second: "Do I have any new messages?"
    -   If User A sent a signal, this file delivers it to User B.

-   **`api/update_status.php`**
    -   Updates whether a user is "Online", "Offline", or "On a Call" so the green/red dots on the dashboard are accurate.

-   **`api/delete_signal.php`**
    -   Cleans up old messages so the database doesn't get clogged with old call requests.

---

### 5. Configuration & Database

-   **`config.php`**
    -   Contains the secret codes to connect to the MySQL database. **Never share this file publicly.**

-   **`database.sql`**
    -   This is a blueprint. If you are setting up this project on a new computer, running this file in your database tool (like phpMyAdmin) will create all the necessary tables (`users`, `signals`, etc.).

---

## 🎵 Sounds Directory

-   **`sounds/` folder**
    -   **`ringtone.mp3`**: Plays when YOU receive a call.
    -   **`ringback.mp3`**: Plays when YOU are calling someone else (waiting for them to pick up).
    -   *(Note: You must provide these files yourself)*.

---

## 🚀 How a Call Happens (Step-by-Step)

If you are debugging a "Call Failed" issue, follow this flow:

1.  **Initiation:** User A clicks "Call". `dashboard.js` sends a signal via `api/send_signal.php`.
2.  **Notification:** User B's `dashboard.js` (which is polling `api/get_signals.php`) sees the signal and opens the "Incoming Call" modal.
3.  **Acceptance:** User B clicks "Accept". They are redirected to `call.php`.
4.  **Connection:** User A is also redirected to `call.php`. Both browsers load `webrtc.js`.
5.  **Handshake:** `webrtc.js` on both ends starts exchanging technical details (ICE Candidates) through the API.
6.  **Media Flow:** Once the handshake is complete, the API is no longer needed. Video flows directly from A to B.

---

## 🛠️ Tips for Teammates

-   **Icons Missing?** Check `css/style.css` for `.control-btn svg`. We force them to be 24px wide for desktop visibility.
-   **Audio Not Playing?** Browsers block auto-playing sound. Click anywhere on the dashboard once after loading to "unlock" audio.
-   **Database Error?** Check `config.php` to ensure your username/password matches your local XAMPP/WAMP settings.

Happy Coding! 🚀
