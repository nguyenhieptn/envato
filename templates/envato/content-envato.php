<?php
/**
 * The default template for displaying content envato
 *
 * Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="row">
		<header class="entry-header col-xs-12 col-sm-5 col-md-5 col-lg-5">
			<div class="row">
				<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 align-center">
					<?php envato_get_item_thumbnail_image(); ?>
					<?php envato_get_item_social_share(); ?>
				</div>
				<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 no-padding">
					<h3 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
				</div>
			</div>
		</header><!-- .entry-header -->

		<div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
			<div class="entry-content">
				<?php envato_get_item_category(); ?>
				<?php envato_the_excerpt(150); ?>
			</div><!-- .entry-content -->
		</div>
		<div class="col-xs-12 col-sm-2 col-md-2 col-lg-2">
			<footer class="entry-meta">
				<?php envato_get_item_price(); ?>
				<a href="#" class="btn btn-primary"><?php esc_html_e('Download', 'twentytwelve'); ?></a>
			</footer><!-- .entry-meta -->
		</div>
	</div>
</article><!-- #post -->
