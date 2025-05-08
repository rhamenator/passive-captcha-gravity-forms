import { generateToken, validateToken } from '../js/token-handler';

test('generates a valid token', () => {
  const token = generateToken();
  
  expect(typeof token).toBe('string');
  expect(token.length).toBeGreaterThan(10);
});

test('validates correct token', () => {
  const token = generateToken();
  expect(validateToken(token)).toBe(true);
});

test('rejects incorrect token', () => {
  expect(validateToken('invalid-token')).toBe(false);
});
