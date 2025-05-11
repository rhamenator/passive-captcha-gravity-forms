import { generateToken, validateToken, generateSessionToken, attachSubmitHandler, attachClickHandler } from '../js/token-handler.js';

describe('Token Handler Module', () => {
  test('generates a valid token', () => {
    const token = generateToken();
    
    expect(typeof token).toBe('string');
    expect(token.length).toBe(32); // Ensure token length matches expected format
  });

  test('validates correct token', () => {
    const token = generateToken();
    expect(validateToken(token)).toBe(true);
  });

  test('rejects incorrect token', () => {
    expect(validateToken('invalid-token')).toBe(false);
    expect(validateToken('')).toBe(false);
    expect(validateToken(12345)).toBe(false);
  });

  test('generates session token correctly', () => {
    const sessionToken = generateSessionToken();
    expect(typeof sessionToken).toBe('string');
    expect(sessionToken.length).toBe(32);
  });

  test('attaches submit handler to forms', () => {
    document.body.innerHTML = '<form id="testForm"><input type="hidden" name="captchaToken"></form>';
    const field = document.querySelector('input[name="captchaToken"]');
    const sessionToken = generateToken();
    const fingerprintHash = "testFingerprint";
    const timeLoaded = Date.now();

    attachSubmitHandler(field, sessionToken, fingerprintHash, timeLoaded);

    // Simulate form submission
    document.querySelector('#testForm').dispatchEvent(new Event('submit'));

    expect(field.value).not.toBe('');
    expect(typeof JSON.parse(atob(field.value))).toBe('object');
  });

  test('attaches click handler to buttons and links', () => {
    document.body.innerHTML = '<button id="testButton">Click Me</button><a id="testLink" href="#">Test Link</a><input type="hidden" name="captchaToken">';
    const field = document.querySelector('input[name="captchaToken"]');
    const sessionToken = generateToken();
    const fingerprintHash = "testFingerprint";
    const timeLoaded = Date.now();

    attachClickHandler(field, sessionToken, fingerprintHash, timeLoaded);

    // Simulate button click
    document.querySelector('#testButton').dispatchEvent(new Event('click'));

    expect(field.value).not.toBe('');
    expect(typeof JSON.parse(atob(field.value))).toBe('object');

    // Simulate link click
    document.querySelector('#testLink').dispatchEvent(new Event('click'));

    expect(field.value).not.toBe('');
    expect(typeof JSON.parse(atob(field.value))).toBe('object');
  });
});
