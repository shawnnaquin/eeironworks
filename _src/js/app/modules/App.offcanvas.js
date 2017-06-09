App.offcanvas = (function($) {


  function init() {
    $(document).ready(function () {
      $('[data-toggle="offcanvas"]').click(function () {
        $('.row-offcanvas').toggleClass('active');
      });
    });
  }

  return {
    init: init
  };

})(jQuery);
