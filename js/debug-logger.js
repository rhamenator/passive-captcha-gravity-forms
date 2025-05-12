function logDebug(msg) {
    if (window.passiveCaptchaDebug) {
        console.log(msg);
        // Send this to server in a future version, if needed
    }
}

function findCaptchaTokenField() {
    const fieldName = 'CAPTCHA Token';
    let field = null;

    // Try exact match by label
    const labels = document.querySelectorAll('label');
    labels.forEach(label => {
        if (label.innerText.trim() === fieldName) {
            const forId = label.getAttribute('for');
            if (forId) {
                const candidate = document.getElementById(forId);
                if (candidate && candidate.type === 'hidden') {
                    field = candidate;
                }
            }
        }
    });

    // Try by input name fallback
    if (!field) {
        const hiddenInputs = document.querySelectorAll('input[type="hidden"]');
        hiddenInputs.forEach(input => {
            if (input.name.toLowerCase().includes('captcha')) {
                field = input;
            }
        });
    }

    return field;
}

function logMissingFieldWarning() {
    const msg = '[CAPTCHA] CAPTCHA hidden field not found';
    console.warn(msg);
    logDebug(msg);
}
