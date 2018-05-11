import $ from 'jquery';
import whatInput from 'what-input';
import WebFont from 'webfontloader';
console.log('hey');
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
		if ( $('body').hasClass('home') ) {

			var playerTwoPlayer = new Vimeo.Player('playertwo', {
				title: false,
				byline: false,
				portrait: false,
				color: '3a6774',
				autoplay:false,
				background:true
			});

			// console.log('ready');

			playerTwoPlayer.play().then( ()=> {
				setTimeout( ()=> {
					data.$body.addClass( data.loadedClass );
					data.$spinner = this.data.$spinner.clone();
					this.data.$spinner.on('webkitTransitionEnd transitionend oTransitionEnd otransitionend', ()=> {
						this.data.$spinner.remove();
					});
				}, 2000 );
			}).catch( (error)=> {
				data.$body.addClass( data.loadedClass );
				data.$spinner = this.data.$spinner.clone();
				this.data.$spinner.on('webkitTransitionEnd transitionend oTransitionEnd otransitionend', ()=> {
					this.data.$spinner.remove();
				});
			    // switch (error.name) {
			    //     case 'PasswordError':
			    //         // the video is password-protected and the viewer needs to enter the
			    //         // password first
			    //         break;

			    //     case 'PrivacyError':
			    //         // the video is private
			    //         break;

			    //     default:
			    //         // some other error occurred
			    //         break;
			    // }
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

	scrollTo(e) {
		console.log('hit');
		document.getElementById('wpcf7-f67-o1').scrollIntoView({
		  behavior: 'smooth'
		});
		e.preventDefault();
	},

	init() {
		console.log('init');
		document.querySelector('.js-contact-button').addEventListener('click', this.scrollTo, false );
		this.loadIframe();
		this.scroll();
		$(window).on('scroll', this.data.scroll );

	}

};

let modules = {
	header: Header
}

for (let [key, module] of Object.entries( modules ) ) {
	console.log(module);
	module.init();
}

console.log('init');
window.App = modules;
window.App.Data = data;

// return modules;
