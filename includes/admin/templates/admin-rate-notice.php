<!-- Show Admin Notice to connect site  -->
<!--<div class="sws-notice sws-rate-notice">-->
<div class="notice is-dismissible notice-info sws-notice-fix">
	<div class="sws-notice-logo sws-column">
		<a href="<?php echo esc_url( SocialWebSuite::get_server_url() ) ?>" target="_blank" title="Social Web Suite">
			<img src="<?php echo esc_url( SocialWebSuite::get_plugin_url() . 'images/sws-logo.png' ) ?>"alt="Social Web Suite Logo"/>
		</a>
	</div>
	<div class="sws-notice-message sws-column">
		<p>
			<?php
			esc_html_e( 'Hey, we noticed you have used Social Web Suite for some time - that\'s awesome! Could you please do us a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation', 'social-web-suite' );
			?>
		</p>
	</div>
	<div class="sws-notice-cta sws-column">
		<!--<a class="notice-dismiss sws-rate-notice" data-rate-action="not-enough" data-target=".sws-rate-notice" href="#" title="Dismiss this notice.">
			<span class="screen-reader-text">Dismiss this notice.</span>
		</a>-->
		<ul class="msg-in-notice-list" data-nonce="<?php echo wp_create_nonce( 'sws_rate_action_nonce' ) ?>">
			<li>
                <a class="sws-data-rate-action" data-rate-action="do-rate"
			       href="https://wordpress.org/support/view/plugin-reviews/social-web-suite?rate=5#postform" target="_blank">
					<strong><?php echo __( 'Ok, you deserve it', 'social-web-suite' ) ?></strong>
				</a>
			</li>
            <li>|</li>
			<li><a class="sws-data-rate-action" data-rate-action="done-rating" href="#"><?php echo __( 'I already did', 'social-web-suite' ) ?></a></li>
            <li>|</li>
			<li><a class="sws-data-rate-action" data-rate-action="not-enough" href="#"><?php echo __( 'No, not good enough', 'social-web-suite') ?></a></li>
		</ul>
	</div>
</div>
