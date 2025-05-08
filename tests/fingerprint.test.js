import { generateFingerprint } from '../js/fingerprint.js';
import { createCanvas } from 'canvas'; // Import from canvas package

// Mock document.createElement to use the 'canvas' package
jest.spyOn(document, 'createElement').mockImplementation(() => createCanvas());

// Test that generateFingerprint() returns a valid fingerprint
test('generates fingerprint with expected properties', async () => {
  const fingerprint = await generateFingerprint();

  expect(typeof fingerprint).toBe('string'); // Fingerprint should be a base64 string
  expect(fingerprint.length).toBeGreaterThan(10); // Ensure it's a valid fingerprint
});

// Test how generateFingerprint() handles errors
test('handles fingerprint errors gracefully', async () => {
  jest.spyOn(navigator, 'userAgent', 'get').mockReturnValue(undefined); // Simulate userAgent error

  const fingerprint = await generateFingerprint();

  expect(typeof fingerprint).toBe('string');
  expect(fingerprint).toBe(btoa(navigator.userAgent)); // Should fall back to userAgent base64 encoding
});
