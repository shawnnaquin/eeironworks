<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header();
$external = get_post_meta( $post->ID, '_dcms_eufi_img' )[0];
if ( $external ) :
	$image = $external;
else :
	$image = get_the_post_thumbnail_url();
endif;
?>
	<header class="featured-hero" role="banner" 
		style="background-image:url('<?php echo $image; ?>');"
	></header>

<div class="main-wrap" role="main">
<?php // get_sidebar(); ?>
<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post();
	$external = get_post_meta( $post->ID, '_dcms_eufi_img' )[0];
	if ( $external ) :
		$image = $external;
	else :
		$image = get_the_post_thumbnail_url();
	endif;
?>
	<header class="featured-hero" role="banner" 
		style="background-image:url('<?php echo $image; ?>');"
	></header>

	<article <?php post_class('main-content') ?> id="post-<?php the_ID(); ?>">
		<div class="single-blog-social">
			<?php echo do_shortcode( '[addtoany buttons="facebook"]' ); ?>
			<div class="a2a_kit a2a_kit_size_32 a2a_default_style a2a_follow">
			    <a class="a2a_button_instagram" data-a2a-follow="ericthesmith"></a>
			</div>
			<?php echo do_shortcode( '[addtoany buttons="twitter"]' ); ?>
		</div>
		<header>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<?php foundationpress_entry_meta(); ?>
		</header>
		<?php do_action( 'foundationpress_post_before_entry_content' ); ?>
		<div class="entry-content">
			<?php the_content(); ?>
			<?php edit_post_link( __( '(Edit)', 'foundationpress' ), '<span class="edit-link">', '</span>' ); ?>
		</div>
		<footer>
			<?php
				wp_link_pages(
					array(
						'before' => '<nav id="page-nav"><p>' . __( 'Pages:', 'foundationpress' ),
						'after'  => '</p></nav>',
					)
				);
			?>
			<p><?php the_tags(); ?></p>
		</footer>
		<?php the_post_navigation(); ?>
		<?php // do_action( 'foundationpress_post_before_comments' ); ?>
		<?php // comments_template(); ?>
		<?php // do_action( 'foundationpress_post_after_comments' ); ?>
	</article>
<?php endwhile;?>

<?php do_action( 'foundationpress_after_content' ); ?>

</div>
<?php get_footer();
