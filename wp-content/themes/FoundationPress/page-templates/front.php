<?php
/*
Template Name: Front
*/
get_header(); ?>
<!-- // test -->
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

        <div data-vimeo-url="https://player.vimeo.com/video/218861893" id="playertwo"></div>

		<!-- <iframe class="iframe js-iframe" src="https://player.vimeo.com/video/218861893?" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe> -->

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

<!-- blog -->
<section class="featured-sections blog">
    <?php
        $args = array( 'numberposts' => '3' );
        $recent_posts = wp_get_recent_posts( $args );

        foreach( $recent_posts as $recent ) {

            // echo get_the_post_thumbnail($recent['ID'], 'thumbnail');

    ?>
        <article class="featured-section-article no-image">
            <div class="featured-section-content">
                <a href="<?php echo get_permalink( $recent['ID'] ); ?>">
                    <h2><?php echo $recent['post_title']; ?></h2>
                </a>
                <small><?php $post_date = get_the_date( 'D M j, Y' ); echo $post_date; ?></small>
                <p>
                <?php
                    $str = $recent['post_excerpt'] ? $recent['post_excerpt'] : $recent['post_content'];
                    $str2 = wordwrap($str,120,'@@@@@');
                    $str_final = substr($str2,0,strpos($str2,'@@@@@'));
                    echo $str_final . '...';
                ?>
                </p>

                <a class="small button sites-button" href="<?php echo get_permalink( $recent['ID'] ); ?>" >
                    Read More <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                </a>

            </div>
        </article>
    <?php } ?>

    <article class="featured-section-article no-image ">
        <div class="featured-section-content">
            <a class="large button sites-button more" href="<?php echo get_permalink( get_option( 'page_for_posts' ) ); ?>" >
                More Stories!
            </a>
        </div>
    </article>

</section>

<?php wp_reset_query();  // Restore global post data stomped by the_post(). ?>

<?php

    $args = array(
        'post_type'=>'section',
        'orderby' => 'menu_order',
        'order'     => 'ASC',
        'meta_key' => 'type',
        'posts_per_page'=> -1,
    );

    $the_query = new WP_Query( $args );

?>

<section class="featured-sections">
    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
    	<?php
    		$postType = get_field('type');
			$p = $postType === 'image' ? '' : 'no-image';
    	?>
    	<article
    		class="featured-section-article <?php echo $p ?>"
    		style="
    			<?php
					if ( $postType === 'image' ) :
    			?>
	    			background-image:url('<?php the_post_thumbnail_url(); ?>');
	    		<?php endif; ?>
    		"
    	>

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

<!-- <div class="section-divider">
	<hr />
</div> -->

<?php

    $args = array(
        'post_type'=>'videos',
        'orderby' => 'menu_order',
        'order'     => 'ASC',
        'posts_per_page'=> -1,
    );

    $the_query = new WP_Query( $args );

?>

<section class="featured-sections">
    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
    	<article class="featured-section-article no-image" >

    		<div class="featured-section-content">
    		<?php if ( get_option( 'blogname' ) ) : ?>
    			<h1><?php the_title(); ?></h1>
    			<div class="section-divider">
    				<hr />
    			</div>
			 <?php endif; ?>
				<div class="featured-section-iframe">
					<?php the_content(); ?>
				</div>
    		</div>

    	</article>
	<?php endwhile; ?>
</section>

<?php wp_reset_query();  // Restore global post data stomped by the_post(). ?>

<!--         <div class="featured-section-content">

            <ul class="tabs" data-tabs id="example-tabs">
              <li class="tabs-title is-active"><a href="#panel1" aria-selected="true">Tab 1</a></li>
              <li class="tabs-title"><a data-tabs-target="panel2" href="#panel2">Tab 2</a></li>
            </ul>

            <div class="tabs-content" data-tabs-content="example-tabs">

              <div class="tabs-panel is-active" id="panel1">
                <p>Vivamus hendrerit arcu sed erat molestie vehicula. Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor. Ut in nulla enim. Phasellus molestie magna non est bibendum non venenatis nisl tempor. Suspendisse dictum feugiat nisl ut dapibus.</p>
              </div>

              <div class="tabs-panel" id="panel2">
                <p>Suspendisse dictum feugiat nisl ut dapibus.  Vivamus hendrerit arcu sed erat molestie vehicula. Ut in nulla enim. Phasellus molestie magna non est bibendum non venenatis nisl tempor.  Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor.</p>
              </div>

            </div>

        </div> -->

<?php

$args = array(
    'post_type'=>'tab',
    'orderby' => 'menu_order',
    'order'     => 'ASC',
    'posts_per_page' => -1
);

$query = new WP_Query($args);
$q = array();

while ( $query->have_posts() ) :
    $query->the_post(); 
    $external = get_post_meta( $post->ID, '_dcms_eufi_img' )[0];
    $url = get_field('url');
    $sharing = get_field('sharing_type');
    // if ( $url ):
    if ( $external ) :
        $image = $external;
    else :
        $image = get_the_post_thumbnail_url();
    endif;

    $link = $sharing ? '<a target="_blank" href="' . $url . '"class="feature-image-link"><i class="fa fa-' . $sharing . '"></i></a>' : '';
    $a = '<button class="featured-image" data-open="featured-image-tab" style="background-image: url(' . $image . '">' . $link . '</button>';
    $categories = get_the_category();
    // $title = get_the_title();
?>
<?php
    foreach ( $categories as $key=>$category ) :
        $b = $category->name;
?>
<?php
    endforeach;
    $q[$b][] = $a; // Create an array with the category names and post titles
?>

<?php

endwhile;
wp_reset_postdata();

?>

<section class="featured-sections photo-tab">
    <article class="featured-section-article no-image" >
        <div class="featured-section-content">
            <ul class="tabs" data-tabs id="example-tabs">
<?php
foreach ($q as $key=>$values) :

?>
                <li class="tabs-title">
                    <a href="#tabs-panel-<?php echo $key; ?>" class="" aria-selected="true" >
                        <h5><?php echo $key  ?></h5>
                    </a>
                </li>
<?php
endforeach;
?>
            </ul>
            <div class="tabs-content" data-tabs-content="example-tabs">
                <?php
                foreach ($q as $key=>$values) :
                    $count = 0;
                ?>
                <div class="tabs-panel <?php echo $is_active; ?>" id="tabs-panel-<?php echo $key; ?>">
                <?php
                    foreach ($values as $value) :
                        if ( $count < 6 ) {
                            // echo $count;
                ?>
                <?php
                                echo $value;
                ?>
                <?php
                            $count++;
                        }
                    endforeach;
                ?>
                </div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
    </article>
</section>

<section class="featured-sections">
	<article class="featured-section-article no-image" style="background-image:url('<?php the_post_thumbnail_url(); ?>');">

		<div class="featured-section-content">
			<h1>Contact</h1>
			<div class="section-divider">
				<hr />
			</div>
			<?php echo do_shortcode( '[contact-form-7 id="67" title="Contact form 1"]' ); ?>
		</div>

	</article>
</section>

<?php get_footer();
