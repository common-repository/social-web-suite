
<?php if(!isset($sharing_active) || ($sharing_active === true && $hide_publish_notice === true)) return; ?>

<?php $suffix = ($sidebar == true)? '' : '-metabox'; ?>

<div id="sws-notify-sharing<?php echo $suffix ?>" class="misc-pub-section"
     style="background:#fee;<?php echo ( ! $manual_paused && 'skip' === $manual_settting ) ? 'display:none' : '' ?>">
	<p>
		<a id="sws-logo-small-link<?php echo $suffix ?>" href="<?php echo esc_url( SocialWebSuite::get_server_url() ) ?>" target="_blank" title="Social Web Suite">
			<img id="sws-logo-small<?php echo $suffix ?>" src="<?php echo esc_url( SocialWebSuite::get_plugin_url() . 'images/sws-logo.png' ) ?>" alt="Social Web Suite Logo"  width="50" height="28"/>
		</a>

        <span>
		<?php if($sharing_active == true): ?>
			<?php if($hide_publish_notice === false): ?>
				<?php echo esc_html__( 'This content will be shared automatically on', 'social-web-suite' ); ?> <?php echo ('Publish' === $action) ? 'Publish' : 'Update' ?>.
			<?php endif; ?>
		<?php else: ?>
			<?php echo esc_html__( 'The sharing for your Social Web Suite account is paused so nothing will be shared', 'social-web-suite' ); ?>.
		<?php endif; ?>
    </span>

	</p>
</div>