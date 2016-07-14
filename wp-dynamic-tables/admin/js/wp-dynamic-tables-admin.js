var wpdt_media_uploader,
    wpdt_current_file_url = false,
    wpdt_files_columns_data = {};

function wpdt_init_admin() {

    'use strict';

    wpdt_init_admin_actions();
    wpdt_init_sortable_columns();
    wpdt_init_file_uploader();
    wpdt_verify_purchase_code();
}

function wpdt_init_admin_actions() {

    'use strict';

    jQuery('#wpdt_table_type').unbind('change').bind('change', function() {

        jQuery('.wpdt-file-upload-wrap').html("");
        jQuery(".wpdt-test-query-wrap").html("").removeClass('success-q').removeClass('error-q');
        jQuery("#wpdt_ext_mysql_query, #wpdt_mysql_query").removeClass('empty-val');

        wpdt_conditional_hide_settings('table_type_sel', jQuery(this));
    });

    jQuery('.checkbox-lbl span').unbind('click').bind('click', function(e) {

        if (e.currentTarget === e.target) {

            var ch = jQuery(this).parent('.checkbox-lbl').find('input[type="checkbox"]');

            if (ch.length) {

                ch.trigger("change");
            }
        }
    });

    jQuery('#wpdt_enable_chart').unbind('click').bind('click', function() {

        wpdt_conditional_hide_settings('wpdt_enable_chart');
    });

    jQuery('#wpdt_enable_front_edit').unbind('click').bind('click', function() {

        wpdt_conditional_hide_settings('wpdt_enable_front_edit');
    });

    jQuery('#wpdt_mysql_query_live_update').unbind('click').bind('click', function() {

        wpdt_conditional_hide_settings('wpdt_mysql_query_live_update');
    });

    jQuery('.wpdt_col_width_class').unbind('change').bind('change', function() {

        wpdt_conditional_hide_settings('table_col_width', jQuery(this));
    });

    jQuery('.wpdt-test-mysql-query').unbind('click').bind('click', function(e) {

        e.preventDefault();

        jQuery(".wpdt-test-query-wrap").html("").removeClass('success-q').removeClass('error-q');
        jQuery("#wpdt_ext_mysql_query, #wpdt_mysql_query").removeClass('empty-val');

        var isExtDb = jQuery(this).hasClass('ext-host') ? true : false,
            dbData = {
                query: "",
                host: "",
                name: "",
                username: "",
                pass: "",
                extdb: 0,
                nnc: jQuery.trim(jQuery('#wp_dynamic_tables_nonce').val())
            },
            validData;

        if (isExtDb) {

            dbData.extdb = 1;
            dbData.host = jQuery.trim(jQuery('#wpdt_ext_mysql_dbhost').val());
            dbData.name = jQuery.trim(jQuery('#wpdt_ext_mysql_dbname').val());
            dbData.username = jQuery.trim(jQuery('#wpdt_ext_mysql_dbusername').val());
            dbData.pass = jQuery.trim(jQuery('#wpdt_ext_mysql_dbpassword').val());
            dbData.query = jQuery.trim(jQuery('#wpdt_ext_mysql_query').val());
        } else {

            dbData.query = jQuery.trim(jQuery('#wpdt_mysql_query').val());
        }

        validData = dbData.query !== '' ? true : false;

        if (validData) {

            jQuery(".wpdt-query-test-spinner").fadeIn('fast');

            jQuery.ajax({
                type: "post",
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'get_db_table_columns_data',
                    dbdata: dbData
                },
                success: function(data, textStatus, XMLHttpRequest) {

                    if (data.error === 0) {

                        wpdt_get_current_columns_data(data.html, dbData.query);

                        jQuery(".wpdt-test-query-wrap").html(data.msg).addClass("success-q");
                    } else {

                        jQuery(".wpdt-test-query-wrap").html(data.msg).addClass("error-q");

                        jQuery(".tables-data-columns-outer").html(data.html).addClass("empty");
                    }

                    jQuery(".wpdt-query-test-spinner").fadeOut('fast');

                },
                error: function(data, textStatus, XMLHttpRequest) {

                    jQuery(".wpdt-query-test-spinner").fadeOut('fast');

                    console.log('error ajax - WP Dynamic tables - MySQL columns data.');
                }
            });
        } else {

            if (isExtDb) {

                jQuery("#wpdt_ext_mysql_query").addClass('empty-val');
            } else {

                jQuery("#wpdt_mysql_query").addClass('empty-val');
            }
        }

        return false;
    });


    jQuery('.wpdt_col_type_class').unbind('change').bind('change', function() {

        wpdt_conditional_hide_settings('table_col_type', jQuery(this));
    });
}

function wpdt_conditional_hide_settings(spec, e) {

    'use strict';

    if (spec === undefined || spec === 'table_type_sel') {

        var t = jQuery('#wpdt_table_type'),
            v = t.val();

        if (v === 'none') {

            jQuery('.wpdt-insert-mysql-table-wrapper').css('display', 'none');
            jQuery('.wpdt-insert-ext-mysql-table-wrapper').css('display', 'none');
            jQuery('.wpdt-insert-file-table-wrapper').css('display', 'none');
            jQuery('.wpdt-mysql-query-live-update-area').css('display', 'none');
        } else if (v === 'mysql_external') {
            jQuery('.wpdt-insert-mysql-table-wrapper').css('display', 'none');
            jQuery('.wpdt-insert-ext-mysql-table-wrapper').fadeIn();
            jQuery('.wpdt-insert-file-table-wrapper').css('display', 'none');
            jQuery('.wpdt-mysql-query-live-update-area').css('display', 'none');
        } else if (v === 'mysql') {
            jQuery('.wpdt-insert-mysql-table-wrapper').fadeIn();
            jQuery('.wpdt-insert-ext-mysql-table-wrapper').css('display', 'none');
            jQuery('.wpdt-insert-file-table-wrapper').css('display', 'none');
            jQuery('.wpdt-mysql-query-live-update-area').fadeIn();
        } else {
            jQuery('.wpdt-insert-mysql-table-wrapper').css('display', 'none');
            jQuery('.wpdt-insert-ext-mysql-table-wrapper').css('display', 'none');
            jQuery('.wpdt-insert-file-table-wrapper').fadeIn();
            jQuery('.wpdt-mysql-query-live-update-area').css('display', 'none');

            switch (v) {
                case 'csv':
                    jQuery('#wpdt_upload_file_src').val(jQuery('#wpdt_upload_file_csv_filename').val());
                    break;
                case 'excel':
                    jQuery('#wpdt_upload_file_src').val(jQuery('#wpdt_upload_file_excel_filename').val());
                    break;
                case 'ods':
                    jQuery('#wpdt_upload_file_src').val(jQuery('#wpdt_upload_file_ods_filename').val());
                    break;
                case 'xml':
                    jQuery('#wpdt_upload_file_src').val(jQuery('#wpdt_upload_file_xml_filename').val());
                    break;
            }
        }
    }

    if (spec === undefined || spec === 'wpdt_enable_chart') {

        if (jQuery('#wpdt_enable_chart').is(':checked')) {

            jQuery('.display_on_enabled_chart').slideDown('fast');
        } else {

            jQuery('.display_on_enabled_chart').slideUp('fast');
        }
    }

    if (spec === undefined || spec === 'wpdt_enable_front_edit') {

        if (jQuery('#wpdt_enable_front_edit').is(':checked')) {

            jQuery('.display_on_front_edit').slideDown('fast');
        } else {

            jQuery('.display_on_front_edit').slideUp('fast');
        }
    }

    if (spec === undefined || spec === 'table_col_width') {

        if (e !== undefined) {

            if (e.val() === 'custom') {

                e.parents('.wp_dtables_columns_setting').find('.on-custom-col-width').slideDown('fast');
            } else {

                e.parents('.wp_dtables_columns_setting').find('.on-custom-col-width').slideUp('fast');
            }
        } else {

            jQuery('.wpdt_col_width_class').each(function() {

                e = jQuery(this);

                if (e.val() === 'custom') {

                    e.parents('.wp_dtables_columns_setting').find('.on-custom-col-width').slideDown('fast');
                } else {

                    e.parents('.wp_dtables_columns_setting').find('.on-custom-col-width').slideUp('fast');
                }

            });
        }
    }

    if (spec === undefined || spec === 'table_col_type') {

        if (e !== undefined) {

            if (e.val() === 'number') {
                e.parents('.tables-data-column-settings').find('.col-num-format-class').slideDown('fast');
                e.parents('.tables-data-column-settings').find('.col-currency-pos-class').slideUp('fast');
            }
            else if (e.val() === 'currency') {
                e.parents('.tables-data-column-settings').find('.col-num-format-class').slideDown('fast');
                e.parents('.tables-data-column-settings').find('.col-currency-pos-class').slideDown('fast');
            }
            else {
                e.parents('.tables-data-column-settings').find('.col-num-format-class').slideUp('fast');
                e.parents('.tables-data-column-settings').find('.col-currency-pos-class').slideUp('fast');
            }
        } else {

            jQuery('.wpdt_col_type_class').each(function() {

                e = jQuery(this);

                console.log( e );

                if (e.val() === 'number') {
                    e.parents('.tables-data-column-settings').find('.col-num-format-class').slideDown('fast');
                    e.parents('.tables-data-column-settings').find('.col-currency-pos-class').slideUp('fast');
                }
                else if (e.val() === 'currency') {
                    e.parents('.tables-data-column-settings').find('.col-num-format-class').slideDown('fast');
                    e.parents('.tables-data-column-settings').find('.col-currency-pos-class').slideDown('fast');
                }
                else {
                    e.parents('.tables-data-column-settings').find('.col-num-format-class').slideUp('fast');
                    e.parents('.tables-data-column-settings').find('.col-currency-pos-class').slideUp('fast');
                }

            });
        }
    }

    if (spec === undefined || spec === 'wpdt_mysql_query_live_update') {

        if (jQuery('#wpdt_mysql_query_live_update').is(':checked')) {

            jQuery('.wp_dtables_edit_rows_settings').slideUp('fast');
        } else {

            jQuery('.wp_dtables_edit_rows_settings').slideDown('fast');
        }
    }
}

function wpdt_init_sortable_columns() {

    'use strict';

    jQuery(".tables-data-columns").sortable({
        cursor: "move",
        scroll: false,
        revert: 0,
        start: function(event, ui) {
            ui.placeholder.height(ui.helper.height());
            ui.placeholder.width(ui.helper.width());
        },
        stop: function(event, ui) {

            var cnt = 0;

            jQuery('.tables-data-columns li').each(function() {
                jQuery(this).find('.hidden_custom_order_num_class').val(cnt);
                cnt = cnt + 1;
            });
        },
        placeholder: "wpdt_sortable_placeholder",
        opacity: '0.8'
    });
}

function wpdt_destroy_sortable_columns() {

    'use strict';

    if (jQuery(".tables-data-columns").data('sortable')) {

        jQuery(".tables-data-columns").sortable("destroy");
    }
}

function wpdt_on_columns_settings_change() {

    'use strict';

    wpdt_destroy_sortable_columns();
    wpdt_init_admin_actions();
    wpdt_init_sortable_columns();
    wpdt_conditional_hide_settings('table_col_width');
}

function wpdt_get_current_columns_data(new_html, new_url) {

    'use strict';

    var cl_cnt,
        columnsLength;

    if (wpdt_current_file_url) {

        if (new_html !== '-is-the-same-') {

            wpdt_files_columns_data[wpdt_current_file_url] = {
                html: '',
                data: {}
            };

            cl_cnt = 0;
            columnsLength = jQuery('.tables-data-columns li').length;

            jQuery('.tables-data-columns li').each(function() {

                var thisObj = jQuery(this),
                    k = {
                        default_order: thisObj.find('.hidden_default_order_num_class').val(),
                        custom_order: thisObj.find('.hidden_custom_order_num_class').val(),
                        publish: thisObj.find('.wpdt_publish_col_class').is(':checked') ? 'yes' : 'no',
                        width: thisObj.find('.wpdt_col_width_class').val(),
                        width_val: thisObj.find('.wpdt_col_width_val_class').val(),
                        width_type: thisObj.find('.wpdt_col_width_type_class').val(),
                        column_type: thisObj.find('.wpdt_col_type_class').val(),


                        column_num_format: thisObj.find('.wpdt_col_nformat_class').val(),
                        column_currency_pos: thisObj.find('.wpdt_col_cpos_class').val(),

                        default_sort: thisObj.find('.wpdt_col_def_sort_class').val(),
                        tablets_disable: thisObj.find('.wpdt_col_disable_tablets_class').is(':checked') ? 'yes' : 'no',
                        mobile_disable: thisObj.find('.wpdt_col_disable_mobiles_class').is(':checked') ? 'yes' : 'no',
                        chart_enable: thisObj.find('.wpdt_chart_enable_class').is(':checked') ? 'yes' : 'no'
                    };

                if (cl_cnt + 1 == columnsLength) {

                    wpdt_files_columns_data[wpdt_current_file_url].html = jQuery('.tables-data-columns-outer').html();
                    wpdt_files_columns_data[wpdt_current_file_url].data[cl_cnt] = k;

                    if (new_html !== '-is-the-same-') {

                        jQuery('.tables-data-columns-outer').removeClass('empty').html(new_html);
                    }

                    wpdt_current_file_url = new_url;

                    if (wpdt_files_columns_data[new_url] !== undefined && wpdt_files_columns_data[new_url].data !== undefined) {

                        var tcnt = 0,
                            te;

                        jQuery('.tables-data-columns li').each(function() {

                            te = wpdt_files_columns_data[new_url].data[tcnt];

                            jQuery(this).find('.hidden_default_order_num_class').val(te.default_order);
                            jQuery(this).find('.hidden_custom_order_num_class').val(te.custom_order);
                            jQuery(this).find('.wpdt_col_width_class').val(te.width);
                            jQuery(this).find('.wpdt_col_width_val_class').val(te.width_val);
                            jQuery(this).find('.wpdt_col_width_type_class').val(te.width_type);
                            jQuery(this).find('.wpdt_col_type_class').val(te.column_type);
                            jQuery(this).find('.wpdt_col_nformat_class').val(te.column_num_format);
                            jQuery(this).find('.wpdt_col_cpos_class').val(te.column_currency_pos);

                            jQuery(this).find('.wpdt_col_def_sort_class').val(te.default_sort);

                            if (te.publish === 'yes') {

                                jQuery(this).find('.wpdt_publish_col_class')[0].checked = true;
                            } else {

                                jQuery(this).find('.wpdt_publish_col_class')[0].checked = false;
                            }

                            if (te.tablets_disable === 'yes') {

                                jQuery(this).find('.wpdt_col_disable_tablets_class')[0].checked = true;
                            } else {

                                jQuery(this).find('.wpdt_col_disable_tablets_class')[0].checked = false;
                            }

                            if (te.mobile_disable === 'yes') {

                                jQuery(this).find('.wpdt_col_disable_mobiles_class')[0].checked = true;
                            } else {

                                jQuery(this).find('.wpdt_col_disable_mobiles_class')[0].checked = false;
                            }

                            if (te.chart_enable === 'yes') {

                                jQuery(this).find('.wpdt_chart_enable_class')[0].checked = true;
                            } else {

                                jQuery(this).find('.wpdt_chart_enable_class')[0].checked = false;
                            }

                            if (tcnt + 1 === columnsLength) {

                                wpdt_on_columns_settings_change();
                            } else {

                                tcnt = tcnt + 1;
                            }
                        });
                    } else {

                        wpdt_on_columns_settings_change();
                    }
                } else {

                    wpdt_files_columns_data[wpdt_current_file_url].data[cl_cnt] = k;

                    cl_cnt = cl_cnt + 1;
                }
            });
        }
    } else {

        if (new_html !== '-is-the-same-') {

            wpdt_current_file_url = new_url;

            if (jQuery('.tables-data-columns-outer').removeClass('empty').html(new_html)) {

                if (wpdt_files_columns_data[new_url] !== undefined && wpdt_files_columns_data[new_url].data !== undefined) {

                    var tcnt = 0,
                        te;

                    columnsLength = jQuery('.tables-data-columns li').length;

                    jQuery('.tables-data-columns li').each(function() {

                        te = wpdt_files_columns_data[new_url].data[tcnt];

                        jQuery(this).find('.hidden_default_order_num_class').val(te.default_order);
                        jQuery(this).find('.hidden_custom_order_num_class').val(te.custom_order);
                        jQuery(this).find('.wpdt_col_width_class').val(te.width);
                        jQuery(this).find('.wpdt_col_width_val_class').val(te.width_val);
                        jQuery(this).find('.wpdt_col_width_type_class').val(te.width_type);
                        jQuery(this).find('.wpdt_col_type_class').val(te.column_type);
                        jQuery(this).find('.wpdt_col_nformat_class').val(te.column_num_format);
                        jQuery(this).find('.wpdt_col_cpos_class').val(te.column_currency_pos);
                        jQuery(this).find('.wpdt_col_def_sort_class').val(te.default_sort);

                        if (te.publish === 'yes') {

                            jQuery(this).find('.wpdt_publish_col_class')[0].checked = true;
                        } else {

                            jQuery(this).find('.wpdt_publish_col_class')[0].checked = false;
                        }

                        if (te.tablets_disable === 'yes') {

                            jQuery(this).find('.wpdt_col_disable_tablets_class')[0].checked = true;
                        } else {

                            jQuery(this).find('.wpdt_col_disable_tablets_class')[0].checked = false;
                        }

                        if (te.mobile_disable === 'yes') {

                            jQuery(this).find('.wpdt_col_disable_mobiles_class')[0].checked = true;
                        } else {

                            jQuery(this).find('.wpdt_col_disable_mobiles_class')[0].checked = false;
                        }

                        if (te.chart_enable === 'yes') {

                            jQuery(this).find('.wpdt_chart_enable_class')[0].checked = true;
                        } else {

                            jQuery(this).find('.wpdt_chart_enable_class')[0].checked = false;
                        }

                        if (tcnt + 1 === columnsLength) {

                            wpdt_on_columns_settings_change();
                        } else {

                            tcnt = tcnt + 1;
                        }
                    });
                } else {

                    wpdt_on_columns_settings_change();
                }
            }
        }
    }
}

function wpdt_get_file_columns_data(url, mime) {

    'use strict';

    var selectedType = jQuery('#wpdt_table_type').val(),
        continueOp = false;

    switch (selectedType) {
        case 'excel':
            continueOp = mime === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || mime === 'application/vnd.ms-excel' ? true : false;
            break;
        case 'csv':
            continueOp = mime === 'text/csv' ? true : false;
            break;
        case 'ods':
            continueOp = mime === 'application/vnd.oasis.opendocument.spreadsheet' ? true : false;
            break;
        case 'xml':
            continueOp = mime === 'application/xml' ? true : false;
            break;
    }

    if (continueOp) {

        if (wpdt_current_file_url && wpdt_files_columns_data[url] !== undefined && wpdt_files_columns_data[url].html !== undefined) {

            if (wpdt_current_file_url !== url) {

                wpdt_get_current_columns_data(wpdt_files_columns_data[url].html, url);
            }
        } else {

            if (wpdt_current_file_url === url) {

                wpdt_get_current_columns_data('-is-the-same-', url);
            } else {

                jQuery.ajax({
                    type: "post",
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        action: 'get_url_file_table_columns_data',
                        url: url,
                        mime: mime
                    },
                    success: function(data, textStatus, XMLHttpRequest) {

                        if (data.error === 0) {

                            wpdt_get_current_columns_data(data.html, url);

                        } else {

                            console.log('error ajax - WP Dynamic Tables - Get file columns data while call completed.');
                        }

                    },
                    error: function(data, textStatus, XMLHttpRequest) {

                        console.log('error ajax - WP Dynamic Tables - Get file columns data.');
                    }
                });

            }
        }
    } else {

        jQuery('.wpdt-file-upload-wrap').html(wpdt_admin_str.file_format_dismatch);
    }
}

function wpdt_init_file_uploader() {

    'use strict';

    jQuery('.wpdt-upload-file-button').unbind('click').bind('click', function(e) {

        e.preventDefault();

        jQuery('.wpdt-file-upload-wrap').html("");

        if (wpdt_media_uploader) {

            wpdt_media_uploader.open();

            return;
        }

        wpdt_media_uploader = wp.media.frames.file_frame = wp.media({
            multiple: false
        });

        wpdt_media_uploader.on('select', function() {

            var attachment = wpdt_media_uploader.state().get('selection').first().toJSON(),
                fileurl = attachment.url,
                filename = attachment.filename,
                mime_type = attachment.mime;

            jQuery('#wpdt_upload_file_src').val(filename);

            switch (jQuery('#wpdt_table_type').val()) {
                case 'csv':
                    jQuery('#wpdt_upload_file_csv_filename').val(filename);
                    jQuery('#wpdt_upload_file_csv_url').val(fileurl);
                    break;
                case 'excel':
                    jQuery('#wpdt_upload_file_excel_filename').val(filename);
                    jQuery('#wpdt_upload_file_excel_url').val(fileurl);
                    break;
                case 'ods':
                    jQuery('#wpdt_upload_file_ods_filename').val(filename);
                    jQuery('#wpdt_upload_file_ods_url').val(fileurl);
                    break;
                case 'xml':
                    jQuery('#wpdt_upload_file_xml_filename').val(filename);
                    jQuery('#wpdt_upload_file_xml_url').val(fileurl);
                    break;
            }

            wpdt_get_file_columns_data(fileurl, mime_type);
        });

        wpdt_media_uploader.open();
    });
}

function wpdt_on_license_activation_complete(msg, error) {

    'use strict';

    jQuery('.wpdt-license-buttons').removeClass('activating');

    if (!error) {

        jQuery('.form-product-license').removeClass('to-activate');

        jQuery('.form-product-license input[type="text"]').attr('disabled', 'disabled');
        jQuery('.form-product-license .wpdt-product-license-settings-save').attr('disabled', 'disabled');

        jQuery('#wpdt_is_active_license').val(1);
        jQuery('#wpdt_can_activate').val(0);
    }

    if (msg) {

        wpdt_display_license_message(msg, error);
    }
}

function wpdt_on_license_deactivation_complete(msg, error) {

    'use strict';

    jQuery('.wpdt-license-buttons').removeClass('activating');

    if (!error) {

        jQuery('.form-product-license').addClass('to-activate');

        jQuery('.form-product-license input[type="text"]').removeAttr('disabled');
        jQuery('.form-product-license .wpdt-product-license-settings-save').removeAttr('disabled');

        jQuery('#wpdt_is_active_license').val(0);
        jQuery('#wpdt_can_activate').val(1);
    }

    if (msg) {

        wpdt_display_license_message(msg, error);
    }
}

function wpdt_verify_purchase_code() {

    'use strict';

    jQuery('.wpdt-activate-license').on("click", function() {

        var can_activate = parseInt(jQuery('#wpdt_can_activate').val(), 10),
            is_already_active = parseInt(jQuery('#wpdt_is_active_license').val(), 10);

        if (is_already_active === 1) {

            wpdt_display_license_message(wpdt_admin_str.already_activated, 1);

            wpdt_on_license_deactivation_complete();
        } else {

            if (can_activate === 1) {

                jQuery('.wpdt-license-buttons').addClass('activating');

                jQuery.ajax({
                    type: "post",
                    url: ajaxurl,
                    dataType: "json",
                    data: {
                        action: 'wpdt_activate_license'
                    },
                    success: function(data, textStatus, XMLHttpRequest) {

                        wpdt_on_license_activation_complete(data.msg, data.error);
                    },
                    error: function(data, textStatus, XMLHttpRequest) {

                        console.log('error ajax - WP Dynamic Tables - activate license');

                        wpdt_on_license_activation_complete(wpdt_admin_str.activation_error_ajax, 1);
                    }
                });
            } else {

                wpdt_display_license_message(wpdt_admin_str.first_save_settings, 1);
            }
        }

        return false;
    });

    jQuery('.wpdt-deactivate-license').on("click", function() {

        var is_already_active = parseInt(jQuery('#wpdt_is_active_license').val(), 10);

        if (is_already_active === 1) {

            jQuery('.wpdt-license-buttons').addClass('activating');

            jQuery.ajax({
                type: "post",
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'wpdt_deactivate_license'
                },
                success: function(data, textStatus, XMLHttpRequest) {

                    wpdt_on_license_deactivation_complete(data.msg, data.error);
                },
                error: function(data, textStatus, XMLHttpRequest) {

                    console.log('error ajax - WP Dynamic Tables - deactivate license');
                    wpdt_on_license_deactivation_complete(wpdt_admin_str.deactivation_error_ajax, 1);
                }
            });
        } else {

            wpdt_display_license_message(wpdt_admin_str.is_not_activated, 1);

            wpdt_on_license_activation_complete();
        }

        return false;
    });
}

function wpdt_hide_license_message() {

    'use strict';

    jQuery('.wpdt-license-messages').fadeOut();
}

function wpdt_display_license_message(c, error) {

    'use strict';

    if (c) {

        if (error) {

            jQuery('.wpdt-license-messages').addClass('error');
        } else {

            jQuery('.wpdt-license-messages').removeClass('error');
        }

        jQuery('.wpdt-license-messages').html(c).fadeIn();
    }
}

(function($) {

    'use strict';

    $(function() {

        wpdt_init_admin();

    });

})(jQuery);
