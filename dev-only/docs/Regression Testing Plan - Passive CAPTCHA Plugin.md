---

# **📋 Regression Testing Plan: Passive CAPTCHA Plugin**

## **Goal:**

Ensure that new changes haven't negatively impacted existing functionality. Specifically, verify that the plugin works as expected across typical usage scenarios and environments.

---

# **Outline:**

## **✅ 1\. Test Environment Setup**

**Local Testing (Docker-based):**

* Pull the latest GitHub commit.

* Execute `make build && make test`.

* Ensure Docker Compose starts all services (WordPress, PHP-FPM, MySQL, NGINX).

* Verify no errors or warnings in Docker logs.

**Online Staging Environment:**

* Deploy latest plugin build to staging site.

* Verify staging closely mirrors production configuration.

* Confirm PHP error logging and JavaScript console logging are enabled for visibility.

---

## **🧪 2\. Functional Tests (Manual)**

### **2.1 Installation and Activation**

* Plugin installs without warnings or errors.

* Activates correctly on single-site and multi-site installations.

* Verify default settings are correctly initialized in the database.

### **2.2 Admin Settings UI**

* All settings fields save and retrieve values correctly.

* Validation errors display clearly.

* IP management (Whitelist/Blacklist) correctly accepts and applies settings.

### **2.3 CAPTCHA Generation (Client-side JavaScript)**

* Token generation script (`index.js`) loads correctly on Gravity Form pages.

* Fingerprinting (`fingerprint.js`) includes all configured checks.

* Session handling works consistently across multiple page loads (`session.js`).

### **2.4 Form Submission Validation (Server-side PHP)**

* Valid tokens successfully pass validation and allow form submissions.

* Invalid or manipulated tokens correctly trigger validation failures.

* Rate limits correctly increment failure counts per IP.

* Banned IP addresses correctly prevented from submitting.

### **2.5 Logging and Alerts**

* Logs populate immediately after plugin activation without errors.

* Recent logs display correctly on the admin settings page.

* Webhook notifications are correctly formatted and received upon failed attempts.

---

## **🔄 3\. Regression Testing**

Run these tests following any significant code changes:

| ID    | Scenario                                                | Priority | Method |
| ----- | ------------------------------------------------------- | -------- | ------ |
| R1    | Valid token submission                                  | High     | Manual |
| R2    | Invalid token submission                                | High     | Manual |
| R3    | IP whitelist/blacklist handling                         | High     | Manual |
| R4    | Rate limiting and ban enforcement                       | High     | Manual |
| R5    | Webhook payload verification                            | Medium   | Manual |
| R6    | JS fingerprinting options toggling                      | Medium   | Manual |
| R7    | Log file write permissions (filesystem)                 | High     | Manual |
| R8    | AJAX error handling and debug logging                   | Medium   | Manual |
| R9    | Multisite compatibility                                 | Medium   | Manual |

---

## **⚙️ 4\. Automated Testing (CI/CD via GitHub Actions)**

* Linting checks pass (`lint.yml`).

* PHPUnit test suite passes consistently (`test.yml`).

* Docker build completes without errors (`build.yml`).

* All automated tests triggered by `make test` pass.

---

## **🌐 5\. Cross-browser and Device Testing**

**Browsers:**

* Chrome (latest)

* Firefox (latest)

* Edge (latest)

* Safari (latest)

**Devices:**

* Desktop (Windows, macOS, Linux)

* Mobile/Tablet (Android, iOS)

Ensure JavaScript execution and AJAX requests behave consistently.

---

## **🚦 6\. Security and Performance**

### **6.1 Security Checks**

* Verify headers and responses via NGINX (JA3 fingerprinting, security headers).

* Confirm webhook payloads are correctly signed (HMAC).

* Validate proper sanitization/escaping of inputs in PHP and JS.

### **6.2 Performance Checks**

* No significant increase in page-load or form-submission times.

* AJAX calls and PHP execution time remain within acceptable thresholds.

---

## **📝 7\. Reporting and Documentation**

* Log and document all discovered issues clearly.

* Include steps to reproduce, screenshots, or logs.

* Prioritize issues by impact and urgency.

---

## **🔍 8\. Continuous Improvement**

* Review regression tests regularly (bi-monthly recommended).

* Adjust scenarios and priorities based on user feedback, real-world usage, and security insights.
