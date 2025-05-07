# Sequence Diagram

This documentation outlines the sequence of events when a user interacts with the Passive CAPTCHA system integrated into Gravity Forms via a WordPress plugin.

## User Interaction Sequence

```mermaid
sequenceDiagram
    participant B as User (Browser)
    participant JS as passive-captcha.js
    participant WP as WordPress Core
    participant GF as Gravity Forms
    participant PCH as Plugin (PHP Hooks)
    participant WH as Webhook Receiver

    B->>+WP: Request page with Gravity Form
    WP->>+PCH: Action: wp_enqueue_scripts
    PCH->>WP: Enqueue passive-captcha.js, localize pchData (nonce, session, ipHash)
    WP-->>-B: Send HTML + JS (pchData included)

    B->>+JS: Page Load: DOMContentLoaded
    JS->>JS: Start timer, add interaction listeners
    Note right of JS: User interacts (mouse, key, scroll)
    JS->>JS: 3 sec timeout starts
    JS->>JS: After timeout: Check interaction, check headless/props, calc WebGL/math hash
    alt Bot detected OR No Interaction OR Too Fast
        JS->>B: Find form's hidden field ('input_...captcha_token')
        JS->>B: Set field value = "no_interaction"
    else Human detected
        JS->>B: Find form's hidden field ('input_...captcha_token')
        JS->>JS: Build final token (time:hash)
        JS->>B: Set field value = generated_token
        JS->>B: Inject hidden fields (pch_nonce, pch_session, pch_iphash)
    end
    JS-->>-B: Ready for submit

    B->>+WP: Submit Gravity Form (POST Request)
    WP->>+GF: Process Submission
    GF->>+PCH: Filter: gform_pre_validation
    PCH->>PCH: pch_validate_passive_captcha(): <br/> Check Blacklist/Whitelist <br/> Check JA3 Header (if configured) <br/> Check Rate Limit <br/> Verify Nonce <br/> Verify Session (Get/Delete Transient) <br/> Verify IP/UA Hash <br/> Verify Token (decode, time, hash length)
    alt Validation Failed
        PCH->>PCH: Register Failure (update transient count)
        PCH->>WH: (Optional) Send Webhook Alert
        PCH-->>-GF: Set $field->failed_validation = true
        GF-->>B: Display Validation Error Message
    else Validation Passed
        PCH-->>GF: Return $form (validation passes)
        GF->>GF: Proceed with form processing (notifications, confirmations)
        GF->>+PCH: Action: gform_after_submission (Optional Alert)
        PCH->>PCH: pch_after_submission_alert(): Check if IP is banned
        alt IP is Banned
             PCH->>WH: Send 'submission_after_ban' Webhook
        end
        PCH-->>-GF: Done with hook
        GF-->>-WP: Submission Complete
        WP-->>-B: Show Confirmation/Redirect
    end
```
