String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};
jQuery.fn.reverse = [].reverse;

$(document).ready(function () {
    // Accordion
    const accordion = () => {
        $('.accordion-section').on('click', function () {
            $(this).toggleClass('active')
            $(this).next().toggle()
            $(this).children('.accord-trigger').attr('aria-expanded', function (i, attr) {
                return attr == 'false' ? 'true' : 'false'
            })
            $(this).find('.hz-icon').toggleClass('icon-chevron-down icon-chevron-up')
        })
    }

    // Get results total and display them at top of the search results
    const totalResults = () => {
        let $totals = $('.counter').text()
        $('.total-results').text($totals)
    }

    // Display active filter header
    const checkActiveFilters = () => {
        if ($('.active-filters').children('li').length) {
            $('.active-filters-wrapper > h6').css('display', 'block')
        } else {
            $('.active-filters-wrapper > h6').css('display', 'none')
        }
    }

    // Open filter panel if checkbox is checked 
    const checkOpenPanels = () => {
        $('.filter-panel').each(function () {
            if ($(this).find('input:checked').length) {
                $(this).prev().addClass('active')
                $(this).prev().children('.accord-trigger').attr('aria-expanded', 'true')
                $(this).css('display', 'block')
            }
        })
    }

    /*
    Build an active-filter pill. Labels and search text are set with .text()
    and .attr() rather than interpolated into HTML, so a search term or tag
    label can't inject markup.
    */
    const activeFilterItem = (cls, value, label) =>
        $('<li>').addClass(cls).attr('value', value).append(
            $('<button>').addClass('active-filter-tag').text(label).append(
                $('<span>').addClass('close-active-filter-tag')
            )
        )

    /*
    Add active filters
    @param input - selector i.e. $('input[value="#"]')
    @param type - string = checkbox || search
    note - search values is located within the form submission
    */
    const addActiveFilter = (input, type) => {
        if (type === 'checkbox') {

            // Check for grandparents
            const parent = input.closest('.filter-option') // Get the <li>
            const grandparent = parent.parents('.filter-option')
            console.log(grandparent)
            
            if (grandparent.length) {
                grandparent.reverse().each(function () {
                    const getFl = $(this).children('label').children('input') // input
                    const getLabel = getFl.siblings('.tagfa-label').text()

                    if (!getFl.is(':checked')) {
                        getFl.prop('checked', true)
                        tags.push(`${getFl.siblings('.tagfa-label').text()}`)
                        $('input#active-tags').val(tags)

                        // Add active filter
                        $('.active-filters').append(activeFilterItem('checked-tag', getFl.attr('value'), getLabel))
                    } else {
                        // Check if filter is not in the tags array, add it
                        if (jQuery.inArray(getLabel, tags) === -1) {
                            console.log('not in tags array')
                            tags.push(getLabel)
                            $('input#active-tags').val(tags)

                            // Add active filter
                            $('.active-filters').append(activeFilterItem('checked-tag', getFl.attr('value'), getLabel))
                        }
                    }
                })
            }
            
            // Add to tags array
            tags.push(input.siblings('.tagfa-label').text())
            
            // Update hidden input
             $('input#active-tags').val(tags)
            
             // Add active filter
            $('.active-filters').append(activeFilterItem('checked-tag', input.attr('value'), input.siblings('.tagfa-label').text()))
        }

        if (type === 'search') {
            const activeTags = $('.search-data')
            const searchValue = input.val()
            
            if (!activeTags.length) {
            $('.active-filters').append(activeFilterItem('search-data', searchValue, searchValue))
            } else {
                // Replace current active tag
                activeTags.remove()
                $('.active-filters').append(activeFilterItem('search-data', searchValue, searchValue))

            }
        }
    }
    /*
    Remove active filters
    @param input - selector i.e. $('input[value="#"]')
    @param type - string = checkbox || search
    note - search values is located within the form submission
    */
    const removeActiveFilters = (input, type) => {
        if (type === 'checkbox') {
            const parent = input.closest('.filter-option') // Get the <li>

            // Check for children
            if (parent.children('ul.option')) {
                const checkedChildren = parent.children('ul.option').find('input:checked')
                checkedChildren.each(function () {
                    $(this).prop('checked', false)

                    // Update hidden input
                    const value = $(this).attr('value')
                    const indexTag = tags.indexOf($(this).siblings('.tagfa-label').text())

                    if (indexTag > -1) {
                        tags.splice(indexTag, 1)
                    }
                    $('input#active-tags').val(tags)

                    // Remove from applied filters
                    $('.checked-tag').each(function () {
                        if ($(this).attr('value') === value) {
                            $(this).remove()
                        }
                    })
                })
            }

            input.prop('checked', false)

            // Update hidden input
            const indexTag = tags.indexOf(input.siblings('.tagfa-label').text())
            const value = input.attr('value')
            if (indexTag > -1) {
                tags.splice(indexTag, 1)
            }

            $('input#active-tags').val(tags)

            // Remove from applied filters
            $('.checked-tag').each(function () {
                if ($(this).attr('value') === value) {
                    $(this).remove()
                }
            })
        }

        if (type === 'search') {
            $('.search-data').remove()
            $('input#search').val('')
        }
    }

    // On load
    $('#limit').removeAttr('onchange').bind('change', function(e) {
        e.preventDefault();

        $('#filter-form').submit();

        return false;
    });

    const observer = new MutationObserver(function (mutationsList) {
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList') {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType === 1 && node.matches('nav.pagination')) {
                        $('#limit').removeAttr('onchange').bind('change', function(e) {
                            e.preventDefault();

                            $('#filter-form').submit();

                            return false;
                        });
                    }
                }
            }
        }
    });

    const cardContainer = document.querySelector('div.card-container');
    observer.observe(cardContainer, {
        childList: true,
        subtree: true
    });

    let tags = []

    // If loading with incoming fl - add appropriate active tags
    // fl comes from the URL, so match checkboxes by comparing values rather
    // than building a selector from it (a stray quote would throw and stop
    // the rest of this handler), and skip values that match no filter.
    if ($('input#fl').val().length) {
        $('input#fl').val().split(',').forEach(function (value) {
            const input = $('input[type=checkbox]').filter(function () {
                return this.value === value
            })

            if (input.length) {
                addActiveFilter(input, 'checkbox')
            }
        })
    }

    // If loading with incoming search
    if ($('input#search').val().length) {
        addActiveFilter($('input#search'), 'search')
    }

    accordion()
    totalResults()
    checkOpenPanels()
    checkActiveFilters()

    // Filters
    $(document).on('change', '#filter-form :checkbox', function () {
        $('input[name=limitstart]').val(0); // Reset pagination

        if ($(this).prop('checked')) {
            addActiveFilter($(this), 'checkbox')

        } else {
            removeActiveFilters($(this), 'checkbox')
        }
        $('#filter-form').submit();
    });

    let timer;
    $(document).on('keypress', 'input[name=search]', function (e) {
        e.stopPropagation();
        $('input[name=limitstart]').val(0); // Reset pagination
        
        if (e.key === 'Enter') {
            e.preventDefault();

            // Throttle form submission
            clearTimeout(timer);
            timer = setTimeout(function(){
                $('input.btn[id=search-btn]').click();                
            },1000)
        }
    });

    $(document).on('click', 'input.btn[id=search-btn]', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#filter-form').submit();
    });

    const updateUrl = () => {
        var url = location.pathname;
        
        var limit = $('#limit').val();
        var limitstart = $('input[name=limitstart]').val();
        var sortby = $('input[name=sortby]').val();
        var queryParams = '?search=';
        var limitParams = `&limit=${limit}&limitstart=${limitstart}`;
        var sortbyParams = `&sortby=${sortby}`;
        var query = $('#search').val();
        var encodedTerms = encodeURIComponent(query).replace(/%20/g, '+');
        var filters = $.merge(
            $('li:has(input:checked) > ul:not(:has(input:checked))').siblings('label').children('input'), 
            $('label:only-child:has(input:checked) > input'))
            .map(function() { return $(this).val(); })
            .get()
            .join(',');
        var filterParams = (filters !== '' ? `&fl=${filters}` : '');

        urlToFetch = `${url}${queryParams}${encodedTerms}${filterParams}${limitParams}${sortbyParams}`;
        
        // Update hidden input
        $('input#fl').val(filters)

        window.history.pushState({href: urlToFetch}, '', urlToFetch);
    }

    $(document).on('submit', '#filter-form', function(e) {
        e.preventDefault();

        updateUrl();

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
                    addActiveFilter($('input#search'), 'search')
                } else {
                    removeActiveFilters($('input#search'), 'search')
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

                totalResults()
                accordion()
                checkOpenPanels()
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
            removeActiveFilters($(`input[value="${activeFilter.attr('value')}"]`), 'checkbox')
        }

        if (getType === 'search-data') {
            removeActiveFilters($('input#search'), 'search')
        }

        $('#filter-form').submit()
    })

    // Reset filters
    $('#reset-btn').on('click', function (e) {
        e.preventDefault();
        $('input[name=limitstart]').val(0); // Reset pagination
        $('input:checked').prop('checked', false)
        $('input#active-tags').val('')
        tags = []
        $('input#search').val('')
        $('.active-filters > li').remove()
        $('.active-filters-wrapper > h6').css('display', 'none')
        $('#filter-form').submit()
    })

    // BEGIN: Ajax-ify pagination
    // $(document).on('click', 'a.pagenav', function(e) {
    //     e.preventDefault();

    //     params = Object.fromEntries(this.href.split(/[?&]/).slice(1).map(s => s.split('=')));
    //     // console.log(params)
    //     $('input[name=limitstart]').val(params.start);
    //     $('input[name=limit]').val(params.limit);
    //     $('#filter-form').submit();
    //     return false;
    // });

    // Sortby
    $('ul.entries-menu a').click(function() {
        $('ul.entries-menu a.active').toggleClass('active');
        $(this).toggleClass('active');
        $('input[name=sortby]').val($(this).data('value'));

        $('#filter-form').submit();
    });

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
        },
        afterShow: function() {
			$('form.submit-options a.btn').on('click', (e) => {
				e.preventDefault();
				$.fancybox.close();
				window.location = e.target.href;
			});
		}
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