/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./blocks/lunar-calendar/index.js":
/*!****************************************!*\
  !*** ./blocks/lunar-calendar/index.js ***!
  \****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _style_css__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./style.css */ "./blocks/lunar-calendar/style.css");







// Import các thư viện cần thiết cho preview (moment, fontawesome, ...)
// Nếu dùng webpack, cần import các thư viện JS calendar vào đây hoặc nhúng qua enqueue script.

(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)('jankx/lunar-calendar', {
  edit: function Edit() {
    const props = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)({
      className: 'lunar-calendar-container lunar-calendar-editor-preview'
    });
    const calendarRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useRef)();
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
      // Nếu đã có window.LunarCalendar thì khởi tạo lại preview
      if (window.LunarCalendar && typeof window.LunarCalendar === 'function') {
        new window.LunarCalendar(calendarRef.current);
      } else {
        // Nếu chưa có, có thể load script động hoặc báo lỗi
        // (Tùy vào cách bạn bundle JS calendar)
      }
    }, []);
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      ...props
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      ref: calendarRef
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-calendar-header"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("h1", null, "L\u1ECBch \xC2m D\u01B0\u01A1ng"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, "Tra c\u1EE9u l\u1ECBch \xE2m d\u01B0\u01A1ng Vi\u1EC7t Nam")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-current-date-section"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "lunar-date-nav-btn",
      id: "prev-day-btn",
      title: "Ng\xE0y tr\u01B0\u1EDBc"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-chevron-left"
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-column"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-label"
    }, "D\u01B0\u01A1ng l\u1ECBch"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-number",
      id: "current-gregorian-day"
    }, "08"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-month-year",
      id: "current-gregorian-month-year"
    }, "Th\xE1ng 08 n\u0103m 2025"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-day",
      id: "current-gregorian-day-name"
    }, "Th\u1EE9 6")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-column"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-label"
    }, "\xC2m l\u1ECBch"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-number",
      id: "current-lunar-day"
    }, "15"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-date-month-year",
      id: "current-lunar-month-year"
    }, "Th\xE1ng 06 n\u0103m \u1EA4t T\u1EF5"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-info",
      id: "current-lunar-details"
    }, "Ng\xE0y K\u1EF7 D\u1EADu - Th\xE1ng Qu\xFD M\xF9i")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "lunar-date-nav-btn",
      id: "next-day-btn",
      title: "Ng\xE0y ti\u1EBFp theo"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-chevron-right"
    }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-holiday-info"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-holiday-title"
    }, "Th\xF4ng tin ng\xE0y l\u1EC5 h\xF4m nay"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-holiday-content",
      id: "holiday-info"
    }, "Kh\xF4ng c\xF3")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-calendar-nav"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "lunar-nav-arrow",
      id: "prev-month"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-chevron-left"
    })), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-nav-center"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-current-month-year",
      id: "current-month-year"
    }, "Th\xE1ng 8 - 2025"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-month-year-selectors"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
      id: "month-selector"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "1"
    }, "Th\xE1ng 1"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "2"
    }, "Th\xE1ng 2"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "3"
    }, "Th\xE1ng 3"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "4"
    }, "Th\xE1ng 4"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "5"
    }, "Th\xE1ng 5"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "6"
    }, "Th\xE1ng 6"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "7"
    }, "Th\xE1ng 7"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "8",
      selected: true
    }, "Th\xE1ng 8"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "9"
    }, "Th\xE1ng 9"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "10"
    }, "Th\xE1ng 10"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "11"
    }, "Th\xE1ng 11"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "12"
    }, "Th\xE1ng 12")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("select", {
      id: "year-selector"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "2023"
    }, "2023"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "2024"
    }, "2024"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "2025",
      selected: true
    }, "2025"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "2026"
    }, "2026"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("option", {
      value: "2027"
    }, "2027")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "lunar-view-btn",
      id: "view-btn"
    }, "Xem"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "lunar-today-btn",
      id: "today-btn"
    }, "H\xF4m nay"))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("button", {
      className: "lunar-nav-arrow",
      id: "next-month"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("i", {
      className: "fas fa-chevron-right"
    }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      id: "loading-indicator",
      className: "lunar-loading-indicator",
      style: {
        display: 'none'
      }
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-loading-spinner"
    }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("span", {
      className: "lunar-loading-text"
    }, "\u0110ang t\u1EA3i d\u1EEF li\u1EC7u...")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      id: "page-loading-overlay",
      className: "lunar-page-loading-overlay",
      style: {
        display: 'none'
      }
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-page-loading-content"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-page-loading-spinner"
    }))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-calendar-grid"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekdays"
    }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekday"
    }, "Th\u1EE9 hai"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekday"
    }, "Th\u1EE9 ba"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekday"
    }, "Th\u1EE9 t\u01B0"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekday"
    }, "Th\u1EE9 n\u0103m"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekday"
    }, "Th\u1EE9 s\xE1u"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekday"
    }, "Th\u1EE9 b\u1EA3y"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-weekday"
    }, "Ch\u1EE7 nh\u1EADt")), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
      className: "lunar-calendar-days",
      id: "calendar-days"
    }))));
  },
  save: function save() {
    return null;
  }
});

/***/ }),

/***/ "./blocks/lunar-calendar/style.css":
/*!*****************************************!*\
  !*** ./blocks/lunar-calendar/style.css ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
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
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkjankx_lunar_calendar_block"] = globalThis["webpackChunkjankx_lunar_calendar_block"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./blocks/lunar-calendar/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map