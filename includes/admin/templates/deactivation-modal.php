<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


	$reasons = array(
				array(
				'id' => '8',
				'text' => __( 'The plugin settings were confusing', 'social-web-suite' ),
				'input_type' => 'textfield',
				'input_placeholder' => __( 'Can you please leave us your email address, so we can help you out with the plugin settings', 'social-web-suite' ),
				),
				array(
				'id' => '9',
				'text' => __( 'I couldn\'t figure out how the plugin works', 'social-web-suite' ),
				'input_type' => 'textfield',
				'input_placeholder' => __( 'Can you please leave us your email address, so we can help you out with the plugin', 'social-web-suite' ),
				),
				array(
				'id' => '1',
				'text' => __( 'I no longer need the plugin', 'social-web-suite' ),
				'input_type' => '',
				'input_placeholder' => '',
				),
				array(
				'id' => '2',
				'text' => __( 'I found a better plugin', 'social-web-suite' ),
				'input_type' => 'textfield',
				'input_placeholder' => __( "What's the plugin's name?", 'social-web-suite' ),
				),
				array(
				'id' => '3',
				'text' => __( 'I only needed the plugin for a short period', 'social-web-suite' ),
				'input_type' => '',
				'input_placeholder' => '',
				),
				array(
				'id' => '4',
				'text' => __( 'The plugin broke my site', 'social-web-suite' ),
				'input_type' => '',
				'input_placeholder' => '',
				),
				array(
				'id' => '5',
				'text' => __( 'The plugin suddenly stopped working', 'social-web-suite' ),
				'input_type' => '',
				'input_placeholder' => '',
				),
				array(
				'id' => '6',
				'text' => __( "It's a temporary deactivation. I'm just debugging an issue.", 'social-web-suite' ),
				'input_type' => '',
				'input_placeholder' => '',
				),
				array(
				'id' => '7',
				'text' => __( 'Other', 'social-web-suite' ),
				'input_type' => 'textfield',
				'input_placeholder' => '',
				),
	);



	$reasons_list_items_html = '';

	foreach ( $reasons as $reason ) {
		$list_item_classes = 'reason' . ( ! empty( $reason['input_type'] ) ? ' has-input' : '' );
		$reasons_list_items_html .= '<li class="' . $list_item_classes . '" data-input-type="' . $reason['input_type'] . '" data-input-placeholder="' . $reason['input_placeholder'] . '"><label><span><input type="radio" name="selected-reason" value="' . $reason['id'] . '"/></span><span>' . $reason['text'] . '</span></label></li>';
	}

?>
<script type="text/javascript">
(function ($) {
	var reasonsHtml = <?php echo wp_json_encode( $reasons_list_items_html ); ?>,
		modalHtml =
			'<div class="sws-modal no-confirmation-message">'
			+ '    <div class="sws-modal-dialog">'
			+ '        <div class="sws-modal-body">'
            + '          <div class="sws-visual">'
            + '           <img src="<?php echo esc_url( SocialWebSuite::get_plugin_url() . 'images/sws-logo.png' ) ?>" alt="Social Web Suite Logo"/>'
            + '          </div>'
			+ '            <div class="sws-modal-panel" data-panel-id="confirm"><p></p></div>'
			+ '            <div class="sws-modal-panel active sws-modal-main" data-panel-id="reasons"><h3><strong><?php  esc_html_e( 'If you have a moment, please let us know why you are deactivating', 'social-web-suite' ); ?>:</strong></h3><ul id="reasons-list">' + reasonsHtml + '</ul></div>'
			+ '        </div>'
			+ '        <div class="sws-modal-footer">'
			+ '            <a href="#" class="button button-secondary button-deactivate"></a>'
			+ '            <a href="#" class="button button-primary button-close"><?php esc_html_e( 'Cancel', 'social-web-suite' ); ?></a>'
			+ '        </div>'
			+ '    </div>'
			+ '</div>',
		$modal = $(modalHtml),
		$deactivateLink = $('#the-list .deactivate > [data-slug="social-web-suite"].sws-slug').prev(),
		selectedReasonID = false;

	$modal.appendTo($('body'));

	sws_register_event_handlers();

	function sws_register_event_handlers() {
		$deactivateLink.click(function (evt) {
			evt.preventDefault();

			sws_show_modal();
		});

		$modal.on('input propertychange', '.reason-input input', function () {
			if (!sws_is_other_reason_selected()) {
				return;
			}

			var reason = $(this).val().trim();

			/**
			 * If reason is not empty, remove the error-message class of the message container
			 * to change the message color back to default.
			 */
			if (reason.length > 0) {
				$('.message').removeClass('error-message');
				sws_enable_deactivate_button();
			}
		});

		$modal.on('blur', '.reason-input input', function () {
			var $userReason = $(this);

			setTimeout(function () {
				if (!sws_is_other_reason_selected()) {
					return;
				}

				/**
				 * If reason is empty, add the error-message class to the message container
				 * to change the message color to red.
				 */
				if (0 === $userReason.val().trim().length) {
					$('.message').addClass('error-message');
					sws_disable_deactivate_button();
				}
			}, 150);
		});

		$modal.on('click', '.button', function (evt) {
			evt.preventDefault();

			if ($(this).hasClass('disabled')) {
				return;
			}

			var _parent = $(this).parents('.sws-modal:first');
			var _this = $(this);

			if (_this.hasClass('allow-deactivate')) {
				var $radio = $('input[type="radio"]:checked');

				if (0 === $radio.length) {
					// If no selected reason, just deactivate the plugin.
					window.location.href = $deactivateLink.attr('href');
					return;
				}

				var $selected_reason = $radio.parents('li:first'),
					$input = $selected_reason.find('textarea, input[type="text"]'),
					userReason = ( 0 !== $input.length ) ? $input.val().trim() : '';

				if (sws_is_other_reason_selected() && ( '' === userReason )) {
					return;
				}

				$.ajax({
					url       : ajaxurl,
					method    : 'POST',
					data      : {
						'action'     : 'sws_submit_uninstall_reason',
						'reason_id'  : $radio.val(),
						'reason_info': userReason
					},
					beforeSend: function () {
						_parent.find('.button').addClass('disabled');
						_parent.find('.button-secondary').text('Processing...');
					},
					complete  : function () {
						// Do not show the dialog box, deactivate the plugin.
						window.location.href = $deactivateLink.attr('href');
					}
				});
			} else if (_this.hasClass('button-deactivate')) {
				// Change the Deactivate button's text and show the reasons panel.
				_parent.find('.button-deactivate').addClass('allow-deactivate');

				sws_show_panel('reasons');
			}
		});

		$modal.on('click', 'input[type="radio"]', function () {
			var $selectedReasonOption = $(this);

			// If the selection has not changed, do not proceed.
			if (selectedReasonID === $selectedReasonOption.val())
				return;

			selectedReasonID = $selectedReasonOption.val();

			var _parent = $(this).parents('li:first');

			$modal.find('.reason-input').remove();
			$modal.find('.button-deactivate').text('<?php esc_html_e( 'Submit and Deactivate', 'social-web-suite' ); ?>');

			sws_enable_deactivate_button();

			if (_parent.hasClass('has-input')) {
				var inputType = _parent.data('input-type'),
					inputPlaceholder = _parent.data('input-placeholder'),
					reasonInputHtml = '<div class="reason-input"><span class="message"></span>' + ( ( 'textfield' === inputType ) ? '<input type="text" />' : '<textarea rows="5"></textarea>' ) + '</div>';

				_parent.append($(reasonInputHtml));
				_parent.find('input, textarea').attr('placeholder', inputPlaceholder).focus();

				if (sws_is_other_reason_selected()) {
					sws_show_message('<?php esc_html_e( 'Kindly tell us the reason so we can improve.', 'social-web-suite' ); ?>');
					sws_disable_deactivate_button();
				}
			}
		});

		// If the user has clicked outside the window, cancel it.
		$modal.on('click', function (evt) {
			var $target = $(evt.target);

			// If the user has clicked anywhere in the modal dialog, just return.
			if ($target.hasClass('sws-modal-body') || $target.hasClass('sws-modal-footer')) {
				return;
			}

			// If the user has not clicked the close button and the clicked element is inside the modal dialog, just return.
			if (!$target.hasClass('button-close') && ( $target.parents('.sws-modal-body').length > 0 || $target.parents('.sws-modal-footer').length > 0 )) {
				return;
			}

			sws_close_modal();
		});
	}

	function sws_is_other_reason_selected() {
		// Get the selected radio input element.
		var $selectedReasonOption = $modal.find('input[type="radio"]:checked'),
			selectedReason = $selectedReasonOption.parent().next().text().trim();

		return ( 'Other' === selectedReason );
	}

	function sws_show_modal() {
		sws_reset_modal();

		// Display the dialog box.
		$modal.addClass('active');

		$('body').addClass('has-sws-modal');
	}

	function sws_close_modal() {
		$modal.removeClass('active');

		$('body').removeClass('has-sws-modal');
	}

	function sws_reset_modal() {
		selectedReasonID = false;

		sws_enable_deactivate_button();

		// Uncheck all radio buttons.
		$modal.find('input[type="radio"]').prop('checked', false);

		// Remove all input fields ( textfield, textarea ).
		$modal.find('.reason-input').remove();

		$modal.find('.message').hide();

		var $deactivateButton = $modal.find('.button-deactivate');

		/*
		 * If the modal dialog has no confirmation message, that is, it has only one panel, then ensure
		 * that clicking the deactivate button will actually deactivate the plugin.
		 */
		if ($modal.hasClass('no-confirmation-message')) {
			$deactivateButton.addClass('allow-deactivate');

			sws_show_panel('reasons');
		} else {
			$deactivateButton.removeClass('allow-deactivate');

			sws_show_panel('confirm');
		}
	}

	function sws_show_message(message) {
		$modal.find('.message').text(message).show();
	}

	function sws_enable_deactivate_button() {
		$modal.find('.button-deactivate').removeClass('disabled');
	}

	function sws_disable_deactivate_button() {
		$modal.find('.button-deactivate').addClass('disabled');
	}

	function sws_show_panel(panelType) {
		$modal.find('.sws-modal-panel').removeClass('active ');
		$modal.find('[data-panel-id="' + panelType + '"]').addClass('active');

		sws_update_button_labels();
	}

	function sws_update_button_labels() {
		var $deactivateButton = $modal.find('.button-deactivate');

		// Reset the deactivate button's text.
		if ('confirm' === sws_get_current_panel()) {
			$deactivateButton.text('<?php esc_html_e( 'Yes, deactivate', 'social-web-suite' ); ?>');
		} else {
			$deactivateButton.text('<?php  esc_html_e( 'Skip and deactivate', 'social-web-suite' ); ?>');
		}
	}

	function sws_get_current_panel() {
		return $modal.find('.sws-modal-panel.active').attr('data-panel-id');
	}
})(jQuery);
</script>
