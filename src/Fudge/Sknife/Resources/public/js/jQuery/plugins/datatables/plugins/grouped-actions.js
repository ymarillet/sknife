/**
 * Author: Yohann Marillet <yohann.marillet@gmail.com>
 * Date: 04/10/13
 */
(function ($) {

    var aiSelected = {};

    $.extend($.fn.dataTable.defaults.oLanguage, {
        "oGroupedActions": {
            "sCount": "Number of selected elements: _SELECTED_",
            "sMenu": "For the elements selected: _MENU_",
            "sMenuSelectAction": "-- Select an action",
            "sButton": "Go!",
            "sConfirmAction": "Are you sure to execute the action \"_ACTION_\" on the _SELECTED_ selected elements ?",
            "sNoAction": "You didn\'t select an action",
            "sNoItemSelected": "No item has been selected yet"
        }
    });

    $.fn.dataTableExt.oApi.fnGroupedActionsSetSelectedCount = function (oSettings, iSelected) {
        aiSelected[oSettings.iApiIndex] = iSelected;
        this.fnGroupedActionsUpdateHtml();
    };

    $.fn.dataTableExt.oApi.fnGroupedActionsUpdateHtml = function (oSettings) {
        $(oSettings.nTableWrapper).find('.iSelected').html(aiSelected[oSettings.iApiIndex]);
    }

    $.fn.dataTableExt.oApi.fnGroupedActionsGetSelectedCount = function (oSettings) {
        if(!aiSelected.hasOwnProperty(oSettings.iApiIndex)) {
            this.fnGroupedActionsSetSelectedCount(0);
        }

        return aiSelected[oSettings.iApiIndex];
    };

    var $fnInitialServerData = $.fn.dataTable.defaults.fnServerData;
    $.fn.dataTable.defaults.fnServerData = function (sSource, aoData, fnCallback, oSettings) {
        var fnInitialCallback = fnCallback;
        var fnCallback = function (data, status, jqXHR) {
            fnInitialCallback(data, status, jqXHR);

            if (data.hasOwnProperty('iSelected')) {
                $(oSettings.nTable).dataTable().fnGroupedActionsSetSelectedCount(data.iSelected);
            }
        };
        $fnInitialServerData(sSource, aoData, fnCallback, oSettings);
    };
}(jQuery));