// session.js
export function initializeSession() {
    if (!window.pchSessionInitialized) {
      window.pchSessionInitialized = true;
      window.pchSessionStart = Date.now();
      // Optionally store in sessionStorage:
      try {
        sessionStorage.setItem('pchSessionStart', window.pchSessionStart);
      } catch {}
    }
  }
  