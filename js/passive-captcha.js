/**
 * Passive CAPTCHA Hardened - Client-Side Logic (Gravity Forms Version - Corrected)
 * Version: 3.6 - Using gform_post_render event and ID lookup
 */

// Use jQuery document ready as Gravity Forms relies on jQuery
// and gform_post_render is often triggered via jQuery events.
jQuery(document).ready(function($) {
  console.log('PCH DEBUG: Document ready (jQuery). Waiting for gform_post_render...');

  // Hook into the Gravity Forms event that fires after a form is rendered/updated
  if (typeof gform !== 'undefined' && typeof gform.addAction === 'function') {
      // Hook with priority 10, pass formId and currentPage arguments
      gform.addAction('gform_post_render', initializeCaptchaForForm, 10, 2);
      console.log('PCH DEBUG: Added action hook for gform_post_render.');
  } else {
      console.error('PCH ERROR: gform object or gform.addAction is not available. Cannot hook into gform_post_render.');
      // Consider a fallback if gform isn't ready, though it should be if GF is active
  }
});

// Function to initialize CAPTCHA logic for a specific form ID
function initializeCaptchaForForm(formId, currentPage) {
  console.log(`PCH DEBUG: gform_post_render event fired for Form ID: ${formId}. Current Page: ${currentPage}`);

  // --- Configuration ---
  // We don't necessarily need a targetFormId if we find the field by label->ID
  const captchaFieldLabelText = 'CAPTCHA Token'; // Label text to find
  const debugDisplayFieldSelector = '.pch-debug-token-display input'; // Selector for the visible debug field
  const minTimeMs = 3000; // Base minimum time

  // --- Find the target fields ---
  let field = null; // The actual hidden CAPTCHA field
  let debugField = null; // The visible debug field
  let targetInputId = null; // Variable to store the ID derived from the label

  // --- Gravity Forms Field Lookup (Label -> For Attribute -> ID) ---
  console.log(`PCH DEBUG: Attempting GF field lookup via label "for" attribute for form ${formId}...`);
  const formWrapper = document.getElementById(`gform_wrapper_${formId}`);
  if (!formWrapper) {
      console.error(`PCH ERROR: Cannot find form wrapper #gform_wrapper_${formId}.`);
      return; // Cannot proceed without the form wrapper
  }

  const gfLabels = formWrapper.querySelectorAll('.gfield_label'); // Search within the specific form
  console.log(`PCH DEBUG: Found ${gfLabels.length} labels within form wrapper #${formId}.`);

  gfLabels.forEach((label) => {
      if (targetInputId) return; // Stop if already found ID

      const labelText = label.textContent ? label.textContent.trim() : '';
      if (labelText.includes(captchaFieldLabelText)) {
          console.log(`PCH DEBUG: Found label containing "${captchaFieldLabelText}":`, label);
          targetInputId = label.getAttribute('for'); // Get the ID the label points to
          if (targetInputId) {
              console.log(`PCH DEBUG: Target input ID from label 'for' attribute is: ${targetInputId}`);
          } else {
              console.warn('PCH DEBUG: Matching label does not have a "for" attribute. Cannot find input by ID this way.');
          }
      }
  });

  // If we found a target ID from the label, try to get the element by that ID
  if (targetInputId) {
      field = document.getElementById(targetInputId); // Use getElementById (searches whole document)
      if (field) {
          console.log(`PCH DEBUG: Successfully found input field using getElementById(#${targetInputId}):`, field);
      } else {
          console.warn(`PCH DEBUG: Could not find input element with ID "${targetInputId}" even though label 'for' attribute exists.`);
      }
  }

  // Fallback if ID lookup failed (maybe 'for' attribute missing) - Try finding input within the label's parent container
  if (!field) {
       console.log('PCH DEBUG: ID lookup failed. Trying parent container lookup...');
       gfLabels.forEach((label) => {
           if (field) return; // Stop if found
           const labelText = label.textContent ? label.textContent.trim() : '';
           if (labelText.includes(captchaFieldLabelText)) {
               const container = label.closest('.gfield');
               if (container) {
                   const inputElement = container.querySelector('input[name^="input_"]');
                   if (inputElement) {
                       field = inputElement;
                       console.log('PCH DEBUG: Found field using parent container lookup:', field);
                   }
               }
           }
       });
  }


  if (!field) {
       console.error(`PCH ERROR: Failed to find the target CAPTCHA input field for Form ID ${formId}. Ensure a Hidden field labeled "CAPTCHA Token" exists.`);
       return; // Cannot proceed without the field
  }
  // --- End Field Lookup ---


  // Find the visible debug field
  debugField = document.querySelector(debugDisplayFieldSelector);
  if (debugField) { console.log('PCH DEBUG: Found visible debug field:', debugField); }
  else { console.log('PCH DEBUG: Visible debug field not found.'); }


  // Check if already initialized for this specific field instance
  if (field.dataset.pchInitialized === 'true') {
       console.log(`PCH DEBUG: Skipping initialization for field ${field.id}, already initialized.`);
       return;
  }
  field.dataset.pchInitialized = 'true'; // Mark as initialized


  // Check if pchData is available
  if (typeof pchData === 'undefined') {
      console.error('PCH ERROR: pchData object not found. Check wp_localize_script. Script terminating.');
      return;
  } else {
       console.log('PCH DEBUG: pchData object found.');
  }


  // --- Initialization ---
  const startTime = Date.now(); // Time when this specific form render initialization starts
  let interacted = false;
  console.log('PCH DEBUG: Initializing checks. Start time:', startTime);

  // --- Interaction Detection ---
  // These listeners are on the document, safe to add once per page load,
  // but we ensure they are added here if the script runs multiple times for AJAX forms.
  // Using a flag to prevent adding multiple listeners.
  if (!window.pchInteractionListenersAdded) {
      ['mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt =>
          document.addEventListener(evt, () => {
              // Set a global flag or attribute to indicate interaction
              window.pchUserInteracted = true;
               // Optional: Log only the first interaction
               // if (!window.pchFirstInteractionLogged) {
               //     console.log(`PCH DEBUG: Interaction detected (${evt}).`);
               //     window.pchFirstInteractionLogged = true;
               // }
          }, { once: true, passive: true })
      );
      window.pchInteractionListenersAdded = true;
      console.log('PCH DEBUG: Added interaction listeners.');
  }


  // --- Bot Detection Functions ---
  function isHeadless() { return navigator.webdriver || /HeadlessChrome/.test(navigator.userAgent) || /slimerjs/i.test(navigator.userAgent) || /phantomjs/i.test(navigator.userAgent) || !('chrome' in window) || ('languages' in navigator && navigator.languages.length === 0); }
  function hasMissingNavigatorProps() { return !navigator.plugins || navigator.plugins.length === 0 || !navigator.languages || navigator.languages.length === 0; }

  // --- Conditionally Enabled Functions ---
  function getWebGLFingerprint() { if (!pchData.enableWebGL) { return 'webgl_disabled'; } try { const canvas = document.createElement('canvas'); const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl'); if (!gl) { return 'no_webgl_support'; } const debugInfo = gl.getExtension('WEBGL_debug_renderer_info'); const vendor = debugInfo ? gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL) : 'unknown_vendor'; const renderer = debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'unknown_renderer'; return btoa(vendor + '|' + renderer); } catch (e) { return 'webgl_error'; } }
  function invisibleMathChallenge() { if (!pchData.enableMath) { return 'math_disabled'; } const a = Math.floor(Math.random() * 10) + 1; const b = Math.floor(Math.random() * 10) + 1; return (a * b).toString(); }

  // --- Hash Building ---
  function buildNavigatorHash() { const data = [ navigator.userAgent, navigator.language, navigator.languages ? navigator.languages.join(',') : '', navigator.platform, getWebGLFingerprint(), invisibleMathChallenge() ].join('|'); return btoa(data); }

  // --- Token Generation and Field Update ---
  console.log(`PCH DEBUG: Setting inner timeout for token generation (${minTimeMs}ms).`);
  setTimeout(() => {
     try {
          console.log('PCH DEBUG: Inner timeout executed for token generation.');

          const headlessCheck = isHeadless();
          const missingPropsCheck = hasMissingNavigatorProps();
          // Check the global interaction flag set by the document listeners
          interacted = window.pchUserInteracted || false;
          let finalTokenValue = 'no_interaction'; // Default value if checks fail

          if (!interacted || headlessCheck || missingPropsCheck) {
              console.log(`PCH DEBUG: Bot signal or no interaction. interacted=${interacted}, headless=${headlessCheck}, missingProps=${missingPropsCheck}.`);
          } else {
              const timeSpent = Date.now() - startTime; // Time since this function was called (gform_post_render)
              console.log(`PCH DEBUG: Time spent since form render init: ${timeSpent}ms.`);

              if (timeSpent < minTimeMs) { // Check time spent since form render/init
                   console.log(`PCH DEBUG: Interaction too fast (${timeSpent}ms < ${minTimeMs}ms).`);
              } else {
                  const navHash = buildNavigatorHash();
                  // Use time since page load for the actual token value if needed,
                  // but timeSpent since render is a decent interaction check too.
                  // Let's stick with timeSpent since render for simplicity here.
                  finalTokenValue = btoa(timeSpent.toString() + ':' + navHash);
                  console.log('PCH DEBUG: Token generated:', finalTokenValue);

                  // Inject helper fields only if a valid token was generated
                  const form = field.closest('form');
                  if (form) {
                      console.log('PCH DEBUG: Parent form found:', form);
                      // Inject fields if they don't exist
                      if (!form.querySelector('input[name="pch_nonce"]')) { form.insertAdjacentHTML('beforeend', `<input type="hidden" name="pch_nonce" value="${pchData.nonce}">`); console.log('PCH DEBUG: Nonce field injected.'); } else { /* Update existing? */ const existingNonce = form.querySelector('input[name="pch_nonce"]'); if(existingNonce.value !== pchData.nonce) { existingNonce.value = pchData.nonce; console.log('PCH DEBUG: Nonce field updated.');} else {console.log('PCH DEBUG: Nonce field already exists with correct value.');} }
                      if (!form.querySelector('input[name="pch_session"]')) { form.insertAdjacentHTML('beforeend', `<input type="hidden" name="pch_session" value="${pchData.sessionToken}">`); console.log('PCH DEBUG: Session field injected.'); } else { /* Update existing? */ const existingSession = form.querySelector('input[name="pch_session"]'); if(existingSession.value !== pchData.sessionToken) { existingSession.value = pchData.sessionToken; console.log('PCH DEBUG: Session field updated.');} else { console.log('PCH DEBUG: Session field already exists.');} }
                      if (!form.querySelector('input[name="pch_iphash"]')) { form.insertAdjacentHTML('beforeend', `<input type="hidden" name="pch_iphash" value="${pchData.ipHash}">`); console.log('PCH DEBUG: IPhash field injected.'); } else { console.log('PCH DEBUG: IPhash field already exists.'); }
                      console.log('PCH DEBUG: Field injection check complete.');
                  } else {
                      console.warn('PCH DEBUG: CAPTCHA field is not inside a <form> element...');
                  }
              }
          }

          // Update fields
          field.value = finalTokenValue;
          console.log('PCH DEBUG: Set hidden field (', field.name, ') value to:', finalTokenValue);
          if (debugField) {
              debugField.value = finalTokenValue;
              console.log('PCH DEBUG: Set visible debug field (', debugField.name, ') value to:', finalTokenValue);
          }
      } catch (e) {
          console.error('PCH ERROR: Error inside inner setTimeout callback:', e);
      }
  }, minTimeMs); // Delay token generation by minTimeMs

} // End initializeCaptchaForForm function
