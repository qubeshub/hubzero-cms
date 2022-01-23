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
		container = $('#mycollectionsmini');
	
	String.prototype.nohtml = function () {
		return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
	};

	container.find('a.comment').fancybox({
		type: 'ajax',
		autoSize: false,
		fitToView: false,
		titleShow: false,
		autoCenter: false,
		width: '100%',
		height: 'auto',
		topRatio: 0,
		tpl: {
			wrap:'<div class="fancybox-wrap post-modal"><div class="fancybox-skin"><div class="fancybox-outer"><div id="post-content" class="fancybox-inner"></div></div></div></div>'
		},
		beforeLoad: function() {
			// Add collections.css
			var link = document.createElement('link');
			link.setAttribute('id', 'collections_css');
			link.setAttribute('rel', 'stylesheet');
			link.setAttribute('href', '/app/components/com_collections/site/assets/css/collections.css');
			document.head.appendChild(link);

			// Add collections.js
			var link = document.createElement('script');
			link.setAttribute('id', 'collections_js');
			link.setAttribute('type', 'text/javascript');
			link.setAttribute('src', '/app/components/com_collections/site/assets/js/collections.js');
			document.head.appendChild(link);

			$(this).attr('href', $(this).attr('href').nohtml());
		},
		beforeShow: function() {
			$(document).trigger('ajaxLoad');
		},
		afterShow: function() {
			var el = this.element;
			if ($('#commentform').length > 0) {
				$('#post-content').on('submit', '#commentform', function(e) {
					e.preventDefault();
					$.post($(this).attr('action'), $(this).serialize(), function(data) {
						$('#post-content').html(data);
						$.fancybox.update();

						var metadata = $(el).parent().parent(); //$('#p' + $(el).attr('data-id')).find('.meta');
						if (metadata.length) {
							$.getJSON(metadata.attr('data-metadata-url').nohtml(), function(data) {
								metadata.find('.likes').text(data.likes);
								metadata.find('.comments').text(data.comments);
								metadata.find('.reposts').text(data.reposts);
							});
						}
					});
				});
			}
		},
		afterClose: function() {
			$('#collections_css').remove();
			$('#collections_js').remove();
		},
		helpers: {
			overlay: {
				css: { background: 'rgba(200, 200, 200, 0.95)' }
			}
		}
	});
});
