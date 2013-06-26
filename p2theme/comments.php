					<section class="post-comments">
					
						<ul class="comments" id="comments-list-<?php the_ID() ?>">
							<?php
								if ( have_comments() )
								wp_list_comments(array(
									'avatar_size' => 36,
									'callback' => 'p2_display_comments'
								))
							?>
						</ul>
						
						<?php if ( is_single() && ! isset($GLOBALS['norespond']) ) p2_comment_form() ?>
					
					</section>