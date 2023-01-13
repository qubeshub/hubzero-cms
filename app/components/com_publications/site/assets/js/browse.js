String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};
jQuery.fn.reverse = [].reverse;

$(document).ready(function () {
    $('.accordion-section').on('click', function () {
        $(this).toggleClass('active')
        $(this).next().toggle('slow')
        $(this).children('.accord-trigger').attr('aria-expanded', function (i, attr) {
            return attr == 'false' ? 'true' : 'false'
        })
        $(this).find('.hz-icon').toggleClass('icon-chevron-down icon-chevron-up')
        
    })
    let tags = []
    $(document).on('change', '#filter-form :checkbox', function () {
        $('input[name=limitstart]').val(0); // Reset pagination
        let tagItem = {
                tag: $(this).attr('id'),
                name: $(this).siblings('.tagfa-label').text()
            }
        const parent = $(this).closest('.filter-option')
        const grandparent = parent.parents('.filter-option')

        if ($(this).prop('checked')) {
            if (parent.children('ul.option')) {
                parent.children('ul.option').prev().val('active');
            }
            if (grandparent) {
                grandparent.reverse().each(function () {
                    const checkbox = $(this).children().children()
                    let tagItem = {
                        tag: checkbox.attr('id'),
                        name: checkbox.siblings('.tagfa-label').text()
                    }
                    if (!checkbox.is(':checked')) {
                        checkbox.prop('checked', true)
                        tags.push(tagItem)
                        $('.active-filters').append(`<li class='checked-tag ${tagItem.tag}'><button class='active-filter-tag'>${tagItem.name}<span class='close-active-filter-tag'></span></button></li>`)
                    }
                })
            }
            tags.push(tagItem)
            $('.active-filters').append(`<li class='checked-tag ${tagItem.tag}'><button class='active-filter-tag'>${tagItem.name}<span class='close-active-filter-tag'></span></button></li>`)
        } else {
            if (parent.children('ul.option')) {
                const checkedChildren = parent.children('ul.option').find('input:checked')
                checkedChildren.each(function () {
                    let tagItem = {
                        tag: $(this).attr('id'),
                        name: $(this).siblings('.tagfa-label').text()
                    }
                    $(this).prop('checked', false)
                    for (i = 0; i < tags.length; i++) {
                        if (tags[i].tag === $(this).attr('id')) {
                            tags.splice(i, 1);
                            break;
                        }
                    }
                    $(`.${tagItem.tag}`).remove()
                })
            } 
            for (i = 0; i < tags.length; i++) {
                    if (tags[i].tag === $(this).attr('id')) {
                        tags.splice(i, 1);
                        break;
                    }
                }
            $(`.${tagItem.tag}`).remove()
        }
        sessionStorage.setItem('tags', JSON.stringify(tags))
        // console.log(tags)
        
        $('#filter-form').submit();
        checkActiveFilters()
    });

    let timer;
    $(document).on('keypress', 'input[name=search]', function (e) {
        e.stopPropagation();
        
        if (e.key === 'Enter') {
            e.preventDefault();

            // Throttle form submission
            clearTimeout(timer);
            timer = setTimeout(function(){
                $('input.btn[id=search-btn]').click();                
            },1000)
        }
        checkActiveFilters()
    });

    $(document).on('click', 'input.btn[id=search-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#filter-form').submit();
        checkActiveFilters()
    });

    const checkActiveFilters = () => {
        if ($('.active-filters').children('li').length) {
            $('.active-filters-wrapper > h6').css('display', 'block')
        } else {
            $('.active-filters-wrapper > h6').css('display', 'none')
        }
    }

    const updateUrl = () => {
        var url = '/publications/browse';
        
        var limit = $('#limit').val();
        var limitstart = $('input[name=limitstart]').val();
        var queryParams = '?search=';
        var limitParams = `&limit=${limit}&limitstart=${limitstart}`;
        var query = $('#search').val();
        var encodedTerms = encodeURIComponent(query).replace(/%20/g, '+');
        var filters = $.merge(
            $('li:has(input:checked) > ul:not(:has(input:checked))').siblings('label').children('input'), 
            $('label:only-child:has(input:checked) > input'))
            .map(function() { return $(this).val(); })
            .get()
            .join(',');
        var filterParams = (filters !== '' ? `&fl=${filters}` : '');

        urlToFetch = `${url}${queryParams}${encodedTerms}${filterParams}${limitParams}`;

        window.history.pushState({href: urlToFetch}, '', urlToFetch);
    }

    $(document).on('submit', 'form', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
    
        // console.log(...formData); // https://stackoverflow.com/questions/25040479/formdata-created-from-an-existing-form-seems-empty-when-i-log-it

        // Save form data in sessionStorage and add active filter
        for (const [key, value] of formData) {
            // console.log('»', key, value)

            if (key === 'keywords') {
                let keys = []
                let inputs = $('.token-input-token-act')
        
                inputs.each(function () {
                    let item = {
                        data: $(this).attr('data-id'),
                        name: $(this).attr('data-name')
                    }
                    keys.push(item)
                    if (!$(`.${item.data}`).length) {
                        $('.active-filters').append(`<li class='keyword-tag ${item.data}'><button class='active-filter-tag'>${item.name}<span class='close-active-filter-tag'></span></button></li>`)
                    }
                })
                
                // Remove active tags if keyword removed via form
                let activeFilters = $('.keyword-tag')
                if (activeFilters.length > keys.length) {
                    activeFilters.each(function () {
                        const findTag = $(this).attr('class').split(' ')[1]
                        if (keys.some(key => key.data === findTag)) {
                            return
                        } else {
                            $(this).remove()
                        }
                    })
                }
               
                sessionStorage.setItem('keywords', JSON.stringify(keys))
            }

            if (key === 'search') {
                let search = ''
                search = value
                if (search.length > 0) {
                    sessionStorage.setItem('search', JSON.stringify(search))
                        if (!$('.search-data').length) {
                            $('.active-filters').append(`<li class='search-data'><button class='active-filter-tag'>${search}<span class='close-active-filter-tag'></span></button></li>`)
                        } else {
                        if ($('.search-data').children().text() !== search) {
                            $('.search-data').remove()
                            $('.active-filters').append(`<li class='search-data'><button class='active-filter-tag'>${search}<span class='close-active-filter-tag'></span></button></li>`)
                        }
                    }
                } else {
                    sessionStorage.removeItem('search')
                    $('.search-data').remove()
                }
            }
        }
 
        updateUrl();
        
        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response, status, xhr) {
                // Save results to sessionStorage
                sessionStorage.setItem('results', JSON.stringify(response))
            
                $('div.card-container').html(JSON.parse(sessionStorage.getItem('results')).html.cards);
                $('div#accord').html(JSON.parse(sessionStorage.getItem('results')).html.filters);

                // Get results total and display them at top of the search results
                let $totals = $('.counter').text()
                $('.total-results').text($totals)

                // Reinitiate accordion
                $('.accordion-section').on('click', function () {
                    $(this).toggleClass('active')
                    $(this).next().toggle('slow')
                    $(this).children('.accord-trigger').attr('aria-expanded', function (i, attr) {
                        return attr == 'false' ? 'true' : 'false'
                    })
                    $(this).find('.hz-icon').toggleClass('icon-chevron-down icon-chevron-up')
                })

                // Open panels with checked children
                $('.filter-panel').each(function () {
                    if ($(this).find('input:checked').length) {
                        $(this).prev().addClass('active')
                        $(this).prev().children('.accord-trigger').attr('aria-expanded', 'true')
                        $(this).css('display', 'block')
                    }
                })
                
                // checkActiveFilters()
                console.log(response);

                if (response.status.length) {
                    // TPL.renderMessages(response.status, 7000); // Little longer for permalink to display
                    console.log(response.status);
                }
            },
            error: function(xhr, status, error) {
            }
        });
    });

    // Remove active filters
    $('#filter-form').on('click', '.active-filter-tag', function (e) {
        e.preventDefault()
        let activeFilter = $(this).parent()
        let getType = $(this).parent().attr('class').split(' ')[0]
        let getFilter = $(this).parent().attr('class').split(' ')[1]

        if (getType === 'checked-tag') {
            // Update sessionStorage
            let storedTags = JSON.parse(sessionStorage.getItem('tags'))
            for (i = 0; i < storedTags.length; i++) {
                if (storedTags[i].tag === getFilter) {
                    storedTags.splice(i, 1);
                    break;
                }
            }

            // Check for children filters
            const filter = $('.filter-panel').find(`#${getFilter}`)
            const parent = filter.closest('.filter-option')
            if (parent.children('ul.option')) {
                const checkedChildren = parent.children('ul.option').find('input:checked')
                checkedChildren.each(function () {
                    const tagID = $(this).attr('id')
                    $(this).prop('checked', false)
                    for (i = 0; i < storedTags.length; i++) {
                        if (storedTags[i].tag === $(this).attr('id')) {
                            storedTags.splice(i, 1);
                            break;
                        }
                    }
                    $(`.${tagID}`).remove()
                })
            }
            sessionStorage.setItem('tags', JSON.stringify(storedTags))

            filter.prop('checked', false)
            $('#filter-form').submit()
            activeFilter.remove()
        }

        if (getType === 'keyword-tag') {
            let storedKeywords = JSON.parse(sessionStorage.getItem('keywords'))
            let newValues = []
            for (i = 0; i < storedKeywords.length; i++) {
                
                if (storedKeywords[i].data === getFilter) {
                    storedKeywords.splice(i, 1)
                    break
                }
            }
            sessionStorage.setItem('keywords', JSON.stringify(storedKeywords))
            $(`.token-input-token-act[data-id='${getFilter}']`).remove()
          
            for (i = 0; i < storedKeywords.length; i++) {
                newValues.push(storedKeywords[i].data)
            }
            $('#keywords').val(newValues)
            $('#filter-form').submit()
            activeFilter.remove()
        }

        if (getType === 'search-data') {
            sessionStorage.removeItem('search')
            $('#search').val('')
            $('#filter-form').submit()
            activeFilter.remove()
        }

        checkActiveFilters();
    })

    // Restore search results if saved in sessionStorage
    if (sessionStorage.getItem('results')) {
        // Not working
        $('div.card-container').html(JSON.parse(sessionStorage.getItem('results')).html.cards);

        if (sessionStorage.getItem('tags')) {
            let tags = JSON.parse(sessionStorage.getItem('tags'))
            for (i = 0; i < tags.length; i++) {
                const parent = $(`#${tags[i].tag}`).closest('ul.option')
                const child = $(`#${tags[i].tag}`).closest('.filter-option').children('ul.option')
                $('.filter-panel').find(`#${tags[i].tag}`).prop('checked', true)
                parent.css('display', 'block')
                child.css('display', 'block')
                parent.closest('.filter-panel').prev().addClass('active')
                parent.closest('.filter-panel').prev().children('.accord-trigger').attr('aria-expanded', 'true')
                parent.closest('.filter-panel').addClass('active')
                $('.active-filters').append(`<li class='checked-tag ${tags[i].tag}'><button class='active-filter-tag'>${tags[i].name}<span class='close-active-filter-tag'></span></button></li>`)
            }
        }

        if (sessionStorage.getItem('keywords')) {
            let keys = JSON.parse(sessionStorage.getItem('keywords'))
            for (i = 0; i < keys.length; i++) {
                $('#keywords').val(keys[i].name)
                $('.active-filters').append(`<li class='keyword-tag ${keys[i].data}'><button class='active-filter-tag'>${keys[i].name}<span class='close-active-filter-tag'></span></button></li>`)
            }
        }

        if (sessionStorage.getItem('search')) {
            let search = JSON.parse(sessionStorage.getItem('search'))
            $('#search').val(search)
            if (search.length) {
                $('.active-filters').append(`<li class='search-data'><button class='active-filter-tag'>${search}<span class='close-active-filter-tag'></span></button></li>`)
        }
            }
    }

    // Open filter panel if checkbox is checked on load
    $('#filter-form :checkbox').each(function () {
        if ($(this).prop('checked')) {
            $(this).closest('.filter-panel').removeAttr('style')
        }
    })

    // Reset filters
    $('#reset-btn').on('click', function () {
        if (sessionStorage.getItem('results')) {
            sessionStorage.removeItem('results')
            sessionStorage.removeItem('search')
            sessionStorage.removeItem('keywords')
            sessionStorage.removeItem('tags')
            $('.active-filters > li').remove()
            $('.active-filters-wrapper > h6').css('display', 'none')
            $('.search-data').remove()
            $('#accord').find('input:checked').prop('checked', false)
            $('.token-input-token-act').remove()
            // $('#filter-form').submit()
        }
        tags = []
    })

    // BEGIN: Ajax-ify pagination
    $(document).on('click', 'a.pagenav', function(e) {
        e.preventDefault();

        params = Object.fromEntries(this.href.split(/[?&]/).slice(1).map(s => s.split('=')));
        // console.log(params)
        $('input[name=limitstart]').val(params.start);
        $('input[name=limit]').val(params.limit);
        $('#filter-form').submit();
        return false;
    });

    // On load
    $('#limit').removeAttr('onchange').bind('change', function(e) {
        e.preventDefault();

        $('#filter-form').submit();

        return false;
    });

    // Get results total and display them at top of the search results
    let $totals = $('.counter').text()
    $('.total-results').text($totals)

    $(document).on('DOMNodeInserted', 'nav.pagination', function (e) {
        $('#limit').removeAttr('onchange').bind('change', function(e) {
            e.preventDefault();

            $('#filter-form').submit();

            return false;
        });
    });

    $('ul.entries-menu a').click(function() {
        $('ul.entries-menu a.active').toggleClass('active');
        $(this).toggleClass('active');
        $('input[name=sortby]').val($(this).data('value'));

        $('#filter-form').submit();
    });

    // Mobile responsiveness
    const filterContainer = $('.filter-container'),
        mobileFilter = $('.browse-mobile-btn'),
        btnIcon = mobileFilter.children('.hz-icon'),
        filterText = mobileFilter.children('span');
    
    mobileFilter.on('click', function () {
        filterContainer.toggleClass('mobile-active')
        btnIcon.toggleClass('icon-filter icon-remove')

        if (filterContainer.hasClass('mobile-active')) {
            $('.browse-mobile-btn-wrapper').css('position', 'fixed')
            filterText.text('Close')
        } else {
            filterText.text('Filter')
            $('.browse-mobile-btn-wrapper').css('position', 'sticky')
        }
    })

    $(window).on('resize', function () {
        if (filterContainer.hasClass('mobile-active') && !mobileFilter.is(':visible')) {
            filterContainer.removeClass('mobile-active')
        }
    })
});