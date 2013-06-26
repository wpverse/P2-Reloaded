<?php if ( have_posts() ): ?>
				
				<!-- the posts -->
<?php while ( have_posts() ): the_post(); ?>

				<article id="post-<?php the_id() ?>" <?php post_class() ?>>
					
					<div class="avatar">
						<?php echo get_avatar(get_the_author_meta('user_email'), 36) ?>
					</div>
					
					<hgroup>
						<?php if ( get_option('p2_show_titles') ): ?><h2><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h2><?php endif; ?>
						<h4 class="meta">Posted by <?php the_author() ?> on <?php p2_post_date() ?> | <a href="<?php comments_link() ?>"><?php comments_number() ?></a></h4>
						<p class="post-actions">
							<a href="<?php the_permalink() ?>">Permalink</a>
							<?php post_reply_link(array(
								'add_below' => 'comments-list',
								'reply_text' => 'Reply',
								'before' => ' | '
							)) ?>
							<?php if ( current_user_can('edit_post') ): ?> | <a href="<?php echo admin_url('post.php?post='. get_the_ID() .'&action=edit') ?>" class="p2-edit-post">Edit</a><?php endif; ?>
						</p>
						<br class="clear">
						<p class="post-tags">
							<?php the_tags() ?>
						</p>
					</hgroup>
					
					<br class="clear">
					
					<section class="post-content">
					
						<?php the_content() ?>
						
						<br class="clear">
						
						<?php p2_attachments() ?>
						
					</section>
					
					<!-- comments -->
					<?php
						// Force WP to load comments on the frontpage
						if ( ! is_single() && is_front_page() )
						{
							global $withcomments;
							$withcomments = true;
						}
						comments_template();
					?>
					<!-- /comments -->
					
				</article>

<?php endwhile; ?>
				<!-- /the posts -->

<?php else: ?>

	<p>No posts yet.</p>

<?php endif; ?>