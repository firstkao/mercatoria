(function () {
  'use strict';

  /* 1. BLOCK RIGHT-CLICK */
  document.addEventListener('contextmenu', function (e) {
    if (e.target.tagName === 'IMG' || e.target.closest('[data-protect]')) {
      e.preventDefault();
      return false;
    }
  });

  /* 2. BLOCK IMAGE DRAG */
  document.addEventListener('dragstart', function (e) {
    if (e.target.tagName === 'IMG') {
      e.preventDefault();
      return false;
    }
  });

  /* 3. BLOCK KEYBOARD SHORTCUT */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'F12') {
      e.preventDefault();
      return false;
    }
    if (e.ctrlKey && e.shiftKey && ['I', 'J', 'C'].indexOf(e.key.toUpperCase()) !== -1) {
      e.preventDefault();
      return false;
    }
    if (e.ctrlKey && e.key.toUpperCase() === 'U') {
      e.preventDefault();
      return false;
    }
    if (e.ctrlKey && e.key.toUpperCase() === 'S') {
      e.preventDefault();
      return false;
    }
    if (e.metaKey && e.altKey && ['I', 'J', 'C'].indexOf(e.key.toUpperCase()) !== -1) {
      e.preventDefault();
      return false;
    }
  });

  /* 4. BLOCK SELECT IMG */
  document.addEventListener('selectstart', function (e) {
    if (e.target.tagName === 'IMG') {
      e.preventDefault();
      return false;
    }
  });
})();