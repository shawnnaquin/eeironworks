<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the "off-canvas-wrap" div and all content after.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>

		</section>
		<div class="footer-container" data-sticky-footer>
			<footer class="footer">
				<article>
					<h1>EE</h1>
					<div class="footer-social">
						<?php echo do_shortcode( '[addtoany buttons="facebook"]' ); ?>
						<div class="a2a_kit a2a_kit_size_32 a2a_default_style a2a_follow">
						    <a class="a2a_button_instagram" data-a2a-follow="ericthesmith"></a>
						</div>
						<?php echo do_shortcode( '[addtoany buttons="twitter"]' ); ?>
					</div>
				</article>
				<article>
					<div>
						<p>
							<a href="" target="_blank">
								<?php
									$street = get_option( 'site_street' );
									if ( $street ) :
										echo $street;
								?>
									<br/>
								<?php
									 endif;
									$add = get_option( 'site_address' );
									if ( $add ) :
										echo $add;
									endif;
								?>
							</a>
							<br>
							<?php
								$tel = get_option('site_phone' );
								if ( $tel ) {
									$phone = preg_replace('/[^0-9]/', '', $tel );
								}
								if ( $tel ) :
							?>
							<a href="tel:+1<?php echo $phone; ?>">
								<?php echo $tel ?>
							</a>
							<?php
								endif;
							?>
						</p>
					</div>
				</article>

				<div class="copyright">
					&copy; EE Iron Studio, LTD, All Right Reserved.
				</div>

				<?php do_action( 'foundationpress_before_footer' ); ?>
				<?php dynamic_sidebar( 'footer-widgets' ); ?>
				<?php do_action( 'foundationpress_after_footer' ); ?>
			</footer>
		</div>

		<?php do_action( 'foundationpress_layout_end' ); ?>

<?php if ( get_theme_mod( 'wpt_mobile_menu_layout' ) === 'offcanvas' ) : ?>
	</div><!-- Close off-canvas content -->
<?php endif; ?>


<?php wp_footer(); ?>
<?php do_action( 'foundationpress_before_closing_body' ); ?>
</body>
</html>
