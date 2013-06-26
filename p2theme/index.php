<?php if ( get_option('p2_homepage') == 'normal' ): ?>

		<?php get_header() ?>

		<!-- post area -->
		<section id="posts">

		<?php if ( is_front_page() ) get_template_part('posting-form') ?>
		
<?php if ( is_front_page() ): ?>
				
				<!-- posts header -->
				<header id="posts-header">
				
					<h1>Recent Updates <a href="<?php bloginfo('rss_url') ?>"><img src="<?php bloginfo('template_url') ?>/images/feed.png" alt="Recent Updates Feed" title="RSS Feed" width="12" height="12"></a></h1>
					
					<p>
						<a href="#" id="p2-hide-threads">Hide threads</a> | 
						<a href="#" id="p2-keyboard-shortcuts">Keyboard shortcuts</a>
					</p>
				
					<br class="clear">
				
				</header>
				<!-- /posts header -->
				
<?php endif; ?>

<?php if ( ! is_single() ): ?>	
	
				<!-- comment form -->
				<section id="comment-form"><?php p2_comment_form() ?></section>
				<!-- /comment-form -->
				
<?php endif; ?>
		
		<?php get_template_part('theloop') ?>
		
		<nav class="pagination">
			<p><?php posts_nav_link() ?></p>
		</nav>
		
		</section>
		<!-- /post area -->
		
		<?php get_sidebar() ?>

		<?php get_footer() ?>
		
<?php else:
	
	require_once TEMPLATEPATH . '/fullscreen.php';
	
endif;

?>