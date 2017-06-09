(function(App, $){

  function init() {
    // $(document).foundation();

    // Load modules.  This implementation will only work on modules that are nested 1 level deep.
    for( var module in App ) {
      //
      if( typeof App[module].init === "function" ) {
        App[module].init();
      }
    }
  }

  init();

})( App || (App = {}), jQuery );
