/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

if (!jq) {
	var jq = $;
}

jQuery(document).ready(function(jq){
	var $ = jq,
		containers = $('.mod_mygroupsmini');

	if (containers.length <= 0) {
		return;
	}

	containers.each(function(i, container) {
		var tabs   = $(container).find('.tab_title'),
			panels = $(container).find('.tab_panel');

		tabs.each(function(i, item) {
			$(item).on('click', function () {
				var tab = $(this);

				tabs.each(function(i, item) {
					$(this).removeClass('active');
				});
				panels.each(function(i, item) {
					$(this).removeClass('active');
				});

				var panel = $('#' + tab.attr('rel'));
				panel.addClass('active');
				tab.addClass('active');
			});
		});
	});
});
