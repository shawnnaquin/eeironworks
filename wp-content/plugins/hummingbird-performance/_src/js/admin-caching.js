import Clipboard from './utils/clipboard';
import Fetcher from './utils/fetcher';

( function( $ ) {
    'use strict';
    WPHB_Admin.caching = {

        module: 'caching',
        selectedServer: '',
        $serverSelector: null,
        $serverInstructions: [],
        $snippets: [],
        selectedExpiryType: '',

        init: function () {
            let self                    = this,
                cloudflareLink          = $('#wphb-box-caching-settings #connect-cloudflare-link, #wphb-box-caching-summary #connect-cloudflare-link'),
                configureLink           = $('#configure-link'),
                cloudFlareDismissLink   = $('#dismiss-cf-notice'),
                cloudFlareDashNotice    = $('.cf-dash-notice'),
                hash                    = window.location.hash,
                viewSnippetLink         = $('#view-snippet-code');

			new Clipboard('.wphb-code-snippet .button');

            if ( wphbCachingStrings )
                self.strings = wphbCachingStrings;

            cloudflareLink.on('click', function(e) {
                e.preventDefault();
				$('#wphb-server-type').val('cloudflare').trigger('sui:change');
				self.hideCurrentInstructions();
                self.setServer('cloudflare');
				self.showServerInstructions('cloudflare');
				self.selectedServer = 'cloudflare';
				$('html, body').animate({ scrollTop: $('#cloudflare-steps').offset().top }, 'slow');
            });
            configureLink.on('click', function(e) {
                e.preventDefault();
				$('html, body').animate({ scrollTop: $('#wphb-box-caching-settings').offset().top }, 'slow');
            });
            if (hash) {
                $('html, body').animate({ scrollTop: $(hash).offset().top }, 'slow');
            }

            this.$serverSelector = $( '#wphb-server-type' );
            this.selectedServer  = this.$serverSelector.val();

            self.$snippets.apache    = $('#wphb-code-snippet-apache').find('pre').first();
			self.$snippets.LiteSpeed    = $('#wphb-code-snippet-litespeed').find('pre').first();
            self.$snippets.nginx     = $('#wphb-code-snippet-nginx').find('pre').first();

            viewSnippetLink.on('click', function(e) {
                e.preventDefault();
                let serverInstructions = $( '#wphb-server-instructions-' + self.selectedServer.toLowerCase() );
                $('#manual-' + self.selectedServer.toLowerCase() ).trigger("click");
                let caching = window.WPHB_Admin.getModule( 'caching' );
                $('html, body').animate({ scrollTop: serverInstructions.offset().top - 50 }, 'slow');
            });

            let instructionsList = $( '.wphb-server-instructions' );
            instructionsList.each( function() {
                self.$serverInstructions[ $(this).data('server') ] = $(this);
            });

            let expirySelectors = $( '.wphb-expiry-select' );
            let expiryChangeNotice = $( '#wphb-expiry-change-notice' );

            expirySelectors.each( function() {
                const type = $(this).data('type');
                if ( type ) {
                    $(this).change( function() {
                        // Expiration selector has changed
                        ( function() {
                            let expiry_times = [];
                            if ( 'all' === type ) {
                                expiry_times = self.getExpiryTimes( 'all' );
                            } else {
                                expiry_times = self.getExpiryTimes();
                            }
                            // Reload the code snippet
                            self.reloadSnippets( expiry_times );
                            expiryChangeNotice.slideDown();

                        })( this );
                    });
                } else {
                    $(this).change( function () {
                        expiryChangeNotice.slideDown();
                    })
                }

            });

            this.showServerInstructions( this.selectedServer );

            this.$serverSelector.change( function() {
                let value = $(this).val();
                self.hideCurrentInstructions();
                self.showServerInstructions( value );
                self.setServer(value);
                self.selectedServer = value;
                $('.hb-server-type').val( value );
                // This is used to trigger the resizing of the tabs.
                $(window).trigger( 'resize' );
            });

            let expiryInput = $("input[name='expiry-set-type']");
            let expirySettingsForm = $('.sui-box-settings-row');
			expiryInput.each( function () {
                if ( this.checked ) {
                    if ( 'expiry-all-types' === $(this).attr('id') ) {
						expirySettingsForm.find( "[data='expiry-single-type']" ).hide();
						expirySettingsForm.find( "[data='expiry-all-types']" ).show();
                        self.selectedExpiryType = 'all';
                    } else if ( 'expiry-single-type' === $(this).attr('id') ) {
						expirySettingsForm.find( "[data='expiry-all-types']" ).hide();
						expirySettingsForm.find( "[data='expiry-single-type']" ).show();
                        self.selectedExpiryType = 'single';
                    }
                }
            });
			expiryInput.on( 'click', function () {
                let expiry_times = [];
                if ( 'expiry-all-types' === $(this).attr('id') ) {
					expirySettingsForm.find( "[data='expiry-single-type']" ).hide();
					expirySettingsForm.find( "[data='expiry-all-types']" ).show();
                    expiry_times = self.getExpiryTimes( 'all' );
                    self.selectedExpiryType = 'all';
                } else if ( 'expiry-single-type' === $(this).attr('id') ) {
					expirySettingsForm.find( "[data='expiry-all-types']" ).hide();
					expirySettingsForm.find( "[data='expiry-single-type']" ).show();
                    expiry_times = self.getExpiryTimes();
                    self.selectedExpiryType = 'single';
                }

                // Reload the code snippet
                self.reloadSnippets( expiry_times );
			});

            cloudFlareDismissLink.click( function(e) {
                e.preventDefault();
                Fetcher.notice.dismissCloudflareDash();
                cloudFlareDashNotice.slideUp();
                cloudFlareDashNotice.parent().addClass('no-background-image');

            });

            let activateButton = $( '.activate-button' );
            activateButton.click( function () {
                let expiry_times = [];
                if ( '' !== self.selectedExpiryType ) {
                    if ('all' === self.selectedExpiryType) {
                        expiry_times = self.getExpiryTimes('all');
                    } else {
                        expiry_times = self.getExpiryTimes();
                    }
                    Fetcher.caching.setExpiration( self.selectedExpiryType, expiry_times );
                }
            });

			/**
			 * Parse rss cache settings.
             *
             * @since 1.8
			 */
			$('.box-caching-rss .sui-box-footer').on('click', '.sui-button[type="submit"]', function (e) {
                e.preventDefault();

				const spinner = $(this).parent().find('.spinner');
				const settings_form = $('form[id="rss-caching-settings"]');

				// Make sure a positive value is always reflected for the rss expiry time input.
                let rss_expiry_time = settings_form.find('#rss-expiry-time');
                rss_expiry_time.val( Math.abs( rss_expiry_time.val() ) );

				spinner.addClass('visible');

				Fetcher.caching.saveSettings( settings_form.serialize() )
					.then( ( response ) => {
						spinner.removeClass('visible');

						if ( 'undefined' !== typeof response && response.success ) {
							WPHB_Admin.notices.show( 'wphb-notice-cache', true, 'success' );
						} else {
							WPHB_Admin.notices.show( 'wphb-notice-cache', true, 'error', wphb.strings.errorSettingsUpdate );
						}
					});
			});

			/**
             * Parse page cache settings.
             *
			 * TODO: this method and the method above are very similar, maybe refactor at some point?
             * @since 1.8.1
			 */
			$('.box-caching-other-settings').on('click', 'input[type="submit"]', function (e) {
			    e.preventDefault();

				const spinner = $(this).parent().find('.spinner');
				const settings_form = $('form[id="other-caching-settings"]');

				spinner.addClass('visible');

				Fetcher.caching.saveOtherSettings( settings_form.serialize() )
					.then( ( response ) => {
						spinner.removeClass('visible');

						if ( 'undefined' !== typeof response && response.success ) {
							WPHB_Admin.notices.show( 'wphb-notice-cache', true, 'success' );
						} else {
							WPHB_Admin.notices.show( 'wphb-notice-cache', true, 'error', wphb.strings.errorSettingsUpdate );
						}
					});
			});

			return this;
        },

        setServer: function( value ) {
            Fetcher.caching.setServer( value );
        },

        hideCurrentInstructions: function() {
            let selected = this.selectedServer;
            if ( this.$serverInstructions[ selected ] ) {
                this.$serverInstructions[ selected ].hide();
            }
        },

        showServerInstructions: function( server ) {
            if ( typeof this.$serverInstructions[ server ] !== 'undefined' ) {
                let serverTab = this.$serverInstructions[ server ];
				serverTab.show();
                // Show tab.
				serverTab.find('.sui-tab:first-child > label').trigger('click');
            }

            if ( 'apache' === server || 'LiteSpeed' === server ) {
                $( '.enable-cache-wrap-' + server ).show();
            }
            else {
                $( '#enable-cache-wrap' ).hide();
            }
        },

        reloadSnippets: function( expiry_times ) {
            let self = this;
            let stop = false;

            for ( let i in self.$snippets ) {
                if ( self.$snippets.hasOwnProperty( i ) ) {
                    Fetcher.caching.reloadSnippets( i, expiry_times )
                        .then( ( response ) => {
                            if ( stop ) {
                                return;
                            }

                            self.$snippets[response.type].text( response.code );
                        });
                }
            }
        },

        getExpiryTimes: function( type ) {
            let expiry_times = [];
            if ( 'all' === type ){
                let all = $('#set-expiry-all').val();
                expiry_times = {
                    expiry_javascript: all,
                    expiry_css: all,
                    expiry_media: all,
                    expiry_images: all,
                }
            } else {
                expiry_times = {
                    expiry_javascript: $('#set-expiry-javascript').val(),
                    expiry_css: $('#set-expiry-css').val(),
                    expiry_media: $('#set-expiry-media').val(),
                    expiry_images: $('#set-expiry-images').val(),
                };
            }
            return expiry_times;
        }
    };
}( jQuery ));