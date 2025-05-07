// ja3Integration.js
// JA3 fingerprinting must be injected server-side (e.g. NGINX/Lua) into pchData.ja3Fingerprint
export function getJA3Fingerprint() {
    return window.pchData?.ja3Fingerprint || null;
  }
  