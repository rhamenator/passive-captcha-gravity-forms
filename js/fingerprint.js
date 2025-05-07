// fingerprint.js
export async function generateFingerprint() {
  try {
    const ua = navigator.userAgent;
    const lang = navigator.language;
    const screenInfo = `${screen.width}x${screen.height}`;
    const webgl = await getWebGLFingerprint();
    const canvas = getCanvasFingerprint();
    const raw = [ua, lang, screenInfo, webgl, canvas].join('|');
    return btoa(raw);
  } catch (e) {
    console.warn('Fingerprint error:', e);
    return btoa(navigator.userAgent);
  }
}

function getCanvasFingerprint() {
  const c = document.createElement('canvas');
  const ctx = c.getContext('2d');
  ctx.textBaseline = 'top';
  ctx.font = '14px Arial';
  ctx.fillText('PassiveCAPTCHA', 2, 2);
  return btoa(ctx.getImageData(0, 0, c.width, c.height).data);
}

function getWebGLFingerprint() {
  return new Promise((resolve) => {
    try {
      const canvas = document.createElement('canvas');
      const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
      if (!gl) return resolve('no_webgl');
      const dbg = gl.getExtension('WEBGL_debug_renderer_info');
      const vendor = dbg ? gl.getParameter(dbg.UNMASKED_VENDOR_WEBGL) : 'unknown';
      const renderer = dbg ? gl.getParameter(dbg.UNMASKED_RENDERER_WEBGL) : 'unknown';
      resolve(btoa(vendor + '|' + renderer));
    } catch {
      resolve('webgl_error');
    }
  });
}
