String.prototype.nohtml = function () {
	return this + (this.indexOf('?') == -1 ? '?' : '&') + 'no_html=1';
};

$(document).ready(function () {
    $('#accordion').accordion({ collapsible:true, active:false, heightStyle:'content', disabled:true});
    $('#accordion h5.ui-accordion-header').click(function(){
        var _this = $(this);
        _this.find('i').toggleClass('fa-chevron-down fa-chevron-up');
        _this.next().slideToggle();
        return false;
    });
    $('.accordion-expand-all').click(function(){
        var headers = $('#accordion h5.ui-accordion-header');
        headers.removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
        headers.next().slideDown();
    });
    $('.accordion-collapse-all').click(function(){
        var headers = $('#accordion h5.ui-accordion-header');
        $('.ui-accordion-header-icon', headers).removeClass('ui-icon-triangle-1-s').addClass('ui-icon-triangle-1-e');
        headers.next().slideUp();
    });
    let tags = []
    $(document).on('change', '#filter-form :checkbox', function() {
        $('input[name=limitstart]').val(0); // Reset pagination
        let tagItem = {
                tag: $(this).attr('id'),
                name: $(this).next('label').text()
            }
        if ($(this).prop('checked')) {
            tags.push(tagItem)
            $('.active-filters').append(`<li class='checked-tag ${tagItem.tag}'><button class='active-filter-tag'>${tagItem.name}<span class='close-active-filter-tag'></span></button></li>`)
        } else {
            for (i = 0; i < tags.length; i++) {
                if (tags[i].tag === $(this).attr('id')) {
                    tags.splice(i, 1);
                    break;
                }
            }
            $(`.${tagItem.tag}`).remove()
        }
        sessionStorage.setItem('tags', JSON.stringify(tags))
      
        $('#filter-form').submit();
        checkActiveFilters()
    });
    $(document).on('change', '#filter-form select.option', function() {
        $('input[name=limitstart]').val(0); // Reset pagination
        let tagItem = {
                tag: $(this).val(),
                name: $(this).children(':selected').text()
            }
        // tags.push(tagItem)
        // $('.active-filters').append(`<li class='checked-tag ${tagItem.tag}'><button class='active-filter-tag'>${tagItem.name}<span class='close-active-filter-tag'></span></button></li>`)
        // for (i = 0; i < tags.length; i++) {
        //     if (tags[i].tag === $(this).attr('id')) {
        //         tags.splice(i, 1);
        //         break;
        //     }
        // }
        // $(`.${tagItem.tag}`).remove()
        // sessionStorage.setItem('tags', JSON.stringify(tags))
      
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

    $(document).on('change', 'div.fad > select', function (e) {
        // print to console "for" attribute of this
        val = 'tagfa-' + $(this).val().split('.')[1];
        $(this).siblings('div.fad, div.fac').hide();
        $(this).siblings('div.fad[for="' + val + '"], div.fac[for="' + val + '"]').show();
    });

    const checkActiveFilters = () => {
        if ($('.active-filters').children('li').length) {
            $('.active-filters-wrapper > h6').css('display', 'block')
        } else {
            $('.active-filters-wrapper > h6').css('display', 'none')
        }
    }

    $(document).on('submit', 'form', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
    
        // console.log(...formData); // https://stackoverflow.com/questions/25040479/formdata-created-from-an-existing-form-seems-empty-when-i-log-it

        // Save form data in sessionStorage and add active filter
        for (const [key, value] of formData) {
            console.log('»', key, value)

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
                // checkActiveFilters()
                // console.log(response);

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
            sessionStorage.setItem('tags', JSON.stringify(storedTags))

            // Remove filter
            $('.filter-panel').find(`#${getFilter}`).prop('checked', false)
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
        $('div.card-container').html(JSON.parse(sessionStorage.getItem('results')).html);

        if (sessionStorage.getItem('tags')) {
            let tags = JSON.parse(sessionStorage.getItem('tags'))
            for (i = 0; i < tags.length; i++) {
                $('.filter-panel').find(`#${tags[i].tag}`).prop('checked', true)
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
            $('#filter-form')[0].reset()
            $('.token-input-token-act').remove()
            $('#filter-form').submit()
        }
    })

    // BEGIN: Ajax-ify pagination
    $(document).on('click', 'a.pagenav', function(e) {
        e.preventDefault();

        params = Object.fromEntries(this.href.split(/[?&]/).slice(1).map(s => s.split('=')));
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
        $('input[name=sort]').val($(this).data('value'));

        $('#filter-form').submit();
    });

    // Mobile responsiveness
    const filterContainer = $('.filter-container'),
        mobileFilter = $('.browse-mobile-btn'),
        btnIcon = mobileFilter.children('i'),
        filterText = mobileFilter.children('span');
    
    mobileFilter.on('click', function () {
        filterContainer.toggleClass('mobile-active')
        btnIcon.toggleClass('fa-bars fa-times')

        if (filterContainer.hasClass('mobile-active')) {
            filterText.text('Close')
        } else {
            filterText.text('Filters')
        }
    })

    $(window).on('resize', function () {
        if (filterContainer.hasClass('mobile-active') && !mobileFilter.is(':visible')) {
            filterContainer.removeClass('mobile-active')
        }
    })
});