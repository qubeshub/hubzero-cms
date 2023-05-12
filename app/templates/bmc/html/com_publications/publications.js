// Shorten list of content files
$(document).ready(function () {
    const $list = $('.file > .pub-content > .element-list').children();
    
    if ($list.length > 3) {
        const $thirdItem = $('.file > .pub-content > .element-list >li:nth-child(3)')
        const $moreLink = $('.more-files')
        $thirdItem.nextAll().addClass('hide')
        $moreLink.removeClass('hide')
    }

    // Show more
	$('a.more-content').on('click', function(e) {
        e.preventDefault();

        $(this).closest('.abstract-preview').addClass('hide');
        $($(this).attr('href')).removeClass('hide');
    })

    // Tags
    const topLevelTag = $('.top-level')
    topLevelTag.each(function () {
        const subTags = $(this).find('.tag')
        let counter = subTags.length

        subTags.each(function () {
            $(this).css('z-index', counter)
            counter--
        })
    })
})

