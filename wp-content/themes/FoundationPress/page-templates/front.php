<?php
/*
Template Name: Front
*/
get_header(); ?>

<header class="front-hero" role="banner">

	<div class="marketing">
		<div class="tagline">
			<h1><?php bloginfo( 'name' ); ?></h1>
			<p>
				Welcome to E.E. Ironworks, a blacksmithing shop that specializes in traditional blacksmithing techniques, metal fabrication, and custom installations.
			</p>
			<a role="button" class="download large button sites-button" href="#">Contact Us</a>
		</div>
	</div>

	<div class="iframe-container js-iframe-container">

		<div class="sk-folding-cube js-spinner spinner">
		  <div class="sk-cube1 sk-cube"></div>
		  <div class="sk-cube2 sk-cube"></div>
		  <div class="sk-cube4 sk-cube"></div>
		  <div class="sk-cube3 sk-cube"></div>
		</div>

		<iframe class="iframe js-iframe" src="https://player.vimeo.com/video/218861893?title=0&byline=0&portrait=0&color=3a6774&autoplay=1&loop=1&background=1" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>

	</div>

</header>

<?php do_action( 'foundationpress_before_content' ); ?>
<?php while ( have_posts() ) : the_post(); ?>
<section class="intro" role="main">
	<div class="fp-intro">

		<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
			<?php do_action( 'foundationpress_page_before_entry_content' ); ?>
			<div class="entry-content">
				<?php the_content(); ?>
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
			<?php do_action( 'foundationpress_page_before_comments' ); ?>
			<?php comments_template(); ?>
			<?php do_action( 'foundationpress_page_after_comments' ); ?>
		</div>

	</div>

</section>
<?php endwhile;?>
<?php do_action( 'foundationpress_after_content' ); ?>

<?php wp_reset_query();  // Restore global post data stomped by the_post(). ?>

<?php

    $args = array(
        'post_type'=>'featured_images',
        'orderby' => 'menu_order',
        'order'     => 'ASC',
        'meta_key' => 'url',
        'meta_key' => 'sharing_type',
        'meta_key' => 'url',
        'posts_per_page'=> 6,
    );

    $the_query = new WP_Query( $args );

?>
	<section class="featured-images">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        	<button class=" featured-image" data-open="featured-image" style="background-image: url( <?php the_post_thumbnail_url( 'large' ); ?> );">
    		<?php
    			$url = get_field('url');
    			if ( $url ):
    		?>
        		<a target="_blank" href="<?php echo $url ?>"class="feature-image-link">
        			<i class="fa fa-<?php echo get_field('sharing_type');?>"></i>
        		</a>
        	<?php
        		endif;
        	?>
        	</button>
        <?php endwhile; ?>
	</section>

	<?php rewind_posts(); ?>

    <div class="reveal" id="featured-image" data-reveal data-close-on-click="true" data-animation-in="fade-in fast" data-animation-out="fade-out fast">
    	<div class="orbit" role="region" aria-label="Favorite Space Pictures" data-orbit>
    	  <div class="orbit-wrapper">
    	    <div class="orbit-controls">
    	      <button class="orbit-previous"><span class="show-for-sr">Previous Slide</span>&#9664;&#xFE0E;</button>
    	      <button class="orbit-next"><span class="show-for-sr">Next Slide</span>&#9654;&#xFE0E;</button>
    	    </div>
    	    <ul class="orbit-container">
    	    	<?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
    	    		<li class="is-active orbit-slide">
    	    		  <figure class="orbit-figure">
	    	    		<?php 
	    	    			the_post_thumbnail( 'large', array('class' => 'orbit-image') ); 
	    	    			$content = get_the_content(); 
	    	    			if ($content):
	    	    		?>
    	    		    <figcaption class="orbit-caption">
    	    		    	<?php echo wp_filter_nohtml_kses( $content ); ?>
    	    		    </figcaption>
	    	    		<? endif; ?>
    	    		  </figure>
    	    		</li>
    	    	<?php endwhile; ?>
    	    </ul>
    	  </div>

	<?php rewind_posts(); ?>

    	  <nav class="orbit-bullets">
    	  	<?php $count = 0; ?>
	    	<?php while ($the_query->have_posts()) : $the_query->the_post();?>

	    	    <button
	    	    	class="<?php if ( $count === 0 ): echo 'is-active'; endif; ?>"
	    	    	data-slide="<?php echo $count ?>"
	    	    >
		    		<?php if ( $count === 0) : ?>
    	    		<span class="show-for-sr">Current Slide</span>
	    	    	<?php endif; ?>
	    	    </button>
	    	<?php $count++; endwhile; ?>
    	  </nav>
    	</div>
    </div>

<?php wp_reset_query();  // Restore global post data stomped by the_post(). ?>

<?php

    $args = array(
        'post_type'=>'section',
        'orderby' => 'menu_order',
        'order'     => 'ASC',
        'posts_per_page'=> -1,
    );

    $the_query = new WP_Query( $args );

?>

<section class="featured-sections">
    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
    	<article class="featured-section-article" style="background-image:url('<?php the_post_thumbnail_url(); ?>');">

    		<div class="featured-section-content">
				<h1><?php the_title(); ?></h1>
				<div class="section-divider">
					<hr />
				</div>
	    		<?php the_content(); ?>
    		</div>

    	</article>
	<?php endwhile; ?>
</section>

<?php wp_reset_query();  // Restore global post data stomped by the_post(). ?>

<div class="section-divider">
	<hr />
</div>


<section class="benefits">
	<header>
		<h2>Build Foundation based sites, powered by WordPress</h2>
		<h4>Foundation is the professional choice for designers, developers and teams. <br /> WordPress is by far, <a href="http://trends.builtwith.com/cms">the world's most popular CMS</a> (currently powering 38% of the web).</h4>
	</header>

	<div class="semantic">
		<img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/assets/images/demo/semantic.svg" alt="semantic">
		<h3>Semantic</h3>
		<p>Everything is semantic. You can have the cleanest markup without sacrificing the utility and speed of Foundation.</p>
	</div>

	<div class="responsive">
		<img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/assets/images/demo/responsive.svg" alt="responsive">
		<h3>Responsive</h3>
		<p>You can build for small devices first. Then, as devices get larger and larger, layer in more complexity for a complete responsive design.</p>

	</div>

	<div class="customizable">
		<img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/assets/images/demo/customizable.svg" alt="customizable">
		<h3>Customizable</h3>
		<p>You can customize your build to include or remove certain elements, as well as define the size of columns, colors, font size and more.</p>

	</div>

	<div class="professional">
		<img src="<?php echo get_stylesheet_directory_uri(); ?>/dist/assets/images/demo/professional.svg" alt="professional">
		<h3>Professional</h3>
		<p>Millions of designers and developers depend on Foundation. We have business support, training and consulting to help grow your product or service.</p>
	</div>

	<div class="why-foundation">
		<a href="/kitchen-sink">See what's in Foundation out of the box →</a>
	</div>

</section>



<?php get_footer();
