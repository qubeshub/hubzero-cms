String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};
jQuery.fn.reverse = [].reverse;

$(document).ready(function () {
    // Accordion
    $('.accordion-section').on('click', function () {
        $(this).toggleClass('active')
        $(this).next().toggle('slow')
        $(this).children('.accord-trigger').attr('aria-expanded', function (i, attr) {
            return attr == 'false' ? 'true' : 'false'
        })
        $(this).find('.hz-icon').toggleClass('icon-chevron-down icon-chevron-up')
        
    })

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

    const checkActiveFilters = () => {
        if ($('.active-filters').children('li').length) {
            $('.active-filters-wrapper > h6').css('display', 'block')
        } else {
            $('.active-filters-wrapper > h6').css('display', 'none')
        }
    }

    // Open filter panel if checkbox is checked on load
    $('#filter-form :checkbox').each(function () {
        if ($(this).prop('checked')) {
            $(this).closest('.filter-panel').css('display', 'block')
            const parent = $(this).closest('.filter-option') // Get the <li>
            parent.children('input').val('active')
            parent.children('ul.option').css('display', 'block')
            // Get the accordion section
            const section = $(this).closest('.filter-panel').prev()
            section.addClass('active') 
        }
    })

    let tags = []
    let fl = []

    // If loading with incoming fl
    if ($('input#fl').val().length) {
        const incFL = $('input#fl').val()

        // Check for grandparents
        const incParent = $('#accord').find(`input[value="${incFL}"]`).closest('.filter-option')
        const incGrandparent = incParent.parents('.filter-option')
        if (incGrandparent) {
            incGrandparent.reverse().each(function () {
                const getFL = $(this).children().children()
                fl.push(getFL.attr('value'))
                tags.push(getFL.siblings('.tagfa-label').text())

                // Add active filter
                $('.active-filters').append(`<li class='checked-tag' value='${getFL.attr('value')}'><button class='active-filter-tag'>${getFL.siblings('.tagfa-label').text()}<span class='close-active-filter-tag'></span></button></li>`)
            })
        }
        // Get active tag
        const incTag = $('#accord').find(`input[value="${incFL}"]`).siblings('.tagfa-label').text()
        fl.push(incFL)
        tags.push(incTag)

        // Add active filter
        $('.active-filters').append(`<li class='checked-tag' value='${incFL}'><button class='active-filter-tag'>${incTag}<span class='close-active-filter-tag'></span></button></li>`)

        // Update hidden input values
        $('input#fl').val(fl)
        $('input#active-tags').val(tags)

        checkActiveFilters()
    }

    // If loading with incoming search
    if ($('input#search').val().length) {
        const incSearch = $('input#search').val()
        const parsedIncSearch = incSearch.split(', ')
        parsedIncSearch.forEach(item => {
            $('.active-filters').append(`<li class='search-data' value='${item}'><button class='active-filter-tag'>${item}<span class='close-active-filter-tag'></span></button></li>`)
        })
        checkActiveFilters()
    }

    // Filters
    $(document).on('change', '#filter-form :checkbox', function () {
        $('input[name=limitstart]').val(0); // Reset pagination
        let tagItem = {
                tag: $(this).attr('id'),
                name: $(this).siblings('.tagfa-label').text(),
                value: $(this).attr('value')
            }

        const parent = $(this).closest('.filter-option') // Parent <li>
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
                        name: checkbox.siblings('.tagfa-label').text(),
                        value: checkbox.attr('value')
                    }
                    if (!checkbox.is(':checked')) {
                        checkbox.prop('checked', true)
                        // Update hidden inputs
                        fl.push(`${tagItem.value}`)
                        $('input#fl').val(fl)
                        tags.push(`${tagItem.name}`)
                        $('input#active-tags').val(tags)

                        // Add active filter
                        $('.active-filters').append(`<li class='checked-tag' value='${tagItem.value}'><button class='active-filter-tag'>${tagItem.name}<span class='close-active-filter-tag'></span></button></li>`)
                    }
                })
            }
            fl.push(`${tagItem.value}`)
            $('input#fl').val(fl)
            tags.push(`${tagItem.name}`)
            $('input#active-tags').val(tags)

            $('.active-filters').append(`<li class='checked-tag' value='${tagItem.value}'><button class='active-filter-tag'>${tagItem.name}<span class='close-active-filter-tag'></span></button></li>`)
        } else {
            if (parent.children('ul.option')) {
                const checkedChildren = parent.children('ul.option').find('input:checked')
                checkedChildren.each(function () {
                    // Update hidden input values
                    const leaf = $(this).attr('value')
                    const indexFl = fl.indexOf(leaf)
                    if (indexFl > -1) {
                        fl.splice(indexFl, 1)
                    }
                    $('input#fl').val(fl)

                    const indexTag = tags.indexOf($(this).siblings('.tagfa-label').text())
                    if (indexTag > -1) {
                        tags.splice(indexTag, 1)
                    }
                    $('input#active-tags').val(tags)

                    // Remove from applied filters
                    $('.checked-tag').each(function () {
                        if ($(this).attr('value') === leaf) {
                            $(this).remove()
                        }
                    })
                })
            }
            
            // Update hidden input values
            const leaf = $(this).attr('value')
            const indexFl = fl.indexOf(leaf)
            if (indexFl > -1) {
                fl.splice(indexFl, 1)
            }
            $('input#fl').val(fl)

            const indexTag = tags.indexOf($(this).siblings('.tagfa-label').text())
            if (indexTag > -1) {
                tags.splice(indexTag, 1)
            }
            $('input#active-tags').val(tags)

            // Remove from applied filters
            $('.checked-tag').each(function () {
                if ($(this).attr('value') === leaf) {
                    $(this).remove()
                }
            })
        }
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

        // Add active filter for search field
        for (const [key, value] of formData) {
            console.log('»', key, value)

            if (key === 'search') {
                let search = ''
                search = value
                $('input#search').attr('value', search)
                if (search.length > 0) {
                    // Look for any active tags for search
                    const activeTags = $('.search-data')
                    const parsed = search.split(', ')
                    // console.log(parsed)
                    if (!activeTags.length) {
                        parsed.forEach(item => {
                            $('.active-filters').append(`<li class='search-data' value='${item}'><button class='active-filter-tag'>${item}<span class='close-active-filter-tag'></span></button></li>`)
                        })
                    } else {
                        parsed.forEach(value => {
                            activeTags.each(function () {
                                if ($(this).attr('value') !== value) {
                                    $('.active-filters').append(`<li class='search-data' value='${value}'><button class='active-filter-tag'>${value}<span class='close-active-filter-tag'></span></button></li>`)
                                }
                            })
                        })
                    }
                }
            }
        }
        
        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (response, status, xhr) {

                $('div.card-container').html(response.html.cards)
                $('div#accord').html(response.html.filters)

                updateUrl();

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
                
                checkActiveFilters()
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
        const activeFilter = $(this).parent() // Get the <li>
        const getType = activeFilter.attr('class')

        if (getType === 'checked-tag') {
            // Update hidden inputs
            const getValue = activeFilter.attr('value')
            const getText = $(this).text()

            const indexFl = fl.indexOf(getValue)
            if (indexFl > -1) {
                fl.splice(indexFl, 1)
            }
            $('input#fl').val(fl)

            const indexTag = tags.indexOf(getText)
            if (indexTag > -1) {
                tags.splice(indexTag, 1)
            }
            $('input#active-tags').val(tags)

            $checkedInputs = $('.filter-panel').find('input:checked')
            $checkedInputs.each(function () {
                if ($(this).attr('value') === getValue) {
                    $(this).prop('checked', false)
                }
            })

            activeFilter.remove()
          
            // Check for children filters
            const filter = $(`input[value="${getValue}"]`)
            const parent = filter.closest('.filter-option') // Get the <li>
            if (parent.children('ul.option')) {
                const checkedChildren = parent.children('ul.option').find('input:checked')
                checkedChildren.each(function () {
                    // Update hidden inputs
                    const getValue = $(this).attr('value')
                    const getText = $(this).siblings('.tagfa-label').text()

                    const indexFl = fl.indexOf(getValue)
                    if (indexFl > -1) {
                        fl.splice(indexFl, 1)
                    }
                    $('input#fl').val(fl)

                    const indexTag = tags.indexOf(getText)
                    if (indexTag > -1) {
                        tags.splice(indexTag, 1)
                    }
                    $('input#active-tags').val(tags)

                    // Remove the active filter
                    $(`ul.active-filters li[value="${getValue}"]`).remove()
                })
            }
        }

        if (getType === 'search-data') {
            const getValue = activeFilter.attr('value')
            const search = $('input#search').attr('value')
            const parsed = search.split(', ')
            // Check if more than one search item
            if (parsed.length > 1) {
                const indexParsed = parsed.indexOf(getValue)
                if (indexParsed > -1) {
                    parsed.splice(indexParsed, 1)
                }
                // Update the new value
                $('input#search').val(parsed)
            } else {
                $('input#search').val('')
            }
            activeFilter.remove()
        }

        $('#filter-form').submit()
        checkActiveFilters();
    })

    // Reset filters
    $('#reset-btn').on('click', function (e) {
        e.preventDefault();

        $('input#fl').val('')
        fl = []
        $('input#active-tags').val('')
        tags = []
        $('input#search').val('')
        $('.active-filters > li').remove()
        $('.active-filters-wrapper > h6').css('display', 'none')
        $('#filter-form').submit()
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

    // Sortby
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