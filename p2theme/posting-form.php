
<?php if ( p2_can_post() ): ?>
				<!-- add post form -->
				<section id="posting-form-container">
					
					<div class="avatar">
						<a href="#"><img src="http://1.gravatar.com/avatar/d10ca8d11301c2f4993ac2279ce4b930?s=48&amp;d=http%3A%2F%2F1.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D48&amp;r=G" width="48" height="48"></a>
					</div>
					
					<div id="posting-form-fields">
						
						<form action="<?php echo bloginfo('siteurl') ?>" method="post" id="post-form">
						
							<p>
								<label id="main-label">Hi, <?php if ( is_user_logged_in() ) echo $GLOBALS['current_user']->display_name; else echo 'Guest'; ?>. Whatcha up to?
								<textarea name="posttext" id="post-text" tabindex="1" rows="3" cols="100" placeholder="Type @user to send an email notification. You may also add a title by adding title=&quot;Your Title&quot;"></textarea></label>
							</p>
							
							<?php if ( get_option('p2_enable_tags') ): ?><p>
								<input type="text" name="posttags" id="post-tags" tabindex="2" placeholder="Tag it" size="80">
							</p><?php endif; ?>
						
						</form>
						
<?php if ( get_option('p2_attachments') && is_user_logged_in() ): ?>
						
						<div id="p2-attachments-container">
							<p id="p2-attachments-selector"><strong>Attachments:</strong></p>
							<ul id="p2-attachments"></ul>
							
							<form action="<?php bloginfo('template_url') ?>/upload/" method="post" enctype="multipart/form-data">
								<input type="hidden" name="_wp_nonce" value="<?php echo wp_create_nonce('p2-upload-attachment') ?>">
								<p id="p2-attachments-input-container">
									<input type="file" name="attachment[]" id="p2-attachments-input" multiple>
								</p>
								<p id="p2-attachments-indicator" class="indicator"><img src="<?php bloginfo('template_url') ?>/images/indicator.gif" width="12" height="12"> Uploading attachments...</p>
							</form>
						</div>
						
<?php endif; ?>
						
						<p class="p2-submit">
							<input type="button" name="postit" id="post-it" tabindex="2" value="Post it">
							<span id="posting-indicator" class="indicator"><img src="<?php bloginfo('template_url') ?>/images/indicator.gif" width="12" height="12" align="absmiddle"> Please wait...</span>
						</p>
						
					</div>
					
					<br class="clear">
				
				</section>
				<!-- /add post form -->
<?php endif; ?>
				