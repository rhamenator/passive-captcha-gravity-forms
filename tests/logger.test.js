import { Logger } from '../js/logger.js';

// Mock console methods
console.log = jest.fn();
console.warn = jest.fn();
console.error = jest.fn();

describe('Logger Module', () => {
  let logger;

  beforeEach(() => {
    logger = new Logger();
    jest.clearAllMocks();
  });

  test('should log messages correctly', () => {
    logger.log('This is a log message');
    expect(console.log).toHaveBeenCalledWith('[Logger]', 'This is a log message');
  });

  test('should log warnings correctly', () => {
    logger.warn('This is a warning message');
    expect(console.warn).toHaveBeenCalledWith('[Logger]', 'This is a warning message');
  });

  test('should log errors correctly', () => {
    logger.error('This is an error message');
    expect(console.error).toHaveBeenCalledWith('[Logger]', 'This is an error message');
  });

  test('should properly store log history', () => {
    logger.log('First log');
    logger.warn('First warning');
    logger.error('First error');

    expect(logger.history).toEqual([
      { level: 'log', message: 'First log' },
      { level: 'warn', message: 'First warning' },
      { level: 'error', message: 'First error' }
    ]);
  });

  test('should clear logs correctly', () => {
    logger.log('Log to clear');
    logger.clear();
    expect(logger.history).toEqual([]);
  });
});
