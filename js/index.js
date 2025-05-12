import { generateFingerprint } from './fingerprint.js';
import { performMathChallenge } from './mathChallenge.js';
import { logDebug, logWarning } from './logger.js';
import { getJA3Fingerprint } from './ja3Integration.js';

async function testPassiveCaptcha() {
  logDebug('Testing Passive CAPTCHA without WordPress.');

  try {
    // Run JavaScript logic outside of WordPress
    const mathResult = performMathChallenge();
    logDebug(`Math challenge result: ${mathResult}`);

    let fingerprint = await generateFingerprint();
    logDebug(`Client fingerprint generated: ${fingerprint}`);

    const ja3 = getJA3Fingerprint();
    if (ja3) {
      fingerprint += `-${ja3}`;
    }

    const hiddenField = document.querySelector('input[name="captcha_token"]');
    if (hiddenField) {
      hiddenField.value = `${fingerprint}:${mathResult}`;
      logDebug(`Hidden CAPTCHA field populated: ${hiddenField.value}`);
    } else {
      logWarning('Hidden CAPTCHA field not found.');
    }
  } catch (err) {
    logWarning(`Initialization error: ${err.message}`);
  }
}

// Run test logic outside of WordPress
document.addEventListener('DOMContentLoaded', testPassiveCaptcha);
// This function is called when the DOM is fully loaded
// and the script is executed in a non-WordPress context 
// (e.g., a standalone HTML page).
// It will not run in the WordPress context, where the script is loaded via WordPress. 
// In that case, the script will be executed in the WordPress context,
// and the testPassiveCaptcha function will not be called.
// This allows for testing the passive CAPTCHA functionality
// in a standalone environment without relying on WordPress.
// The script will log debug messages to the console
// and populate the hidden CAPTCHA field with the fingerprint and math challenge result. 
