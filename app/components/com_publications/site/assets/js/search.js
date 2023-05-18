$(document).ready(function() {
  String.prototype.nohtml = function () {
  	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
  };

  // Submit Resource Btn
	$('#submit-resource').fancybox({
      type: 'ajax',
      scrolling: true,
      autoSize: false,
      fitToView: true,
      titleShow: false,
      tpl: {
        wrap:'<div class="fancybox-wrap"><div class="fancybox-skin"><div class="fancybox-outer"><div id="sbox-content" class="fancybox-inner"></div></div></div></div>'
      },
      beforeLoad: function() {
        href = $(this).attr('href');
        $(this).attr('href', href.nohtml());
      }
  });
});
