			
			<!-- sidebar -->
			<aside id="sidebar">
			
				<ul>
				
<?php if ( ! dynamic_sidebar('right-sidebar') ): ?>
				
					<li>
						<?php get_search_form() ?>
					</li>
					
					<li>
						<?php get_calendar() ?>
					</li>
					
					<li>
						<h2>Tags</h2>
						<?php wp_tag_cloud() ?>
					</li>
					
					<li>
						<h2>Recent Comments</h2>
						<?php p2_recent_comments() ?>
					</li>
					
					<li>
						<?php wp_list_bookmarks() ?>
					</li>
				
<?php endif; ?>
				
				</ul>
			
			</aside>
			<!-- /sidebar -->
			