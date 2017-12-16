import $ from 'jquery';
import whatInput from 'what-input';
import WebFont from 'webfontloader';

WebFont.load({
  google: {
    families: ['Droid Sans', 'Droid Serif', 'Droid Serif:italic', 'Bree Serif']
  }
});

window.$ = $;

import Foundation from 'foundation-sites';
// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below
import './lib/foundation-explicit-pieces';
// import './lib/demosite';

$(document).foundation();

let data = {
	$window: $(window),
	$body: $('body'),
	loadedClass: 'is-loaded',
	offClass: 'is-off',
	scrolled: {
		status: false,
		$el: $('body'),
		class: 'is-scrolled',
		threshold: 200,
		get state() {
			return this.status;
		},
		set state(state) {

			let h = this.$el.hasClass( this.class );
			let s = this.status;
			let t = this.threshold;
			if ( s < t && state >= t && !h ) {
				this.$el.addClass( this.class );
			} else if ( s >= t && state < t && h ) {
				this.$el.removeClass( this.class );
			}

			this.status = state;
		}
	},
	$spinner: null,
};

let Header = {

	data: {

		$iFrame: $('.js-iframe'),
		$spinner: $('.js-spinner'),
		$topBar: $('.top-bar'),
		scroll() {
			modules.header.scroll();
		}
	},

	loadIframe() {
		if ( this.data.$iFrame.html() ) {
			this.data.$iFrame.on('load', ()=> {

				data.$body.addClass( data.loadedClass );
				data.$spinner = this.data.$spinner.clone();
				this.data.$spinner.on('webkitTransitionEnd transitionend oTransitionEnd otransitionend', ()=> {
					this.data.$spinner.remove();
				});

			});
		} else {
			this.data.$topBar.css({
				'transition-delay': '0.5s',
			});
			data.$body.addClass( data.loadedClass );
		}

	},

	scroll() {
		data.scrolled.state = data.$window.scrollTop();
	},

	init() {

		this.loadIframe();
		this.scroll();
		$(window).on('scroll', this.data.scroll );

	}

};

let modules = {
	header: Header
}

for (let [key, module] of Object.entries( modules ) ) {

	module.init();

}

window.App = modules;
window.App.Data = data;

// return modules;
