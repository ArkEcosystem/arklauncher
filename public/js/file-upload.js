(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/js/file-upload"],{

/***/ "./resources/js/file-upload.js":
/*!*************************************!*\
  !*** ./resources/js/file-upload.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports) {

window.uploadPhoto = function (_ref) {
  var url = _ref.url,
      onUpload = _ref.onUpload;
  return {
    url: url,
    onUpload: onUpload,
    upload: function upload(e) {
      var _this = this;

      if (!e.target.files.length) return;
      var data = new FormData();
      data.append('logo', e.target.files[0]);
      fetch(this.url, {
        method: 'POST',
        body: data
      }).then(function () {
        return _this.onUpload();
      });
    },
    select: function select() {
      document.getElementById('photo').click();
    }
  };
};

/***/ }),

/***/ 1:
/*!*******************************************!*\
  !*** multi ./resources/js/file-upload.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/josip/Work/deployer/resources/js/file-upload.js */"./resources/js/file-upload.js");


/***/ })

},[[1,"/js/manifest"]]]);