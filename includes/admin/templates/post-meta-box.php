<?php
$meta_manual = get_post_meta( $post->ID, 'sws_meta_manual', true );
if ( empty( $meta_manual ) ) {
	$meta_manual = SWS_DEFAULT_META_MANUAL;
}
$show_misc     = get_post_meta( $post->ID, 'sws_show_misc_options', true );
$meta_send_now = get_post_meta( $post->ID, 'sws_meta_send_now', true );
$meta_repeat   = get_post_meta( $post->ID, 'sws_meta_repeat', true );

$meta_schedule_date     = get_post_meta( $post->ID, 'sws_meta_schedule_date', true );
$meta_schedule_calendar = get_post_meta( $post->ID, 'sws_meta_schedule_calendar', true );
$meta_schedule_hours    = get_post_meta( $post->ID, 'sws_meta_schedule_hours', true );
$meta_schedule_mins     = get_post_meta( $post->ID, 'sws_meta_schedule_mins', true );
$meta_schedule_ampm     = get_post_meta( $post->ID, 'sws_meta_schedule_ampm', true );

if ( empty( $meta_schedule_hours ) ) {
	$meta_schedule_hours = 12;
}
$startdate = get_post_meta( $post->ID, 'sws_meta_startdate_date', true );
if ( ! empty( $startdate ) ) {
	$meta_startdate_date     = date( 'Y-m-d', $startdate );
	$meta_startdate_calendar = date( 'm/d/Y', $startdate );
	$meta_startdate_hours    = date( 'h', $startdate );
	$meta_startdate_mins     = date( 'i', $startdate );
	$meta_startdate_ampm     = date( 'a', $startdate );
} else {
	$meta_startdate_date     = '';
	$meta_startdate_calendar = '';
	$meta_startdate_hours    = 12;
	$meta_startdate_mins     = '';
	$meta_startdate_ampm     = '';
}
$enddate = get_post_meta( $post->ID, 'sws_meta_enddate_date', true );
if ( ! empty( $enddate ) ) {

	$meta_enddate_date     = date( 'Y-m-d', $enddate );
	$meta_enddate_calendar = date( 'm/d/Y', $enddate );
	$meta_enddate_hours    = date( 'h', $enddate );
	$meta_enddate_mins     = date( 'i', $enddate );
	$meta_enddate_ampm     = date( 'a', $enddate );
} else {
	$meta_enddate_date     = '';
	$meta_enddate_calendar = '';
	$meta_enddate_hours    = 12;
	$meta_enddate_mins     = '';
	$meta_enddate_ampm     = '';
}

$meta_include_image = get_post_meta( $post->ID, 'sws_meta_include_image', true );
if ( empty( $meta_include_image ) ) {
	$meta_include_image = 'default';
}
$meta_format = get_post_meta( $post->ID, 'sws_meta_format', true );
if ( ! $meta_format || empty( $meta_format ) ) {
	//$meta_format = SocialWebSuite::get_option( 'post_format' );
	$meta_format = '';

}
$meta_format_helper      = SocialWebSuite::get_option( 'post_format' );
$meta_custom_message     = trim( get_post_meta( $post->ID, 'sws_meta_custom_message', true ) );
$meta_use_cutom_messages = (int) get_post_meta( $post->ID, 'sws_meta_use_cutom_messages', true );

$sws_share_error_message = SocialWebSuite::get_option( 'share_error_message' );

if ( ! empty( $sws_share_error_message ) ) {
	SocialWebSuite::delete_option( 'share_error_message' );
}

$sws_subscription_expired         = SocialWebSuite::get_option( 'subscription_expired' );
$sws_subscription_expired_message = SocialWebSuite::get_option( 'subscription_expired_message' );


$meta_custom_message_variations_data = json_decode( get_post_meta( $post->ID, 'sws_meta_custom_message_variations_data', true ) );


$meta_custom_message_variations = isset( $meta_custom_message_variations_data->variations ) ? $meta_custom_message_variations_data->variations : array();
$meta_variation_last_key        = isset( $meta_custom_message_variations_data->last_shared_key ) ? $meta_custom_message_variations_data->last_shared_key : "-1";
if ( ! empty( $meta_custom_message ) ) {
	$meta_custom_message_variations[] = (object) array( 'message' => $meta_custom_message );
	$meta_use_cutom_messages          = 1;
}

$meta_custom_message = '';

$meta_use_hashtags = get_post_meta( $post->ID, 'sws_meta_use_hashtags', true );
if ( empty( $meta_use_hashtags ) ) {
	$meta_use_hashtags = 'default';
}

$meta_hashtags = get_post_meta( $post->ID, 'sws_meta_hashtags', true );

wp_nonce_field( 'save-post-meta', 'sws_post_nonce' );

$meta_tags = array(
	//'sitename',
	'title',
	'url',
	'hashtags',
	'excerpt',
	//'category',
	//'date',
	//'author',
);

$show_schedule_error_msg = false;

if ( 'custom' === $meta_manual && 'schedule' === $meta_send_now ) {
	$next_year_date = strtotime( '+1 year' );
	$scheduled_date = strtotime( $meta_schedule_date );
	if ( $next_year_date < $scheduled_date ) {
		$show_schedule_error_msg = true;
		$meta_schedule_calendar  = date( 'm/d/Y', $scheduled_date );
	}
}
$social_accounts               = SocialWebSuite::get_social_accounts();
$social_accounts_check_default = SocialWebSuite::get_option( 'social_profiles_check', 'unchecked' );
$meta_social_accounts_exclude  = get_post_meta( $post->ID, 'sws_meta_social_accounts_exclude', true );
$meta_social_accounts_exclude  = explode( ',', $meta_social_accounts_exclude );
$url                           = SocialWebSuite::get_plugin_url();
?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        if (typeof sws_script_active === 'undefined') {
            $('#sws-blocked').show();
        }
    });
</script>
<script type="text/javascript">
    var sws_dialog_text = {
        remove_title: "<?php esc_html_e( "Remove the custom message?", 'social-web-suite' );  ?>",
        remove_message: "<?php esc_html_e( "This item will be permanently deleted and cannot be recovered. Are you sure?", 'social-web-suite' );  ?>",
        add_limit_message: "<?php esc_html_e( "You can have maximum 12 message variations!", 'social-web-suite' );  ?>",
    };
    var sws_custom_message_info = {
        description: "<?php  esc_html_e( 'Note: This custom message will be shared on social media instead of the post title.', 'social-web-suite' ) ?>",
        type_description: "<?php  esc_html_e( 'The maximum length of the message that you can send is 200 characters.', 'social-web-suite' ) ?>",
    };
    var sws_custom_message_counter_text = {
        label: "<?php  esc_html_e( 'Times to share', 'social-web-suite' ) ?>",
        description: "<?php  esc_html_e( 'Note: Set to zero (0) to share this message unlimited times (depending on your site settings for the maximum number of times the same post can be shared).', 'social-web-suite' ) ?>",
    };

</script>
<style type="text/css">
    #sws_miscellaneous_options label {
        font-weight: bold;
    }

    input#sws_meta_format {
        min-width: 424px;
    }

    .sws_meta_tag {
        text-decoration: none;
    }

    .sws_meta_tag:hover {
        text-decoration: underline;
    }

    .sws_meta_tag:focus, .sws_meta_tag:active {
        box-shadow: none;
    }

    .checkbox-inline {
        margin-top: 5px;
        margin-right: 10px;
        display: inline-block;
    }

    .ui-icon {
        width: 18px !important;
        height: 18px !important;
    }
</style>
<div id="sws_meta">
    <div id="sws_meta_sharing_notice" style="display:none">
		<?php SocialWebSuite::show_admin_sharing_notice( $post, false ); ?>
    </div>
	<?php if ( $sws_subscription_expired == true ): ?>
        <p id="sws_subscription_expired" style="color:red;">
			<?php echo esc_attr( $sws_subscription_expired_message ); ?>
        </p>
	<?php endif; ?>
	<?php if ( ! empty( $sws_share_error_message ) ): ?>
        <p id="sws_share_error_message" style="color:red;">
			<?php echo esc_attr( $sws_share_error_message ); ?>
        </p>
	<?php endif; ?>
    <p id="sws-blocked" style="display:none;color:red;">
		<?php
		echo sprintf(
			wp_kses(
			/* translators: %s is replaced with "string" */
				__( 'It seems that your AdBlock is activated and you can\'t adjust the Social Web Suite settings. Please disable the AdBlock for all the pages for this domain. If you need help with this <a href="%s" target="_blank">contact us</a>.', 'social-web-suite' ),
				array(
					'a' => array(
						'href'   => array(),
						'target' => array( '_blank' ),
					),
				)
			),
			esc_url( 'https://socialwebsuite.com/contact-us/' )
		);
		?>
    </p>

    <p style="clear: both;">
        <select style="float:right;margin-top:15px;" id="sws_meta_manual" name="sws_meta_manual">
            <option value="default"<?php selected( 'default', $meta_manual ) ?>><?php esc_html_e( 'Use the default settings', 'social-web-suite' ) ?></option>
            <option value="skip" <?php selected( 'skip', $meta_manual ) ?>><?php esc_html_e( "Don't share this", 'social-web-suite' ) ?></option>
            <option value="custom" <?php selected( 'custom', $meta_manual ) ?>><?php esc_html_e( 'Share it using the following settings', 'social-web-suite' ) ?></option>
            <option value="notevergreen" <?php selected( 'notevergreen', $meta_manual ) ?>><?php esc_html_e( 'Share it only between specific dates', 'social-web-suite' ) ?></option>
        </select>
        <img src="<?php echo $url . '/images/sws-logo-small.png'; ?>" alt="sws-logo"
             style="width:45px;float:left;margin-right:15px;margin-top:6px;margin-bottom: 30px;">
    <h1 style="font-size:20px;"><?php esc_html_e( 'Customize your settings', 'social-web-suite' ) ?></h1>
    <div class="description"><?php esc_html_e( 'Any changes to the following settings will override the general site settings for this post.', 'social-web-suite' ) ?></div>
    <div class="clearfix"></div>
    </p>
    <div id="sws_meta_notevergreen_options" <?php echo 'notevergreen' === $meta_manual ? '' : ' style="display:none"' ?>>
        <div id="sws_meta_date_range">

            <p>
                <input type="hidden" id="sws_meta_startdate_date" name="sws_meta_startdate_date"
                       value="<?php echo esc_attr( $meta_startdate_date ) ?>">

                <label for="sws_meta_startdate_date"><?php esc_html_e( 'Choose Start Date', 'social-web-suite' ) ?></label>:
                <input type="text" id="sws_meta_startdate_calendar" name="sws_meta_startdate_calendar"
                       value="<?php echo esc_attr( $meta_startdate_calendar ) ?>" class="sws-date-control"/>
                <span id="sws_meta_startdate_calendar_btn" class="dashicons dashicons-calendar-alt"
                      aria-hidden="true"></span>
                <span id="sws_meta_startdate_reset" class="dashicons dashicons-no-alt" aria-hidden="true"
                      title="reset start date and time"></span>

            </p>
            <p>
                <label><?php esc_html_e( 'Choose Start Time', 'social-web-suite' ) ?></label>:

                <select name="sws_meta_startdate_hours">
					<?php for ( $i = 1; $i < 13; $i ++ ) : ?>
                        <option value="<?php echo (int) $i ?>"<?php selected( $i, $meta_startdate_hours ) ?>>
							<?php echo esc_attr( str_pad( $i, 2, '0', STR_PAD_LEFT ) ); ?>
                        </option>
					<?php endfor; ?>
                </select>

                <select name="sws_meta_startdate_mins">
					<?php for ( $i = '00'; $i < 60; $i += 15 ) : ?>
                        <option value="<?php echo (int) $i ?>"<?php selected( $i, $meta_startdate_mins ) ?>>
							<?php echo (int) $i ?>
                        </option>
					<?php endfor; ?>
                </select>

                <select name="sws_meta_startdate_ampm">
                    <option value="am"<?php selected( 'am', $meta_startdate_ampm ) ?>>AM</option>
                    <option value="pm"<?php selected( 'pm', $meta_startdate_ampm ) ?>>PM</option>
                </select>

            </p>
            <p>
                <input type="hidden" id="sws_meta_enddate_date" name="sws_meta_enddate_date"
                       value="<?php echo esc_attr( $meta_enddate_date ) ?>">

                <label for="sws_meta_enddate_date"><?php esc_html_e( 'Choose End Date', 'social-web-suite' ) ?></label>:
                <input type="text" id="sws_meta_enddate_calendar" name="sws_meta_enddate_calendar"
                       value="<?php echo esc_attr( $meta_enddate_calendar ) ?>" class="sws-date-control"/>
                <span id="sws_meta_enddate_calendar_btn" class="dashicons dashicons-calendar-alt"
                      aria-hidden="true"></span>
                <span id="sws_meta_enddate_reset" class="dashicons dashicons-no-alt" aria-hidden="true"
                      title="reset end date and time"></span>
            </p>
            <p>
                <label><?php esc_html_e( 'Choose End Time', 'social-web-suite' ) ?></label>:

                <select name="sws_meta_enddate_hours">
					<?php for ( $i = 1; $i < 13; $i ++ ) : ?>
                        <option value="<?php echo (int) $i ?>"<?php selected( $i, $meta_enddate_hours ) ?>>
							<?php echo esc_attr( str_pad( $i, 2, '0', STR_PAD_LEFT ) ); ?>
                        </option>
					<?php endfor; ?>
                </select>

                <select name="sws_meta_enddate_mins">
					<?php for ( $i = '00'; $i < 60; $i += 15 ) : ?>
                        <option value="<?php echo (int) $i ?>"<?php selected( $i, $meta_enddate_mins ) ?>>
							<?php echo (int) $i ?>
                        </option>
					<?php endfor; ?>
                </select>

                <select name="sws_meta_enddate_ampm">
                    <option value="am"<?php selected( 'am', $meta_enddate_ampm ) ?>>AM</option>
                    <option value="pm"<?php selected( 'pm', $meta_enddate_ampm ) ?>>PM</option>
                </select>

            </p>
            <div id="sws_empty_nonevergreen_dates" class="description" style="display:none">
                <p>
					<?php esc_html_e( 'Warning: Both start and end date are empty. Either choose one or both of these, or settings will be switched back to default settings upon post update.', 'social-web-suite' ) ?></p>
            </div>
            <p class="description"><?php esc_html_e( 'IMPORTANT: In order to make sure that start and end time will match your local time please make sure to set correctly your Time Zone in WordPress Dashboard &rarr; Settings &rarr; Time Zone.', 'social-web-suite' ) ?></p>

        </div>
    </div>
    <div id="sws_meta_sharing_options"<?php echo 'custom' === $meta_manual ? '' : ' style="display:none"' ?>>

        <fieldset>
            <legend><?php esc_html_e( 'Schedule share date/time', 'social-web-suite' ) ?></legend>

            <select id="sws_meta_send_now" name="sws_meta_send_now">
                <option
                        value="now" <?php selected( 'now', $meta_send_now ) ?>><?php esc_html_e( 'Share it right away', 'social-web-suite' ) ?></option>
                <option
                        value="schedule"<?php selected( 'schedule', $meta_send_now ) ?>><?php esc_html_e( 'Schedule it to be shared later', 'social-web-suite' ) ?></option>
            </select>

            <div id="sws_meta_schedule"<?php echo ( 'schedule' === $meta_send_now ) ? '' : ' style="display:none"' ?>>

                <p>
                    <input type="hidden" id="sws_meta_schedule_date" name="sws_meta_schedule_date"
                           value="<?php echo esc_attr( $meta_schedule_date ) ?>">

                    <label for="sws_meta_schedule_date"><?php esc_html_e( 'Choose Date', 'social-web-suite' ) ?></label>:
                    <input type="text" id="sws_meta_schedule_calendar" name="sws_meta_schedule_calendar"
                           value="<?php echo esc_attr( $meta_schedule_calendar ) ?>" class="sws-date-control"/>
                    <span id="sws_meta_schedule_calendar_btn" class="dashicons dashicons-calendar-alt"
                          aria-hidden="true"></span>
					<?php if ( $show_schedule_error_msg === true ): ?>
                        <span id="sws_meta_schedule_date_error" style="color:red;">
                            <?php esc_html_e( 'Schedule date can be maximum within 1 year range from current date.', 'social-web-suite' ) ?>
                        </span>
					<?php endif; ?>
                </p>
                <p>
                    <label><?php esc_html_e( 'Choose Time', 'social-web-suite' ) ?></label>:

                    <select name="sws_meta_schedule_hours">
						<?php for ( $i = 1; $i < 13; $i ++ ) : ?>
                            <option value="<?php echo (int) $i ?>"<?php selected( $i, $meta_schedule_hours ) ?>>
								<?php echo esc_attr( str_pad( $i, 2, '0', STR_PAD_LEFT ) ); ?>
                            </option>
						<?php endfor; ?>
                    </select>

                    <select name="sws_meta_schedule_mins">
						<?php for ( $i = '00'; $i < 60; $i += 15 ) : ?>
                            <option value="<?php echo (int) $i ?>"<?php selected( $i, $meta_schedule_mins ) ?>>
								<?php echo (int) $i ?>
                            </option>
						<?php endfor; ?>
                    </select>

                    <select name="sws_meta_schedule_ampm">
                        <option value="am"<?php selected( 'am', $meta_schedule_ampm ) ?>>AM</option>
                        <option value="pm"<?php selected( 'pm', $meta_schedule_ampm ) ?>>PM</option>
                    </select>
                </p>

                <p class="description"><?php esc_html_e( 'IMPORTANT: In order to make sure that scheduled time will match your local time please make sure to set correctly your Time Zone in WordPress Dashboard &rarr; Settings &rarr; Time Zone.', 'social-web-suite' ) ?></p>

            </div>
        </fieldset>


    </div>
	<?php
	/*
    <label for="sws_show_misc_options">
    <input type="checkbox" id="sws_show_misc_options" name="sws_show_misc_options" value="1" class="checkbox" <?php echo $show_misc == '1' ? 'checked=checked' : '' ?>/>
    <?php echo esc_html__( 'Override general site settings', 'social-web-suite' ) ?>

    </label> */
	?>
    <p class="description">

		<?php //esc_html_e( 'Override site settings for this post - Image settings, hashtags, message format or use custom message', 'social-web-suite' ) ?>
    </p>
    <div id="sws_miscellaneous_options" <?php //echo $show_misc === '1' ? '' : ' style="display:none"' ?>>
        <fieldset>
            <div style="width:100%;" class="clearfix sws_meta_social_accounts_exclude_wrapper">
                <!--<img src="<?php /*echo $url . '/images/sws-logo-small.png'; */ ?>" alt="sws-logo" style="width:45px;float:left;margin-right:15px;margin-top:6px;">-->
                <h1 style="font-size:20px;"><?php esc_html_e( 'Select a social profile you want to share this post to:', 'social-web-suite' ) ?></h1>
                <div class="clearfix"></div>
                <br>
				<?php
    
				if ( count( $social_accounts ) > 0 ): ?>
					<?php $twitter_checked = false; ?>
					<?php foreach ( $social_accounts as $social_account ): ?>
						<?php
						$social_account_checked  = '';
						$social_account_disabled = '';
						if ( ! in_array( $social_account->id, $meta_social_accounts_exclude ) ) {
							// &&
							if ( $social_account->service === 'twitter' ) {
								if ( $twitter_checked === false ) {
									if ( $social_accounts_check_default === 'checked' ) {
										$social_account_checked = 'checked="checked"';
									}
									$twitter_checked = true; //allow only one twitter acc to be checked
								} else {
									$social_account_disabled = 'disabled="disabled"';
								}
							} else {
								if ( $social_accounts_check_default === 'checked' ) {
									$social_account_checked = 'checked="checked"';
								}

							}
						} else {
							if ( $social_account->service === 'twitter' ) {
								if ( $twitter_checked === true ) {
									$social_account_disabled = 'disabled="disabled"';
								}

							}
						}

						?>
                        <div class="sws_social_account  sws_social_account_service_<?php echo esc_attr( $social_account->service ); ?>">
                            <label>
                                <span class="sws_social_account_profile_image">
                                    <?php
                                    //$sws_social_profile_image = SocialWebSuite_Helpers::check_image( esc_attr($social_account->profile_image) );
                                    ?>
                                    <img src="<?php echo esc_attr( $social_account->profile_image ); ?>" alt="">
                                      <span class="sws_social_account_service sws_service_<?php echo esc_attr( $social_account->service ); ?>">
                                     <?php echo esc_attr( $social_account->service ); ?>
                                      </span>
                                </span>
                                <span class="sws_social_account_username">
                                    <?php echo esc_attr( $social_account->profile_name ); ?>
                                </span>


                                <div class="checkbox-inline checkbox-info">
                                    <label class="control control--checkbox">
                                        <input class="custom-checkbox sws_social_account_checkbox sws_social_<?php echo esc_attr( $social_account->service ); ?>"
                                               type="checkbox" name="sws_meta_social_accounts_exclude[]"
                                               value="<?php echo esc_attr( $social_account->id ); ?>" <?php echo esc_attr( $social_account_checked ); ?><?php echo esc_attr( $social_account_disabled ); ?>/>
                                        <div class="control__indicator"></div>
                                    </label>
                                </div>
                            </label>
                        </div>
					<?php endforeach; ?>
					<?php if ( $twitter_checked === true ): ?>
                        <div class="sws_warning">
							<?php esc_html_e( "Due to the latest Twitter rules, you are not allowed to post the same or similar message to multiple Twitter profiles. Because of this only one Twitter profile can be selected here.", 'social-web-suite' ); ?>
                        </div>
					<?php endif; ?>
				<?php else: ?>
                    <div>
						<?php esc_html_e( "You don't have any social profiles connected. Please log in to your Social Web Suite account and connect this site with your social profiles.", 'social-web-suite' ); ?>
                    </div>
				<?php endif; ?>
                <div class="sws-clearfix"></div>
            </div>
            <br><br>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?php esc_html_e( 'Share should include the Featured Image (if set)', 'social-web-suite' ) ?></strong>
                </div>
                <div class="panel-body">
                    <!--<select id="sws_meta_include_image" name="sws_meta_include_image">
			<option value="default" <?php /*selected( 'default', $meta_include_image ) */ ?>><?php /*esc_html_e( 'Use the default setting', 'social-web-suite' ) */ ?></option>
			<option value="skip" <?php /*selected( 'skip', $meta_include_image ) */ ?> ><?php /*esc_html_e( "Don't include", 'social-web-suite' ) */ ?></option>
			<option value="include" <?php /*selected( 'include', $meta_include_image ) */ ?>><?php /*esc_html_e( 'Include image', 'social-web-suite' ) */ ?></option>
			</select>-->
                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Default
                            <input onclick="if (this.checked) {jQuery('#sws_meta_include_image_skip,#sws_meta_include_image_include').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_include_image" id="sws_meta_include_image_default"
                                   value="default"
                                   class="custom-checkbox" <?php checked( 'default', $meta_include_image ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>

                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Skip
                            <input onclick="if (this.checked) {jQuery('#sws_meta_include_image_default,#sws_meta_include_image_include').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_include_image" id="sws_meta_include_image_skip"
                                   value="skip" class="custom-checkbox" <?php checked( 'skip', $meta_include_image ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>

                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Include
                            <input onclick="if (this.checked) {jQuery('#sws_meta_include_image_default,#sws_meta_include_image_skip').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_include_image" id="sws_meta_include_image_include"
                                   value="include"
                                   class="custom-checkbox invisible" <?php checked( 'include', $meta_include_image ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>
                </div>
            </div>
            <p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?php esc_html_e( 'Use #hashtags?', 'social-web-suite' ) ?></strong><br>
					<?php echo esc_html__( 'Please note: To use hashtags make sure that the message format (below), if set to override default format from site settings, is configured to include {hashtags}.', 'social-web-suite' ) ?>
                </div>
                <div class="panel-body">
                    <!--<select id="sws_meta_use_hashtags" name="sws_meta_use_hashtags">
					<option value="default" <?php /*selected( 'default', $meta_use_hashtags ) */ ?>><?php /*esc_html_e( 'Use the default settings', 'social-web-suite' ) */ ?></option>
					<option value="none" <?php /*selected( 'none', $meta_use_hashtags ) */ ?> ><?php /*esc_html_e( "Don't use #hashtags", 'social-web-suite' ) */ ?></option>
					<option value="cats" <?php /*selected( 'cats', $meta_use_hashtags ) */ ?> ><?php /*esc_html_e( 'Use categories as #hashtags', 'social-web-suite' ) */ ?></option>
					<option value="tags" <?php /*selected( 'tags', $meta_use_hashtags ) */ ?> ><?php /*esc_html_e( 'Use tags as #hashtags', 'social-web-suite' ) */ ?></option>
					<option value="custom" <?php /*selected( 'custom', $meta_use_hashtags ) */ ?> ><?php /*esc_html_e( 'Use the custom #hashtags', 'social-web-suite' ) */ ?></option>
				</select>-->
                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Use the default settings
                            <input onclick="if (this.checked) {jQuery('#sws_meta_use_hashtags_none,#sws_meta_use_hashtags_cats,#sws_meta_use_hashtags_tags,#sws_meta_use_hashtags_custom').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_use_hashtags" id="sws_meta_use_hashtags_default"
                                   value="default"
                                   class="custom-checkbox invisible" <?php checked( 'default', $meta_use_hashtags ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>

                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Don't use #hashtags
                            <input onclick="if (this.checked) {jQuery('#sws_meta_use_hashtags_default,#sws_meta_use_hashtags_cats,#sws_meta_use_hashtags_tags,#sws_meta_use_hashtags_custom').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_use_hashtags" id="sws_meta_use_hashtags_none"
                                   value="none"
                                   class="custom-checkbox invisible" <?php checked( 'none', $meta_use_hashtags ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>

                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Use categories as #hashtags
                            <input onclick="if (this.checked) {jQuery('#sws_meta_use_hashtags_none,#sws_meta_use_hashtags_default,#sws_meta_use_hashtags_tags,#sws_meta_use_hashtags_custom').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_use_hashtags" id="sws_meta_use_hashtags_cats"
                                   value="cats"
                                   class="custom-checkbox invisible" <?php checked( 'cats', $meta_use_hashtags ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>

                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Use tags as #hashtags
                            <input onclick="if (this.checked) {jQuery('#sws_meta_use_hashtags_none,#sws_meta_use_hashtags_cats,#sws_meta_use_hashtags_default,#sws_meta_use_hashtags_custom').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_use_hashtags" id="sws_meta_use_hashtags_tags"
                                   value="tags"
                                   class="custom-checkbox invisible" <?php checked( 'tags', $meta_use_hashtags ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>

                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox">Use the custom #hashtags
                            <input onclick="if (this.checked) {jQuery('#sws_meta_use_hashtags_none,#sws_meta_use_hashtags_cats,#sws_meta_use_hashtags_tags,#sws_meta_use_hashtags_default').attr('checked', false); } else { return false; }"
                                   type="checkbox" name="sws_meta_use_hashtags" id="sws_meta_use_hashtags_custom"
                                   value="custom"
                                   class="custom-checkbox invisible" <?php checked( 'custom', $meta_use_hashtags ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>
                    <p <?php echo 'custom' === $meta_use_hashtags ? '' : ' style="display:none"' ?>
                            id="sws_meta_hashtags_field">
                        <label for="sws_meta_hashtags"><?php esc_html_e( 'Use these #hashtags', 'social-web-suite' ) ?> </label>:<br/>
                        <input type="text" class="regular-text" id="sws_meta_hashtags" name="sws_meta_hashtags"
                               value="<?php echo esc_attr( $meta_hashtags ) ?>"/><br/>
                    </p>
                </div>
            </div>
            </p>

            <!--<p<?php /*echo 'none' !== $meta_use_hashtags ? '' : ' style="display:none"' */ ?> class="description">
				<?php /*echo esc_html__( 'Warning: To use hashtags make sure that the message format (below), if set to override default format from site settings, is configured to include {hashtags}.', 'social-web-suite' ) */ ?>
			</p>-->

            <p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?php esc_html_e( 'Customize the message format', 'social-web-suite' ) ?> </strong><br>
					<?php esc_html_e( 'Allowed tags: ', 'social-web-suite' ) ?>
					<?php foreach ( $meta_tags as $meta_tag ) : ?>
                        <a href="#" class="sws_meta_tag"
                           data-meta-tag="<?php echo esc_attr( $meta_tag ) ?>">{<?php echo esc_attr( $meta_tag, 'social-web-suite' ) ?>
                            }</a>
					<?php endforeach; ?>
                    <br>
                    <span class="description"><?php esc_html_e( 'Note: Leave blank to use the default format.', 'social-web-suite' ) ?></span>
                </div>
                <div class="panel-body">
                    <textarea style="width:100%;" id="sws_meta_format" type="text" class="regular-text"
                              name="sws_meta_format"
                              placeholder="<?php echo esc_attr( $meta_format_helper ) ?>"><?php echo $meta_format ?></textarea>
                </div>
            </div>
            </p>
            <p>
            </p>
			<?php /* ?>
			<p>
			   <label for="sws_meta_custom_message"><?php esc_html_e( 'Use custom message', 'social-web-suite' );  ?> </label>      <br />
				<textarea name="sws_meta_custom_message" id="sws_meta_custom_message" cols="60" rows="5" maxlength="200"><?php echo esc_attr( $meta_custom_message );  ?></textarea> <br />
				<span id="sws_meta_custom_message_counter"></span><br />
				<span class="description"><?php  esc_html_e( 'Note: This custom message will be shared on social media instead of the post title.
The maximum length of the message that you can send is 200 characters.', 'social-web-suite' ) ?>  <br />
	<?php  esc_html_e( 'The maximum length of the message that you can send is 200 characters', 'social-web-suite' ) ?>
				</span>
			</p>
<?php */ ?>
            <p>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?php esc_html_e( 'Use custom message templates instead of the post title to be shared on social media:', 'social-web-suite' ) ?></strong>
                </div>
                <div class="panel-body">
                    <div class="checkbox-inline checkbox-info">
                        <label class="control control--checkbox"><label
                                    for="sws_meta_use_cutom_messages"><?php esc_html_e( 'Use custom message templates', 'social-web-suite' ) ?> </label>
                            <input type="checkbox" name="sws_meta_use_cutom_messages" id="sws_meta_use_cutom_messages"
                                   value="1"
                                   class="custom-checkbox invisible sws_meta_use_cutom_messages" <?php checked( 1, $meta_use_cutom_messages ) ?>>
                            <div class="control__indicator"></div>
                        </label>
                    </div>
                </div>
            </div>
            </p>
            <div class="sws_meta_custom_message_variations_wrapper" <?php echo ( $meta_use_cutom_messages !== 1 ) ? 'style="display:none;"' : ''; ?>>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <label for="sws_meta_custom_message_variations"><?php esc_html_e( 'Custom message templates', 'social-web-suite' ); ?> </label>
                        <br/>
                    </div>
                    <ul id="sws_meta_custom_message_variations">
						<?php if ( count( $meta_custom_message_variations ) > 0 ): ?>
							<?php foreach ( $meta_custom_message_variations as $key => $meta_custom_message_variation ): ?>
                                <li class="sws_custom_message_variation<?php echo ( $key === $meta_variation_last_key ) ? ' sws_last_shared ' : ''; ?><?php echo ( $key === ( $meta_variation_last_key + 1 ) || ( count( $meta_custom_message_variations ) === ( $meta_variation_last_key + 1 ) && $key === 0 ) ) ? ' sws_next_shared ' : ''; ?>">
                                    <div class="panel-body">
                                        <div>
											<?php if ( $key === $meta_variation_last_key ) : ?>
                                                <span class="sws_last_shared_notice">
                                        <b><?php esc_html_e( 'Note: This message was last one used.', 'social-web-suite' ) ?></b>
                                    </span>
											<?php endif; ?>
											<?php /* if ( $key === ( $meta_variation_last_key + 1 ) || ( count( $meta_custom_message_variations ) === ( $meta_variation_last_key + 1 ) && $key === 0 ) ) : ?>
                              <span class="sws_next_shared_notice">
                                        <b><?php  esc_html_e( 'Note: This message will be next one used.', 'social-web-suite' ) ?></b>
                                    </span>
                          <?php endif; */ ?>
                                        </div>
                                        <div class="sws-column sws-column-50">

                                            <div class="sws-column sws-column-10 sws-sort-handle"><span
                                                        class="ui-icon ui-icon-arrow-2-n-s"></span></div>
                                            <div class="sws-column sws-column-80 sws-custom-message"><textarea
                                                        name="sws_meta_custom_message_variations[]" cols="40" rows="5"
                                                        maxlength="200"><?php echo esc_attr( $meta_custom_message_variation->message ); ?> </textarea>
                                            </div>
                                            <div class="sws-column sws-column-10 sws-add-remove-handles">
                                                <span class="ui-icon ui-icon-plus sws-variation-add"></span> <br> <span
                                                        class="ui-icon ui-icon-minus sws-variation-remove"></span>
                                            </div>
                                            <br/>
                                            <div class="sws_custom_message_info">
                                                <span class="sws_meta_custom_message_counter"></span><br/>
                                                <span class="description"><?php esc_html_e( 'Note: This custom message will be shared on social media instead of the post title.', 'social-web-suite' ) ?>  <br/>
                                      <?php esc_html_e( 'The maximum length of the message that you can send is 200 characters.', 'social-web-suite' ) ?>
                              </span>
                                            </div>
                                        </div>
                                        <div class="sws-column sws-column-50 sws-times-share-container">
                                            <label><?php esc_html_e( 'Times to share', 'social-web-suite' ) ?>: </label>
                                            <input style="margin:5px; width:80px;" type="number"
                                                   name="sws_meta_variations_share_times[]" min="0"
                                                   value="<?php echo ( isset( $meta_custom_message_variation->share_times ) ) ? esc_attr( $meta_custom_message_variation->share_times ) : 0; ?>"/>
                                            <span class="description"><?php esc_html_e( 'Note: Set to zero (0) to share this message unlimited times (depending on your site settings for the maximum number of times the same post can be shared).', 'social-web-suite' ) ?></span>
                                            <span class="sws_share_count"><?php esc_html_e( 'Times shared', 'social-web-suite' ) ?>: <span
                                                        class="inner"><?php echo ( isset( $meta_custom_message_variation->share_count ) ) ? esc_attr( $meta_custom_message_variation->share_count ) : 0; ?></span></span>
                                            <input type="hidden" class="sws_share_count_reset"
                                                   name="sws_meta_variations_share_count_reset[]" value="0">
                                            <a href="#"
                                               class="sws_reset_counter"><?php esc_html_e( 'Reset counter', 'social-web-suite' ) ?></a>
                                        </div>
                                        <div class="sws-clearfix"></div>
                                    </div>
                                </li>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php if ( count( $meta_custom_message_variations ) === 0 ): ?>
                            <li class="sws_custom_message_variation">
                                <div class="sws-column sws-column-50">
                                    <div class="sws-column sws-column-10 sws-sort-handle"><span
                                                class="ui-icon ui-icon-arrow-2-n-s"></span></div>
                                    <div class="sws-column sws-column-80 sws-custom-message"><textarea
                                                name="sws_meta_custom_message_variations[]" cols="40" rows="5"
                                                maxlength="200"></textarea></div>
                                    <div class="sws-column sws-column-10 sws-add-remove-handles">
                                        <span class="ui-icon ui-icon-plus sws-variation-add"></span> <br> <span
                                                class="ui-icon ui-icon-minus sws-variation-remove"></span>
                                        <br>

                                    </div>

                                    <br>
                                    <div class="sws_custom_message_info">
                                        <span class="sws_meta_custom_message_counter"></span><br/>
                                        <span class="description"><?php esc_html_e( 'Note: This custom message will be shared on social media instead of the post title.', 'social-web-suite' ) ?>  <br/>
                                    <?php esc_html_e( 'The maximum length of the message that you can send is 200 characters.', 'social-web-suite' ) ?>
                                </span>
                                    </div>
                                </div>
                                <div class="sws-column sws-column-50 sws-times-share-container">
                                    <label><?php esc_html_e( 'Times to share', 'social-web-suite' ) ?></label>
                                    <input style="margin:5px; width:80px;" type="number"
                                           name="sws_meta_variations_share_times[]" min="0" value="0"/>
                                    <span class="description"><?php esc_html_e( 'Note: Set to zero (0) to share this message unlimited times (depending on your site settings for the maximum number of times the same post can be shared).', 'social-web-suite' ) ?></span>
                                    <input type="hidden" class="sws_share_count_reset"
                                           name="sws_meta_variations_share_count_reset[]" value="0">
                                </div>
                                <div class="sws-clearfix"></div>
                            </li>
						<?php endif; ?>
                    </ul>
                </div>


        </fieldset>


    </div>
</div>
