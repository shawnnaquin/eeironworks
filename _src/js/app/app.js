var App = (function ($) {

  // App-wide utility functions and settings
  var utils = {
    captureMouse: function (element, relativeElem) {
      // relativeToElem = typeof relativeToElem !== 'undefined' ? relativeToElem : null;
      var mouse = {x: 0, y: 0};
      var offsets = element.getBoundingClientRect();
      var offsetLeft;
      var offsetTop;

      // console.log(relativeElem);
      if(typeof relativeElem === 'undefined') {
        offsets = element.getBoundingClientRect();
        offsetLeft = offsets.left;
        offsetTop = offsets.top;
      } else {
        var relativeElemRect = relativeElem.getBoundingClientRect();
        offsetLeft = offsets.left - relativeElemRect.left;
        offsetTop = offsets.top - relativeElemRect.top;
      }

      // this doesn't work
      // offsetLeft -= parseInt(getComputedStyle(element).getPropertyValue('border-left-width').split('px')[0]);
      // offsetTop -= parseInt(getComputedStyle(element).getPropertyValue('border-top-width').split('px')[0]);

      function calcMousePos(event) {
        var x, y;
        if (event.pageX || event.pageY) {
          x = event.pageX;
          y = event.pageY;
        } else {
          x = event.clientX + document.body.scrollLeft +
            document.documentElement.scrollLeft;
          y = event.clientY + document.body.scrollTop +
            document.documentElement.scrollTop;
        }

        // console.log('should be called once', x, y);

        x -= offsetLeft;
        y -= offsetTop;

        mouse.x = x;
        mouse.y = y;
      }

      // make sure we're not stacking events
      element.removeEventListener('mousemove', calcMousePos, false);

      element.addEventListener('mousemove', calcMousePos, false);

      return mouse;
    },
    degToRad: function(deg) {
      return deg * Math.PI / 180;
    },
    radToDeg: function(rad) {
      return rad * 180 / Math.PI;

    }
    // captureTouch: function (element) {
    //   var touch = {x: null, y: null, isPressed: false};
    //
    //   element.addEventListener('touchstart', function (event) {
    //     touch.isPressed = true;
    //     //TODO get 05-touch-event.html to work as it's supposed to.  pg. 28
    //     //touch.isPressed doesn't seem to = true on touchstart
    //     //touch.x and touch.y are null at touchstart
    //   }, false);
    //
    //   element.addEventListener('touchend', function (event) {
    //     touch.isPressed = false;
    //     touch.x = null;
    //     touch.y = null;
    //   }, false);
    //
    //   element.addEventListener('touchmove', function (event) {
    //     var x, y,
    //       touch_event = event.touches[0]; //first touch
    //
    //     if (touch_event.pageX || touch_event.pageY) {
    //       x = touch_event.pageX;
    //       y = touch_event.pageY;
    //     } else {
    //       x = touch_event.clientX + document.body.scrollLeft +
    //         document.documentElement.scrollLeft;
    //       y = touch_event.clientY + document.body.scrollTop +
    //         document.documentElement.scrollTop;
    //     }
    //     x -= element.offsetLeft;
    //     y -= element.offsetTop;
    //
    //     touch.x = x;
    //     touch.y = y;
    //   }, false);
    //
    //   return touch;
    // }
  };

  var settings = {
    captureMouseElem: null
  };

  var elems = {
    $win: $(window),
    $doc: $(document),
    $body: $('body, html')
  };

  return {
    utils: utils,
    settings: settings,
    elems: elems
  };

})(jQuery);
