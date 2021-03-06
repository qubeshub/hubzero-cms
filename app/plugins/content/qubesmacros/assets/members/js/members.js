/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$(document).ready(function () {
    $('.show-more').on('click', function () {
        var $bio = $(this).siblings('.member-bio');
        
        $bio.toggleClass('is-truncated').toggleClass('not-truncated');
        
        $(this).attr('aria-expanded', function (i, attr) {
            return attr == 'true' ? 'false' : 'true';
        });

        $(this).toggleClass('icon-plus').toggleClass('icon-minus');

        $bio.animate({ scrollTop: 0 }, 'fast');
    });
});