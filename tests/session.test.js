import { initializeSession } from '../js/session.js';

test('session initializes correctly', () => {
  initializeSession();
  
  expect(window.pchSessionInitialized).toBe(true);
  expect(typeof window.pchSessionStart).toBe('number');
});

test('session start time is stored', () => {
  initializeSession();

  // Ensure sessionStorage is storing the timestamp
  expect(sessionStorage.getItem('pchSessionStart')).toEqual(window.pchSessionStart.toString());
});
