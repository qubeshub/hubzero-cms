String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};

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

    $('a.tab').on('click', function (e) {
		e.preventDefault()
    
        var url = $(this).attr('href')
        var container = $('.content-display');

        window.history.pushState(null, '', url)

        // Update active class on menu
        $(this).parent().addClass('active')
        $(this).prepend(`<span class="fc fc-caret-right"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 48">
        <path d="M20.4,0.3c-1.2,0.5-1.8,1.5-1.8,2.8v41.8c0,1.3,0.6,2.2,1.8,2.8c1.2,0.5,2.3,0.3,3.2-0.6l20.9-20.9c0.6-0.7,0.9-1.4,0.8-2.2
	    c0-0.7-0.3-1.4-0.8-2.1L23.6,1C22.7,0,21.6-0.2,20.4,0.3z"></path></svg></span>`)
        $('a.tab').not($(this)).parent().removeClass('active')
        $('a.tab').not($(this)).children('.fc-caret-right').remove()
      

        container.load(`${url} .content-display > *`, function () {

            if (window.location.href.indexOf('usage') > -1) {
                $('<style type="text/css">@import url("/app/plugins/publications/usage/assets/css/usage.css")</style>').appendTo("head");
                $.getScript('/app/plugins/publications/usage/assets/js/usage.js')
            }
            if (window.location.href.indexOf('comments') > -1) {
                $('<style type="text/css">@import url("/app/plugins/publications/comments/assets/css/comments.css")</style>').appendTo("head");
                $.getScript('/app/plugins/publications/comments/assets/js/comments.js')
            }
        })
        
	})
})

