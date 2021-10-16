// Sticky Header
window.addEventListener("DOMContentLoaded", function () {
    const observer = new IntersectionObserver(function (entries) {
        // no intersection with screen
        // console.log(entries[0].intersectionRatio)
        if(entries[0].intersectionRatio < 1)
            document.querySelector(".title-wrapper").classList.add("isSticky")
        // fully intersects with screen
        else if(entries[0].intersectionRatio === 1)
            document.querySelector(".title-wrapper").classList.remove("isSticky")
    }, {
        threshold: [0, 1]
    });

    observer.observe(document.querySelector(".title-wrapper-holder"));

    // Adjust header placement on scroll
    document.querySelector('.content-panel').onscroll = function () {
        const   subMenu = document.querySelector(".sub"),
                header = document.querySelector('.title-wrapper')
        if (header.classList.contains('isSticky') && subMenu.classList.contains('out')) {
            header.style.top = "56px"
        } else if (header.classList.contains('isSticky') && !subMenu.classList.contains('out')) {
            header.style.top = "113px"
        }
    }
 
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

