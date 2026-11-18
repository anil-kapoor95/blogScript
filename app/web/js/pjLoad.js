(function (window, undefined){
	"use strict";
	var document = window.document,
		validate = (pjQ.$.fn.validate !== undefined);
	
	function loadDelayedCSS(opts) {
		pjQ.$(window).load(function() {
						pjQ.$('body').append(pjQ.$('<link rel="stylesheet" type="text/css" />').attr('href', opts.server+'app/web/css/transitions.css'+(opts.transitions_ver || '')));
		});
	}
	function Blog(opts) {
		if (!(this instanceof Blog)) {
			return new Blog(opts);
		}
		this.reset.call(this);
		this.init.call(this, opts);
		return this;
	}
	
	Blog.prototype = {
		reset: function () {
			this.opts = null;
			this.$container = null;
			this.container = null;
					
			return this;
		},
		disableButtons: function () {
			this.$container.find(".btn").each(function (i, el) {
				pjQ.$(el).attr("disabled", "disabled");
			});
		},
		enableButtons: function () {
			this.$container.find(".btn").removeAttr("disabled");
		},
		init: function (opts) {
			var self = this;
			
			this.opts = opts;
			this.container = document.getElementById("pjSbContainer_" + self.opts.index);
			this.$container = pjQ.$(this.container);
			
			var $frmSendToFriend = pjQ.$('#pjSbSendToFriend_' + self.opts.index);
			var $frmPostComment = pjQ.$('#pjSbPostComment_' + self.opts.index);
			
			loadDelayedCSS(opts);
			
			if (pjQ.$('#pjWrapper .pjBlFiltersDropdown').length) {
				
				var $filtersTrigger = pjQ.$('#pjWrapper .pjBlFiltersTrigger');
				var $filtersRemove = pjQ.$('#pjWrapper .pjBlFiltersDropdown .pjBlFiltersRemove');
				var filtersDropdownActiveClass = 'pjBlFiltersDropdownActive';
				var filtersTriggerActiveClass = 'pjBlFiltersTriggerActive';
				var filterActiveClass = 'pjBlFilterActive';

				$filtersTrigger.on('click', function(e) {
					var $currentFilterTrigger = pjQ.$(this);
					var $currentFilterName = $currentFilterTrigger.attr('data-pjBlFilter');
					var $currentFilters = $currentFilterTrigger.parents('#pjWrapper').find('.pjBlFiltersDropdown');
					var $currentFilter = $currentFilters.find('.pjBlFilter').filter($currentFilterName);

					if ($currentFilterTrigger.hasClass(filtersTriggerActiveClass)) {
						$currentFilters.removeClass(filtersDropdownActiveClass);
					} else {
						$currentFilters.addClass(filtersDropdownActiveClass);
						$currentFilter.addClass(filterActiveClass).siblings().removeClass(filterActiveClass);
					};

					$currentFilterTrigger.toggleClass(filtersTriggerActiveClass).siblings().removeClass(filtersTriggerActiveClass);

					e.preventDefault();
				});

				$filtersRemove.on('click', function(e) {
					pjQ.$(this).parent().removeClass(filtersDropdownActiveClass);
					$filtersTrigger.removeClass(filtersTriggerActiveClass);

					e.preventDefault();
				});
			};
			
			if(pjQ.$('#pjBlSendMessage').length > 0)
			{
				pjQ.$('.modal-dialog').css("z-index", "9999"); 
			}
			if($frmSendToFriend.length > 0)
			{
				$frmSendToFriend.validate({
					rules: {
						"subject": {required: true},
						"send_to": {required: true,
									email: true},
						"message": {required: true},
						"captcha": {required: true,
									remote: self.opts.folder + "index.php?controller=pjFront&action=pjActionCheckSendCaptcha"}
					},
					messages: {
						"subject": {required: self.opts.required_field},
						"send_to": {required: self.opts.required_field,
									email: self.opts.invalid_email},
						"message": {required: self.opts.required_field},
						"captcha": {required: self.opts.required_field,
									remote: self.opts.captcha_incorrect}
					},
					onkeyup: false,
					errorElement: 'li',
					errorPlacement: function (error, element) {
						if(element.attr('name') == 'captcha')
						{
							error.appendTo(element.parent().next().find('ul'));
						}else{
							error.appendTo(element.next().find('ul'));
						}
					},
		            highlight: function(ele, errorClass, validClass) {
		            	var element = pjQ.$(ele);
		            	if(element.attr('name') == 'captcha')
						{
		            		element.parent().parent().removeClass('has-success').addClass('has-error');
						}else{
							element.parent().removeClass('has-success').addClass('has-error');
						}
		            },
		            unhighlight: function(ele, errorClass, validClass) {
		            	var element = pjQ.$(ele);
		            	if(element.attr('name') == 'captcha')
						{
		            		element.parent().parent().removeClass('has-error').addClass('has-success');
						}else{
							element.parent().removeClass('has-error').addClass('has-success');
						}
		            },
					submitHandler: function (form) {
						var $send_message = pjQ.$('#pjSbSendMessage_' + self.opts.index);
						self.disableButtons();
						$send_message.html(self.opts.send_msg);
						$send_message.parent().css('display', 'block');
						pjQ.$.ajax({
							type: "POST",
							dataType: "json",
							data: $frmSendToFriend.serialize(),
							url: self.opts.folder + 'index.php?controller=pjLoad&action=pjActionSend',
							success: function (result) {
								if (result && result.code == 201) {
									$send_message.html(self.opts.captcha_incorrect);
								} else {
									$send_message.html(self.opts.send_ok);
								}
								self.enableButtons();
							}
						});
					}
				});
			}
			if($frmPostComment.length > 0 && validate)
			{
				pjQ.$.validator.addMethod('checkwords', function(value, element) {
					var banned_word_str = self.opts.banned_words;
					if(banned_word_str != ""){
				    	banned_word_str = banned_word_str.replace(/\s+/g, '');
				    	banned_word_str = banned_word_str.replace(new RegExp(",","g"),"|");
				    	return !new RegExp(banned_word_str).test(value);
			    	}else{
			    		return true;
			    	}
				});
				pjQ.$.validator.addMethod('checklink', function(value, element) {
					var valid = true,
						reg = /(\b(?:(https?|ftp):\/\/)?((?:www\d{0,3}\.)?([a-z0-9.-]+\.(?:[a-z]{2,4}|museum|travel)(?:\/[^\/\s]+)*))\b)/;
					if(self.opts.reject_links == 'Yes'){
						if(value != "" && value.match(reg) != null){
					    	valid = false;
					    }
					}
					return valid;
				});
				$frmPostComment.validate({
					rules: {
						"name": {	required: true,
									checklink: true},
						"email": {	required: true,
									email: true},
						"comment": {required: true,
									checkwords: true,
									checklink: true},
						"captcha":{	required: true,
									remote: self.opts.folder + "index.php?controller=pjFront&action=pjActionCheckCaptcha"}
					},
					messages: {
						"name": {required: self.opts.required_field,
							     checklink: self.opts.reject_msg},
						"email": {	required: self.opts.required_field,
									email: self.opts.invalid_email},
						"comment": {required: self.opts.required_field,
									checkwords: self.opts.banned_msg,
									checklink: self.opts.reject_msg},
						"captcha": {required: self.opts.required_field,
									remote: self.opts.captcha_incorrect}
					},
					onkeyup: false,
					errorElement: 'li',
					errorPlacement: function (error, element) {
						if(element.attr('name') == 'captcha')
						{
							error.appendTo(element.parent().next().find('ul'));
						}else{
							error.appendTo(element.next().find('ul'));
						}
					},
		            highlight: function(ele, errorClass, validClass) {
		            	var element = pjQ.$(ele);
		            	if(element.attr('name') == 'captcha')
						{
		            		element.parent().parent().removeClass('has-success').addClass('has-error');
						}else{
							element.parent().removeClass('has-success').addClass('has-error');
						}
		            },
		            unhighlight: function(ele, errorClass, validClass) {
		            	var element = pjQ.$(ele);
		            	if(element.attr('name') == 'captcha')
						{
		            		element.parent().parent().removeClass('has-error').addClass('has-success');
						}else{
							element.parent().removeClass('has-error').addClass('has-success');
						}
		            },
					submitHandler: function (form) {
						// JS embed: post through the embed runtime (stays on the host page).
						if (self.opts.embed && window.PJBLOG && window.PJBLOG.comment) {
							window.PJBLOG.comment(form);
						} else {
							form.submit();
						}
					}
				});

				if(self.opts.show_message == true){
					pjQ.$('html,body').animate({
						scrollTop: $frmPostComment.offset().top},
			        'slow');
				}
			}
			this.$container.on('click.sb', '.pjSbArchiveItem', function(e){
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var $searchForm = pjQ.$('#pjSbSearchForm_' + self.opts.index);
				var archive = pjQ.$(this).attr('data-archive');
				if (self.opts.embed && window.PJBLOG && window.PJBLOG.archive) {
					window.PJBLOG.archive($searchForm.get(0), archive);
					return;
				}
				$searchForm.find('input[name="archive"]').val(archive);
				$searchForm.submit();
			}).on('click.sb', '.pjSbStarRating', function(e){
				if (e && e.preventDefault) {
					e.preventDefault();
				}
				var post_id = pjQ.$(this).attr('data-post_id');
				var rate = pjQ.$(this).val();
				var $star_message = pjQ.$('#pjSbRatingMessage_' + post_id);
				pjQ.$.ajax({
					type: "POST",
					dataType: "json",
					data: {rate: rate, id: post_id},
					url: self.opts.folder + 'index.php?controller=pjLoad&action=pjActionRating',
					success: function (result) {
						switch (result.code) {
							case 101:
								$star_message.html(self.opts.already_voted);
								break;
							case 102:
								$star_message.html(self.opts.thank_vote);
								break;
						}
						pjQ.$('.pjSbStarRating-' + post_id).attr('disabled', 'disabled');
					}
				});
			});
		}
	};
	
	// expose
	window.Blog = Blog;
})(window);