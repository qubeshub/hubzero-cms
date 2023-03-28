jQuery(document).ready(function() {

  String.prototype.nohtml = function () {
  	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
  };

  // Informational button
  $("a.modal-link").fancybox({
    maxWidth : 450
  });
});
