<!-- Create a header in the default WordPress 'wrap' container -->
<div class="wrap">

    <img src="<?php echo esc_url( SocialWebSuite::get_plugin_url() . 'images/sws-logo.png' ) ?>"
         style="height:80px" alt="Social Web Suite Logo"/>

    <!-- <h1><?php esc_html_e( SocialWebSuite::get_plugin_name(), 'social-web-suite' ) ?></h1> -->

	<?php if ( SocialWebSuite::get_option( 'activated' ) ) : ?>

        <h2 class="nav-tab-wrapper"><?php esc_html_e( 'Plugin is activated', 'social-web-suite' ) ?></h2>

        <p><?php esc_html_e( "You can fine-tune all the plugin's settings and much more using a control panel on our server.", 'social-web-suite' ); ?></p>


        <p>
            <a href="<?php echo esc_url( SocialWebSuite::get_server_url() . 'site/' . $settings->site_id ) ?>"
               class="button-primary sws-button-size" title="Go to server and configure the plugin" target="_blank"> <i
                        class="dashicons dashicons-admin-generic" aria-hidden="true" style="padding-top: 3px;"></i>
				<?php esc_html_e( 'Go to Control Panel', 'social-web-suite' ); ?>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=social-web-suite&action=refresh-connection' ) ); ?>"
               class="button-primary sws-button-size" title="Refresh connection with Social Web Suite">
                <i class="dashicons dashicons-controls-repeat" aria-hidden="true" style="padding-top: 3px;"></i>&nbsp;
				<?php esc_html_e( 'Refresh', 'social-web-suite' ); ?>
            </a>
        </p>

        <p>
            <a href="https://www.youtube.com/channel/UCuOPStwAbLCYCT2KeRrCZ4w" class="button-primary sws-button-size" title="Our video library"
               target="_blank">
                <i class="dashicons dashicons-video-alt3" aria-hidden="true"
                   style="padding-top: 3px;"></i>&nbsp;<?php esc_html_e( 'Video Library', 'social-web-suite' ); ?>
            </a>
            <a href="https://socialwebsuite.com/help/" class="button-primary sws-button-size" title="Help articles"
               target="_blank">
                <i class="dashicons dashicons-testimonial"
                   style="padding-top: 3px;"></i>&nbsp;<?php esc_html_e( 'Help', 'social-web-suite' ); ?></span>
            </a>
        </p>
	<?php else : ?>

    <h2 class="nav-tab-wrapper">
        <a onclick="jQuery('#settings-tab-activate').css('display', 'block');jQuery('#settings-tab-videos').css('display', 'none');jQuery(this).addClass('nav-tab-active');jQuery(this).next().removeClass('nav-tab-active');"
           href="#" class="nav-tab nav-tab-active"><?php esc_html_e( 'Activate', 'social-web-suite' ); ?></a>
        <a onclick="jQuery('#settings-tab-videos').css('display', 'block');jQuery('#settings-tab-activate').css('display', 'none');jQuery(this).addClass('nav-tab-active');jQuery(this).prev().removeClass('nav-tab-active');""
        href="#" class="nav-tab nav-tab"><?php esc_html_e( 'Videos', 'social-web-suite' ); ?></a>
    </h2>
    <div id="settings-tab-activate" style="display:block; padding:15px;">
        <p>
			<?php esc_html_e( 'Please connect your site with our server so you can enjoy using the plugin.', 'social-web-suite' ); ?>
            <br/>

			<?php esc_html_e( 'Simply click on the button " Connect to Social Web Suite" and follow our 5 minutes setup wizard.', 'social-web-suite' ); ?>
        </p>

        <form method="post" action="<?php echo esc_url( SocialWebSuite::get_server_url() ) ?>wp/connect"
              target="_blank">
			<?php $this->generate_secrets() ?>
            <input type="submit" class="button button-primary sws-button-connect"
                   value="<?php _e( 'Connect to Social Web Suite', 'social-web-suite' ); ?>">
            <a href="https://www.socialwebsuite.com/help" target="_blank" class="button"
               title="Learn how to use the plugin" style="margin-left:10px">
				<?php esc_html_e( 'Read the Manual', 'social-web-suite' ); ?>
            </a>
            <a href="https://www.youtube.com/channel/UCuOPStwAbLCYCT2KeRrCZ4w" target="_blank" class="button" title="Watch video tutorial"
               style="margin-left:10px">
				<?php esc_html_e( 'Watch How To Video\'s', 'social-web-suite' ); ?>
            </a>
        </form>

		<?php endif; ?>

		<?php $saved_hash = SocialWebSuite::get_option( 'delete-hash' ); ?>
		<?php $action = filter_input( INPUT_GET, 'action' ); ?>
		<?php if ( ! empty( $saved_hash ) && 'delete-data' === $action ): ?>
            <h2><?php esc_html_e( 'Are you sure you want to delete all data related with Social Web Suite?', 'social-web-suite' ); ?></h2>
            <p style="color: red;"><?php esc_html_e( 'This action will delete all related schedules and settings for your site from the Social Web Suite dashboard and it cannot be undone!', 'social-web-suite' ); ?></p>
            <span>
           <a href="<?php echo esc_url( admin_url( 'admin.php?page=social-web-suite&action=delete-data&hash=' . $saved_hash ) ); ?>"
              class="sws-cancel-delete button-primary button-cancel"
              title="Download all Social Web Suite plugin data in one archive">
		    <?php esc_html_e( 'Delete', 'social-web-suite' ); ?>

        </a>
       </span>
            <span>
           <a href="<?php echo esc_url( admin_url( 'admin.php?page=social-web-suite' ) ); ?>"
              class="sws-delete-all-data button-primary button-delete"
              title="Download all Social Web Suite plugin data in one archive">
		    <?php esc_html_e( 'Cancel', 'social-web-suite' ); ?>

        </a>
    </span>

		<?php else: ?>
            <p><em>
					<?php esc_html_e( 'I authorize Social Web Suite to read/process posts pages and meta information associated with them like: tags, categories and images. Social Web Suite will not collect any personal or any other infomation than the info that we need to process in order to share your posts and pages.', 'social-web-suite' ); ?>
                </em>
            </p>

            <!-- GDPR actions -->
            <h2><?php esc_html_e( 'GDPR Actions', 'social-web-suite' ); ?></h2>
            <span>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=social-web-suite&action=download-export-data' ) ); ?>"
           class="button-secondary" title="Download all Social Web Suite plugin data in one archive">
            <?php esc_html_e( 'Export and Download Social Web Suite plugin data', 'social-web-suite' ); ?>
        </a>
    </span>
            <span>

        <a href="<?php echo esc_url( admin_url( 'admin.php?page=social-web-suite&action=delete-data' ) ); ?>"
           class="sws-delete-all-data button-secondary button-delete"
           title="Delete all related Social Web Suite plugin data from this site">
            <?php esc_html_e( 'Delete Social Web Suite plugin data', 'social-web-suite' ); ?>
        </a>
    </span>
            <!-- //GDPR actions -->

		<?php endif; ?>

    </div>
    <div id="settings-tab-videos" style="display:none; padding:15px;text-align: left;">
        <iframe src="https://player.vimeo.com/video/360845479?title=0&byline=0&portrait=0" width="640" height="360"
                frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
        <p><a href="https://vimeo.com/360845479">Connect your WordPress site and set up your social sharing in less than
                5 minutes with Social Web Suite</a> from <a href="https://vimeo.com/socialwebsuite">Social Web Suite</a>
            on <a href="https://vimeo.com">Vimeo</a>.</p>

        <iframe src="https://player.vimeo.com/video/266525895?title=0&byline=0&portrait=0" width="640" height="360"
                frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
        <p><a href="https://vimeo.com/266525895">Manage all your social strategy with Social Web Suite&#039;s social
                media marketing calendar</a> from <a href="https://vimeo.com/socialwebsuite">Social Web Suite</a> on <a
                    href="https://vimeo.com">Vimeo</a>.</p>

        <iframe src="https://player.vimeo.com/video/359599433?title=0&byline=0&portrait=0" width="640" height="360"
                frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
        <p><a href="https://vimeo.com/359599433">GET ORGANIZED WITH OUR CONTENT CATEGORIES</a> from <a
                    href="https://vimeo.com/socialwebsuite">Social Web Suite</a> on <a
                    href="https://vimeo.com">Vimeo</a>.</p>
    </div>

</div><!-- /.wrap -->
