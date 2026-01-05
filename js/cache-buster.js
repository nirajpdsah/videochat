// Clear Browser Cache Utility
// This script should be added to help users clear their cache

(function() {
    'use strict';
    
    // Force HTTPS redirect (must run first, before anything else)
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        location.replace('https://' + location.hostname + location.pathname + location.search);
        return; // Stop execution while redirecting
    }
    
    // Version number to track updates
    const APP_VERSION = '2.0.0'; // Increment this when you make changes
    const VERSION_KEY = 'videochat_version';
    
    // Check stored version
    const storedVersion = localStorage.getItem(VERSION_KEY);
    
    if (storedVersion !== APP_VERSION) {
        console.log('New version detected, clearing cache...');
        
        // Clear localStorage except for critical data
        const keysToKeep = ['user_session']; // Add keys you want to keep
        const allKeys = Object.keys(localStorage);
        
        allKeys.forEach(key => {
            if (!keysToKeep.includes(key)) {
                localStorage.removeItem(key);
            }
        });
        
        // Clear sessionStorage
        sessionStorage.clear();
        
        // Update version
        localStorage.setItem(VERSION_KEY, APP_VERSION);
        
        console.log('Cache cleared, version updated to ' + APP_VERSION);
    }
    
    // Unregister any service workers (if they exist)
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for (let registration of registrations) {
                registration.unregister();
                console.log('Service worker unregistered');
            }
        });
    }
    
    // Clear cache storage API
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }
    
})();
