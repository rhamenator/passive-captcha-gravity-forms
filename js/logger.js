// logger.js
const ajaxUrl = window.pchData?.ajaxUrl || '/wp-admin/admin-ajax.php';

export function logDebug(msg) {
  if (window.pchData?.debug) {
    console.debug('PCH DEBUG:', msg);
  }
}

export function logWarning(msg) {
  console.warn('PCH WARN:', msg);
  // also send to PHP endpoint
  fetch(`${ajaxUrl}?action=pch_log_warning`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ level: 'warn', message: msg }),
  }).catch(() => {
    console.warn('PCH WARN: Failed to log warning to server.');
  });
}
