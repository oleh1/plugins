jQuery.fn.dataTable.TableTools.buttons.download = jQuery.extend(
    true, {},
    jQuery.fn.dataTable.TableTools.buttonBase, {
        "sButtonText": "Download",
        "sUrl": "",
        "sType": "POST",
        "fnData": false,
        "fnClick": function(button, config) {

            var dt = new jQuery.fn.dataTable.Api(this.s.dt),
                data = dt.ajax.params() || {},
                tr_cnt = 0,
                td_cnt = 0,
                theTable,
                isEditable,
                contentWindow,
                form,
                action_input,
                data_input,
                order_input,
                title_input,
                nonce_input,
                nonce_tit_input,
                exp_nnc,
                exp_nnc_tit;

            if (config.fnData) {
                config.fnData(data);
            }

            var tttt = jQuery(button).parents('.wpdt-outs').find('table.wpdt-ins').dataTable(), tttap, tti;
            tttt = tttt.api();
            tttap = tttt.order();

            var iframe = jQuery('<iframe/>', {
                    id: "RemotingIFrame"
                }).css({
                    border: 'none',
                    width: 0,
                    height: 0
                })
                .appendTo('body');

            contentWindow = iframe[0].contentWindow;
            contentWindow.document.open();
            contentWindow.document.close();

            form = contentWindow.document.createElement('form');
            form.setAttribute('method', config.sType);
            form.setAttribute('action', config.sUrl);

            theTable = jQuery(button).parents('.wpdt-outs').find('.dataTables_scrollBody table.wpdt-ins');

            isEditable = theTable.data('editable') !== undefined ? 1 : 0;

            exp_nnc = jQuery(button).parents('.wpdt-outs').find('.wpdt_export_nonce');

            nonce_input = contentWindow.document.createElement('input');
            nonce_input.name = 'wpdt_export_nonce';
            nonce_input.value = exp_nnc ? exp_nnc.val() : 0;

            exp_nnc_tit = exp_nnc && exp_nnc.attr('name') !== undefined ? exp_nnc.attr('name') : 0;

            nonce_tit_input = contentWindow.document.createElement('input');
            nonce_tit_input.name = 'wpdt_nctit';
            nonce_tit_input.value = exp_nnc_tit;

            action_input = contentWindow.document.createElement('input');
            action_input.name = 'action';
            action_input.value = 'wpdt_custom_export_xls';

            data_input = contentWindow.document.createElement('input');
            data_input.name = 'tableId';
            data_input.value = config.tableId;

            title_input = contentWindow.document.createElement('input');
            title_input.name = 'table_title';
            title_input.value = jQuery(button).parents('.wpdt-outs').find('table.wpdt-ins').data('table-title');

            form.appendChild(action_input);
            form.appendChild(data_input);
            
            form.appendChild(title_input);
            form.appendChild(nonce_input);
            form.appendChild(nonce_tit_input);

            order_input = contentWindow.document.createElement('input');
            order_input.name = 'order';
            order_input.value = tttap;
            contentWindow.document.body.appendChild(form);

            form.appendChild(order_input);

            form.submit();

            return false;
        }
    }
);

jQuery.fn.dataTable.pipeline = function(opts) {
    var conf = jQuery.extend({
        pages: 5,
        url: '',
        data: null,
        method: 'GET'
    }, opts);

    var cacheLower = -1;
    var cacheUpper = null;
    var cacheLastRequest = null;
    var cacheLastJson = null;

    return function(request, drawCallback, settings) {
        var ajax = false;
        var requestStart = request.start;
        var drawStart = request.start;
        var requestLength = request.length;
        var requestEnd = requestStart + requestLength;

        if (settings.clearCache) {
            ajax = true;
            settings.clearCache = false;
        } else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
            ajax = true;
        } else if (JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) ||
            JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
            JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)
        ) {
            ajax = true;
        }
        cacheLastRequest = jQuery.extend(true, {}, request);

        if (ajax) {

            if (requestStart < cacheLower) {
                requestStart = requestStart - (requestLength * (conf.pages - 1));

                if (requestStart < 0) {
                    requestStart = 0;
                }
            }

            cacheLower = requestStart;
            cacheUpper = requestStart + (requestLength * conf.pages);

            request.start = requestStart;
            request.length = requestLength * conf.pages;

            if (jQuery.isFunction(conf.data)) {
                var d = conf.data(request);
                if (d) {
                    jQuery.extend(request, d);
                }
            } else if (jQuery.isPlainObject(conf.data)) {
                jQuery.extend(request, conf.data);
            }

            settings.jqXHR = jQuery.ajax({
                "type": conf.method,
                "url": conf.url,
                "data": request,
                "dataType": "json",
                "cache": false,
                "success": function(json) {

                    cacheLastJson = jQuery.extend(true, {}, json);

                    if (cacheLower != drawStart) {
                        json.data.splice(0, drawStart - cacheLower);
                    }
                    json.data.splice(requestLength, json.data.length);

                    drawCallback(json);
                }
            });
        } else {
            json = jQuery.extend(true, {}, cacheLastJson);
            json.draw = request.draw;
            json.data.splice(0, requestStart - cacheLower);
            json.data.splice(requestLength, json.data.length);

            drawCallback(json);
        }
    };
};

jQuery.fn.dataTable.Api.register('clearPipeline()', function() {
    return this.iterator('table', function(settings) {
        settings.clearCache = true;
    });
});

var wpdt_charts = [],
    wpdt_charts_cnt = 0,
    wpdt_tables = [],
    wpdt_tablesTools = [],
    wpdt_cnt = 0,
    wpdt_modal_isOpen = 0,
    wpdt_modal_inited = 0,
    wpdt_modal_html = '<div class="wpdt-modal-out"><div class="wpdt-modal-middle"><div class="wpdt-modal-in"><div class="wpdt-modal-content"><div class="wpdt-modal-content-inner"></div><div class="wpdt-modal-buttons-wrapper"><button class="wpdt-cancel-modal DTTT_button DTTT_button_text">' + wpdt_public_str.cancel_str + '</button><button class="wpdt-save-modal DTTT_button DTTT_button_text">' + wpdt_public_str.save_str + '</button></div></div></div></div></div>',
    wpdt_modal_html_add = '<div class="wpdt-modal-out"><div class="wpdt-modal-middle"><div class="wpdt-modal-in"><div class="wpdt-modal-content"><div class="wpdt-modal-content-inner"></div><div class="wpdt-modal-buttons-wrapper"><button class="wpdt-cancel-modal DTTT_button DTTT_button_text">' + wpdt_public_str.cancel_str + '</button><button class="wpdt-add-modal DTTT_button DTTT_button_text">' + wpdt_public_str.create_str + '</button></div></div></div></div></div>',
    wpdt_modal_html_remove = '<div class="wpdt-modal-out"><div class="wpdt-modal-middle"><div class="wpdt-modal-in"><div class="wpdt-modal-content"><div class="wpdt-modal-content-inner"></div><div class="wpdt-modal-buttons-wrapper"><button class="wpdt-cancel-modal DTTT_button DTTT_button_text">' + wpdt_public_str.cancel_str + '</button><button class="wpdt-remove-modal DTTT_button DTTT_button_text">' + wpdt_public_str.remove_str + '</button></div></div></div></div></div>',
    wpdt_modal_inited_add = 0,
    wpdt_modal_inited_remove = 0;

function wpdt_init_tables() {

    'use strict';

    var tblsL = jQuery('.wpdt-ins').length;

    jQuery('.wpdt-ins').each(function() {

        var that = this,
            j,
            dataTable_attr = {
                "pagingType": "simple_numbers",
                "oLanguage": {
                    "oArea": {
                        "sSortAscending": wpdt_public_str.sSortAscending,
                        "sSortDescending": wpdt_public_str.sSortDescending
                    },
                    "oPaginate": {
                        "sFirst": wpdt_public_str.sFirst,
                        "sLast": wpdt_public_str.sLast,
                        "sNext": wpdt_public_str.sNext,
                        "sPrevious": wpdt_public_str.sPrevious
                    },
                    "sEmptyTable": wpdt_public_str.sEmptyTable,
                    "sInfo": wpdt_public_str.sInfo,
                    "sInfoEmpty": wpdt_public_str.sInfoEmpty,
                    "sInfoFiltered": wpdt_public_str.sInfoFiltered,
                    "sInfoPostFix": "",
                    "sInfoThousands": "'",
                    "sLengthMenu": wpdt_public_str.show_str + ' <select>' +
                        '<option value="5">' + wpdt_public_str.five_str + '</option>' +
                        '<option value="10">' + wpdt_public_str.ten_str + '</option>' +
                        '<option value="15">' + wpdt_public_str.fifteen_str + '</option>' +
                        '<option value="20">' + wpdt_public_str.twenty_str + '</option>' +
                        '<option value="25">' + wpdt_public_str.twentyfive_str + '</option>' +
                        '<option value="30">' + wpdt_public_str.thirty_str + '</option>' +
                        '<option value="40">' + wpdt_public_str.forty_str + '</option>' +
                        '<option value="50">' + wpdt_public_str.fifty_str + '</option>' +
                        '<option value="100">' + wpdt_public_str.ahundred_str + '</option>' +
                        '<option value="-1">' + wpdt_public_str.all_str + '</option>' +
                        '</select> ' + wpdt_public_str.entries_str,
                    "sLoadingRecords": wpdt_public_str.sLoadingRecords,
                    "sProcessing": wpdt_public_str.sProcessing,
                    "sSearch": wpdt_public_str.sSearch,
                    "sZeroRecords": wpdt_public_str.sZeroRecords
                }
            },
            table_title = jQuery(this).data('show-tools') !== undefined ? jQuery(this).data('table-title') : '',
            lazy_load = jQuery(this).data('lazy-load') !== undefined ? true : false,
            entries_perPage = jQuery(this).data('epp') !== undefined ? parseInt(jQuery(this).data('epp'), 10) : 10,
            t_wpdt_id = jQuery(this).data('wpdt-id') !== undefined ? parseInt(jQuery(this).data('wpdt-id'), 10) : 0,
            searchExcl = false,
            columns_num = jQuery(this).data('wpdt-columns-num') !== undefined ? parseInt(jQuery(this).data('wpdt-columns-num'), 10) : 0,
            isEditable = jQuery(this).data('editable') !== undefined ? 1 : 0,
            exportColumns = [],
            hCols = jQuery(this).data('wpdt-hc') !== undefined ? jQuery(this).data('wpdt-hc') : '';

        columns_num = isEditable ? columns_num + 2 : columns_num;

        if ( jQuery(this).data('show-tools') !== undefined && jQuery(this).data('show-tools') === true) {

            for (j = 0; j < columns_num; j++) {

                if (!isEditable || j > 1) {

                    exportColumns.push(j);
                }
            }

            dataTable_attr.dom = 'fT<"clear">lrtip';
            dataTable_attr.oTableTools = {
                "sSwfPath": wpdt_public_str.tablestools_swf_url,
                "aButtons": [{
                    "sExtends": "copy",
                    "sButtonText": wpdt_public_str.copy_button_text,
                    "sInfo": "<h6>" + wpdt_public_str.copy_view_title + "</h6><p>" + wpdt_public_str.copy_view_msg + "</p>",
                    "sLines": wpdt_public_str.lines_str_txt,
                    "sLine": wpdt_public_str.line_str_txt,
                    "mColumns": exportColumns
                }, {
                    "sExtends": "print",
                    "sButtonText": wpdt_public_str.print_button_text,
                    "sInfo": "<h6>" + wpdt_public_str.print_view_title + "</h6><p>" + wpdt_public_str.print_view_msg + "</p>"
                }, {
                    "sExtends": "pdf",
                    "sButtonText": wpdt_public_str.pdf_button_text,
                    "sTitle": table_title,
                    "mColumns": exportColumns
                }, {
                    "sExtends": "download",
                    "sButtonText": wpdt_public_str.excel_button_text,
                    "sUrl": wpdt_public_str.wpdt_ajaxurl,
                    "sType": "POST",
                    "tableId": t_wpdt_id
                }, {
                    "sExtends": "csv",
                    "sButtonText": wpdt_public_str.csv_button_text,
                    "sTitle": table_title,
                    "mColumns": exportColumns
                }, ]
            };
        }

        if (jQuery(this).data('show-search') !== undefined && jQuery(this).data('show-search') === true) {

            dataTable_attr.searching = true;

            if (jQuery(this).data('wpdt-s-excl') !== undefined) {

                searchExcl = jQuery(this).data('wpdt-s-excl');

                if (searchExcl) {

                    if (searchExcl.length) {

                        dataTable_attr.columns = [];

                        for (j = 0; j < columns_num; j = j + 1) {

                            if (isEditable && (j === 0 || j === 1)) {

                                dataTable_attr.columns[j] = {
                                    'searchable': false
                                };

                            } else {

                                dataTable_attr.columns[j] = {
                                    'searchable': searchExcl.indexOf(j) > -1 ? false : true
                                };
                            }
                        }
                    }
                }
            }
        } else {

            dataTable_attr.searching = false;
        }

        if (isEditable) {

            dataTable_attr.columnDefs = [{
                "targets": 'wpdt-not-sort',
                "orderable": false
            }];

            dataTable_attr.fnRowCallback = function(nRow, aData, iDisplayIndex) {

                wpdt_init_tables_buttons_actions(nRow, aData);

                return nRow;
            };

            if( wpdt_cnt + 1 === tblsL ){
                wpdt_init_table_header_edit();
            }
        }

        if (lazy_load) {
            dataTable_attr.processing = true;
            dataTable_attr.serverSide = true;
            dataTable_attr.ajax = jQuery.fn.dataTable.pipeline({
                pages: parseInt(entries_perPage, 10) > 0 ? entries_perPage : 200,
                url: wpdt_public_str.wpdt_ajaxurl,
                method: 'POST',
                data: {
                    action: 'get_sside_tdata',
                    wpdt_id: t_wpdt_id,
                    hc : hCols,
                    editable: isEditable
                }
            });
        }

        wpdt_tables[wpdt_cnt] = jQuery(that).DataTable(dataTable_attr);

        wpdt_cnt = wpdt_cnt + 1;
    });
}

function wpdt_init_table_header_edit() {

    jQuery('.wpdt-edit-header-row').unbind('click').bind('click', function() {

        var that = this,
            table = jQuery(this).parents('.wpdt-ins'),
            dt_ins,
            tableId = table.data("wpdt-id"),
            hiddenColums,
            tablePage_info,
            isLazyLoad,
            tableHeaders = [],
            columnsNum,
            row = jQuery(this).closest('tr'),
            rowNumId = 'header',
            rowIndex = 'header',
            rowData = [],
            i;

        jQuery('.wpdt-ins').each(function(){

            var tt = jQuery(this);

            if( table[0] !== tt[0] && tt.data("wpdt-id") === tableId ){

                hiddenColums = tt.data("wpdt-hc");
                dt_ins = tt.dataTable();
                tablePage_info = dt_ins.api().page.info();
                isLazyLoad = tt.data('lazy-load') !== undefined ? 1 : 0;
            }
        });

        jQuery.each(dt_ins.dataTableSettings[0].aoColumns, function(i,v) {
            
            if(i>1){
                
                tableHeaders.push("Column " + (i-1) );
                rowData.push(v.sTitle);
            }
        });

        columnsNum = rowData.length;

        wpdt_edit_row_modal(row, tableId, rowNumId, rowIndex, rowData, tableHeaders, hiddenColums, isLazyLoad, dt_ins);

        return false;
    });
}

function wpdt_init_tables_buttons_actions(t, b) {

    'use strict';

    jQuery(t).find('.wpdt-edit-row').unbind('click').bind('click', function() {

        var table = jQuery(this).parents('.wpdt-ins'),
            row = jQuery(this).closest('tr'),
            tableId = table.data("wpdt-id"),
            hiddenColums = table.data("wpdt-hc"),
            dt_ins = jQuery(table).dataTable(),
            tablePage_info = dt_ins.api().page.info(),
            isLazyLoad = table.data('lazy-load') !== undefined ? 1 : 0,
            tableHeaders = [],
            columnsNum,
            rowNumId,
            rowIndex,
            rowData = [],
            i;

        jQuery.each(dt_ins.dataTableSettings[0].aoColumns, function(i,v) {
            
            if(i>1){
                
                tableHeaders.push(v.sTitle);
            }
        });

        for (i = 0; i < b.length; i = i + 1) {

            if (i > 1) {

                rowData.push(b[i]);
            }
        }

        columnsNum = rowData.length;

        rowIndex = dt_ins.fnGetPosition(row[0]) + (tablePage_info.length * tablePage_info.page);
        rowNumId = parseInt(jQuery(this).closest('tr').find('td:nth-child(2)').text(), 10);

        wpdt_edit_row_modal(row, tableId, rowNumId, rowIndex, rowData, tableHeaders, hiddenColums, isLazyLoad, dt_ins);

        return false;
    });

    jQuery(t).find('.wpdt-add-row').unbind('click').bind('click', function() {

        var table = jQuery(this).parents('.wpdt-ins'),
            row = jQuery(this).closest('tr'),
            tableId = table.data("wpdt-id"),
            hiddenColums = table.data("wpdt-hc"),
            dt_ins = jQuery(table).dataTable(),
            tablePage_info = dt_ins.api().page.info(),
            isLazyLoad = table.data('lazy-load') !== undefined ? 1 : 0,
            rowNumId = parseInt(jQuery(this).closest('tr').find('td:nth-child(2)').text(), 10),
            tableHeaders = [],
            rowData = [];

        jQuery.each(dt_ins.dataTableSettings[0].aoColumns, function(i,v) {
            
            if(i>1){
                
                tableHeaders.push(v.sTitle);
                rowData.push(v.sTitle);
            }
        });

        wpdt_add_row_modal( table, tableId, row, rowNumId, dt_ins, tablePage_info, isLazyLoad, tableHeaders, rowData );

        return false;
    });

    jQuery(t).find('.wpdt-remove-row').unbind('click').bind('click', function() {

        var table = jQuery(this).parents('.wpdt-ins'),
            row = jQuery(this).closest('tr'),
            tableId = table.data("wpdt-id"),
            hiddenColums = table.data("wpdt-hc"),
            dt_ins = jQuery(table).dataTable(),
            tablePage_info = dt_ins.api().page.info(),
            isLazyLoad = table.data('lazy-load') !== undefined ? 1 : 0,
            rowNumId = parseInt(jQuery(this).closest('tr').find('td:nth-child(2)').text(), 10);

        wpdt_remove_row_modal( table, tableId, row, rowNumId, dt_ins, tablePage_info, isLazyLoad );

        return false;
    });
}

function wpdt_add_row_modal( table, tableId, row, rowNumId, dt_ins, tablePage_info, isLazyLoad, headers, records ){

    'use strict';

    if (wpdt_modal_inited_add === 0) {

        var aa = '<div class="wpdt-new-row-pos-area"><label style="max-width:40%;">Add new row: </label><select id="wpdt-new-row-rel-pos" style="max-width:40%;"><option value="before">Before selected row</option><option value="after" selected="selected">After selected row</option></select></div>',
            loopArr = records.length > headers.length ? records : headers,
            loopArr_length = loopArr.length,
            cnt,
            t = '',
            ht,
            hidden_tid,
            hidden_rid,
            hidden_hc,
            recordOuter,
            recordVal,
            recordLbl;

        if ( jQuery('body').append( wpdt_modal_html_add ) ) {

            for (cnt = 0; cnt < loopArr_length; cnt = cnt + 1) {

                ht = headers[cnt] !== undefined ? headers[cnt] : '';

                recordOuter = jQuery('<div></div>').addClass('wpdt-arrow-setting');
                recordVal = jQuery('<input type="text" class="wpdt-edit-col wpdt-edit-col-' + cnt + '">').attr('value', '');
                recordLbl = jQuery('<label></label>').html(ht + ':');

                recordOuter.prepend(recordLbl).append(recordVal);

                t = t + jQuery('<div></div>').append(recordOuter).html();
            }

            jQuery(".wpdt-modal-content-inner").before(aa);

            jQuery(".wpdt-modal-content-inner").prepend(t);

            jQuery(".wpdt-modal-out").fadeIn('fast', function() {

                wpdt_onAdd_modalShow( this, tableId, row, rowNumId, isLazyLoad, dt_ins );
            });
        }

        wpdt_modal_inited_add = 1;
    }
    else{

        jQuery(".wpdt-modal-out").fadeIn('fast', function() {

            wpdt_onAdd_modalShow( this, tableId, row, rowNumIdw, isLazyLoad, dt_ins ); 
        });
    }

    wpdt_modal_isOpen = 1;
}

function wpdt_remove_row_modal( table, tableId, row, rowNumId, dt_ins, tablePage_info, isLazyLoad ){

    'use strict';

    if (wpdt_modal_inited_remove === 0) {

        var recordOuter = jQuery('<div></div>').addClass('wpdt-arrow-setting'),
            recordMsg = '<p class="wpdt-modal-p">Confirm row removal.</p>',
            recordMsg2 = '<p class="wpdt-modal-p sec">This action cannot be undone.</p>',
            t='';

        t = recordMsg + recordMsg2;

        if (jQuery('body').append(wpdt_modal_html_remove)) {

            jQuery(".wpdt-modal-content-inner").html( t );

            jQuery(".wpdt-modal-out").fadeIn('fast', function() {

                wpdt_onRemove_modalShow( this, tableId, row, rowNumId, isLazyLoad, dt_ins );
            });
        }

        wpdt_modal_inited_add = 1;
    }
    else{

        jQuery(".wpdt-modal-out").fadeIn('fast', function() {

            wpdt_onRemove_modalShow( this, tableId, row, rowNumId, isLazyLoad, dt_ins ); 
        });
    }

    wpdt_modal_isOpen = 1;
}

function wpdt_edit_row_modal(row, tableId, rowId, rowIndex, records, headers, hiddenColums, isLazyLoad, dt_ins) {

    'use strict';

    var loopArr = records.length > headers.length ? records : headers,
        loopArr_length = loopArr.length,
        cnt,
        t = '',
        ht = '',
        dt,
        hidden_tid,
        hidden_rid,
        hidden_hc,
        recordOuter,
        recordVal,
        recordLbl;

    if (wpdt_modal_inited === 0) {

        if (jQuery('body').append(wpdt_modal_html)) {

            hidden_tid = jQuery('<input type="hidden" class="wpdt-md-tid"/>').attr('value', tableId);
            hidden_rid = jQuery('<input type="hidden" class="wpdt-md-rid"/>').attr('value', rowId);
            hidden_hc = jQuery('<input type="hidden" class="wpdt-md-hc"/>').attr('value', hiddenColums);

            jQuery(".wpdt-modal-content-inner").append(hidden_tid).append(hidden_rid);

            for (cnt = 0; cnt < loopArr_length; cnt = cnt + 1) {

                ht = headers[cnt] !== undefined ? headers[cnt] : '';
                dt = records[cnt] !== undefined ? records[cnt] : '';

                recordOuter = jQuery('<div></div>').addClass('wpdt-arrow-setting');
                recordVal = jQuery('<input type="text" class="wpdt-edit-col wpdt-edit-col-' + cnt + '">').attr('value', dt);
                recordLbl = jQuery('<label></label>').html(ht + ':');

                recordOuter.prepend(recordLbl).append(recordVal);

                t = t + jQuery('<div></div>').append(recordOuter).html();
            }

            jQuery(".wpdt-modal-content-inner").prepend(t);

            jQuery(".wpdt-modal-out").fadeIn('fast', function() {

                wpdt_onEdit_modalShow(this, row, rowId, rowIndex, hiddenColums, isLazyLoad, dt_ins);
            });
        }

        document.onkeydown = function(evt) {

            evt = evt || window.event;

            if (evt.keyCode == 27) {

                if (wpdt_modal_isOpen) {

                    wpdt_close_row_modal();
                }
            }
        };

        wpdt_modal_inited = 1;
    } else {

        hidden_tid = jQuery('<input type="hidden" class="wpdt-md-tid"/>').attr('value', tableId);
        hidden_rid = jQuery('<input type="hidden" class="wpdt-md-rid"/>').attr('value', rowId);
        hidden_hc = jQuery('<input type="hidden" class="wpdt-md-hc"/>').attr('value', hiddenColums);

        jQuery(".wpdt-modal-content-inner").append(hidden_tid).append(hidden_rid);

        for (cnt = 0; cnt < loopArr_length; cnt = cnt + 1) {

            ht = headers[cnt] !== undefined ? headers[cnt] : '';
            dt = records[cnt] !== undefined ? records[cnt] : '';

            recordOuter = jQuery('<div></div>').addClass('wpdt-arrow-setting');
            recordVal = jQuery('<input type="text" class="wpdt-edit-col wpdt-edit-col-' + cnt + '">').attr('value', dt);
            recordLbl = jQuery('<label></label>').html(ht + ':');

            recordOuter.prepend(recordLbl).append(recordVal);

            t = t + jQuery('<div></div>').append(recordOuter).html();
        }

        jQuery(".wpdt-modal-content-inner").prepend(t);

        jQuery(".wpdt-modal-out").fadeIn('fast', function() {

            wpdt_onEdit_modalShow(this, row, rowId, rowIndex, hiddenColums, isLazyLoad, dt_ins);
        });
    }

    wpdt_modal_isOpen = 1;
}

function wpdt_onAdd_modalShow( w, tableId, row, rowNumId, isLazyLoad, dt_ins ){

    'use strict';

    jQuery(w).find('.wpdt-add-modal').unbind('click').bind('click', function() {

        var table = jQuery("table[data-wpdt-id='" + tableId + "']"),
            dt_ins = table.dataTable(),
            tableInfo = table.DataTable(),
            tnnc = table.parents('.wpdt-outs').find('.wpdt_export_nonce').val(),
            tnnct = table.parents('.wpdt-outs').find('.wpdt_export_nonce').attr('name'),
            beforeClickedRow = jQuery('#wpdt-new-row-rel-pos').val() === 'before' ? 1 : 0,
            rowCells = row.find('td'),
            rowCells_length = jQuery('.wpdt-edit-col').length,
            ccnt = 0,
            rr = [];

       jQuery('.wpdt-edit-col').each(function(){

            rr.push( jQuery(this).val() );

            if( ( ccnt + 1 ) === rowCells_length ){

                jQuery.ajax({
                    type: "post",
                    url: wpdt_public_str.wpdt_ajaxurl,
                    dataType: 'json',
                    data: {
                        action: 'wpdt_add_table_row',
                        tid: tableId,
                        rid: rowNumId,
                        bfr: beforeClickedRow,
                        newrow: rr,
                        nnc: tnnc,
                        nnct: tnnct
                    },
                    success: function(data, textStatus, XMLHttpRequest) {

                        if (data.error === 0) {

                            dt_ins.api().clearPipeline().draw(false);

                        } else {

                            console.log('error ajax - WP Dynamic tables - Add row to table.');
                        }
                    },
                    error: function(data, textStatus, XMLHttpRequest) {

                        console.log('error ajax - WP Dynamic tables - Add row to table.');
                    }
                });
            }

            ccnt++;
        });

        setTimeout(function() {
            wpdt_close_row_modal();
        }, 300);
    });

    jQuery(w).find('.wpdt-cancel-modal').unbind('click').bind('click', function() {

        wpdt_close_row_modal();
    });
}

function wpdt_onRemove_modalShow( w, tableId, row, rowNumId, isLazyLoad, dt_ins ){

    jQuery(w).find('.wpdt-remove-modal').unbind('click').bind('click', function() {

        var table = jQuery("table[data-wpdt-id='" + tableId + "']"),
            dt_ins = table.dataTable(),
            tableInfo = table.DataTable(),
            tnnc = table.parents('.wpdt-outs').find('.wpdt_export_nonce').val(),
            tnnct = table.parents('.wpdt-outs').find('.wpdt_export_nonce').attr('name'),
            beforeClickedRow = 0;

        jQuery.ajax({
            type: "post",
            url: wpdt_public_str.wpdt_ajaxurl,
            dataType: 'json',
            data: {
                action: 'wpdt_remove_table_row',
                tid: tableId,
                rid: rowNumId,
                nnc: tnnc,
                nnct: tnnct
            },
            success: function(data, textStatus, XMLHttpRequest) {

                if (data.error === 0) {

                    dt_ins.api().clearPipeline().draw(false);

                } else {

                    console.log('error ajax - WP Dynamic tables - Remove row from table.');
                }
            },
            error: function(data, textStatus, XMLHttpRequest) {

                console.log('error ajax - WP Dynamic tables - Remove row from table.');
            }
        });

        setTimeout(function() {
            wpdt_close_row_modal();
        }, 300);
    });

    jQuery(w).find('.wpdt-cancel-modal').unbind('click').bind('click', function() {

        wpdt_close_row_modal();
    });
}

function wpdt_onEdit_modalShow(w, row, rowId, rowIndex, hiddenColums, isLazyLoad, dt_ins) {

    'use strict';

    if( rowIndex === "header" ){

        jQuery('.wpdt-save-modal').addClass('wpdt-save-modal-headers').removeClass('wpdt-save-modal');
    }

    jQuery(w).find('.wpdt-cancel-modal').unbind('click').bind('click', function() {

        wpdt_close_row_modal();
    });

    jQuery(w).find('.wpdt-save-modal-headers').unbind('click').bind('click', function() {

        var recordsObj = jQuery('.wpdt-modal-out').find('.wpdt-edit-col'),
            recordsLength = recordsObj.length,
            cnt = 0,
            vlarr = [],
            wpdtTId = jQuery('.wpdt-modal-out').find('.wpdt-md-tid').val(),
            wpdtRId = jQuery('.wpdt-modal-out').find('.wpdt-md-rid').val(),
            fc = 0;

        recordsObj.each(function() {

            vlarr.push(jQuery(this).val());

            if (cnt + 1 === recordsLength) {

                row.find('th').each(function() {

                    var tui, preHt, that = this;

                    if (fc > 1) {

                        tui = vlarr[fc - 2];
                        
                        preHt = jQuery(this).html();

                        jQuery.each(dt_ins.dataTableSettings[0].aoColumns, function(i,v) {
        
                            if(i>1){
                                
                                if( v.sTitle === preHt ){

                                    dt_ins.dataTableSettings[0].aoColumns[i].sTitle = tui;
                                    jQuery(that).html( tui );
                                }
                            }
                        });

                        if (fc + 1 === row.find('th').length) {

                            wpdt_save_row_changes(wpdtTId, wpdtRId, hiddenColums, vlarr, isLazyLoad);
                        }
                    }

                    fc = fc + 1;
                });

                setTimeout(function() {
                    wpdt_close_row_modal();
                }, 300);
            } else {

                cnt = cnt + 1;
            }
        });

        return false;
    });

    jQuery(w).find('.wpdt-save-modal').unbind('click').bind('click', function() {

        var recordsObj = jQuery('.wpdt-modal-out').find('.wpdt-edit-col'),
            recordsLength = recordsObj.length,
            cnt = 0,
            vlarr = [],
            wpdtTId = jQuery('.wpdt-modal-out').find('.wpdt-md-tid').val(),
            wpdtRId = jQuery('.wpdt-modal-out').find('.wpdt-md-rid').val(),
            fc = 0;

        recordsObj.each(function() {

            vlarr.push(jQuery(this).val());

            if (cnt + 1 === recordsLength) {

                row.find('td').each(function() {

                    if (fc > 1) {

                        if (!isLazyLoad) {

                            dt_ins.fnUpdate(vlarr[fc - 2], rowIndex, fc);
                        } else {

                            jQuery(this).html(vlarr[fc - 2]);
                        }

                        if (fc + 1 === row.find('td').length) {

                            wpdt_save_row_changes(wpdtTId, wpdtRId, hiddenColums, vlarr, isLazyLoad);
                        }
                    }

                    fc = fc + 1;
                });

                setTimeout(function() {
                    wpdt_close_row_modal();
                }, 300);
            } else {

                cnt = cnt + 1;
            }
        });
    });
}

function wpdt_close_row_modal() {

    'use strict';

    jQuery(".wpdt-modal-out").fadeOut('fast', function() {

        jQuery(".wpdt-modal-out").remove();
    });

    wpdt_modal_inited_remove = 0;
    wpdt_modal_inited_add = 0;
    wpdt_modal_inited = 0;

    wpdt_modal_isOpen = 0;
}

function wpdt_save_row_changes(tableId, rowId, hiddenColumns, rowData, isLazyLoad) {

    'use strict';

    var table = jQuery("table[data-wpdt-id='" + tableId + "']"),
        dt_ins = table.dataTable(),
        tnnc = table.parents('.wpdt-outs').find('.wpdt_export_nonce').val(),
        tnnct = table.parents('.wpdt-outs').find('.wpdt_export_nonce').attr('name');

    jQuery.ajax({
        type: "post",
        url: wpdt_public_str.wpdt_ajaxurl,
        dataType: 'json',
        data: {
            action: 'wpdt_fe_update_table',
            tid: tableId,
            rid: rowId,
            hc: hiddenColumns,
            rd: rowData,
            nnc: tnnc,
            nnct: tnnct
        },
        success: function(data, textStatus, XMLHttpRequest) {

            if (data.error === 0) {

                if (isLazyLoad) {

                    dt_ins.api().clearPipeline().draw(false);
                }
            } else {

                console.log('error ajax - WP Dynamic tables - Save table row data.');
            }
        },
        error: function(data, textStatus, XMLHttpRequest) {

            console.log('error ajax - WP Dynamic tables - Update table row data.');
        }
    });
}

function wpdt_init_charts() {

    'use strict';

    jQuery('.wpdt_chart_wrapper').each(function() {

        var columns_wrapperId = jQuery(this).attr('id'),
            columns = jQuery(this).find('.chart_hidden_calues'),
            columnsLength = columns.length,
            columnsValues = [],
            columnsCnt = 0,
            chartType = jQuery(this).data("chart-type") !== undefined ? jQuery.trim(jQuery(this).data("chart-type")) : 'line';

        if (columnsLength > 0) {

            wpdt_charts[wpdt_charts_cnt] = c3.generate({
                bindto: '#' + columns_wrapperId,
                data: {
                    columns: [],
                    type: chartType
                }
            });

            columns.each(function() {

                var uy = jQuery(this).val();

                uy = uy.split(',');
                columnsValues[columnsCnt] = uy;
                columnsCnt = columnsCnt + 1;

                if (columnsCnt === columnsLength) {

                    wpdt_charts[wpdt_charts_cnt].load({
                        columns: columnsValues
                    });

                    wpdt_charts_cnt = wpdt_charts_cnt + 1;
                }
            });
        }
    });
}

(function($) {

    'use strict';

    jQuery(function() {

        wpdt_init_tables();
        wpdt_init_charts();
    });

})(jQuery);
