/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 HUBzero Foundation, LLC.
 * @license    http://opensource.org/licenses/MIT MIT
 */

if (!jq) {
	var jq = $;
}

String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};

// Based on app/templates/bmc/js/hub.js (not loaded within supergroups)
(function(TPL, $, undefined) {
	/**
	 * Render messages send via JSON
	 *
	 * @param   object  messages  JavaScript object containing the messages to render
	 * @return  void
	 */
	TPL.renderMessages = function(messages, success_duration = 4000, $container = $('#fancybox-message-container')) {
		messages = (Array.isArray(messages) ? messages : [messages]);
		var messages = ['error', 'success', 'info', 'warning']
			.map(function(type) { return {type: type, messages: messages.filter(msg => msg.type == type)} });

		var $dl = ($container.children('dl').length ?
					$container.children('dl') :
					$('<dl>')
						.attr('id', 'system-message')
						.attr('role', 'alert')
						.appendTo($container));
	
		$.each(messages, function (ind, item) {
			// Add descriptor
			if ($dl.children('dt.' + item['type']).length) {
				var $list = $dl.children('dd.' + item['type']).children('ul');
			} else if (item['messages'].length) {
				$('<dt>')
					.addClass(item['type'])
					.html(item['type'].charAt(0).toUpperCase() + item['type'].slice(1))
					.appendTo($dl);
				var $dd = $('<dd>')
					.addClass(item['type'])
					.addClass('message')
					.attr('data-duration', (item['type'] == 'success' ? success_duration : Infinity))
					.appendTo($dl);
				var $list = $('<ul>').appendTo($dd);
			}

			$.each(item['messages'], function (ind, item) {
				$('<li>')
					.html(item['message'])
					.appendTo($list);
			});
		});
	
		$(document).trigger('renderMessages');
	};

	/**
	 * Remove messages
	 *
	 * @return  void
	 */
	TPL.removeMessages = function($container = $('#fancybox-message-container')) {
		$container.children('dl').remove();
	}

	/**
	 * Set message durations (click to close for most; success messages fade away)
	 */
	TPL.setMessageDurations = function($container = $('#fancybox-message-container')) {
		if ($container.children('dl').children().length) {
			$container.find('dd').each(function(i, el) {
				var duration = $(el).data('duration');
				if (!duration) {
					// Set to defaults
					duration = ($(el).hasClass('success') ? 4000 : Infinity);
				}
				if (duration === Infinity) {
					$('<button>')
					.addClass('close')
					.appendTo($(el));
				} else {
					var tm = Math.max(($(el).text().length * 10) + 500, duration);
					clearTimeout($(el).data('fade'));
					$(el).data('fade', setTimeout(function() {
						$(el).removeData('fade');
						$(el).fadeOut(500);
					}, tm));
					clearTimeout($(el).data('out'));
					$(el).data('out', setTimeout(function() {
						$(el).removeData('out');
						$(el).siblings('dt.success').remove();
						$(el).remove();
					}, tm+500));
				}
			});
		}
	}
}( window.TPL = window.TPL || {}, jQuery ));

HUB.template = TPL;

jQuery(document).ready(function(jq){
	var $ = jq;

	// group pane toggle
	$('#group a.toggle, #group-info .close').on('click', function(event) {
		event.preventDefault();

		$('#group-info').slideToggle('normal');
		$('#group-body').toggleClass('opened');
	});

	// Template
	HUB.template.body = $('body');

	// Account panel
	HUB.template.accountLogin = $('.user-account-link:not(.loggedin)');
	HUB.template.accountTrigger = $('.user-account-link.loggedin');
	HUB.template.accountPanel = $('.account-details');
	HUB.template.accountCloseTrigger = HUB.template.accountPanel.find('.close');

	$(HUB.template.accountLogin).fancybox({
		type: 'ajax',
		tpl: {
			wrap:'<div class="fancybox-wrap"><div class="fancybox-skin"><div class="fancybox-outer"><div id="fancybox-message-container"></div><div id="sbox-content" class="fancybox-inner"></div></div></div></div>'
		},
		beforeLoad: function() {
			// Add login.css
			var link = document.createElement('link');
			link.setAttribute('id', 'login_css');
			link.setAttribute('rel', 'stylesheet');
			link.setAttribute('href', '/app/templates/bmc/html/com_users/login.css');
			document.head.appendChild(link);

			// Add register.css
			var link = document.createElement('link');
			link.setAttribute('id', 'register_css');
			link.setAttribute('rel', 'stylesheet');
			link.setAttribute('href', '/core/components/com_members/site/assets/css/register.css');
			document.head.appendChild(link);

			// Add providers.css
			var link = document.createElement('link');
			link.setAttribute('id', 'providers_css');
			link.setAttribute('rel', 'stylesheet');
			link.setAttribute('href', '/core/components/com_login/site/assets/css/providers.css');
			document.head.appendChild(link);

			// Add google.css
			var link = document.createElement('link');
			link.setAttribute('id', 'google_css');
			link.setAttribute('rel', 'stylesheet');
			link.setAttribute('href', '/core/plugins/authentication/google/assets/css/google.css');
			document.head.appendChild(link);

			href = $(this).attr('href');
			$(this).attr('href', href.nohtml());
		},
		beforeShow: function(current, upcoming) {
			// Remove header (if needed)
			// document.getElementById('content-header').remove();
		},
		afterClose: function () {
			// Remove unnecessary css elements
			document.getElementById('login_css').remove();
			document.getElementById('register_css').remove();
			document.getElementById('providers_css').remove();
			document.getElementById('google_css').remove();
		}
	});

	$(document).on('click', '.fancybox-inner a:not(.primary):not(.google)', function(e) {
		e.preventDefault();
		$.get($(this).attr('href').nohtml(), {}, function(response) {
			// Remove header (if needed)
			// var doc = new DOMParser().parseFromString(response, "text/html");
			// var header = doc.getElementsByTagName('header');
			// header[0].parentNode.removeChild(header[0]);
			// $('div#sbox-content').html(doc.body.innerHTML);
			$('div#sbox-content').html(response);
			$.fancybox.update();
		});
	})
	.on('submit', 'form', function(e) {
		e.preventDefault();

		var el = $(this);
		var formData = new FormData(this);
		$.ajax({
			method: 'POST',
			url: $(this).attr('action').nohtml(),
			data: formData,
			processData: false,
			contentType: false,
			dataType: "json",
			success: function(response, status, xhr) {
				// Need to refactor /community/members/reminding?controller=credentials
				//  to return error message using TPL notifications (see comments.js)
				console.log(response);
				if (response.error) {
					// Convert
					message = { type: 'error', message: response.error };
					HUB.template.renderMessages(message, Infinity, $container = $('#fancybox-message-container')); // This example makes the success messages sticky
				} else if (response.success) {
					$.fancybox.close(true);
					location.href = response.redirect;
				} // else {
				// 	$.get(response.freturn, {}, function(response) {
				// 		$('div#sbox-content').html(response);
				// 		$.fancybox.update();
				// 	});
				// }
			},
			error: function(xhr, status, error) {
				console.log("Error!");
			}
		});
	});

	$(HUB.template.accountTrigger).on('click', function(e) {
		if(!(HUB.template.accountPanel.hasClass('open'))) {
			HUB.template.closeAllPanels();
			HUB.template.openAccountPanel();
		}
		else {
			HUB.template.closeAllPanels();
		}

		e.preventDefault();
	});

	$(HUB.template.accountCloseTrigger).on('click', function(e) {
		HUB.template.closeAllPanels();
		e.preventDefault();
	});

	HUB.template.openAccountPanel = function() {
		HUB.template.body.addClass('panel-open');
		HUB.template.accountPanel.addClass('open');
	};

	HUB.template.closeAccountPanel = function() {
		HUB.template.accountPanel.removeClass('open');
	};

	// Member dash
	HUB.template.dashTrigger = $('.subnav-membership > .toggle');
	HUB.template.dashPanel = $('.group-dash');
	HUB.template.dashCloseTrigger = HUB.template.dashPanel.find('.close');

	$(HUB.template.dashTrigger).on('click', function(e) {
		if(!(HUB.template.dashPanel.hasClass('open'))) {
			HUB.template.closeAllPanels();
			HUB.template.openDashPanel();
		}
		else {
			HUB.template.closeAllPanels();
		}

		e.preventDefault();
	});

	$(HUB.template.dashCloseTrigger).on('click', function(e) {
		HUB.template.closeAllPanels();
		e.preventDefault();
	});

	HUB.template.openDashPanel = function() {
		HUB.template.body.addClass('panel-open');
		HUB.template.dashPanel.addClass('open');
	};

	HUB.template.closeDashPanel = function() {
		HUB.template.dashPanel.removeClass('open');
	};

	// Escape button to the rescue for those who like to press it in a hope to close whatever is open
	$(document).keyup(function(e) {
		if(e.keyCode == 27) {
			HUB.template.closeAllPanels();
		}
	});

	HUB.template.closeAllPanels = function() {
		HUB.template.closeAccountPanel();
		HUB.template.closeDashPanel();
		HUB.template.body.removeClass('panel-open');
	};

	HUB.template.overlay = $('.hub-overlay');
	$(HUB.template.overlay).on('click', function(e) {
		HUB.template.closeAllPanels();
		e.preventDefault();
	});

	$(window).resize(function() {
		HUB.template.closeAllPanels();
	});

	HUB.template.setMessageDurations();
	$(document).on("renderMessages", function() {
		HUB.template.setMessageDurations();
	});

	$(document).on('click', 'dd.message button.close', function(el) {
		el.preventDefault();

		$(this).parent().prev().remove();
		$(this).parent().remove();
	});

	HUB.template.init = function() {
	};

	HUB.template.init();
});
