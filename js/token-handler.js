function generateSessionToken() {
    const array = new Uint8Array(16);
    window.crypto.getRandomValues(array);
    return Array.from(array).map(b => b.toString(16).padStart(2, '0')).join('');
}

function attachSubmitHandler(field, sessionToken, fingerprintHash, timeLoaded) {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const elapsed = Date.now() - timeLoaded;
            const payload = {
                ts: Date.now(),
                elapsed,
                token: sessionToken,
                hash: fingerprintHash,
            };
            field.value = btoa(JSON.stringify(payload));
            logDebug(`[CAPTCHA] Payload set: ${JSON.stringify(payload)}`);
        });
    });
}
