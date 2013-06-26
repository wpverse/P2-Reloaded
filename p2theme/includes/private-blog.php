<?php get_header() ?>

	<section id="posts" style="padding: 10px">
	
		<h1><?php _e('Private Blog', 'p2') ?></h1>
		
		<p><?php echo stripcslashes(get_option('p2_private_msg')) ?></p>
	
	</section>

<?php get_footer() ?>