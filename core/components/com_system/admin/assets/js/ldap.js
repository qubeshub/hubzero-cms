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
		_DEBUG = false;

	_DEBUG = document.getElementById('system-debug') ? true : false;

	$('#deleteUsers').on('click', function(e){
		return Hubzero.submitbutton('deleteUsers');
	});

	$('#exportGroups').on('click', function(e){
		return Hubzero.submitbutton('exportGroups');
	});

	$('#deleteGroups').on('click', function(e){
		return Hubzero.submitbutton('deleteGroups');
	});

	var BatchRecords = new function() {
		this.timer     = null;
		this.checker   = null;
		this.start     = 0;
		this.processed = 0;

		this.init = function() {

			var btn = $('#exportUsers'),
				self = this;

			if (btn.length) {
				btn.on('click', function(e) {
					e.preventDefault();

					var delay = parseInt(btn.attr('data-delay'));
					delay = delay ? delay : 3;

					self.process(btn.attr('data-progress'));

					self.checker = setInterval(function(){
						self.process(btn.attr('data-progress'));
					}, 1000 * delay);
				});

				this.progress();
			}
		}

		this.process = function(url) {
			var self = this;

			if (_DEBUG) {
				window.console && console.log('calling: ' + url + self.start);
			}

			// start processing
			$.ajax({
				type: 'get',
				dataType: 'json',
				url: url + self.start,
				complete: function(data) {
				},
				error: function(jqXHR, textStatus, errorThrown) {
					self.stopImportProgressChecker();

					if (jQuery.growl) {
						$.growl.settings.displayTimeout = 0;
						$.growl('', '<dl id="system-message"><dt class="error">Error</dt><dd class="error message">There was an error trying to process the records.</dd></dl>');
					}
				},
				success: function(data) {
					var percent = 0;

					if (_DEBUG) {
						window.console && console.log(data);
					}

					if (data && typeof data.processed !== 'undefined') {
						self.processed += parseInt(data.processed);
						percent = (self.processed / parseInt(data.total)) * 100;
						if (data.start !== 'undefined') {
							if (data.start == self.start) {
								self.stopImportProgressChecker();
							} else {
								self.start = data.start;
							}
						}

						self.setProgress(percent);
					} else {
						self.stopImportProgressChecker();

						$.growl.settings.displayTimeout = 0;
						$.growl('', '<dl id="system-message"><dt class="error">Error</dt><dd class="error message">There was an error trying to process the records.</dd></dl>');
					}

					if (typeof data.errors !== 'undefined' && data.errors.length > 0) {
						$.growl.settings.displayTimeout = 0;
						$.growl('', '<dl id="system-message"><dt class="error">Error</dt><dd class="error message">' + data.errors.join('<br />') + '</dd></dl>');
					}

					if (percent >= 100) {
						self.stopImportProgressChecker();

						$.growl('', '<dl id="system-message"><dt class="success">Success</dt><dd class="success message">Records processed.</dd></dl>');
					}
				}
			});
		}

		this.progress = function() {
			$('.progress').progressbar({
				value: 0.01
			});
		};

		this.setProgress = function(newValue) {
			if (_DEBUG) {
				window.console && console.log('setting progress: ' + Math.round(newValue));
			}
			$('.progress').progressbar("value", newValue);
			$('.progress-percentage').html(Math.round(newValue) + '%');
		};

		this.stopImportProgressChecker = function() {
			clearTimeout(this.checker);
			if (_DEBUG) {
				window.console && console.log('stopped progress');
			}
		};
	};

	BatchRecords.init();
});
