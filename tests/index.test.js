import { initCaptcha } from '../js/index';

test('initializes Passive CAPTCHA correctly', () => {
  document.body.innerHTML = '<form id="testForm"></form>';
  
  initCaptcha();
  
  expect(document.querySelector('#testForm input[name="captchaToken"]')).not.toBeNull();
});
