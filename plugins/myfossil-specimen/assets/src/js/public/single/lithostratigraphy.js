(function($) {
    'use strict';

    function set_stratum(rank, stratum) {
        var inputBox = $('input#fossil-stratum-' + rank);

        inputBox.val(stratum.name)
            .data('name', stratum.name)
            .data('rank', stratum.rank)
            .data('n_occs', stratum.n_occs)
            .data('n_colls', stratum.n_colls);
    }

    // {{{ save_lithostratigraphy 
    function save_lithostratigraphy() {
            var nonce = $('#myfossil_specimen_nonce').val();
            var post_id = parseInt($('#post_id').val());

            var strata = {};
            $.map(['member', 'formation', 'group'], function(rank) {
                strata[rank] = $('input#fossil-stratum-' + rank).val();
            });

            $.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    'action': 'myfossil_save_lithostratigraphy',
                    'nonce': nonce,
                    'post_id': post_id,
                    'strata': strata,
                    'comment': $('#edit-fossil-lithostratigraphy-comment').val()
                },
                dataType: 'json',
                success: function(data) {
                    console.info(data);
                    $('#fossil-lithostratigraphy-success').show().fadeOut();
                    $('#edit-fossil-lithostratigraphy-save-alert').fadeOut();
                      
                },
                complete: function(data) {
                    $('#fossil-lithostratigraphy-loading').hide();
                    window.location.reload(true);
                },
                error: function(err) {
                    console.error(err);
                    $('#fossil-lithostratigraphy-error').show().fadeOut(1000);
                }
            });

        }
        // }}}

    // {{{ autocomplete_lithostratigraphy
    function autocomplete_stratum() {
            if ($(this).val().length < 1) return;

            var query = $(this).val();

            var rank = $(this).data('rank');

            // Auto-complete unordered list.
            var ul = $('ul#edit-fossil-stratum-' + rank + '-results');

            // @todo Make the PBDB URL some kind of constant.
            var url = "http://paleobiodb.org/data1.1/strata/auto.json" + "?limit=40&vocab=pbdb" + "&name=" + query + "&rank=" + rank;

            // Query the PBDB with the current lithostratigraphy name partial.
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    autocomplete_update_results(rank, data.records);
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }
        // }}}

    // {{{ autocomplete_update_results
    function autocomplete_update_results(rank, strata) {
            var ul = $('ul#edit-fossil-stratum-' + rank + '-results');

            // Remove current taxa from the auto-complete list.
            ul.empty();

            // foreach lithostratigraphy result from the auto-complete
            $.map(strata, function(stratum) {
                // Build list item, including phylopic.
                var stratum_li = $('<li></li>')
                    .addClass('hover-hand')
                    .append(stratum.name)
                    .data('name', stratum.name)
                    .data('rank', stratum.rank)
                    .data('n_occs', stratum.n_occs)
                    .data('n_colls', stratum.n_colls)
                    .click(function() {
                        set_stratum(rank, stratum);
                    });

                // show at most 10 results
                if (ul.children().length > 10)
                    stratum_li.css('display', 'none');

                // Add list item to the results.
                ul.append(stratum_li);
            });
        }
        // }}}

    // {{{ autocomplete_stratum_sort
    function autocomplete_stratum_sort(ev) {
            var rank = ev.data.rank;
            var sortby = $('select#edit-fossil-stratum-' + rank + '-sortby').val();
            var ul = $('ul#edit-fossil-stratum-' + rank + '-results');

            var strata = [];
            ul.children().each(function() {
                strata.push({
                    name: $(this).data('name'),
                    rank: $(this).data('rank'),
                    n_occs: $(this).data('n_occs'),
                    n_colls: $(this).data('n_colls')
                });
            });

            // Sort by number of occurrences or number of collections
            console.log('sorting strata by ' + sortby);
            switch (sortby) {
                case 'name':
                    strata = strata.sort(_compare_strata_name);
                    break;

                case 'occs':
                    strata = strata.sort(_compare_strata_occs);
                    break;

                case 'colls':
                    strata = strata.sort(_compare_strata_colls);
                    break;
            }

            autocomplete_update_results(rank, strata);
        }
        // }}}

    function save_prompt() {
        $('#edit-fossil-lithostratigraphy-save-alert').show();
    }

    function toggle_comment() {
        $('#edit-fossil-lithostratigraphy-comment-form-group').toggle();
        $(this).fadeOut(400);
        // $( '#edit-fossil-lithostratigraphy-comment-toggle > button' ).click( toggle_comment );
    }

    

    $(function() {
        $('#edit-fossil-lithostratigraphy-save').click(save_lithostratigraphy);
        $('#edit-fossil-lithostratigraphy-comment-toggle > button').click(toggle_comment);

        $.map(['member', 'formation', 'group'], function(rank) {
            $('input#edit-fossil-stratum-' + rank)
                .keyup(autocomplete_stratum);

            //$('input#fossil-stratum-' + rank)
            //    .change(save_prompt);
            $('ul#edit-fossil-stratum-' + rank + '-results')
                .click(function () {
                    save_prompt();
                    $('div#edit-fossil-stratum-' + rank).popup("hide");
                });

            $('input#fossil-stratum-' + rank)
                .keyup(save_prompt);

            $('select#edit-fossil-stratum-' + rank + '-sortby')
                .change({
                        rank: rank,
                    },
                    function(ev) {
                        autocomplete_stratum_sort(ev);
                    }
                );

            $('div#edit-fossil-stratum-' + rank).popup({
                type: 'tooltip',
                opacity: 1,
                background: false,
                transition: 'all 0.2s',
            });
        }); // $.map

    }); // $( function()

    // {{{ comparators
    function _compare_strata_name(a, b) {
        if (a.name < b.name) return -1;
        if (a.name > b.name) return 1;
        return 0;
    }

    function _compare_strata_occs(a, b) {
        if (a.n_occs < b.n_occs) return 1;
        if (a.n_occs > b.n_occs) return -1;
        return 0;
    }

    function _compare_strata_colls(a, b) {
            if (a.n_colls < b.n_colls) return 1;
            if (a.n_colls > b.n_colls) return -1;
            return 0;
        }
        // }}}

}(jQuery));
