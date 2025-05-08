import { generateChallenge, validateAnswer } from '../js/mathChallenge';

test('generates a math challenge', () => {
  const challenge = generateChallenge();
  expect(challenge).toHaveProperty('question');
  expect(challenge).toHaveProperty('answer');
  expect(typeof challenge.question).toBe('string');
  expect(typeof challenge.answer).toBe('number');
});

test('validates correct answer', () => {
  const challenge = generateChallenge();
  expect(validateAnswer(challenge.answer, challenge)).toBe(true);
});

test('rejects incorrect answer', () => {
  const challenge = generateChallenge();
  expect(validateAnswer(challenge.answer + 1, challenge)).toBe(false);
});
