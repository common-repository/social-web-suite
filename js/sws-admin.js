var sws_script_active = true;
jQuery(
	function ($)
	{
		if($('#sws-notify-sharing').length === 0){
			console.log('sharing notice is off');
			$('#sws_meta_sharing_notice').show();
		}else{
			$('#sws_meta_sharing_notice').hide();
		}
		var show_sws_notice = localStorage.getItem( "sws-notice" );
		//var show_sws_notice = 'show';
		if (show_sws_notice == 'hide') {
			$( '.sws-connect-notice' ).hide();
		} else {
			$( '.sws-connect-notice' ).show();
		}
		$( '.sws-notice .notice-dismiss' ).click(
			function(e)
			{
				e.preventDefault();
				var targetContainer = $(this).data('target');
				$( targetContainer ).hide();
				localStorage.setItem( "sws-notice", "hide" );

			}
		);

        var rateNoticeContainer = $('.sws-rate-notice');
        if (rateNoticeContainer.length) {
            console.log(' yes length');
            rateNoticeContainer.find('.sws-data-rate-action').click(function() {
                rateNoticeContainer.remove();

                var rateAction = $(this).attr('data-rate-action');
                console.log('triggered');
                $.post(
                    ajaxurl,
                    {
                        action: 'sws_notice_rate',
                        rate_action: rateAction,
                        _n: rateNoticeContainer.find('ul:first').attr('data-nonce')
                    },
                    function(result) {

                        console.log('triggered ajax');
					}
                );

                if ('do-rate' !== rateAction) {
                    return false;
                }
            });
        }else{
        	console.log('no length');
		}

		$('.sws-delete-all-data').click(function(e){
			e.preventDefault();
			var deleteUrl = $(this).attr('href');
            var r = confirm("Are you sure you want to delete all Social Web Suite data on this site?");
            if (r === true) {
               location.href = deleteUrl;
            }
		});
		$( '.sws-delete-log-item' ).click(
			function(e)
			{
				e.preventDefault();
				var confirm = window.confirm( "Are you sure you want to delete this log?" );
				if (confirm == true) {
					window.location.href = $( this ).attr( 'href' );
				}
			}
		);
		$( '#sws_meta_manual' ).change(
			function ()
			{
				var val = $( this ).val();
				$( '#sws_meta_notevergreen_options' ).toggle( val === 'notevergreen' );
				$( '#sws_meta_sharing_options' ).toggle( val === 'custom' );
				$( '#sws-notify-sharing' ).toggle( val !== 'skip' );
			}
		);

		$( '#sws_meta_send_now' ).change(
			function ()
			{
				$( '#sws_meta_schedule' ).toggle( $( this ).val() === 'schedule' );
			}
		);

		$( '#sws_meta_use_hashtags' ).change(
			function ()
			{
				$( '#sws_meta_hashtags_field' ).toggle( $( this ).val() === 'custom' );
			}
		);
		//$( '#sws_meta_use_hashtags_custom' ).change(
		$( '[name="sws_meta_use_hashtags"]' ).change(
			function ()
			{
				$( '#sws_meta_hashtags_field' ).toggle( $( this ).is(':checked') && $( this ).attr( 'id' ) === 'sws_meta_use_hashtags_custom' );
			}
		);

		$( '#sws_meta_schedule_calendar' )
		.prop( 'readonly', true )
		.datepicker(
			{
				autoSize: true,
				dateFormat: 'mm/dd/yy',
				altField: "#sws_meta_schedule_date",
				altFormat: "yy-mm-dd",
				minDate: 0,
                maxDate: '+1Y',
				constrainInput: true,
				defaultDate: 0,
				onClose: function (dateValue, pickerObj)
				{
					try {
						var d = $.datepicker.parseDate( pickerObj.settings.dateFormat, dateValue );
						$( '#sws_meta_schedule_date_error' ).hide();

					} catch (e) {
						$( this ).datepicker( 'setDate', null );
						alert( 'Please enter the valid date!' );
					}
				}
			}
		);
		$( ' #sws_meta_startdate_calendar ' )
		.prop( 'readonly', true )
		.datepicker(
			{
				autoSize: true,
				dateFormat: 'mm/dd/yy',
				altField: "#sws_meta_startdate__date",
				altFormat: "yy-mm-dd",
				minDate: 0,
				constrainInput: true,
				defaultDate: 0,
				onClose: function (dateValue, pickerObj)
				{
					try {
						var d = $.datepicker.parseDate( pickerObj.settings.dateFormat, dateValue );

					} catch (e) {
						$( this ).datepicker( 'setDate', null );
						alert( 'Please enter the valid date!' );
					}

				},
				onSelect: function()
				{

					var endDate = $( '#sws_meta_enddate_calendar' );
					var minDate = $( this ).datepicker( 'getDate' );
					if (minDate != null && endDate.datepicker( 'getDate' ) < minDate && endDate.datepicker( 'getDate' ) != null ) {
						endDate.datepicker( 'setDate', minDate );
					}
					endDate.datepicker( 'option', 'minDate', minDate );
					if (minDate != null) {
						$( '#sws_empty_nonevergreen_dates' ).hide();
					}
				},
			}
		);
		$( '#sws_meta_enddate_calendar' )
		.prop( 'readonly', true )
		.datepicker(
			{
				autoSize: true,
				dateFormat: 'mm/dd/yy',
				altField: "#sws_meta_enddate_date",
				altFormat: "yy-mm-dd",
				minDate: 0,
				constrainInput: true,
				defaultDate: 0,
				onClose: function (dateValue, pickerObj)
				{
					try {
						var d = $.datepicker.parseDate( pickerObj.settings.dateFormat, dateValue );

					} catch (e) {
						$( this ).datepicker( 'setDate', null );
						alert( 'Please enter the valid date!' );
					}
				},
				onSelect: function ()
				{

					var startDate = $( '#sws_meta_startdate_calendar' );
					var maxDate = $( this ).datepicker( 'getDate' );
					if (maxDate != null && startDate.datepicker( 'getDate' ) > maxDate ) {
						if (startDate.datepicker( 'getDate' ) != null) {
							startDate.datepicker( 'setDate', maxDate );
						}

					}
					if (maxDate != null) {
						$( '#sws_empty_nonevergreen_dates' ).hide();
					}
				},

			}
		);
		$( '#sws_meta_startdate_reset, #sws_meta_enddate_reset' ).click(
			function (e)
			{
				if ($( this ).attr( 'id' ).indexOf( 'startdate' ) != -1) {
					$( '#sws_meta_startdate_calendar' ).datepicker( 'setDate', null );
				}
				if ($( this ).attr( 'id' ).indexOf( 'enddate' ) != -1) {
					$( '#sws_meta_enddate_calendar' ).datepicker( 'setDate', null );
				}
				var startDate = $( '#sws_meta_startdate_calendar' ).datepicker( 'getDate' );
				var endDate = $( '#sws_meta_enddate_calendar' ).datepicker( 'getDate' );
				if (startDate == null && endDate == null) {
					$( '#sws_empty_nonevergreen_dates' ).show();
				}

			}
		);
		$( '#sws_meta_schedule_calendar_btn , #sws_meta_startdate_calendar_btn, #sws_meta_enddate_calendar_btn' ).click(
			function (e)
			{
				e.preventDefault();
				if ($( this ).attr( 'id' ).indexOf( 'schedule' ) != -1) {
					$( '#sws_meta_schedule_calendar' ).datepicker( 'show' );
				}
				if ($( this ).attr( 'id' ).indexOf( 'startdate' ) != -1) {
					$( '#sws_meta_startdate_calendar' ).datepicker( 'show' );
				}
				if ($( this ).attr( 'id' ).indexOf( 'enddate' ) != -1) {
					$( '#sws_meta_enddate_calendar' ).datepicker( 'show' );
				}
			}
		);

		var startDate = $( '#sws_meta_startdate_calendar' ).datepicker( 'getDate' );
		var endDate = $( '#sws_meta_enddate_calendar' ).datepicker( 'getDate' );

		if (startDate == null && endDate == null) {
			   $( '#sws_empty_nonevergreen_dates' ).show();
		}

		if ('skip' === $( '#sws_meta_manual' ).val() ) {
			 $( '#sws_miscellaneous_options' ).hide();
		} else {
			 $( '#sws_miscellaneous_options' ).show();
		}
		$( '#sws_meta_manual' ).change(
			function(e)
			{
				if ('skip' === $( this ).val() ) {
					$( '#sws_miscellaneous_options' ).hide();
				} else {
					$( '#sws_miscellaneous_options' ).show();
				}
			}
		);

		$( '.sws_meta_tag' ).click(
			function(e)
			{
				e.preventDefault();
				var sws_meta_tag = '{' + $( this ).data( 'meta-tag' ) + '}';
				var sws_meta_format = $( '#sws_meta_format' ).val();
				if (sws_meta_format.indexOf( sws_meta_tag ) == -1) {
					if (sws_meta_format == '') {
						sws_meta_format += sws_meta_tag;
					} else {
						sws_meta_format += ' ' + sws_meta_tag;
					}
					$( '#sws_meta_format' ).val( sws_meta_format );
				}

			}
		);
        sws_invoke_custom_message_characters();
		function sws_invoke_custom_message_characters(){
            if ($( ".sws_custom_message_variation" ).length > 0) {
                $( ".sws_custom_message_variation" ).each( function(){
                    var text_message_parent = $(this);
                    var text_message = text_message_parent.find('textarea');
                    sws_custom_message_characters(text_message, text_message_parent);
                    text_message.on(
                        'keyup paste', function()
                        {
                            sws_custom_message_characters(text_message, text_message_parent);
                        }
                    );
                });

            }
		}



		function sws_custom_message_characters(text_message, text_message_parent)
		{
			   var sws_meta_custom_message_val = text_message.val();
			if (sws_meta_custom_message_val === undefined) {
				return;
			}
			   var sws_characters = text_message.val().replace( /(<([^>]+)>)/ig,"" ).length;

            text_message_parent.find( ".sws_meta_custom_message_counter" ).text( "Characters left: " + (200 - sws_characters) );
		}
		/*$('#sws_show_misc_options').click(function(e){
        if($(this).is(':checked')){
        $('#sws_miscellaneous_options').show(200);
        }else{
        $('#sws_miscellaneous_options').hide(200);
        }

        }); */
		var sws_message_variations = $( "#sws_meta_custom_message_variations" );
		if( sws_message_variations.length > 0 ){


			sws_message_variations.sortable({
				handle: '.sws-sort-handle',
			} );

			sws_message_variations.on( 'click', '.sws-variation-add', function(){
				if( $( '.sws_custom_message_variation' ).length < 12 ) {
					sws_add_message_variation();

				}else{
					alert( sws_dialog_text.add_limit_message );
				}
			});

			sws_message_variations.on( 'click', '.sws-variation-remove', function(){
				var sws_message_item = $( this ).parent().parent().parent();
				console.log(sws_message_item.attr('class'));
				var confirmation = confirm(sws_dialog_text.remove_title +  ' ' +  sws_dialog_text.remove_message );
				console.log('confirmation results ', confirmation);
				if( true === confirmation ){

					sws_message_item.remove();
					if( $( '.sws_custom_message_variation' ).length === 0 ){
						sws_add_message_variation();
					}
				}

			/*	if($( '#sws-dialog-confirm' ).length === 0){
					var sws_dialog = '<div id="sws-dialog-confirm" title="' + sws_dialog_text.title + '">\n' +
						'<p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>' +
						sws_dialog_text.message + '</p>\n' +
						'</div>';
					$( 'body' ).append(sws_dialog);
				}*/


			   /* $( "#sws-dialog-confirm" ).dialog({
					resizable: false,
					height: "auto",
					width: 400,
					modal: true,
					buttons: {
						"Yes": function() {
							sws_message_item.remove();
							$( this ).dialog( "close" );
						},
						Cancel: function() {
							$( this ).dialog( "close" );
						}
					}
				});*/
			});

			sws_message_variations.on( 'click', '.sws_reset_counter', function(e){
				console.log('clicked reset');
				e.preventDefault();
				var sws_counter_container = $( this ).parent();
				sws_counter_container.find( '.sws_share_count' ).find('.inner').text( '0' );
				sws_counter_container.find( '.sws_share_count_reset' ).val( 1 );

				//sws_share_count_reset
				//sws_share_count
			});
			$( '.sws_meta_use_cutom_messages' ).click( function(){

				var variations_wrapper = $( '.sws_meta_custom_message_variations_wrapper' );
				if( $(this).is(':checked') ){
                    variations_wrapper.show( 200 );
				}else{
                    variations_wrapper.hide( 200 );
				}
			});
        }
        function sws_mark_next_shared_image(){
			$( '.sws_custom_message_variation' ).each( function(){
                $( '.sws_custom_message_variation' ).removeClass('sws_next_shared');
               if( $( this ).hasClass( 'sws_last_shared' ) ){

			   }
			});
		}
		function sws_add_message_variation(){
            var sws_message_variations = $( "#sws_meta_custom_message_variations" );
            var sws_variation_element = '<li class="sws_custom_message_variation">' +
				'<div class="sws-column sws-column-50">' +
                '<div class="sws-column sws-column-10 sws-sort-handle"><span class="ui-icon ui-icon-arrow-2-n-s"></span></div>' +
                '<div class="sws-column sws-column-80 sws-custom-message"><textarea name="sws_meta_custom_message_variations[]" cols="40" rows="5" maxlength="200"></textarea></div>' +
                '<div class="sws-column sws-column-10 sws-add-remove-handles"><span class="ui-icon ui-icon-plus sws-variation-add"></span> <br> <span class="ui-icon ui-icon-minus sws-variation-remove"></span>' +
				'<br/>' +
				'</div>' +
                '<div class="sws_custom_message_info">' +
				'<span class="sws_meta_custom_message_counter"></span>' +
				'<br />' +
				'<span class="description">' + sws_custom_message_info.description + '<br />' + sws_custom_message_info.type_description +
        		'</span>' +
				'</div>' +
				'</div>' +
                '<div class="sws-column sws-column-50 sws-times-share-container">' +
                '<label>' + sws_custom_message_counter_text.label + '</label>' +
                '<input type="number" name="sws_meta_variations_share_times[]" min="0" value="0"/>' +
                '<span class="description">' + sws_custom_message_counter_text.description + '</span>'+
                '<input type="hidden" class="sws_share_count_reset" name="sws_meta_variations_share_count_reset[]" value="0">' +
                '</div>' +
				'<div class="sws-clearfix"></div>' +
				'</li>';

            sws_message_variations.append( sws_variation_element );
            sws_invoke_custom_message_characters();
		}

		$(".sws_social_account_checkbox").change(function(){
			//console.log('change');
			if($(this).hasClass('sws_social_twitter')){
				if($(this).prop("checked")){
					$('.sws_social_twitter').not(this).each(function(){
                        $(this).prop("disabled", true);
                    });

				}else{
                    $('.sws_social_twitter').each(function(){
                        $(this).prop("disabled", false);
                    });
				}
			}
		});
	}
);
