<?php
/*
Template Name: Fullscreen To-Do List
*/
?>
<!DOCTYPE html>
<html>
<head>
	
	<meta charset="<?php bloginfo('charset') ?>">
	
	<title><?php bloginfo('title') ?> <?php wp_title() ?></title>
	
	<link rel="stylesheet" href="<?php bloginfo('template_url') ?>/css/todo-fullscreen.css">
	<link rel="stylesheet" href="<?php bloginfo('template_url') ?>/css/facebox.css">
	
	<script>
		//<![CDATA[
		var P2 = {
			site_url: '<?php bloginfo('url') ?>',
			ajax_url: '<?php echo admin_url('admin-ajax.php') ?>',
			theme_url: '<?php bloginfo('template_url') ?>',
<?php if ( p2_can_post() ): ?>
			post_nonce: '<?php echo wp_create_nonce('p2-post') ?>',
<?php endif; ?>
			comment_nonce: '<?php echo wp_create_nonce('p2-comment') ?>',
<?php if ( current_user_can('p2_edit_todo') ): ?>
			mark_comment_nonce: '<?php echo wp_create_nonce('p2-mark-comment') ?>'
<?php endif; ?>
		};
		//]]>
	</script>
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script src="<?php bloginfo('template_url') ?>/js/jquery.facebox.js"></script>
	<script src="<?php bloginfo('template_url') ?>/js/todo-fullscreen.js"></script>
	
	<?php wp_head() ?>
	
</head>
<body>

<?php

// If this is used as a page template, get our posts.
if ( is_page() )
{
	query_posts(array());
}

$can_mark = current_user_can('p2_edit_todo');
$logged_in = is_user_logged_in();

?>

	<div id="wrap">
	
		<header>
		
			<h1><a href="<?php bloginfo('url') ?>"><?php bloginfo('name') ?></a></h1>
			<?php if ( p2_can_post() ): ?><p><a href="#new-list-modal" class="facebox" id="new-list">+ Add New List</a></p><?php endif; ?>
		
		</header>
	
		<div id="content">
	
			<section id="lists">

				<ul>
				
<?php if ( have_posts() ): while ( have_posts() ): 
the_post(); $todos = p2_get_todos(get_the_ID()); ?>
					<li id="list-<?php the_ID() ?>" class="post"><?php if ( $logged_in ): ?><a href="#add-todo-modal" class="facebox" rel="facebox[.post<?php the_ID() ?>]"><?php the_title(); ?> +</a><?php else: ?><?php the_title() ?><?php endif; ?>
						<ul>
<?php if ( empty($todos ) ): ?>
							<li>No items yet.</li>
<?php else: foreach ( $todos as $todo ): ?>
							<li>
								<label><input type="checkbox" id="todo-<?php echo $todo->comment_ID ?>" value="<?php echo $todo->comment_ID ?>" class="todo"<?php if ( ! $can_mark ): ?> disabled<?php endif; ?><?php if ( $todo->checked ): ?> checked<?php endif; ?>> <?php echo $todo->comment_content ?></label>
							</li>
<?php endforeach; endif; ?>
						</ul>
					</li>
					
<?php endwhile; endif; ?>
				
				</ul>
			
			</section>
			
			<br class="clear">
		
		</div>
		
		<footer>
			<p><?php bloginfo('name') ?><br>
				<small>&copy; 2011 <?php bloginfo('name') ?></small></p>
		</footer>
	
	</div>
	
	<div style="display: none">
	
<?php if ( p2_can_post() ): ?>
		<div id="new-list-modal">
			
			<form action="" method="post" class="new-list-form">
			
				<p><label>Title:<br>
					<input type="text" name="new-list-title" class="new-list-title" size="52"></label></p>
				
				<p><label>Short Description:<br>
					<textarea name="new-list-desc" class="new-list-desc" rows="10" cols="50"></textarea></label></p>
					
				<p><input type="submit" name="submit" value="Add List"></p>
			
			</form>
			
		</div>
<?php endif; ?>
	
<?php if ( $logged_in ): ?>
		<div id="add-todo-modal">
			
			<form action="" method="post" class="add-todo-form">
				
				<p><label>Add To-Do:<br>
					<textarea name="add-todo-msg" class="add-todo-msg" rows="2" cols="50"></textarea></label></p>
					
				<p><input type="submit" name="submit" value="Add To-Do Item"></p>
			
			</form>
			
		</div>
<?php endif; ?>
		
<?php if ( $can_mark ): ?>
		<div id="mark-todo-modal">
			
			<form action="" method="post" class="mark-todo-form">
				
				<p><label>Optional Message:<br>
					<textarea name="mark-todo-msg" class="mark-todo-msg" rows="10" cols="50"></textarea></label></p>
				
				<input type="hidden" class="mark-todo-id" value="">
				<input type="hidden" class="mark-todo-postid" value="">
				
				<p><input type="submit" name="submit" value="Check To-Do"></p>
			
			</form>
			
		</div>
<?php endif; ?>
	
	</div>

<?php wp_footer() ?>

</body>	
</html>