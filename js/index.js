import { generateFingerprint } from './fingerprint.js';
import { performMathChallenge } from './mathChallenge.js';
import { logDebug, logWarning } from './logger.js';
import { initializeSession } from './session.js';
import { getJA3Fingerprint } from './ja3Integration.js';

async function initPassiveCaptcha() {
  logDebug('Initializing Passive CAPTCHA.');

  try {
    // 1. Session management
    initializeSession();

    // 2. Invisible math challenge
    const mathResult = performMathChallenge();
    logDebug(`Math challenge result: ${mathResult}`);

    // 3. Client fingerprint
    let fingerprint = await generateFingerprint();
    logDebug(`Client fingerprint: ${fingerprint}`);

    // 4. Optional JA3 from pchData (provided by PHP if available)
    const ja3 = getJA3Fingerprint();
    if (ja3) {
      logDebug(`JA3 fingerprint added: ${ja3}`);
      fingerprint += `-${ja3}`;
    }

    // 5. Populate hidden CAPTCHA field
    const hiddenField = document.querySelector('input[type=hidden][name="captcha_token"], input[type=hidden][name="CAPTCHA Token"]');
    if (hiddenField) {
      hiddenField.value = `${fingerprint}:${mathResult}`;
      logDebug('Hidden CAPTCHA field populated.');
    } else {
      logWarning('Hidden CAPTCHA field not found. Make sure a Hidden field labeled "CAPTCHA Token" or name="captcha_token" exists.');
    }
  } catch (err) {
    logWarning(`Initialization error: ${err.message}`);
  }
}

document.addEventListener('DOMContentLoaded', initPassiveCaptcha);
