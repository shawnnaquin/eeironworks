<?php
/*
Template Name: Blog
*/
get_header(); 
$external = get_post_meta( $post->ID, '_dcms_eufi_img' )[0];
if ( $external ) :
    $image = $external;
else :
    $image = get_the_post_thumbnail_url();
endif;
?>

<?php if ($image) : ?>
    <header class="featured-hero" role="banner" 
        style="background-image:url('<?php echo $image; ?>');"
    ></header>
<?php endif; ?>

<!-- blog -->
<section class="featured-sections blog">

    <article class="featured-section-article no-image">
        <div class="featured-section-content">
            <?php get_sidebar(); ?>
        </div>
    </article>

    <?php
        $args = array( 'numberposts' => '-1' );
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

</section>

<?php wp_reset_query();  // Restore global post data stomped by the_post(). ?>

<?php get_footer();
