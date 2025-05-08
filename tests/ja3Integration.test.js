import { getJA3Fingerprint } from '../js/ja3Integration.js';

test('returns JA3 fingerprint when available', () => {
  window.pchData = { ja3Fingerprint: 'mocked_fingerprint_hash' };

  expect(getJA3Fingerprint()).toBe('mocked_fingerprint_hash');
});

test('returns null when JA3 fingerprint is missing', () => {
  window.pchData = {}; // No fingerprint injected

  expect(getJA3Fingerprint()).toBe(null);
});
