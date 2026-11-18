var jQuery_1_8_2 = jQuery_1_8_2 || $.noConflict();
(function ($, undefined) {
	$(function () {
		"use strict";
		var $frmUpdateOptions = $("#frmUpdateOptions"),
			$frmInstall = $("#frmInstall"),
			validate = ($.fn.validate !== undefined),
			multiselect = ($.fn.multiselect !== undefined),
			tabs = ($.fn.tabs !== undefined),
			tipsy = ($.fn.tipsy !== undefined),
			$tabs = $("#tabs");
		
		if (multiselect) {
			$("#user_id").multiselect({noneSelectedText: myLabel.choose});
		}
		
		if ($tabs.length > 0 && tabs) {
			$tabs.tabs();
		}
		
		if (tipsy) {
			$(".option-tip").tipsy({
				offset: 1,
				opacity: 1,
				html: true,
				gravity: "nw",
				className: "tipsy-listing"
			});
		}
		
		$(".field-int").spinner({
			min: 0
		});
		
		if ($frmUpdateOptions.length > 0 && validate) {

			$.validator.addMethod('validateHost',
			    function (value) {
					if(value == '')
					{
						return true;
					}else{
						var result = /(?:^|\b)([\w.]+\.[a-z]{2,3}(?:\:[0-9]{1,5})?(?:\/.*)?)(?:[,\s]|$)/i.test(value);
						if (result == true) {
							return true;
						}else{
							var pattern = /\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/;
							if (!pattern.test(value)) 
							{
								return false;
							}else{
								return true;
							}
						}
					}
			    }, myLabel.validate_host
			);
			
			$frmUpdateOptions.validate({
				rules:{
					"value-string-o_smtp_host":{
						required: function(){
							if($('select[name=value-enum-o_send_email]').val() == 'mail|smtp::smtp')
							{
								return true;
							}else{
								return false;
							}
						},
						validateHost: true
					},
					"value-string-o_smtp_pass":{
						required: function(){
							if($('select[name=value-enum-o_send_email]').val() == 'mail|smtp::smtp')
							{
								return true;
							}else{
								return false;
							}
						}
					},
					"value-int-o_smtp_port":{
						required: function(){
							if($('select[name=value-enum-o_send_email]').val() == 'mail|smtp::smtp')
							{
								return true;
							}else{
								return false;
							}
						},
						number: true,
						range: [1, 65535]
					},
					"value-string-o_smtp_user":{
						required: function(){
							if($('select[name=value-enum-o_send_email]').val() == 'mail|smtp::smtp')
							{
								return true;
							}else{
								return false;
							}
						}
					}
				},
				errorPlacement: function (error, element) {
					if(	element.attr('name') == 'value-string-o_smtp_host' || 
						element.attr('name') == 'value-string-o_smtp_pass' ||
						element.attr('name') == 'value-string-o_smtp_port' ||
						element.attr('name') == 'value-string-o_smtp_user')
					{
						error.insertAfter(element);
					}else{
						error.insertAfter(element.parent());
					}
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ""
			});
		}
		function validateURL(value){
			var expression = /[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi;
			var regex = new RegExp(expression);
			return value.match(regex);
	    }
		if ($frmInstall.length > 0 && validate) {
			$.validator.addMethod("validateURL", function(value, element) {
			      return this.optional(element) || validateURL(value);
		    }, "");
			$frmInstall.validate({
				errorPlacement: function (error, element) {
					error.insertAfter(element.parent().parent());
				},
				onkeyup: false,
				errorClass: "err",
				wrapper: "em",
				ignore: ""
			});
		}
		
		$("#content").on("focusin", ".textarea_install", function (e) {
			$(this).select();
		}).on("change", "select[name='value-enum-o_send_email']", function (e) {
			switch ($("option:selected", this).val()) {
			case 'mail|smtp::mail':
				$(".boxSmtp").hide();
				break;
			case 'mail|smtp::smtp':
				$(".boxSmtp").show();
				break;
			}
		}).on("change", "#send_notify", function (e) {
			if($(this).val() == 'Yes')
			{
				$('.sbNotification').show();
			}else{
				$('.sbNotification').hide();
			}
		}).on("click", ".pj-use-theme", function (e) {
			var theme = $(this).attr('data-theme');
			$('.pj-loader').css('display', 'block');
			$.ajax({
				type: "GET",
				async: false,
				url: 'index.php?controller=pjAdminOptions&action=pjActionUpdateTheme&theme=' + theme,
				success: function (data) {
					$('.theme-holder').html(data);
					$('.pj-loader').css('display', 'none');
				}
			});
		});

		// ===== Email Settings: reCAPTCHA toggle, SMTP test connection & send test email =====
		var L = window.pjEmailLabels || {};
		function lbl(key, def) {
			return (L && typeof L[key] === 'string' && L[key] !== '') ? L[key] : def;
		}
		function enumVal(v) {
			if (typeof v !== 'string') { return ''; }
			var i = v.indexOf('::');
			return i >= 0 ? v.substring(i + 2) : v;
		}
		var EMAIL_RE = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
		var AJAX_TIMEOUT = 25000;
		var baseUrl = window.location.pathname;
		var dialogAvailable = ($.fn.dialog !== undefined);
		var $emailTestDialog = $('#emailTestDialog');

		function paint($box, kind, text) {
			if (!$box || $box.length === 0) { return; }
			var bg = '#eef', color = '#333', border = '#ccd';
			if (kind === 'success') { bg = '#e6f4ea'; color = '#1e7e34'; border = '#bfe3c9'; }
			else if (kind === 'error') { bg = '#fdecea'; color = '#b02a37'; border = '#f5c2c7'; }
			else if (kind === 'loading') { bg = '#eef2ff'; color = '#3b4a6b'; border = '#c9d4f0'; }
			$box.css({ 'background': bg, 'color': color, 'border': '1px solid ' + border }).html(text).show();
		}
		function showTestResult(kind, text) { paint($('#emailTestResult'), kind, text); }

		function collectEmailSettings() {
			var send = enumVal($('select[name="value-enum-o_send_email"]').val());
			var data = {
				send_email: send,
				from_email: $.trim($('input[name="value-string-o_from_email"]').val() || ''),
				from_name:  $.trim($('input[name="value-string-o_from_name"]').val() || '')
			};
			if (send === 'smtp') {
				data.smtp_host   = $.trim($('input[name="value-string-o_smtp_host"]').val() || '');
				data.smtp_port   = $.trim($('input[name="value-int-o_smtp_port"]').val() || '');
				data.smtp_secure = enumVal($('select[name="value-enum-o_smtp_secure"]').val());
				data.smtp_auth   = enumVal($('select[name="value-enum-o_smtp_auth"]').val());
				data.smtp_user   = $.trim($('input[name="value-string-o_smtp_user"]').val() || '');
				data.smtp_pass   = $('input[name="value-string-o_smtp_pass"]').val() || '';
			}
			return data;
		}
		function postTest(action, data, $box, $btn, loadingText, failText) {
			paint($box, 'loading', loadingText);
			if ($btn) { $btn.prop('disabled', true); }
			return $.ajax({
				type: 'POST',
				url: baseUrl + '?controller=pjAdminOptions&action=' + action,
				data: data,
				dataType: 'json',
				timeout: AJAX_TIMEOUT
			}).done(function (resp) {
				if (resp && resp.status === 'OK') { paint($box, 'success', resp.text); }
				else { paint($box, 'error', (resp && resp.text) ? resp.text : failText); }
			}).fail(function (xhr, textStatus) {
				var msg = (textStatus === 'timeout') ? failText : lbl('unexpected', 'An unexpected error occurred.');
				paint($box, 'error', msg);
			}).always(function () {
				if ($btn) { $btn.prop('disabled', false); }
			});
		}
		function doSendTestEmail(email, $msg) {
			email = $.trim(email || '');
			if (!email) { paint($msg, 'error', lbl('enterEmail', 'Please enter an email address.')); return; }
			if (!EMAIL_RE.test(email)) { paint($msg, 'error', lbl('validEmail', 'Please enter a valid email address.')); return; }
			var data = collectEmailSettings();
			data.email = email;
			postTest('pjActionAjaxSend', data, $msg, null,
				lbl('sending', 'Sending test email, please wait...'),
				lbl('sendFail', 'The test email could not be sent.'));
		}

		if ($emailTestDialog.length > 0 && dialogAvailable) {
			$emailTestDialog.dialog({
				modal: true, autoOpen: false, resizable: false, draggable: false, width: 480,
				buttons: [
					{ text: lbl('cancel', 'Cancel'), click: function () { $(this).dialog('close'); } },
					{ text: lbl('sendEmail', 'Send Email'), click: function () { doSendTestEmail($('#emailTestModalEmail').val(), $('#emailTestModalMsg')); } }
				]
			});
		}

		$("#content")
			.on("change", "select[name='value-enum-o_captcha_provider']", function (e) {
				switch ($("option:selected", this).val()) {
				case 'phpjabbers|recaptcha::phpjabbers':
					$(".boxRecaptcha").hide();
					break;
				case 'phpjabbers|recaptcha::recaptcha':
					$(".boxRecaptcha").show();
					break;
				}
			})
			.on("click", "#btnTestConnection", function (e) {
				e.preventDefault();
				var data = collectEmailSettings();
				if (!data.smtp_host || !data.smtp_port) {
					showTestResult('error', lbl('enterHost', 'Please enter the SMTP host and port first.'));
					return;
				}
				postTest('pjActionAjaxSmtp', {
					smtp_host: data.smtp_host, smtp_port: data.smtp_port,
					smtp_secure: data.smtp_secure, smtp_auth: data.smtp_auth,
					smtp_user: data.smtp_user, smtp_pass: data.smtp_pass
				}, $('#emailTestResult'), $(this),
				lbl('testing', 'Testing SMTP connection, please wait...'),
				lbl('connFail', 'Connection failed. Please check your SMTP settings.'));
			})
			.on("click", "#btnSendTestEmail", function (e) {
				e.preventDefault();
				var data = collectEmailSettings();
				if ($emailTestDialog.length > 0 && dialogAvailable) {
					$('#emailTestModalMsg').hide().empty();
					$('#emailTestModalEmail').val(data.from_email || '');
					$emailTestDialog.dialog('open');
					$('#emailTestModalEmail').focus();
				} else {
					var email = window.prompt(lbl('promptEmail', 'Enter the email address to send the test email to:'), data.from_email || '');
					if (email !== null) { doSendTestEmail(email, $('#emailTestResult')); }
				}
			});
	});
})(jQuery_1_8_2);