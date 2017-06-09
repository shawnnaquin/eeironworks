App.introEvents = (function($){

  function init() {

    var showClass = 'show';
    var noScrollClass = 'no-scroll';

  	$('.modal').each(function(i){

  	  var $this = $(this);
  		var gameId = $this.attr('id');
      var eventSpace = gameId+'intro-close';

      $this.on('shown.bs.modal', function() {
        var $intro = $('.js-'+gameId+'-intro');

        App.elems.$body.addClass(noScrollClass);

        $intro.addClass(showClass);
      	App.elems.$doc.on('click.'+eventSpace, '.js-'+gameId+'-dismiss-modal-intro', function(e){
      		$intro.removeClass(showClass);
      	});
        App.elems.$doc.on('click.'+eventSpace, '.js-'+gameId+'-show-help', function(e) {
          $('.js-'+gameId+'-intro').addClass(showClass);
        });
      });

      $this.on('hidden.bs.modal', function() {
        App.elems.$body.removeClass(noScrollClass);
      	App.elems.$doc.off('.'+eventSpace);
      });

  	});

  }

  return {
    init: init
  };
})(jQuery);
