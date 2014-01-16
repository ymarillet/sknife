/**
 * Date: 03/10/13
 */
window.sknife = {

    datatableColumnSearchTimer: null,

    /**
     * Registers a function with ajax notifications
     * @param $callback
     * @param $container
     */
    registerAjaxResponseMessagesHandler: function($callback, $container) {
        $(document).ajaxComplete(function(event,xhr,settings) {
            // display flash messages for all typed ajax responses
            var $response;
            if(xhr.hasOwnProperty('responseJSON')) {
                $response = xhr.responseJSON;
            } else if(xhr.hasOwnProperty('responseText')) {
                try {
                    $response = $.parseJSON(xhr.responseText);
                } catch($e) {
                    //do nothing here, the response is not JSON parsable
                }
            }

            if((typeof $response) != 'undefined' && $response.hasOwnProperty('type') && $response.type == "sknife.ajax") {
                var setDelay = $response.hasOwnProperty('delay')?$response.delay:0;
                for(var $k in $response.messages) {
                    var delay = setDelay;
                    if(delay==0) {
                        delay = ($k == 'success'?5:10);
                    }
                    var $params = {};
                    $params[$k] = $response.messages[$k];
                    $callback($container, $params, delay);
                }
            } else if(xhr.status>=300) {
                $callback($container, {'danger': ['An unrecoverable error has occurred: "'+xhr.status+' '+xhr.statusText+'"']}, 5);
            }
        });
    },

    registerAjaxSpinner: function($spinnerContainer) {
        $(document).ajaxStart(function(){
            $($spinnerContainer).show();
        });

        $(document).ajaxStop(function(){
            $($spinnerContainer).hide();
        });
    },

    registerFileUploadButtonsListener: function($selector) {
        if((typeof $selector == 'undefined') || $selector=='') {
            $selector = '[data-type="choose-file"]';
        }
        $(document).on('click', $selector+' button', function(){
            $(this).parents($selector).find('input').trigger('click');
        });
    },

    replaceFileUploadButton: function($elm, $buttonTemplate, $wrapperHtml) {
        if((typeof $wrapperHtml == 'undefined') || $wrapperHtml == '') {
            $wrapperHtml = '<span data-type="choose-file" />';
        }
        $elm.css('display','none');
        var $wrapper = $elm.wrap($wrapperHtml).parent();
        $wrapper.append($buttonTemplate);
    },

    closeModalIfEmpty: function($modalElm) {
        var $modal = $($modalElm);
        $modal.on('show.bs.modal', function() {
            $(document).ajaxComplete(function(event,xhr,settings) {
                if('' == $modal.html()) {
                    $modal.modal('hide');
                }
                $(document).unbind(event);
            });
        });
    },

    emptyModalOnClose: function($elm) {
        $($elm).on('hidden.bs.modal', function () {
            $(this).html('');
            $(this).removeData('bs.modal');
        });
    },

    initDatatable: function($id, $columns, $translationFile, $customOptions, $groupedActions) {
        $.getJSON($translationFile, function(languageData) {
            var $delayBeforeSearch = 400;
            var $params = {
                "sDom": 'lifrtp',
                "sPaginationType": "bootstrap",
                "oLanguage": languageData,
                "aoColumns": $columns
            };
            $.extend($params, $customOptions);
            var $table = $($id).dataTable($params);
            $table.fnSetFilteringDelay($delayBeforeSearch);

            /* Add a select menu for each Td element in the table header */
            $($id+' thead td').each( function ( $i ) {
                if($(this).data('selectable')) {
                    $(this).html($table.fnCreateSelect($i));
                    var $filter = $('select', this);
                    $filter.css('margin','0 5%')
                        .css('width','90%')
                        .change( function () {
                            $table.fnFilter($(this).val(), $i);
                        });
                    if($(this).data('value')) {
                        //this below hasn't been tested
                        $filter.val($(this).data('value'));
                        $filter.trigger('change');
                    }
                } else if($(this).data('filterable')) {
                    var $filterableItems = $(this).data('filterable-items');
                    var $input;
                    if($filterableItems) {
                        $input = $('<select>');
                    } else {
                        $input = $('<input />');
                    }
                    $(this).html($input);

                    var searchFunc = function($table, $that, $i) {
                        $table.fnFilter( $that.val(), $i );
                    };
                    $input
                        .css('margin','0 2%')
                        .css('width','90%')
                    ;

                    if($filterableItems) {
                        $input.append('<option value=""></option>');
                        var $isArray = $.isArray($filterableItems);
                        for(var $j in $filterableItems) {
                            $input.append('<option value="'+($isArray?$filterableItems[$j]:$j)+'">'+$filterableItems[$j]+'</option>');
                        }
                        var $select2Params = {
                            placeholder: " ",
                            allowClear: true,
                            dropdownAutoWidth: true
                        };
                        $input.select2($select2Params)
                            .on('change', function(){searchFunc($table, $(this), $i)})
                        ;

                        if($(this).data('value')) {
                            $input.select2('val', $(this).data('value'), true);
                        }
                    } else {
                        $input.on('keyup', function(e){
                            var $doSetTimeout = function($table, $that, $i, $delay) {
                                $timer = setTimeout(function(){searchFunc($table, $that, $i)}, $delay);
                                return $timer;
                            };
                            if ($(this).val() !== $(this).data("oldValue")) {
                                $(this).data("oldValue", $(this).val());
                                clearTimeout(this.datatableColumnSearchTimer);
                                this.datatableColumnSearchTimer = $doSetTimeout($table, $(this), $i, $delayBeforeSearch);
                            }
                        });

                        if($(this).data('value')) {
                            //this below hasn't been tested
                            $input.val($(this).data('value'));
                            $input.trigger('keyup');
                        }
                    }
                }
            } );

            /* move the column filters below the column titles */
            $($id+' tr.filters-container').appendTo($id+' thead');

            // table has grouped actions
            // @todo move this into a full proper datatable plugin
            if(((typeof $groupedActions) == 'object') && Object.keys($groupedActions).length > 0) {
                var oSettings = $table.fnSettings();

                // add the grouped actions toolbar
                var nGroupedActionsToolbar = $(oSettings.nTableWrapper).find('.groupedActionsToolbar');
                var sGroupedActionsMenuOptions = '<option value="" data-target="#">'+oSettings.oLanguage.oGroupedActions.sMenuSelectAction+'</option>';
                for(var $g in $groupedActions) {
                    sGroupedActionsMenuOptions += '<option value="" data-target="'+Routing.generate($groupedActions[$g]['route'])+'">'+
                        $groupedActions[$g]['label']+
                        '</option>';
                }
                var sGroupedActionsMenu =
                    '<select>' +
                        sGroupedActionsMenuOptions +
                        '</select> ' +
                        '<button type="button" class="btn btn-xs btn-default">' +
                        oSettings.oLanguage.oGroupedActions.sButton +
                        '</button>';
                var sGroupedActionsToolbarHtml='';
                sGroupedActionsToolbarHtml +=
                    '<div>'+
                        (oSettings.oLanguage.oGroupedActions.sCount.replace('_SELECTED_','<span class="iSelected"></span>'))+
                        '</div>'+
                        '<div>'+
                        (oSettings.oLanguage.oGroupedActions.sMenu.replace('_MENU_',sGroupedActionsMenu)) +
                        '</div>';
                nGroupedActionsToolbar.html(sGroupedActionsToolbarHtml);

                nGroupedActionsToolbar.find('button').on('click', function() {
                    var $optionSelected = $(this).prev().find('option:selected');
                    var $target = $optionSelected.data('target');
                    if('undefined' != (typeof $target) && '' != $target && '#' != $target) {
                        var iSelected = $table.fnGroupedActionsGetSelectedCount();
                        if(iSelected > 0) {
                            var $confirmMessage = oSettings.oLanguage.oGroupedActions.sConfirmAction.replace('_ACTION_',$optionSelected.text()).replace('_SELECTED_',iSelected);
                            if(confirm($confirmMessage)) {
                                $.post($target).success(function(){
                                    $table.fnDraw(false);
                                });
                            }
                        } else {
                            alert(oSettings.oLanguage.oGroupedActions.sNoItemSelected);
                        }
                    } else {
                        alert(oSettings.oLanguage.oGroupedActions.sNoAction);
                    }

                });

                // click on the header checkbox
                $($id+'_all').on('change', function() {

                    var $checked = $(this).is(':checked');
                    var $index = $(this).parent().index();

                    $($id+' tbody tr td:nth-of-type('+($index+1)+') input').prop('checked', $checked);

                    var $path = Routing.generate($(this).data('target'), {'status': $checked?'on':'off'});
                    var $filters = {
                        "iColumns": oSettings.aoColumns.length,
                        "sSearch": $( 'input', oSettings.aanFeatures.f).val()
                    };
                    for(var rowIndex in oSettings.aoColumns) {
                        $filters['bSearchable_'+rowIndex] = oSettings.aoColumns[rowIndex].bSearchable;
                        $filters['sSearch_'+rowIndex] = oSettings.aoPreSearchCols[rowIndex].sSearch;
                    }
                    var $res = $.post($path, $filters)
                        .success(function(data) {
                            $table.fnGroupedActionsSetSelectedCount(data.data);
                        });
                });

                // click on a row checkbox
                $($id).on('change', "input[type=checkbox][data-route][data-single-target=1][data-control='groupedActions']", function() {
                    var $params = {};
                    $params[$(this).data('prop-id')] = $(this).data('id');
                    $params[$(this).data('prop-status')] = $(this).is(':checked')?'on':'off';
                    var $url = Routing.generate($(this).data('route'), $params);
                    $.get($url).success(function(data){
                        $table.fnGroupedActionsSetSelectedCount(data.data)
                    });

                });

            }

        });
    }
};