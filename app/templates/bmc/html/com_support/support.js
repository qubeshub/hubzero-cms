$(document).ready(function () {
    $('.kb-container').accordion({
        active: false,
        collapsible: true,
        heightStyle: "content",
        icons: false
    })

     $('.ui-accordion-header').on('click', function () {
		var $icon = $(this).find('.hz-icon');

		$('.ui-accordion-header').not($(this)).find('.hz-icon').removeClass('icon-chevron-up').addClass('icon-chevron-down');
		if ($(this).hasClass('ui-accordion-header-active')) {
			$icon.removeClass('icon-chevron-down');
			$icon.addClass('icon-chevron-up');
		} else {
			$icon.removeClass('icon-chevron-up');
			$icon.addClass('icon-chevron-down');
		}
	});
})
