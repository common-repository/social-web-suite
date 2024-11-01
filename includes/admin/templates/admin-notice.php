<!-- Show Admin Notice to connect site  -->
<div class="notice is-dismissible notice-success sws-notice-fix" style="display: block;">
    <div class="sws-notice-logo sws-column">
        <a href="<?php echo esc_url( SocialWebSuite::get_server_url() ) ?>" target="_blank" title="Social Web Suite">
            <img src="<?php echo esc_url( SocialWebSuite::get_plugin_url() . 'images/sws-logo.png' ) ?>"alt="Social Web Suite Logo"/>
        </a>
    </div>
    <div class="sws-notice-message sws-column">
        <p>
			<?php
			esc_html_e( 'Connect this site to your Social Web Suite dashboard so you can auto-post, schedule, automate, manage, publish, customize, share and promote your posts to all your social network accounts, engage with your audience and measure the success of your social media campaigns. All this from one dashboard only.', 'social-web-suite' );
			?>
        </p>
    </div>
    <div class="sws-notice-cta sws-column">
        <!--<a class="notice-dismiss" data-target=".sws-connect-notice" href="#" title="Dismiss this notice.">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </a>-->
        <form method="post" action="<?php echo esc_url( SocialWebSuite::get_server_url() ) ?>wp/connect" target="_blank">
			<?php $this->generate_secrets() ?>
            <input style="margin-top:10px;margin-bottom:20px;" type="submit" class="button button-primary sws-button-connect" value="<?php esc_html_e( 'Connect to Social Web Suite', 'social-web-suite' ); ?>">
        </form>
    </div>
</div>
