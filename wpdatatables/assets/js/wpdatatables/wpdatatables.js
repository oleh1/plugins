/** New JS controller for wpDataTables **/

var wpDataTables = {};
var wpDataTableDialogs = {};
var wpDataTablesSelRows = {};
var wpDataTablesFunctions = {};
var wpDataTablesUpdatingFlags = {};
var wpDataTablesResponsiveHelpers = {};
var wdtBreakpointDefinition = {
    tablet: 1024,
    phone: 480
};
var wdtCustomUploader = null;

(function ($) {
    $(function () {

        $('table.wpDataTable').each(function () {
            var tableDescription = $.parseJSON($('#' + $(this).data('described-by')).val());

            // Parse the DataTable init options
            var dataTableOptions = tableDescription.dataTableParams;

            
            
            // Apply the selecter to show entries
            dataTableOptions.fnInitComplete = function( oSettings, json ) {
                jQuery('#' + tableDescription.tableId + '_length select').selecter();
            }
            // Init the DataTable itself
            wpDataTables[tableDescription.tableId] = $(tableDescription.selector).dataTable(dataTableOptions);

            

            // Add the draw callback
            wpDataTables[tableDescription.tableId].addOnDrawCallback = function (callback) {
                if (typeof callback !== 'function') {
                    return;
                }

                var index = wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.length + 1;

                wpDataTables[tableDescription.tableId].fnSettings().aoDrawCallback.push({
                    sName: 'user_callback_' + index,
                    fn: callback
                });

            }

            

            // Init row grouping if enabled
            if ((tableDescription.columnsFixed == 0) && (tableDescription.groupingEnabled)) {
                wpDataTables[tableDescription.tableId].rowGrouping({iGroupingColumnIndex: tableDescription.groupingColumnIndex});
            }

            

            $(window).load(function () {
                // Show table if it was hidden
                if (tableDescription.hideBeforeLoad) {
                    $(tableDescription.selector).show(300);
                }
            });

        });

        

    })

    

})(jQuery);

function wdtApplyCellAction( $cell, action, setVal ){
    switch( action ){
        case 'setCellColor':
                $cell.css( 'background-color', setVal );
            break;
        case 'defaultCellColor':
            $cell.css( 'background-color', '' );
            break;
        case 'setCellContent':
                $cell.html( setVal );
            break;
        case 'setCellClass':
            $cell.addClass(setVal);
            break;
        case 'removeCellClass':
            $cell.removeClass(setVal);
            break;
        case 'setRowColor':
            $cell.closest('tr').find('td').css('background-color', setVal);
            break;
        case 'defaultRowColor':
            $cell.closest('tr').find('td').css('background-color', '');
            break;
        case 'setRowClass':
            $cell.closest('tr').addClass(setVal);
            break;
        case 'addColumnClass':
            var index = $cell.index()+1;
            $cell
                .closest('table.wpDataTable')
                .find('tbody td:nth-child('+index+')')
                .addClass(setVal);
            break;
        case 'setColumnColor':
            var index = $cell.index()+1;
            $cell
                .closest('table.wpDataTable')
                .find('tbody td:nth-child('+index+')')
                .css('background-color', setVal);
            break;
    }
}

function wdtDialog(str, title) {
    var dialogId = Math.floor((Math.random() * 1000) + 1);
    var dialog_str = '<div class="remodal wpDataTables wdtRemodal" id="remodal-' + dialogId + '"><h1>' + title + '</h1>';
    dialog_str += str;
    dialog_str += '</div>';
    jQuery(dialog_str).remodal({
        type: 'inline',
        preloader: false
    });
    return jQuery('#remodal-' + dialogId);
}

function wdtAddOverlay(table_selector) {
    jQuery(table_selector).addClass('overlayed');
}

function wdtRemoveOverlay(table_selector) {
    jQuery(table_selector).removeClass('overlayed');
}

jQuery.fn.dataTableExt.oStdClasses.sWrapper = "wpDataTables wpDataTablesWrapper";
