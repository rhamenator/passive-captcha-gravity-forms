/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./js/fingerprint.js"
/*!***************************!*\
  !*** ./js/fingerprint.js ***!
  \***************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   generateFingerprint: () => (/* binding */ generateFingerprint)\n/* harmony export */ });\nfunction _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = \"function\" == typeof Symbol ? Symbol : {}, n = r.iterator || \"@@iterator\", o = r.toStringTag || \"@@toStringTag\"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, \"_invoke\", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError(\"Generator is already running\"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = \"next\"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError(\"iterator result is not an object\"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i[\"return\"]) && t.call(i), c < 2 && (u = TypeError(\"The iterator does not provide a '\" + o + \"' method\"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, \"GeneratorFunction\")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, \"constructor\", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, \"constructor\", GeneratorFunction), GeneratorFunction.displayName = \"GeneratorFunction\", _regeneratorDefine2(GeneratorFunctionPrototype, o, \"GeneratorFunction\"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, \"Generator\"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, \"toString\", function () { return \"[object Generator]\"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }\nfunction _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, \"\", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o(\"next\", 0), o(\"throw\", 1), o(\"return\", 2)); }, _regeneratorDefine2(e, r, n, t); }\nfunction asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }\nfunction _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, \"next\", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, \"throw\", n); } _next(void 0); }); }; }\n// fingerprint.js\nfunction generateFingerprint() {\n  return _generateFingerprint.apply(this, arguments);\n}\nfunction _generateFingerprint() {\n  _generateFingerprint = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {\n    var ua, lang, screenInfo, webgl, canvas, raw, _t;\n    return _regenerator().w(function (_context) {\n      while (1) switch (_context.p = _context.n) {\n        case 0:\n          _context.p = 0;\n          ua = navigator.userAgent;\n          lang = navigator.language;\n          screenInfo = \"\".concat(screen.width, \"x\").concat(screen.height);\n          _context.n = 1;\n          return getWebGLFingerprint();\n        case 1:\n          webgl = _context.v;\n          canvas = getCanvasFingerprint();\n          raw = [ua, lang, screenInfo, webgl, canvas].join('|');\n          return _context.a(2, btoa(raw));\n        case 2:\n          _context.p = 2;\n          _t = _context.v;\n          console.warn('Fingerprint error:', _t);\n          return _context.a(2, btoa(navigator.userAgent));\n      }\n    }, _callee, null, [[0, 2]]);\n  }));\n  return _generateFingerprint.apply(this, arguments);\n}\nfunction getCanvasFingerprint() {\n  var c = document.createElement('canvas');\n  var ctx = c.getContext('2d');\n  ctx.textBaseline = 'top';\n  ctx.font = '14px Arial';\n  ctx.fillText('PassiveCAPTCHA', 2, 2);\n  return btoa(ctx.getImageData(0, 0, c.width, c.height).data);\n}\nfunction getWebGLFingerprint() {\n  return new Promise(function (resolve) {\n    try {\n      var canvas = document.createElement('canvas');\n      var gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');\n      if (!gl) return resolve('no_webgl');\n      var dbg = gl.getExtension('WEBGL_debug_renderer_info');\n      var vendor = dbg ? gl.getParameter(dbg.UNMASKED_VENDOR_WEBGL) : 'unknown';\n      var renderer = dbg ? gl.getParameter(dbg.UNMASKED_RENDERER_WEBGL) : 'unknown';\n      resolve(btoa(vendor + '|' + renderer));\n    } catch (_unused) {\n      resolve('webgl_error');\n    }\n  });\n}\n\n//# sourceURL=webpack://passive-captcha-gravity-forms/./js/fingerprint.js?\n}");

/***/ },

/***/ "./js/index.js"
/*!*********************!*\
  !*** ./js/index.js ***!
  \*********************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   initPassiveCaptcha: () => (/* binding */ initPassiveCaptcha)\n/* harmony export */ });\n/* harmony import */ var _fingerprint_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./fingerprint.js */ \"./js/fingerprint.js\");\n/* harmony import */ var _mathChallenge_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./mathChallenge.js */ \"./js/mathChallenge.js\");\n/* harmony import */ var _logger_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./logger.js */ \"./js/logger.js\");\n/* harmony import */ var _ja3Integration_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ja3Integration.js */ \"./js/ja3Integration.js\");\n/* harmony import */ var _session_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./session.js */ \"./js/session.js\");\nfunction _regenerator() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */ var e, t, r = \"function\" == typeof Symbol ? Symbol : {}, n = r.iterator || \"@@iterator\", o = r.toStringTag || \"@@toStringTag\"; function i(r, n, o, i) { var c = n && n.prototype instanceof Generator ? n : Generator, u = Object.create(c.prototype); return _regeneratorDefine2(u, \"_invoke\", function (r, n, o) { var i, c, u, f = 0, p = o || [], y = !1, G = { p: 0, n: 0, v: e, a: d, f: d.bind(e, 4), d: function d(t, r) { return i = t, c = 0, u = e, G.n = r, a; } }; function d(r, n) { for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) { var o, i = p[t], d = G.p, l = i[2]; r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0)); } if (o || r > 1) return a; throw y = !0, n; } return function (o, p, l) { if (f > 1) throw TypeError(\"Generator is already running\"); for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) { i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u); try { if (f = 2, i) { if (c || (o = \"next\"), t = i[o]) { if (!(t = t.call(i, u))) throw TypeError(\"iterator result is not an object\"); if (!t.done) return t; u = t.value, c < 2 && (c = 0); } else 1 === c && (t = i[\"return\"]) && t.call(i), c < 2 && (u = TypeError(\"The iterator does not provide a '\" + o + \"' method\"), c = 1); i = e; } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break; } catch (t) { i = e, c = 1, u = t; } finally { f = 1; } } return { value: t, done: y }; }; }(r, o, i), !0), u; } var a = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} t = Object.getPrototypeOf; var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () { return this; }), t), u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c); function f(e) { return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, \"GeneratorFunction\")), e.prototype = Object.create(u), e; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, \"constructor\", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, \"constructor\", GeneratorFunction), GeneratorFunction.displayName = \"GeneratorFunction\", _regeneratorDefine2(GeneratorFunctionPrototype, o, \"GeneratorFunction\"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, \"Generator\"), _regeneratorDefine2(u, n, function () { return this; }), _regeneratorDefine2(u, \"toString\", function () { return \"[object Generator]\"; }), (_regenerator = function _regenerator() { return { w: i, m: f }; })(); }\nfunction _regeneratorDefine2(e, r, n, t) { var i = Object.defineProperty; try { i({}, \"\", {}); } catch (e) { i = 0; } _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) { function o(r, n) { _regeneratorDefine2(e, r, function (e) { return this._invoke(r, n, e); }); } r ? i ? i(e, r, { value: n, enumerable: !t, configurable: !t, writable: !t }) : e[r] = n : (o(\"next\", 0), o(\"throw\", 1), o(\"return\", 2)); }, _regeneratorDefine2(e, r, n, t); }\nfunction asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }\nfunction _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, \"next\", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, \"throw\", n); } _next(void 0); }); }; }\n;\n\n\n\n\nfunction initPassiveCaptcha() {\n  return _initPassiveCaptcha.apply(this, arguments);\n}\n\n// Run test logic outside of WordPress\nfunction _initPassiveCaptcha() {\n  _initPassiveCaptcha = _asyncToGenerator(/*#__PURE__*/_regenerator().m(function _callee() {\n    var mathResult, fingerprint, ja3, hiddenField, _t;\n    return _regenerator().w(function (_context) {\n      while (1) switch (_context.p = _context.n) {\n        case 0:\n          (0,_logger_js__WEBPACK_IMPORTED_MODULE_2__.logDebug)('Initializing Passive CAPTCHA.');\n          _context.p = 1;\n          (0,_session_js__WEBPACK_IMPORTED_MODULE_4__.initializeSession)();\n          // Run JavaScript logic outside of WordPress\n          mathResult = (0,_mathChallenge_js__WEBPACK_IMPORTED_MODULE_1__.performMathChallenge)();\n          (0,_logger_js__WEBPACK_IMPORTED_MODULE_2__.logDebug)(\"Math challenge result: \".concat(mathResult));\n          _context.n = 2;\n          return (0,_fingerprint_js__WEBPACK_IMPORTED_MODULE_0__.generateFingerprint)();\n        case 2:\n          fingerprint = _context.v;\n          (0,_logger_js__WEBPACK_IMPORTED_MODULE_2__.logDebug)(\"Client fingerprint generated: \".concat(fingerprint));\n          ja3 = (0,_ja3Integration_js__WEBPACK_IMPORTED_MODULE_3__.getJA3Fingerprint)();\n          if (ja3) {\n            fingerprint += \"-\".concat(ja3);\n          }\n          hiddenField = document.querySelector('input[name=\"captcha_token\"], input[name=\"captchaToken\"]');\n          if (hiddenField) {\n            hiddenField.value = \"\".concat(fingerprint, \":\").concat(mathResult);\n            (0,_logger_js__WEBPACK_IMPORTED_MODULE_2__.logDebug)(\"Hidden CAPTCHA field populated: \".concat(hiddenField.value));\n          } else {\n            (0,_logger_js__WEBPACK_IMPORTED_MODULE_2__.logWarning)('Hidden CAPTCHA field not found. Make sure a Hidden field labeled \"CAPTCHA Token\" or name=\"captcha_token\" exists.');\n          }\n          _context.n = 4;\n          break;\n        case 3:\n          _context.p = 3;\n          _t = _context.v;\n          (0,_logger_js__WEBPACK_IMPORTED_MODULE_2__.logWarning)(\"Initialization error: \".concat(_t.message));\n        case 4:\n          return _context.a(2);\n      }\n    }, _callee, null, [[1, 3]]);\n  }));\n  return _initPassiveCaptcha.apply(this, arguments);\n}\ndocument.addEventListener('DOMContentLoaded', initPassiveCaptcha);\n// This function is called when the DOM is fully loaded\n// and the script is executed in a non-WordPress context \n// (e.g., a standalone HTML page).\n// It will not run in the WordPress context, where the script is loaded via WordPress. \n// In that case, the script will be executed in the WordPress context,\n// and the testPassiveCaptcha function will not be called.\n// This allows for testing the passive CAPTCHA functionality\n// in a standalone environment without relying on WordPress.\n// The script will log debug messages to the console\n// and populate the hidden CAPTCHA field with the fingerprint and math challenge result.\n\n//# sourceURL=webpack://passive-captcha-gravity-forms/./js/index.js?\n}");

/***/ },

/***/ "./js/ja3Integration.js"
/*!******************************!*\
  !*** ./js/ja3Integration.js ***!
  \******************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   getJA3Fingerprint: () => (/* binding */ getJA3Fingerprint)\n/* harmony export */ });\n// ja3Integration.js\n// JA3 fingerprinting must be injected server-side (e.g. NGINX/Lua) into pchData.ja3Fingerprint\nfunction getJA3Fingerprint() {\n  var _window$pchData;\n  return ((_window$pchData = window.pchData) === null || _window$pchData === void 0 ? void 0 : _window$pchData.ja3Fingerprint) || null;\n}\n\n//# sourceURL=webpack://passive-captcha-gravity-forms/./js/ja3Integration.js?\n}");

/***/ },

/***/ "./js/logger.js"
/*!**********************!*\
  !*** ./js/logger.js ***!
  \**********************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   Logger: () => (/* binding */ Logger),\n/* harmony export */   logDebug: () => (/* binding */ logDebug),\n/* harmony export */   logWarning: () => (/* binding */ logWarning)\n/* harmony export */ });\nfunction _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nvar _window$pchData;\nfunction _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError(\"Cannot call a class as a function\"); }\nfunction _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, \"value\" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }\nfunction _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, \"prototype\", { writable: !1 }), e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\n// logger.js\nvar ajaxUrl = ((_window$pchData = window.pchData) === null || _window$pchData === void 0 ? void 0 : _window$pchData.ajaxUrl) || '/wp-admin/admin-ajax.php';\nfunction logDebug(msg) {\n  var _window$pchData2;\n  if ((_window$pchData2 = window.pchData) !== null && _window$pchData2 !== void 0 && _window$pchData2.debug) {\n    console.debug('PCH DEBUG:', msg);\n  }\n}\nfunction logWarning(msg) {\n  console.warn('PCH WARN:', msg);\n  // also send to PHP endpoint\n  fetch(\"\".concat(ajaxUrl, \"?action=pch_log_warning\"), {\n    method: 'POST',\n    headers: {\n      'Content-Type': 'application/json'\n    },\n    body: JSON.stringify({\n      level: 'warn',\n      message: msg\n    })\n  })[\"catch\"](function () {\n    console.warn('PCH WARN: Failed to log warning to server.');\n  });\n}\nvar Logger = /*#__PURE__*/function () {\n  function Logger() {\n    _classCallCheck(this, Logger);\n    this.history = [];\n  }\n  return _createClass(Logger, [{\n    key: \"log\",\n    value: function log(message) {\n      this.history.push({\n        level: 'log',\n        message: message\n      });\n      console.log('[Logger]', message);\n    }\n  }, {\n    key: \"warn\",\n    value: function warn(message) {\n      this.history.push({\n        level: 'warn',\n        message: message\n      });\n      console.warn('[Logger]', message);\n    }\n  }, {\n    key: \"error\",\n    value: function error(message) {\n      this.history.push({\n        level: 'error',\n        message: message\n      });\n      console.error('[Logger]', message);\n    }\n  }, {\n    key: \"clear\",\n    value: function clear() {\n      this.history = [];\n    }\n  }]);\n}();\n\n//# sourceURL=webpack://passive-captcha-gravity-forms/./js/logger.js?\n}");

/***/ },

/***/ "./js/mathChallenge.js"
/*!*****************************!*\
  !*** ./js/mathChallenge.js ***!
  \*****************************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   performMathChallenge: () => (/* binding */ performMathChallenge)\n/* harmony export */ });\n// mathChallenge.js\nfunction performMathChallenge() {\n  var a = Math.floor(Math.random() * 10) + 1;\n  var b = Math.floor(Math.random() * 10) + 1;\n  // store for later server validation if needed:\n  window.pchMathAnswer = a * b;\n  return \"\".concat(a, \"x\").concat(b); // e.g. \"3x7\"\n}\n\n//# sourceURL=webpack://passive-captcha-gravity-forms/./js/mathChallenge.js?\n}");

/***/ },

/***/ "./js/session.js"
/*!***********************!*\
  !*** ./js/session.js ***!
  \***********************/
(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

eval("{__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   initializeSession: () => (/* binding */ initializeSession)\n/* harmony export */ });\n// session.js\nfunction initializeSession() {\n  if (!window.pchSessionInitialized) {\n    window.pchSessionInitialized = true;\n    window.pchSessionStart = Date.now();\n    // Optionally store in sessionStorage:\n    try {\n      sessionStorage.setItem('pchSessionStart', window.pchSessionStart);\n    } catch (_unused) {}\n  }\n}\n\n//# sourceURL=webpack://passive-captcha-gravity-forms/./js/session.js?\n}");

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter/value functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			if(Array.isArray(definition)) {
/******/ 				var i = 0;
/******/ 				while(i < definition.length) {
/******/ 					var key = definition[i++];
/******/ 					var binding = definition[i++];
/******/ 					if(!__webpack_require__.o(exports, key)) {
/******/ 						if(binding === 0) {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, value: definition[i++] });
/******/ 						} else {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, get: binding });
/******/ 						}
/******/ 					} else if(binding === 0) { i++; }
/******/ 				}
/******/ 			} else {
/******/ 				for(var key in definition) {
/******/ 					if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 						Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module can't be inlined because the eval devtool is used.
/******/ 	let __webpack_exports__ = __webpack_require__("./js/index.js");
/******/ 	
/******/ })()
;