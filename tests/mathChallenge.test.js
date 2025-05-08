import { performMathChallenge } from '../js/mathChallenge.js';

test('generates a valid multiplication challenge', () => {
  const challenge = performMathChallenge();
  
  expect(typeof challenge).toBe('string'); // Should be a string like "3x7"
  expect(/\d+x\d+/.test(challenge)).toBe(true); // Matches format "AxB"
});

test('stores answer in window object', () => {
  performMathChallenge();
  
  expect(typeof window.pchMathAnswer).toBe('number'); // Answer should be a number
  expect(window.pchMathAnswer).toBeGreaterThanOrEqual(1); // Ensure it's a valid multiplication result
});
