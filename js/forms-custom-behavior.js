var $_GET=[];

window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(a,name,value){$_GET[name]=value;});

(function (window) {
  var last = +new Date();
  var delay = 100; // default delay

  // Manage event queue
  var stack = [];

  function callback() {
      var now = +new Date();
      if (now - last > delay) {
          for (var i = 0; i < stack.length; i++) {
              stack[i]();
          }
          last = now;
      }
  }

  // Public interface
  var onDomChange = function (fn, newdelay) {
      if (newdelay) delay = newdelay;
      stack.push(fn);
  };

  // Naive approach for compatibility
  function naive() {

      var last = document.getElementsByTagName('*');
      var lastlen = last.length;
      var timer = setTimeout(function check() {

          // get current state of the document
          var current = document.getElementsByTagName('*');
          var len = current.length;

          // if the length is different
          // it's fairly obvious
          if (len != lastlen) {
              // just make sure the loop finishes early
              last = [];
          }

          // go check every element in order
          for (var i = 0; i < len; i++) {
              if (current[i] !== last[i]) {
                  callback();
                  last = current;
                  lastlen = len;
                  break;
              }
          }

          // over, and over, and over again
          setTimeout(check, delay);

      }, delay);
  }

  //
  //  Check for mutation events support
  //

  var support = {};

  var el = document.documentElement;
  var remain = 3;

  // callback for the tests
  function decide() {
      if (support.DOMNodeInserted) {
          window.addEventListener("DOMContentLoaded", function () {
              if (support.DOMSubtreeModified) { // for FF 3+, Chrome
                  el.addEventListener('DOMSubtreeModified', callback, false);
              } else { // for FF 2, Safari, Opera 9.6+
                  el.addEventListener('DOMNodeInserted', callback, false);
                  el.addEventListener('DOMNodeRemoved', callback, false);
              }
          }, false);
      } else if (document.onpropertychange) { // for IE 5.5+
          document.onpropertychange = callback;
      } else { // fallback
          naive();
      }
  }

  // checks a particular event
  function test(event) {
      el.addEventListener(event, function fn() {
          support[event] = true;
          el.removeEventListener(event, fn, false);
          if (--remain === 0) decide();
      }, false);
  }

  // attach test events
  if (window.addEventListener) {
      test('DOMSubtreeModified');
      test('DOMNodeInserted');
      test('DOMNodeRemoved');
  } else {
      decide();
  }

  // do the dummy test
  var dummy = document.createElement("div");
  el.appendChild(dummy);
  el.removeChild(dummy);

  // expose
  window.onDomChange = onDomChange;
})(window);

form_changed = false;
form_submitButton_linked = false;
form_semaphor = false;
form_timeout = 2000;

onDomChange(function(){

  if (!form_changed && !form_semaphor) {
    Object.keys($_GET).forEach(key => {
      let field = document.querySelector('input[name = '+ key + ']')

      if (field != null) {
        setTimeout(function () {
          form_semaphor = true;
          field.focus();
          field.value = $_GET[key];
          field.blur();
          //field.setAttribute("readonly","");
          form_changed = true;
          form_semaphor = false;
          form_timeout = 0;
        }, form_timeout);
      }
    });
  }

  function removeReadOnly() {
    Object.keys($_GET).forEach(key => {
      let field = document.querySelector('input[name = '+ key + ']')

      if (field != null) {
        field.removeAttribute("readonly");
      }
    });
  }

  let submitButton = document.querySelector('input[class = primary]');
  if (submitButton != null && !form_submitButton_linked) {
    submitButton.addEventListener('click', removeReadOnly);
    form_submitButton_linked = true;
  }
});

//
/*
window.onload = function () {
  setTimeout(function () {

    // set to local storage

    Object.keys($_GET).forEach(key => {
      let field = document.querySelector('input[name = '+ key + ']')

      if (field != null) {
        field.value = $_GET[key];
        field.setAttribute("readonly","");

      }

    });

    /*let submitButton = document.querySelector('input[class = primary]');

    function removeReadOnly() {
      Object.keys($_GET).forEach(key => {
        let field = document.querySelector('input[name = '+ key + ']')

        if (field != null) {
          field.removeAttribute("readonly");
        }
      });
    }

    submitButton.addEventListener('click', removeReadOnly)
    */
/*

  }, 1000);
};
*/