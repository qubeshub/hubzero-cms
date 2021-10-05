// Sticky Header
window.addEventListener("DOMContentLoaded", function () {
    // get the sticky element
    const stickyElm = document.querySelector('.title-wrapper')

    const observer = new IntersectionObserver(
        ([e]) => e.target.classList.toggle('isSticky', e.intersectionRatio < 1),
        {
            rootMargin: '-56.36px 0px 0px 0px',
            threshold: [1]
        }
    );
    

    observer.observe(stickyElm)
})

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
})

