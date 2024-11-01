<!-- Create a header in the default WordPress 'wrap' container -->
<?php
$current_user = wp_get_current_user();
	?>
	<script type="text/javascript">
	 jQuery(document).ready(function($){
	
	$('.sws-trigger').click(function(e){
		e.preventDefault();
		$(this).parent().toggleClass('sws-open');
		
	});
	
	});
	</script>
<div class="wrap sws-optin-wrap">
	<div id="sws_connect">
		<div class="sws-visual">
            <img src="<?php echo esc_url( SocialWebSuite::get_plugin_url() . 'images/sws-logo.png' ) ?>" alt="Social Web Suite Logo"/>

		</div>
		<div class="sws-content">
			<p>
	<?php
				/* translators: %s is replaced with "string" */
				echo sprintf( esc_html__( 'Hey %s,', 'social-web-suite' ), esc_attr( $current_user->display_name ) );
				?>
				<br><?php esc_html_e( 'Please help us improve', 'social-web-suite' ); ?>
				<b><?php esc_html_e( SocialWebSuite::get_plugin_name() ); ?></b>!
				<?php  esc_html_e( 'If you opt-in, some data about your usage of', 'social-web-suite' ); ?>
				<b><?php  esc_html_e( SocialWebSuite::get_plugin_name() , 'social-web-suite' ); ?></b>
				<?php  esc_html_e( 'will be sent to', 'social-web-suite' ); ?>
				<a href="https://app.socialwebsuite.com/" target="_blank"> <?php  esc_html_e( 'Social Web Suite ', 'social-web-suite' ); ?></a>.
				<?php  esc_html_e( 'If you skip this, that\'s okay!', 'social-web-suite' ); ?>
				<b><?php esc_html_e( SocialWebSuite::get_plugin_name() , 'social-web-suite' ); ?></b>
				<?php  esc_html_e( 'will still work just fine.', 'social-web-suite' ); ?>
				
			</p>
		</div>
		<div class="sws-actions">
		
			<a href="<?php echo esc_attr( wp_nonce_url( admin_url( 'admin.php?page=social-web-suite&sws_action=sws-skip-optin' ), 'sws-skip-optin' ) ) ?>" class="button button-secondary" tabindex="2">
				<?php esc_html_e( 'Skip', 'social-web-suite' ); ?>
			</a>

			<form action="<?php echo esc_attr( admin_url( 'admin.php?page=social-web-suite' ) ); ?>" method="POST">
				<input type="hidden" name="sws_action" value="sws-activate-optin">
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'sws-activate-optin' ) ) ?>">
				<input type="hidden" name="_wp_http_referer" value="<?php  echo esc_attr( admin_url( 'admin.php?page=social-web-suite' ) ); ?>">
				<button class="button button-primary sws-button-connect" tabindex="1" type="submit">
		<?php esc_html_e( 'Allow &amp; Continue', 'social-web-suite' ); ?>
				</button>
			</form>
		</div>
		<div class="sws-permissions">
			<a class="sws-trigger" href="#">
				<?php esc_html_e( 'What permissions are being granted?', 'social-web-suite' ); ?>
			</a>
			<ul>
				<li id="sws-permission-profile" class="sws-permission sws-profile">
					<i class="dashicons dashicons-admin-users">
					</i>

					<div>
						<span>
		<?php esc_html_e( 'Your Profile Overview', 'social-web-suite' ); ?>
						</span>

						<p>
		<?php esc_html_e( 'Name and email address', 'social-web-suite' ); ?>
						</p>
					</div>
				</li>
				<li id="sws-permission-site" class="sws-permission sws-site">
					<i class="dashicons dashicons-admin-settings">
					</i>

					<div>
						<span>
		<?php esc_html_e( 'Your Site Overview', 'social-web-suite' ); ?>
						</span>

						<p>
		<?php esc_html_e( 'Site URL, WP version, PHP info, plugins &amp; themes', 'social-web-suite' ); ?>
						</p>
					</div>
				</li>
				<li id="sws-permission-events" class="sws-permission sws-events">
					<i class="dashicons dashicons-admin-plugins">
					</i>

					<div>
						<span>
		<?php esc_html_e( 'Current Plugin Events', 'social-web-suite' ); ?>
						</span>

						<p>
		<?php esc_html_e( 'Activation, deactivation and uninstall', 'social-web-suite' ); ?>
						</p>
					</div>
				</li>
			</ul>
		</div>

		<div class="sws-terms">
			<a href="https://socialwebsuite.com/privacy-policy/" target="_blank">
				<?php esc_html_e( 'Privacy Policy', 'social-web-suite' ); ?>
			</a>
			&nbsp;&nbsp;-&nbsp;&nbsp;
			<a href="https://socialwebsuite.com/terms-of-service/" target="_blank">
				<?php esc_html_e( 'Terms of Service', 'social-web-suite' ); ?>
			</a>
		</div>
	</div>

</div>
