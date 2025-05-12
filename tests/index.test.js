import { initPassiveCaptcha } from '../js/index.js';
import { generateFingerprint } from '../js/fingerprint.js';
import { performMathChallenge } from '../js/mathChallenge.js';
import { initializeSession } from '../js/session.js';
import { getJA3Fingerprint } from '../js/ja3Integration.js';
import { logDebug, logWarning } from '../js/logger.js';

jest.mock('../js/fingerprint.js', () => ({
  generateFingerprint: jest.fn(() => Promise.resolve('mockFingerprint')),
}));

jest.mock('../js/mathChallenge.js', () => ({
  performMathChallenge: jest.fn(() => 42),
}));

jest.mock('../js/session.js', () => ({
  initializeSession: jest.fn(),
}));

jest.mock('../js/ja3Integration.js', () => ({
  getJA3Fingerprint: jest.fn(() => 'mockJA3'),
}));

jest.mock('../js/logger.js', () => ({
  logDebug: jest.fn(),
  logWarning: jest.fn(),
}));

describe('Passive CAPTCHA Initialization', () => {
  beforeEach(() => {
    document.body.innerHTML = '<form id="testForm"><input type="hidden" name="captchaToken"></form>';
    jest.clearAllMocks();
  });

  test('initializes Passive CAPTCHA and sets hidden field', async () => {
    await initPassiveCaptcha();

    const hiddenField = document.querySelector('input[name="captchaToken"]');
    expect(hiddenField).not.toBeNull();
    expect(hiddenField.value).toContain('mockFingerprint');
    expect(hiddenField.value).toContain('42'); // Math challenge result
  });

  test('calls initialization functions correctly', async () => {
    await initPassiveCaptcha();

    expect(initializeSession).toHaveBeenCalled();
    expect(generateFingerprint).toHaveBeenCalled();
    expect(performMathChallenge).toHaveBeenCalled();
    expect(getJA3Fingerprint).toHaveBeenCalled();
    expect(logDebug).toHaveBeenCalledWith('Initializing Passive CAPTCHA.');
  });

  test('logs warning if hidden CAPTCHA field is missing', async () => {
    document.body.innerHTML = '<form id="testForm"></form>'; // No hidden field

    await initPassiveCaptcha();

    expect(logWarning).toHaveBeenCalledWith(
      'Hidden CAPTCHA field not found. Make sure a Hidden field labeled "CAPTCHA Token" or name="captcha_token" exists.'
    );
  });
});
test('logs warning on initialization error', async () => {
  generateFingerprint.mockImplementationOnce(() => {
    throw new Error('Fingerprint generation failed');
  });

  await initPassiveCaptcha();

  expect(logWarning).toHaveBeenCalledWith('Initialization error: Fingerprint generation failed');
}); 