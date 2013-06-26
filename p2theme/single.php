<?php get_header() ?>

		<!-- post area -->
		<section id="posts" class="single">

		<?php if ( is_front_page() ) get_template_part('posting-form') ?>
		
		<?php get_template_part('theloop') ?>
		
		<nav class="pagination">
			<p><?php p2_post_nav() ?></p>
		</nav>
		
		</section>
		<!-- /post area -->
		
		<?php get_sidebar() ?>

<?php get_footer() ?>