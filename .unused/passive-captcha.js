document.addEventListener('DOMContentLoaded', () => {
  try {
      logDebug('[CAPTCHA] DOMContentLoaded');

      const tokenField = findCaptchaTokenField();
      if (!tokenField) {
          logMissingFieldWarning();
          return;
      }

      const sessionToken = generateSessionToken();
      const fingerprintHash = buildClientFingerprint(); // From fingerprint.js
      const timeLoaded = Date.now();

      attachSubmitHandler(tokenField, sessionToken, fingerprintHash, timeLoaded);
  } catch (e) {
      console.error('[CAPTCHA] JS Error:', e);
      logDebug(`[CAPTCHA] JS Exception: ${e.message}`);
  }
});
