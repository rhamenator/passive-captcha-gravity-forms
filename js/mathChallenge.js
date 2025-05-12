// mathChallenge.js
export function performMathChallenge() {
  const a = Math.floor(Math.random() * 10) + 1;
  const b = Math.floor(Math.random() * 10) + 1;
  // store for later server validation if needed:
  window.pchMathAnswer = a * b;
  return `${a}x${b}`; // e.g. "3x7"
}
