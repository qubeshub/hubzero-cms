/**
 * @package     hubzero-cms
 * @file        plugins/groups/projects/projects.js
 * @copyright   Copyright (c) 2005-2023 The Regents of the University of California.
 * @license     http://opensource.org/licenses/MIT MIT
 */

String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};

if (!jq) {
	var jq = $;
}

jQuery(document).ready(function(jq){
    var $ = jq;
    
    $('a.modal').fancybox({
        type: 'ajax',
        autoSize : false,
        width: '80%',
        height : '95%',
        beforeLoad: function() {
            href = $(this).attr('href');
            $(this).attr('href', href.nohtml());
        },
        ajax: {
            complete: function () {
                // Scroll to anchor tags within a modal
                $('.modal-menu-item').on('click', function (e) {
                    e.preventDefault()

                    const href = $(this).attr('href')
                    const anchor = $('.fancybox-inner').find(`div${href}`)

                    $('.fancybox-inner').animate({
                        scrollTop: anchor.offset().top
                    }, 1000)
                    return false
                })
                
                //Copy text using the copy icon
                $(function () {
                    $(".copied-toast").hide();
                    $(".copy-me").click(function () {
                        var copiedtext = $(this).next(".copy-text").text();
                        if (navigator.clipboard) {
                            navigator.clipboard.writeText(copiedtext)
                                .then(() => {
                                    $(".copied-toast").text("Copied!").show().fadeOut(1200);
                                })
                                .catch((error) => {
                                    $(".copied-toast").text("Not copied!").show().fadeOut(1200);
                                });
                        } else {
                            $(".copied-toast").text("Not copied!").show().fadeOut(1200);
                        }
                    });
                });
            }
        }
    })
});