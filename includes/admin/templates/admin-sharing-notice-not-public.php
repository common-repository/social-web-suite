
<?php if(!isset($sharing_active) || ($sharing_active === true && $hide_publish_notice === true)) return; ?>

<?php $suffix = ($sidebar == true)? '' : '-metabox'; ?>

<div id="sws-notify-sharing<?php echo $suffix ?>" class="misc-pub-section"
     style="background:#fee;<?php echo ( ! $manual_paused && 'skip' === $manual_settting ) ? 'display:none' : '' ?>">
	<p>
		<a id="sws-logo-small-link<?php echo $suffix ?>" href="<?php echo esc_url( SocialWebSuite::get_server_url() ) ?>" target="_blank" title="Social Web Suite">
			<img id="sws-logo-small<?php echo $suffix ?>" src="<?php echo esc_url( SocialWebSuite::get_plugin_url() . 'images/sws-logo.png' ) ?>" alt="Social Web Suite Logo"  width="50" height="28"/>
		</a>

        <span>
			<?php echo esc_html__( 'Posts that are not public are not going to be shared.', 'social-web-suite' ); ?>.
	    </span>

	</p>
</div>