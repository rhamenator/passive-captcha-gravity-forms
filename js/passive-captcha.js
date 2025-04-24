document.addEventListener('DOMContentLoaded', function() {
    const field = document.querySelector('input[name^="input_"][name$="captcha_token"]');
    if (!field || typeof pchData === 'undefined') return;

    const startTime = Date.now();
    let interacted = false;

    ['mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt =>
        document.addEventListener(evt, () => interacted = true, { once: true })
    );

    function isHeadless() {
        return navigator.webdriver ||
               /HeadlessChrome/.test(navigator.userAgent) ||
               !('chrome' in window) ||
               ('languages' in navigator && navigator.languages.length === 0);
    }

    function hasMissingNavigatorProps() {
        return !navigator.plugins || navigator.plugins.length === 0 ||
               !navigator.languages || navigator.languages.length === 0;
    }

    function getWebGLFingerprint() {
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (!gl) return 'no_webgl';
            const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
            const vendor = debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'unknown_vendor';
            const renderer = debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'unknown_renderer';
            return btoa(vendor + '|' + renderer);
        } catch (e) {
            return 'webgl_error';
        }
    }

    function invisibleMathChallenge() {
        const a = Math.floor(Math.random() * 10) + 1;
        const b = Math.floor(Math.random() * 10) + 1;
        return (a * b).toString();
    }

    function buildNavigatorHash() {
        const data = [
            navigator.userAgent,
            navigator.language,
            navigator.languages ? navigator.languages.join(',') : '',
            navigator.platform,
            getWebGLFingerprint(),
            invisibleMathChallenge()
        ].join('|');
        return btoa(data);
    }

    setTimeout(() => {
        if (!interacted || isHeadless() || hasMissingNavigatorProps()) {
            field.value = 'no_interaction';
            return;
        }
        const timeSpent = Date.now() - startTime;
        const navHash = buildNavigatorHash();
        const token = btoa(timeSpent.toString() + ':' + navHash);
        field.value = token;

        const form = field.closest('form');
        if (form) {
            form.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="pch_nonce" value="${pchData.nonce}">
                <input type="hidden" name="pch_session" value="${pchData.sessionToken}">
                <input type="hidden" name="pch_iphash" value="${pchData.ipHash}">
            `);
        }
    }, 3000);
});
