<!DOCTYPE html>
<html>
<head>

	<meta charset="utf-8">

	<title><?php bloginfo('title') ?> <?php wp_title() ?></title>
	
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url') ?>">
	<link rel="stylesheet" href="<?php bloginfo('template_url') ?>/css/<?php echo get_option('p2_color_scheme') ?>.css">
	
	<!--[if lt IE 9]>
	<script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
	<script src="<?php bloginfo('template_url') ?>/js/jquery.color.js"></script>
	<script src="<?php bloginfo('template_url') ?>/js/jquery.scrollto.js"></script>
	
	<script>
		//<![CDATA]
		var P2 = {
			status: '',
			last_refresh: <?php echo time() ?>,
			site_url: '<?php echo bloginfo('url') ?>',
			ajax_url: '<?php echo admin_url('admin-ajax.php') ?>',
			theme_url: '<?php bloginfo('template_url') ?>',
			posts_nonce: '<?php echo wp_create_nonce('p2-get-posts') ?>',
			comments_nonce: '<?php echo wp_create_nonce('p2-get-comments') ?>',
<?php if ( p2_can_post() ): ?>
			post_nonce: '<?php echo wp_create_nonce('p2-post') ?>',
<?php endif; ?>
			comment_nonce: '<?php echo wp_create_nonce('p2-comment') ?>',
<?php if ( is_user_logged_in() ): ?>
			comment_unfiltered_html_nonce: '<?php echo wp_create_nonce('p2-unfiltered-html-comment') ?>',
<?php endif; ?>
<?php if ( current_user_can('edit_posts') ): ?>
			edit_post_nonce: '<?php echo wp_create_nonce('p2-edit-post') ?>',
<?php endif; ?>
<?php if ( current_user_can('edit_comment') ): ?>
			edit_comment_nonce: '<?php echo wp_create_nonce('p2-edit-comment') ?>',
<?php endif; ?>
<?php if ( current_user_can('p2_edit_todo') ): ?>
			mark_comment_nonce: '<?php echo wp_create_nonce('p2-mark-comment') ?>',
<?php endif; ?>
			l10n: {}
		};
		//]]>
	</script>
	<script src="<?php bloginfo('template_url') ?>/js/p2.js"></script>
<?php if ( get_option('p2_attachments') && is_user_logged_in() ): ?>
	<script>P2.remove_attachment_nonce = '<?php echo wp_create_nonce('p2-remove-attachment') ?>';</script>
	<script src="<?php bloginfo('template_url') ?>/js/p2-attachments.js"></script>
<?php endif; ?>
	
	<?php wp_enqueue_script('comment-reply') ?>
	<?php wp_head() ?>
	
</head>
<body>

	<!-- main wrapper -->
	<div id="wrap">
	
		<!-- header -->
		<header>
		
			<hgroup id="site-title">
				<h1><a href="<?php bloginfo('siteurl') ?>"><?php bloginfo('title') ?></a></h1>
				<h2><?php bloginfo('description') ?></h2>
			</hgroup>
			
			<nav id="user-nav">
<?php if ( is_user_logged_in() ): ?>
				<p>Hi <strong><?php global $current_user; echo $current_user->display_name ?></strong>, 
				<?php if ( current_user_can('publish_posts') ): ?><a href="<?php echo admin_url('post-new.php') ?>">Add Post</a><?php endif; ?> | <a href="<?php echo wp_logout_url(get_bloginfo('url')) ?>">Logout</a>
<?php else: ?>
				<p>Hi <strong>Guest</strong>, <a href="<?php echo wp_login_url(get_bloginfo('url')) ?>">Login</a></p>
<?php endif; ?>
			</nav>
		
			<br class="clear">
		
			<?php
			if ( ! get_option('p2_is_private') || ( get_option('p2_is_private') && is_user_logged_in() ) )
			{
				wp_nav_menu(array(
					'menu' => 'topmenu',
					'container' => 'nav',
					'container_id' => 'pages-nav',
					'fallback_cb' => 'p2_pages_menu'
				));
			}
			?>
		
		</header>
		<!-- /header -->
		
		<!-- main content -->
		<section id="content">
		
