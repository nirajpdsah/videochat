# 🎨 CSS Styling Guide - style.css

**File:** `css/style.css`  
**Size:** ~2,148 lines  
**Purpose:** Complete dark-theme styling with glassmorphism design, responsive layouts, and video call UI  
**Used By:** All HTML pages (call.php, dashboard.php, login.php, etc.)

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Design Philosophy](#design-philosophy)
3. [CSS Variables & Color Palette](#css-variables--color-palette)
4. [Layout Systems](#layout-systems)
5. [Key Component Styles](#key-component-styles)
6. [Z-Index Hierarchy](#z-index-hierarchy)
7. [Animations & Effects](#animations--effects)
8. [Responsive Design](#responsive-design)
9. [Debugging Tips](#debugging-tips)

---

## 📖 Overview

This file contains **all styling for the Wartalaap video chat application**. It uses:
- **Glassmorphism Design**: Semi-transparent cards with blur effects
- **Dark Theme**: Deep dark backgrounds (#0f172a) with bright accent colors
- **Neon Aesthetics**: Purple (#8b5cf6), Pink (#ec4899), and Cyan (#06b6d4)
- **Responsive Layout**: Works on mobile, tablet, and desktop

### Key Statistics
- **Font Families**: Inter (body), Outfit (headings)
- **CSS Variables**: 20+ custom properties for colors, effects, radius
- **Animations**: 5+ keyframe animations (pulsing, sliding, fading)
- **Breakpoints**: Mobile (< 768px), Tablet (768px-1024px), Desktop (> 1024px)
- **Z-Index Range**: 1-10000 (organized hierarchy)

---

## 🎯 Design Philosophy

### Glassmorphism
Creates a "frosted glass" effect with:
```css
background: rgba(255, 255, 255, 0.08);
backdrop-filter: blur(16px) saturate(180%);
border: 1px solid rgba(255, 255, 255, 0.08);
```

This makes elements look layered and modern. Used for:
- Modal dialogs
- Card backgrounds
- Navigation elements

### Dark Theme
- **Background**: Gradient from dark blue to purple
- **Text**: Near-white (#f8fafc) for contrast
- **Accents**: Bright neon colors for interactive elements
- **Reduces eye strain** in low-light environments

### Neon Accents
Color palette for primary actions:
```css
--primary: #8b5cf6       /* Purple - main brand */
--secondary: #ec4899     /* Pink - alternatives */
--accent: #06b6d4        /* Cyan - contrast */
--success: #10b981       /* Green - positive */
--danger: #ef4444        /* Red - warnings */
--warning: #f59e0b       /* Orange - caution */
```

---

## 🎨 CSS Variables & Color Palette

### How to Use CSS Variables

Instead of hardcoding colors, use variables:

```css
/* ❌ BAD - Hard to maintain */
button { background: #8b5cf6; }

/* ✅ GOOD - Easy to change globally */
button { background: var(--primary); }
```

Change a color once, and it updates everywhere!

### Complete Color Reference

#### Primary Colors
| Name | Value | Usage |
|------|-------|-------|
| `--primary` | #8b5cf6 | Primary buttons, headings |
| `--primary-dark` | #7c3aed | Hover states |
| `--primary-light` | #a78bfa | Highlights, focus states |
| `--secondary` | #ec4899 | Mic-off badges, alternatives |
| `--accent` | #06b6d4 | Accents, decorative |

#### Status Colors
| Name | Value | Meaning |
|------|-------|---------|
| `--success` | #10b981 | Online status, positive actions |
| `--danger` | #ef4444 | Errors, end call, warnings |
| `--warning` | #f59e0b | Caution, busy status |

#### Dark Theme
| Name | Value | Purpose |
|------|-------|---------|
| `--dark` | #0f172a | Main background |
| `--darker` | #020617 | Deeper backgrounds |
| `--surface` | rgba(255,255,255,0.08) | Card backgrounds |
| `--surface-hover` | rgba(255,255,255,0.12) | Hover state |
| `--border` | rgba(255,255,255,0.08) | Subtle borders |

#### Text Colors
| Name | Value | Usage |
|------|-------|-------|
| `--text-main` | #f8fafc | Primary text |
| `--text-muted` | #94a3b8 | Secondary text, captions |

#### Effects
| Name | Effect | Usage |
|------|--------|-------|
| `--glass` | blur(16px) saturate(180%) | Glassmorphism |
| `--shadow-sm` | 4px 6px blur | Small shadows |
| `--shadow-lg` | 20px 25px blur | Floating elements |
| `--glow` | 0 0 20px purple | Neon glow |

---

## 📐 Layout Systems

### Flexbox Container
Used for centering and alignment:

```css
display: flex;
justify-content: center;  /* Horizontal centering */
align-items: center;      /* Vertical centering */
gap: 1rem;               /* Space between items */
```

### Grid System
Multi-column layouts:

```css
display: grid;
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
gap: 2rem;
```

Auto-adjusts columns based on available space.

### Common Classes

| Class | Purpose |
|-------|---------|
| `.container` | Max-width wrapper, centered |
| `.flex-center` | Centered flexbox |
| `.flex-between` | Space-between layout |
| `.grid-auto` | Auto-responsive grid |

---

## 🎬 Key Component Styles

### Authentication Pages (Login/Signup)

```css
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 1rem;
}

.auth-box {
    background: var(--surface);
    backdrop-filter: var(--glass);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 3rem;
    width: 100%;
    max-width: 400px;
    box-shadow: var(--shadow-lg);
}
```

Features:
- Glassmorphic card with blur effect
- Centered on screen
- Responsive padding
- Responsive max-width

### Dashboard Interface

```css
.dashboard-container {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
    padding: 2rem;
    min-height: 100vh;
}

.sidebar {
    background: var(--surface);
    border-radius: var(--radius);
    padding: 1.5rem;
    height: fit-content;
    position: sticky;
    top: 2rem;
}

.user-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.user-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: var(--radius);
    cursor: pointer;
    transition: background 0.3s ease;
}

.user-item:hover {
    background: var(--surface-hover);
}
```

**Key Features:**
- Two-column grid (sidebar + content)
- Sticky sidebar stays while scrolling
- Hover effects for interactivity

### Video Call Interface (call.php)

#### Remote Video Container
```css
.remote-video-container {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: var(--darker);
    border-radius: var(--radius);
    overflow: hidden;
}

#remoteVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
}
```

#### Video Off Overlay
```css
.video-off-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 100;
    opacity: 0.95;
}

.video-off-overlay.visible {
    display: flex;
}

.video-off-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    margin-bottom: 1rem;
    box-shadow: var(--glow);
}

.video-off-text {
    font-size: 1.1rem;
    font-weight: 500;
    color: var(--text-main);
}
```

#### Mic-Off Indicator (Badge)
```css
.mic-off-indicator {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    width: 50px;
    height: 50px;
    background: var(--secondary);
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    z-index: 150;
    box-shadow: 0 0 20px rgba(236, 72, 153, 0.6);
    animation: pulse-mic 1.5s infinite;
}

.mic-off-indicator.visible {
    display: flex;
}
```

#### Picture-in-Picture (Local Video)
```css
#localVideoContainer {
    position: absolute;
    bottom: 2rem;
    right: 2rem;
    width: 200px;
    height: 150px;
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--darker);
    border: 2px solid var(--primary);
    cursor: move;
    z-index: 10;
    box-shadow: var(--shadow-lg);
}

#localVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scaleX(-1);  /* Mirror effect */
    z-index: auto;
}
```

### Control Buttons
```css
.control-button {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    background: var(--primary);
    color: white;
    font-size: 1.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.control-button:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
    box-shadow: var(--glow);
}

.control-button.danger {
    background: var(--danger);
}

.control-button.danger:hover {
    background: #dc2626;
}
```

---

## 📚 Z-Index Hierarchy

**Important:** Higher z-index = on top of lower z-index

| Z-Index | Element | Purpose |
|---------|---------|---------|
| auto | Video streams | Base layer |
| 10 | Local video container | Floating over remote video |
| 50 | Video info bar | Below overlays |
| 100 | Video-off overlay | Shows avatar when camera off |
| 150 | Mic-off indicator | Badge with pulsing animation |
| 1000 | Modals/Dialogs | Above all page content |
| 10000 | Notifications | Topmost layer |

### Why This Matters
```
⬆️ z-index: 10000 (Notifications - always on top)
⬆️ z-index: 1000 (Modal dialog - overlay everything)
⬆️ z-index: 150 (Mic-off badge - visible over overlay)
⬆️ z-index: 100 (Video-off overlay - visible over video)
⬆️ z-index: 50 (Video info - above video)
⬆️ z-index: 10 (Local video - above remote)
⬇️ z-index: auto (Remote video - base)
```

**Example:** When camera is off:
1. Remote video shows at z-index: auto
2. Video-off overlay (avatar) shows at z-index: 100 → appears OVER video
3. Mic badge shows at z-index: 150 → appears OVER overlay

---

## ✨ Animations & Effects

### 1. Pulsing Animation (Mic-Off Badge)
```css
@keyframes pulse-mic {
    0%, 100% {
        box-shadow: 0 0 20px rgba(236, 72, 153, 0.6);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 0 30px rgba(236, 72, 153, 0.8);
        transform: scale(1.05);
    }
}
```

**Usage:** `.mic-off-indicator { animation: pulse-mic 1.5s infinite; }`

Creates smooth pulsing effect that repeats forever.

### 2. Slide-In Animation
```css
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

Slides elements up while fading in. Used for modal dialogs.

### 3. Fade-In Animation
```css
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
```

Simple opacity transition.

### 4. Button Hover Animation
```css
transition: all 0.3s ease;
```

Smooth 300ms transition for color, transform, and shadow changes.

### How to Debug Animations
1. **Check element exists**: Open DevTools → Elements tab
2. **Check animation applied**: Inspect element → Styles
3. **Verify @keyframes**: Search for animation name in CSS
4. **Test in browser**: You should see smooth, continuous effect
5. **Slow it down**: In DevTools, adjust animation duration temporarily

---

## 📱 Responsive Design

### Breakpoints
```css
/* Mobile First: Default styles apply to mobile */
.component { width: 100%; }

/* Tablet */
@media (min-width: 768px) {
    .component { width: 50%; }
}

/* Desktop */
@media (min-width: 1024px) {
    .component { width: 33.33%; }
}

/* Large Desktop */
@media (min-width: 1280px) {
    .component { width: 25%; }
}
```

### Mobile Responsive Examples

#### Dashboard Grid
```css
/* Mobile: Stack vertically */
.dashboard-container {
    grid-template-columns: 1fr;
}

/* Desktop: Sidebar + main content */
@media (min-width: 768px) {
    .dashboard-container {
        grid-template-columns: 280px 1fr;
    }
}
```

#### Video Call Interface
```css
/* Mobile: Full screen video */
.remote-video-container {
    width: 100%;
    height: 100vh;
}

/* Tablet: Smaller height */
@media (min-width: 768px) {
    .remote-video-container {
        height: calc(100vh - 150px);
    }
}
```

#### Button Sizing
```css
/* Mobile: Larger for touch */
.control-button {
    width: 60px;
    height: 60px;
}

/* Desktop: Standard size */
@media (min-width: 768px) {
    .control-button {
        width: 50px;
        height: 50px;
    }
}
```

### Testing Responsiveness
1. **Chrome DevTools**: Press F12 → Click device toggle icon (📱)
2. **Test breakpoints**: Try 320px, 768px, 1024px widths
3. **Check overlays**: Ensure overlays still visible on mobile
4. **Test touch**: Buttons should be > 44px on mobile
5. **Check text**: Should be readable without zoom

---

## 🐛 Debugging Tips

### Common Issues & Solutions

#### Issue: Overlay Not Visible
```css
/* Check z-index */
.video-off-overlay {
    z-index: 100;  /* ✅ Must be higher than video (auto) */
    display: flex;  /* ✅ Must be flex, not none */
}

/* JavaScript class must be added */
.video-off-overlay.visible {
    display: flex;  /* ✅ Visible class adds to display */
}
```

**Debug:** 
```javascript
// Check if element exists
console.log(document.getElementById('remoteVideoOffOverlay'));

// Check if visible class is applied
console.log(document.getElementById('remoteVideoOffOverlay').classList);

// Check computed styles
console.log(window.getComputedStyle(document.getElementById('remoteVideoOffOverlay')));
```

#### Issue: Mic Badge Not Pulsing
```css
/* Verify animation exists */
@keyframes pulse-mic { /* ... */ }

/* Verify element uses it */
.mic-off-indicator.visible {
    animation: pulse-mic 1.5s infinite;  /* ✅ Check this line */
}
```

#### Issue: Glassmorphism Not Working
```css
/* All three required for glass effect */
.card {
    background: var(--surface);        /* ✅ Semi-transparent background */
    backdrop-filter: var(--glass);     /* ✅ Blur effect */
    border: 1px solid var(--border);   /* ✅ Subtle border */
}
```

**Browser Support:** Backdrop-filter supported in Chrome 76+, Safari 9+, Edge 79+. Firefox: partial support.

#### Issue: Colors Not Changing
```css
/* Use CSS variables correctly */
color: var(--text-main);  /* ✅ Correct */
color: --text-main;       /* ❌ Wrong */
color: var(text-main);    /* ❌ Missing dashes */
```

### DevTools Inspection
1. **Right-click element** → Inspect
2. **Elements tab** → Find the element in DOM
3. **Styles panel** → See all CSS rules
4. **Computed tab** → See final calculated styles
5. **Look for red strikethrough** → Conflicting rules
6. **Check inheritance** → Inherited from parent

### Performance Tips
- Use `transition` instead of frequent JavaScript style changes
- Limit animations to non-critical elements
- Use `will-change` sparingly: `.mic-off-indicator { will-change: transform; }`
- Keep animations smooth: 60fps = smooth, < 30fps = janky
- Test on slower devices to ensure animations remain smooth

---

## 📝 Summary

**style.css** is the visual foundation of Wartalaap. Key takeaways:

✅ **CSS Variables** - Change colors globally via `:root`  
✅ **Z-Index Hierarchy** - Overlays at 100, badges at 150  
✅ **Glassmorphism** - Semi-transparent + blur = modern look  
✅ **Dark Theme** - Reduces eye strain, looks professional  
✅ **Responsive** - Mobile-first design adapts to all screens  
✅ **Animations** - Pulsing badge, smooth transitions  

**When modifying:**
1. Always use CSS variables, not hardcoded colors
2. Check z-index when adding overlays
3. Test responsiveness at 320px, 768px, 1024px
4. Keep animations smooth (use transitions, not JavaScript)
5. Use DevTools to debug styling issues

---

## 🔗 Related Files

- [call.php](CALL.md) - Uses style.css for video interface
- [webrtc.js](WEBRTC.md) - Adds/removes CSS classes for overlays
- [dashboard.php](DASHBOARD.md) - Uses style.css for layout
- [ARCHITECTURE.md](ARCHITECTURE.md) - Overall design system
