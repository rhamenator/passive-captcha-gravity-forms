// Generates a random session token
export function generateToken() {
  const array = new Uint8Array(16);
  window.crypto.getRandomValues(array);
  return Array.from(array)
    .map((b) => b.toString(16).padStart(2, "0"))
    .join("");
}

// Validates token format
export function validateToken(token) {
  return typeof token === "string" && token.length === 32; // Example rule: token length
}

export function generateSessionToken() {
  return generateToken(); // Uses generateToken internally
}

export function attachSubmitHandler(
  field,
  sessionToken,
  fingerprintHash,
  timeLoaded
) {
  const forms = document.querySelectorAll("form");
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const elapsed = Date.now() - timeLoaded;
      const payload = {
        ts: Date.now(),
        elapsed,
        token: sessionToken,
        hash: fingerprintHash,
      };
      field.value = btoa(JSON.stringify(payload));
      console.debug(`[CAPTCHA] Payload set: ${JSON.stringify(payload)}`);
    });
  });
}

export function attachClickHandler(
  field,
  sessionToken,
  fingerprintHash,
  timeLoaded
) {
  const buttons = document.querySelectorAll("button");
  buttons.forEach((button) => {
    button.addEventListener("click", (e) => {
      const elapsed = Date.now() - timeLoaded;
      const payload = {
        ts: Date.now(),
        elapsed,
        token: sessionToken,
        hash: fingerprintHash,
      };
      field.value = btoa(JSON.stringify(payload));
      console.debug(`[CAPTCHA] Payload set: ${JSON.stringify(payload)}`);
    });
  });

  const links = document.querySelectorAll("a");
  links.forEach((link) => {
    link.addEventListener("click", (e) => {
      const elapsed = Date.now() - timeLoaded;
      const payload = {
        ts: Date.now(),
        elapsed,
        token: sessionToken,
        hash: fingerprintHash,
      };
      field.value = btoa(JSON.stringify(payload));
      console.debug(`[CAPTCHA] Payload set: ${JSON.stringify(payload)}`);
    });
  });
}
