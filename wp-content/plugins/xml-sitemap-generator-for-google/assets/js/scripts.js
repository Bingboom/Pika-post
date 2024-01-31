"use strict";

jQuery(document).ready(function ($) {
    /** Tabs */
    let activeIndex = $('.nav-tab-active').index(),
        $contentList = $('.nav-tabs-content .section'),
        $tabsList = $('.nav-tab-wrapper a');

    if ( location.hash.length > 0 ) {
        let currentIndex = $(`.nav-tab-wrapper a[data-id="${location.hash.substr(1)}"]`).index();
        activeIndex = currentIndex > -1 ? currentIndex : activeIndex;

        $tabsList.removeClass('nav-tab-active');
        $tabsList.eq(activeIndex).addClass('nav-tab-active');
    }

    $contentList.hide().eq(activeIndex).show();

    $('.nav-tab-wrapper').on('click', 'a', function (e) {
        e.preventDefault();

        let $current = $(e.currentTarget),
            index = $current.index(),
            id = $current.data('id');

        $tabsList.removeClass('nav-tab-active');
        $current.addClass('nav-tab-active');
        $contentList.hide().eq(index).show();
        location.hash = id;
    });

    /** Dependency */
    $('.has-dependency').click(function () {
        sgg_dependency(`.${$(this).data('target')}`, !this.checked);
    }).each(function () {
        sgg_dependency(`.${$(this).data('target')}`, !this.checked);
    });

    /** Add Field */
    $('#add_new_url').on('click', function(e) {
        e.preventDefault();
        $('.no_urls').remove();
        $('#additional_urls').append('<tr>' +
            '<td><input type="text" name="additional_urls[]"></td>' +
            '<td>' + $('#additional_priorities_selector').html() + '</td>' +
            '<td>' + $('#additional_frequencies_selector').html() + '</td>' +
            '<td><input type="datetime-local" name="additional_lastmods[]"></td>' +
            '<td><a href="#" class="remove_url">x</a></td>' +
            '</tr>');
    });

    /** Remove Field */
    $(document).on('click', '.remove_url', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
    })

    /** Expand */
    $('.expand-toggle').click(function (e) {
        e.preventDefault();
        $(this).toggleClass('active');
        $(this).siblings('ul').toggleClass('active');
        $(this).html($(this).hasClass('active') ? 'Show Less &#9650;' : 'Show More &#9660;');
    });

    /** Autocomplete */
    $('.sgg-autocomplete').each(function() {
        let $el = $(this);
        let target = $el.data('target');
        let type = $el.data('type');
        let terms = sgg_get_terms(target);

        sgg_render_terms(terms, target);

        $el.autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: sgg.ajax_url,
                    method: 'post',
                    dataType: 'json',
                    data: {
                        action: 'sgg_autocomplete_search',
                        term: request.term,
                        type
                    },
                    success: function (res) {
                        if (res?.success) {
                            response(res?.data);
                        } else {
                            response([{
                                label: res?.message,
                                value: 'false'
                            }])
                        }
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                terms = sgg_get_terms(target);

                if (terms.findIndex(el => el.value == ui.item.value) === -1) {
                    terms.unshift(ui.item);

                    let $target = $(`#${target}`).siblings('.expand');
                    $target.children('.expand-toggle').addClass('active').html('Show Less &#9650;' );
                    $target.children('ul').addClass('active');
                }

                sgg_update_terms(terms, target);

                this.value = '';
                return false;
            }
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            if (item.value === 'false') {
                return $('<li class="ui-state-disabled">' + item.label + '</li>').appendTo(ul);
            } else {
                return $('<li>').append(item.label).appendTo(ul);
            }
        };
    });

    /** Remove Term */
    $(document).on('click', '.sgg-autocomplete-terms .remove-term', function (e) {
        e.preventDefault();
        let termValue = $(this).data('value');
        let target = $(this).data('target');
        let terms = sgg_get_terms(target);

        if (termValue) {
            terms = terms.filter(el => el.value != termValue)

            sgg_update_terms(terms, target);
        }
    });

    /** Form Actions */
    $('#clear-sitemap-cache').on('mouseup', function () {
        $('input[name="clear_cache"]').val('clear');
    });

    $('#youtube-check-api-key').on('mouseup', function () {
        $('input[name="youtube_check_api_key"]').val('check');
    });

    $('#clear-youtube-cache').on('mouseup', function () {
        $('input[name="clear_youtube_cache"]').val('clear');
    });

    function sgg_get_terms(target) {
        let selector = $(`#${target}`)

        return JSON.parse(!selector.val() ? '[]' : selector.val());
    }

    function sgg_update_terms(terms, target) {
        $(`#${target}`).val(JSON.stringify(terms));

        sgg_render_terms(terms, target);
    }

    function sgg_render_terms(terms, target) {
        let $target = $(`#${target}`);
        $target.siblings('.expand').children('.sgg-autocomplete-terms').html('');

        terms.forEach(term => {
            $target.siblings('.expand').children('.sgg-autocomplete-terms')
                .append(`<li>${term.label} <a href="#" class="remove-term" data-value="${term.value}" data-target="${target}">x</a></li>`)
        });

        $target.siblings('.expand').children('.expand-toggle').toggle(terms.length > 3);
    }

    function sgg_dependency(elements, checked) {
        $(elements).attr('disabled', checked).toggleClass('dependency-disabled', checked);
    }
});