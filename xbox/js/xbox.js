window.XBOX = (function (window, document, $) {
    'use strict';
    var xbox = {
        duplicate: false,
        media: {
            frames: {},
        },
    };

    xbox.init = function () {
        var $xbox = $('.xbox');

        $(window)
            .resize(function () {
                if (viewport().width <= 850) {
                    $('#post-body').addClass('xbox-columns-1');
                } else {
                    $('#post-body').removeClass('xbox-columns-1');
                }
            })
            .resize();

        xbox.init_switcher();

        xbox.init_spinner();

        xbox.init_tab();

        xbox.init_tooltip();

        xbox.init_checkbox();

        xbox.init_image_selector();

        xbox.init_dropdown();

        xbox.init_colorpicker();

        xbox.init_code_editor();

        xbox.init_sortable_preview_items();

        xbox.init_sortable_repeatable_items();

        xbox.init_sortable_group_items();

        xbox.load_oembeds();

        $xbox.on('click', '#xbox-reset', xbox.on_click_reset_values);
        $xbox.on('click', '#xbox-import', xbox.on_click_import_values);
        $xbox.on('ifClicked', '.xbox-type-import .xbox-radiochecks input', xbox.toggle_import);
        $xbox.on('click', '.xbox-type-import .xbox-radiochecks input', xbox.toggle_import);

        $xbox.on('click', '.xbox-add-group-item', xbox.new_group_item);
        $xbox.on('click', '.xbox-duplicate-group-item', xbox.new_group_item);
        $xbox.on('click', '.xbox-remove-group-item', xbox.remove_group_item);
        $xbox.on('click', '.xbox-group-control-item', xbox.on_click_group_control_item);
        $xbox.on('sort_group_items', '.xbox-group-wrap', xbox.sort_group_items);
        $xbox.on('sort_group_control_items', '.xbox-group-control', xbox.sort_group_control_items);

        $xbox.on('click', '.xbox-add-repeatable-item', xbox.add_repeatable_item);
        $xbox.on('click', '.xbox-remove-repeatable-item', xbox.remove_repeatable_item);
        $xbox.on('sort_repeatable_items', '.xbox-repeatable-wrap', xbox.sort_repeatable_items);

        $xbox.on('click', '.xbox-upload-file, .xbox-preview-item .xbox-preview-handler', xbox.wp_media_upload);
        $xbox.on('click', '.xbox-remove-preview', xbox.remove_preview_item);
        $xbox.on('click', '.xbox-get-oembed', xbox.get_oembed);
        $xbox.on('click', '.xbox-get-image', xbox.get_image_from_url);
        $xbox.on('focusout', '.xbox-type-colorpicker input', xbox.on_focusout_input_colorpicker);
        $xbox.on('change', '.xbox-type-textarea textarea.xbox-element', xbox.on_change_textarea);
        $xbox.on('click', '.xbox-section.xbox-toggle-1 .xbox-section-header, .xbox-section .xbox-toggle-icon', xbox.toggle_section);
    };

    xbox.toggle_section = function (event) {
        event.stopPropagation();
        var $btn = $(this);
        var $section = $btn.closest('.xbox-section.xbox-toggle-1');
        var $section_body = $section.find('.xbox-section-body');
        var data_toggle = $section.data('toggle');
        var $icon = $section.find('.xbox-toggle-icon').first();
        if ($btn.hasClass('xbox-section-header') && data_toggle.target == 'icon') {
            return;
        }
        var object_toggle = {
            duration: parseInt(data_toggle.speed),
            complete: function () {
                if ($section_body.css('display') == 'block') {
                    $icon.find('i').removeClass(data_toggle.close_icon).addClass(data_toggle.open_icon);
                } else {
                    $icon.find('i').removeClass(data_toggle.open_icon).addClass(data_toggle.close_icon);
                }
            },
        };
        if (data_toggle.effect == 'slide') {
            $section_body.slideToggle(object_toggle);
        } else if (data_toggle.effect == 'fade') {
            $section_body.fadeToggle(object_toggle);
        }
        return false;
    };

    xbox.toggle_import = function (event) {
        var $input = $(this);
        var $wrap_input_file = $('.xbox-wrap-input-file');
        var $wrap_input_url = $('.xbox-wrap-input-url');

        if ($input.next('img').length || ($input.val() != 'from_file' && $input.val() != 'from_url')) {
            $wrap_input_file.hide();
            $wrap_input_url.hide();
        }
        if ($input.val() == 'from_file') {
            $wrap_input_file.show();
            $wrap_input_url.hide();
        }
        if ($input.val() == 'from_url') {
            $wrap_input_url.show();
            $wrap_input_file.hide();
        }
    };

    xbox.on_change_textarea = function (event) {
        $(this).text($(this).val());
    };

    xbox.on_click_reset_values = function (event) {
        var $btn = $(this);
        var $xbox_form = $btn.closest('.xbox-form');
        $.xboxConfirm({
            title: XBOX_JS.text.reset_popup.title,
            content: XBOX_JS.text.reset_popup.content,
            confirm_class: 'xbox-btn-pink',
            confirm_text: XBOX_JS.text.popup.accept_button,
            cancel_text: XBOX_JS.text.popup.cancel_button,
            onConfirm: function () {
                $xbox_form.prepend('<input type="hidden" name="' + $btn.attr('name') + '" value="true">');
                $xbox_form.submit();
            },
            onCancel: function () {
                return false;
            },
        });
        return false;
    };

    xbox.on_click_import_values = function (event) {
        var $btn = $(this);
        var $xbox_form = $btn.closest('.xbox-form');
        if (!$xbox_form.length) {
            $xbox_form = $btn.closest('form#post');
        }
        $.xboxConfirm({
            title: XBOX_JS.text.import_popup.title,
            content: XBOX_JS.text.import_popup.content,
            confirm_class: 'xbox-btn-pink',
            confirm_text: XBOX_JS.text.popup.accept_button,
            cancel_text: XBOX_JS.text.popup.cancel_button,
            onConfirm: function () {
                $xbox_form.prepend('<input type="hidden" name="' + $btn.attr('name') + '" value="true">');
                $xbox_form.submit();
            },
            onCancel: function () {
                return false;
            },
        });
        return false;
    };

    xbox.get_image_from_url = function (event) {
        var $btn = $(this);
        var $field = $btn.closest('.xbox-field');
        var $input = $field.find('.xbox-element-text');
        var $wrap_preview = $field.find('.xbox-wrap-preview');
        if (is_empty($input.val())) {
            $.xboxConfirm({
                title: XBOX_JS.text.validation_url_popup.title,
                content: XBOX_JS.text.validation_url_popup.content,
                confirm_text: XBOX_JS.text.popup.accept_button,
                hide_cancel: true,
            });
            return false;
        }
        var image_class = $wrap_preview.data('image-class');
        var $new_item = $('<li />', { class: 'xbox-preview-item xbox-preview-image' });
        $new_item.html('<img src="' + $input.val() + '" class="' + image_class + '">' + '<a class="xbox-btn xbox-btn-iconize xbox-btn-small xbox-btn-red xbox-remove-preview"><i class="xbox-icon xbox-icon-times-circle"></i></a>');
        $wrap_preview.fadeOut(400, function () {
            $(this).html('').show();
        });
        $field.find('.xbox-get-image i').addClass('xbox-icon-spin');
        setTimeout(function () {
            $wrap_preview.html($new_item);
            $field.find('.xbox-get-image i').removeClass('xbox-icon-spin');
        }, 1200);
        return false;
    };

    xbox.load_oembeds = function (event) {
        $('.xbox-type-oembed').each(function (index, el) {
            if ($(el).find('.xbox-wrap-oembed').data('preview-onload')) {
                xbox.get_oembed($(el).find('.xbox-get-oembed'));
            }
        });
    };

    xbox.get_oembed = function (event) {
        var $btn;
        if ($(event.currentTarget).length) {
            $btn = $(event.currentTarget);
        } else {
            $btn = event;
        }
        var $field = $btn.closest('.xbox-field');
        var $input = $field.find('.xbox-element-text');
        var $wrap_preview = $field.find('.xbox-wrap-preview');
        if (is_empty($input.val()) && $(event.currentTarget).length) {
            $.xboxConfirm({
                title: XBOX_JS.text.validation_url_popup.title,
                content: XBOX_JS.text.validation_url_popup.content,
                confirm_text: XBOX_JS.text.popup.accept_button,
                hide_cancel: true,
            });
            return false;
        }
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: XBOX_JS.ajax_url,
            data: {
                action: 'xbox_get_oembed',
                oembed_url: $input.val(),
                preview_size: $wrap_preview.data('preview-size'),
                ajax_nonce: XBOX_JS.ajax_nonce,
            },
            beforeSend: function () {
                $wrap_preview.fadeOut(400, function () {
                    $(this).html('').show();
                });
                $field.find('.xbox-get-oembed i').addClass('xbox-icon-spin');
            },
            success: function (response) {
                if (response) {
                    if (response.success) {
                        var $new_item = $('<li />', { class: 'xbox-preview-item xbox-preview-oembed' });
                        $new_item.html(
                            '<div class="xbox-oembed xbox-oembed-provider-' +
                                response.provider +
                                ' xbox-element-oembed ">' +
                                response.oembed +
                                '<a class="xbox-btn xbox-btn-iconize xbox-btn-small xbox-btn-red xbox-remove-preview"><i class="xbox-icon xbox-icon-times-circle"></i></a>' +
                                '</div>'
                        );
                        $wrap_preview.html($new_item);
                    } else {
                        $wrap_preview.html(response.message);
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {},
            complete: function (jqXHR, textStatus) {
                $field.find('.xbox-get-oembed i').removeClass('xbox-icon-spin');
            },
        });
        return false;
    };

    xbox.wp_media_upload = function (event) {
        if (wp === undefined) {
            return;
        }
        var $btn = $(this);
        var media = xbox.media;
        media.$field = $btn.closest('.xbox-field');
        media.field_id = media.$field.closest('.xbox-row').data('field-id');
        media.frame_id = media.$field.closest('.xbox').attr('id') + '_' + media.field_id;
        media.$upload_btn = media.$field.find('.xbox-upload-file');
        media.$wrap_preview = media.$field.find('.xbox-wrap-preview');
        media.multiple = media.$field.hasClass('xbox-has-multiple');
        media.$preview_item = undefined;
        media.attachment_id = undefined;

        if ($btn.closest('.xbox-preview-item').length) {
            media.$preview_item = $btn.closest('.xbox-preview-item');
        } else if (!media.multiple) {
            media.$preview_item = media.$field.find('.xbox-preview-item').first();
        }
        if (media.$preview_item) {
            media.attachment_id = media.$preview_item.find('.xbox-attachment-id').val();
        }

        if (media.frames[media.frame_id] !== undefined) {
            media.frames[media.frame_id].open();
            return;
        }

        media.frames[media.frame_id] = wp.media({
            title: media.$field.closest('.xbox-type-file').find('.xbox-element-label').first().text(),
            multiple: media.multiple ? 'add' : false,
        });
        media.frames[media.frame_id].on('open', xbox.on_open_wp_media).on('select', xbox.on_select_wp_media);
        media.frames[media.frame_id].open();
    };

    xbox.on_open_wp_media = function (event) {
        var media = xbox.media;
        var selected_files = xbox.media.frames[media.frame_id].state().get('selection');
        if (is_empty(media.attachment_id)) {
            return selected_files.reset();
        }
        var wp_attachment = wp.media.attachment(media.attachment_id);
        wp_attachment.fetch();
        selected_files.set(wp_attachment ? [wp_attachment] : []);
    };

    xbox.on_select_wp_media = function (event) {
        var media = xbox.media;
        var selected_files = media.frames[media.frame_id].state().get('selection').toJSON();
        var preview_size = media.$wrap_preview.data('preview-size');
        var attach_name = media.$wrap_preview.data('field-name');
        var control_img_id = media.$field.closest('.xbox-type-group').find('.xbox-group-control').data('image-field-id');

        media.$field.trigger('xbox_before_add_files', [selected_files, xbox.media]);
        $(selected_files).each(function (index, obj) {
            var image = '';
            var inputs = '';
            var item_body = '';
            var $new_item = $('<li />', { class: 'xbox-preview-item xbox-preview-file' });

            if (obj.type == 'image') {
                $new_item.addClass('xbox-preview-image');
                item_body = '<img src="' + obj.url + '" style="width: ' + preview_size.width + '; height: ' + preview_size.height + '" data-full-img="' + obj.url + '" class="xbox-image xbox-preview-handler">';
            } else {
                item_body =
                    '<img src="' +
                    obj.icon +
                    '" class="xbox-preview-icon-file xbox-preview-handler"><a href="' +
                    obj.url +
                    '" class="xbox-preview-download-link">' +
                    obj.filename +
                    '</a><span class="xbox-preview-mime xbox-preview-handler">' +
                    obj.mime +
                    '</span>';
            }

            if (media.multiple) {
                inputs = '<input type="hidden" name="' + media.$upload_btn.data('field-name') + '" value="' + obj.url + '" class="xbox-element xbox-element-hidden">';
            }
            inputs += '<input type="hidden" name="' + attach_name + '" value="' + obj.id + '" class="xbox-attachment-id">';

            $new_item.html(inputs + item_body + '<a class="xbox-btn xbox-btn-iconize xbox-btn-small xbox-btn-red xbox-remove-preview"><i class="xbox-icon xbox-icon-times-circle"></i></a>');

            if (media.multiple) {
                if (media.$preview_item) {
                    //Sólo agregamos los nuevos
                    if (media.attachment_id != obj.id) {
                        media.$preview_item.after($new_item);
                    }
                } else {
                    media.$wrap_preview.append($new_item);
                }
            } else {
                media.$wrap_preview.html($new_item);
                media.$field.find('.xbox-element').attr('value', obj.url);
                if (obj.type == 'image') {
                    //Sincronizar con la imagen de control de un grupo
                    if (media.field_id == control_img_id) {
                        xbox.synchronize_selector_preview_image('.xbox-control-image', media.$wrap_preview, 'add', obj.url);
                    }
                    //Sincronizar con otros elementos
                    xbox.synchronize_selector_preview_image('', media.$wrap_preview, 'add', obj.url);
                }
            }
        });
        media.$field.trigger('xbox_after_add_files', [selected_files, media]);
    };

    xbox.remove_preview_item = function (event) {
        var $btn = $(this);
        var $field = $btn.closest('.xbox-field');
        var field_id = $field.closest('.xbox-row').data('field-id');
        var control_data_img = $field.closest('.xbox-type-group').find('.xbox-group-control').data('image-field-id');
        var $wrap_preview = $field.find('.xbox-wrap-preview');
        var multiple = $field.hasClass('xbox-has-multiple');

        $field.trigger('xbox_before_remove_preview_item', [multiple]);

        if (!multiple) {
            $field.find('.xbox-element').attr('value', '');
        }
        $btn.closest('.xbox-preview-item').remove();

        if (!multiple && $btn.closest('.xbox-preview-item').hasClass('xbox-preview-image')) {
            if (field_id == control_data_img) {
                xbox.synchronize_selector_preview_image('.xbox-control-image', $wrap_preview, 'remove', '');
            }
            xbox.synchronize_selector_preview_image('', $wrap_preview, 'remove', '');
        }

        $field.trigger('xbox_after_remove_preview_item', [multiple]);
        return false;
    };

    xbox.synchronize_selector_preview_image = function (selectors, $wrap_preview, action, value) {
        selectors = selectors || $wrap_preview.data('synchronize-selector');
        if (!is_empty(selectors)) {
            selectors = selectors.split(',');
            $.each(selectors, function (index, selector) {
                var $element = $(selector);
                if ($element.closest('.xbox-type-group').length) {
                    if ($element.closest('.xbox-group-control').length) {
                        $element = $element.closest('.xbox-group-control-item.xbox-active').find(selector);
                    } else {
                        $element = $element.closest('.xbox-group-item.xbox-active').find(selector);
                    }
                }
                if ($element.is('img')) {
                    $element.fadeOut(300, function () {
                        if ($element.closest('.xbox-group-control').length) {
                            $element.attr('src', value);
                        } else {
                            $element.attr('src', value);
                        }
                    });
                } else {
                    $element.fadeOut(300, function () {
                        if ($element.closest('.xbox-group-control').length) {
                            $element.css('background-image', 'url(' + value + ')');
                        } else {
                            $element.css('background-image', 'url(' + value + ')');
                        }
                    });
                }
                if (action == 'add') {
                    $element.fadeIn(300);
                }
                var $input = $element.closest('.xbox-field').find('input.xbox-element');
                if ($input.length) {
                    $input.attr('value', value);
                }

                var $close_btn = $element.closest('.xbox-preview-item').find('.xbox-remove-preview');
                if ($close_btn.length) {
                    if (action == 'add' && $input.is(':visible')) {
                        $close_btn.show();
                    }
                    if (action == 'remove') {
                        $close_btn.hide();
                    }
                }
            });
        }
    };

    xbox.reinit_js_plugins = function ($new_element, $source_item) {
        //Inicializar Tabs
        $new_element.find('.xbox-tab').each(function (iterator, item) {
            xbox.init_tab($(item));
        });

        //Inicializar Switcher
        $new_element.find('.xbox-type-switcher input.xbox-element').each(function (iterator, item) {
            $(item).xboxSwitcher('destroy');
            xbox.init_switcher($(item));
        });

        //Inicializar Spinner
        $new_element.find('.xbox-type-number .xbox-field').each(function (iterator, item) {
            xbox.init_spinner($(item));
        });

        //Inicializar radio buttons y checkboxes
        $new_element.find('.xbox-has-icheck .xbox-radiochecks.init-icheck').each(function (iterator, item) {
            xbox.destroy_icheck($(item));
            xbox.init_checkbox($(item));
        });

        //Inicializar Colorpicker
        $new_element.find('.xbox-colorpicker-color').each(function (iterator, item) {
            xbox.init_colorpicker($(item));
        });

        //Inicializar Dropdown
        $new_element.find('.ui.selection.dropdown').each(function (iterator, item) {
            xbox.init_dropdown($(item));
        });

        //Inicializar Sortables de grupos
        $new_element.find('.xbox-group-control.xbox-sortable').each(function (iterator, item) {
            xbox.init_sortable_group_items($(item));
        });

        //Inicializar Sortable de items repetibles
        $new_element.find('.xbox-repeatable-wrap.xbox-sortable').each(function (iterator, item) {
            xbox.init_sortable_repeatable_items($(item));
        });

        //Inicializar Sortable de preview items
        $new_element.find('.xbox-wrap-preview-multiple').each(function (iterator, item) {
            xbox.init_sortable_preview_items($(item));
        });

        //Inicializar Ace editor
        $new_element.find('.xbox-code-editor').each(function (iterator, item) {
            xbox.destroy_ace_editor($(item));
            xbox.init_code_editor($(item));
        });

        //Inicializar Tooltip
        xbox.init_tooltip($new_element.find('.xbox-tooltip-handler'));
    };

    xbox.destroy_wp_editor = function ($selector) {
        if (typeof tinyMCEPreInit === 'undefined' || typeof tinymce === 'undefined' || typeof QTags == 'undefined') {
            return;
        }

        //Destroy editor
        $selector.find('.quicktags-toolbar, .mce-tinymce.mce-container').remove();
        tinymce.execCommand('mceRemoveEditor', true, $selector.find('.wp-editor-area').attr('id'));

        //Register editor to init
        $selector.addClass('init-wp-editor');
    };

    xbox.on_change_wp_editor = function (wp_editor) {
        if (typeof tinymce === 'undefined') {
            return;
        }
        var $textarea = $(wp_editor.settings.selector);
        wp_editor.on('change mouseleave input', function (e) {
            var value = tinymce.get($textarea.attr('id')).getContent();
            $textarea.text(value).val(value);
        });
    };

    xbox.init_wp_editor = function ($selector) {
        if (typeof tinyMCEPreInit === 'undefined' || typeof tinymce === 'undefined' || typeof QTags == 'undefined') {
            return;
        }
        $selector.removeClass('init-wp-editor');
        $selector.removeClass('html-active').addClass('tmce-active');
        var $textarea = $selector.find('.wp-editor-area');
        var ed_id = $textarea.attr('id');
        var old_ed_id = $selector.closest('.xbox-group-wrap').find('.xbox-group-item').eq(0).find('.wp-editor-area').first().attr('id');

        $textarea.show();

        var ed_settings = jQuery.extend({}, tinyMCEPreInit.mceInit[old_ed_id]);

        ed_settings.body_class = ed_id;
        ed_settings.selector = '#' + ed_id;
        ed_settings.mode = 'tmce';
        tinyMCEPreInit.mceInit[ed_id] = ed_settings;

        // Initialize wp_editor tinymce instance
        tinymce.init(tinyMCEPreInit.mceInit[ed_id]);
        //tinymce.execCommand( 'mceAddEditor', true, ed_id );

        //Quick tags Settings
        var qt_settings = jQuery.extend({}, tinyMCEPreInit.qtInit[old_ed_id]);
        qt_settings.id = ed_id;
        new QTags(ed_id);
        QTags._buttonsInit();
    };

    xbox.init_switcher = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-type-switcher input.xbox-element') : $selector;
        $selector.xboxSwitcher();
    };

    xbox.init_spinner = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-type-number .xbox-field') : $selector;
        $selector.spinner('delay', 300);
    };

    xbox.init_tab = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-tab') : $selector;
        $selector.find('.xbox-tab-nav .xbox-item').removeClass('active');
        $selector.find('.xbox-accordion-title').removeClass('active');
        var type_tab = 'responsive';
        if ($selector.closest('#side-sortables').length) {
            type_tab = 'accordion';
        }
        $selector.xboxTabs({
            collapsible: true,
            type: type_tab,
        });
    };

    xbox.init_tooltip = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-tooltip-handler') : $selector;
        $selector.each(function (index, el) {
            var title_content = '';
            var title_tooltip = $(el).data('tipso-title');
            var position = 'left';
            if (!is_empty(title_tooltip)) {
                title_content = '<h3>' + title_tooltip + '</h3>';
            }
            $(el).tipso({
                delay: 10,
                speed: 100,
                offsetY: 2,
                position: position,
                titleContent: title_content,
                onBeforeShow: function ($element, element, e) {
                    $(e.tipso_bubble).addClass($(el).closest('.xbox').data('skin'));
                },
                onShow: function ($element, element, e) {
                    //$(e.tipso_bubble).removeClass('top').addClass(position);
                },
                //hideDelay: 1000000
            });
        });
    };

    xbox.init_checkbox = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-has-icheck .xbox-radiochecks.init-icheck') : $selector;
        $selector.find('input').iCheck({
            radioClass: 'iradio_flat-pink',
            checkboxClass: 'icheckbox_flat-pink',
        });
    };

    xbox.destroy_icheck = function ($selector) {
        $selector.find('input').each(function (index, input) {
            $(input).attr('style', '');
            $(input).next('ins').remove();
            $(input).unwrap();
        });
    };

    xbox.init_image_selector = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-type-image_selector .init-image-selector, .xbox-type-import .init-image-selector') : $selector;
        $selector.xboxImageSelector({
            active_class: 'xbox-active',
        });
    };

    xbox.init_dropdown = function ($selector) {
        $selector = is_empty($selector) ? $('.ui.selection.dropdown') : $selector;
        $selector.each(function (index, el) {
            var max_selections = parseInt($(el).data('max-selections'));
            var value = $(el).find('input[type="hidden"]').val();
            if (max_selections > 1 && $(el).hasClass('multiple')) {
                $(el).dropdown({
                    maxSelections: max_selections,
                });
                $(el).dropdown('set selected', value.split(','));
            } else {
                $(el).dropdown();
            }
        });
    };

    xbox.on_focusout_input_colorpicker = function ($selector) {
        var value = $(this).val();
        $(this).attr('value', value);
        $(this).next().find('.xbox-colorpicker-color').attr('value', value).css('background-color', value);
        return false;
    };

    xbox.init_colorpicker = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-colorpicker-color') : $selector;
        $selector.colorPicker({
            cssAddon: '.cp-color-picker {margin-top:6px;}',
            buildCallback: function ($elm) {},
            renderCallback: function ($elm, toggled) {
                var $field = $elm.closest('.xbox-field');
                this.$UI.find('.cp-alpha').toggle($field.hasClass('xbox-has-alpha'));
                var value = this.color.toString('rgb', true);
                if (!$field.hasClass('xbox-has-alpha')) {
                    //|| value.endsWith(', 1)')
                    value = '#' + this.color.colors.HEX;
                }
                value = value.indexOf('NAN') > -1 ? '' : value;
                $field.find('input').attr('value', value);
                $field.find('.xbox-colorpicker-color').attr('value', value).css('background-color', value);

                //Para la gestión de eventos
                $field.find('input').trigger('change');
            },
        });
    };

    xbox.destroy_ace_editor = function ($selector) {
        var $textarea = $selector.closest('.xbox-field').find('textarea.xbox-element');
        $selector.text($textarea.val());
    };

    xbox.init_code_editor = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-code-editor') : $selector;
        $selector.each(function (index, el) {
            var editor = ace.edit($(el).attr('id'));
            var language = $(el).data('language');
            var theme = $(el).data('theme');
            editor.setTheme('ace/theme/' + theme);
            editor.getSession().setMode('ace/mode/' + language);
            editor.setFontSize(15);
            editor.setShowPrintMargin(false);
            editor.getSession().on('change', function (e) {
                $(el).closest('.xbox-field').find('textarea.xbox-element').text(editor.getValue());
            });

            //Include auto complete
            ace.config.loadModule('ace/ext/language_tools', function () {
                editor.setOptions({
                    enableBasicAutocompletion: true,
                    enableSnippets: true,
                });
            });
        });
    };

    xbox.init_sortable_preview_items = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-wrap-preview-multiple') : $selector;
        $selector
            .sortable({
                items: '.xbox-preview-item',
                placeholder: 'xbox-preview-item xbox-sortable-placeholder',
                start: function (event, ui) {
                    ui.placeholder.css({
                        width: ui.item.css('width'),
                        height: ui.item.css('height'),
                    });
                },
            })
            .disableSelection();
    };

    xbox.init_sortable_repeatable_items = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-repeatable-wrap.xbox-sortable') : $selector;
        $selector
            .sortable({
                handle: '.xbox-sort-item',
                items: '.xbox-repeatable-item',
                placeholder: 'xbox-repeatable-item xbox-sortable-placeholder',
                start: function (event, ui) {
                    ui.placeholder.css({
                        width: ui.item.css('width'),
                        height: ui.item.css('height'),
                    });
                },
                update: function (event, ui) {
                    // No funciona bien con wp_editor, mejor usamos 'stop'
                    // var $repeatable_wrap = $(event.target);
                    // $repeatable_wrap.trigger('sort_repeatable_items');
                },
                stop: function (event, ui) {
                    var $repeatable_wrap = $(event.target);
                    $repeatable_wrap.trigger('sort_repeatable_items');
                },
            })
            .disableSelection();
    };

    xbox.init_sortable_group_items = function ($selector) {
        $selector = is_empty($selector) ? $('.xbox-group-control.xbox-sortable') : $selector;
        $selector
            .sortable({
                items: '.xbox-group-control-item',
                placeholder: 'xbox-sortable-placeholder',
                start: function (event, ui) {
                    ui.placeholder.css({
                        width: ui.item.css('width'),
                        height: ui.item.css('height'),
                    });
                },
                update: function (event, ui) {
                    var $group_control = $(event.target);
                    var $group_wrap = $group_control.next('.xbox-group-wrap');

                    var old_index = ui.item.attr('data-index');
                    var new_index = $group_control.find('.xbox-group-control-item').index(ui.item);
                    var $group_item = $group_wrap.children('.xbox-group-item[data-index=' + old_index + ']');
                    var $group_item_reference = $group_wrap.children('.xbox-group-item[data-index=' + new_index + ']');
                    var start_index = 0;
                    var end_index;

                    if (old_index < new_index) {
                        $group_item.insertAfter($group_item_reference);
                        start_index = old_index;
                        end_index = new_index;
                    } else {
                        $group_item.insertBefore($group_item_reference);
                        start_index = new_index;
                        end_index = old_index;
                    }

                    $group_control.trigger('sort_group_control_items');

                    $group_wrap.trigger('sort_group_items', [start_index, end_index]);

                    //Click event, to initialize some fields -> (WP Editors)
                    if (ui.item.hasClass('xbox-active')) {
                        ui.item.trigger('click');
                    }
                },
            })
            .disableSelection();
    };

    xbox.add_repeatable_item = function (event) {
        var $btn = $(this);
        var $repeatable_wrap = $btn.closest('.xbox-repeatable-wrap');
        $repeatable_wrap.trigger('xbox_before_add_repeatable_item');

        var $source_item = $btn.prev('.xbox-repeatable-item');
        var index = parseInt($source_item.data('index'));
        var $cloned = $source_item.clone();
        var $new_item = $('<div />', { class: $cloned.attr('class'), 'data-index': index + 1, style: 'display: none' });

        xbox.set_changed_values($cloned, $repeatable_wrap.closest('.xbox-row').data('field-type'));

        $new_item.html($cloned.html());
        $source_item.after($new_item);
        $new_item.slideDown(150, function () {
            //Ordenar y cambiar ids y names
            $repeatable_wrap.trigger('sort_repeatable_items');
            //Actualizar eventos
            xbox.reinit_js_plugins($new_item, $source_item);
        });
        $repeatable_wrap.trigger('xbox_after_add_repeatable_item');
        return false;
    };

    xbox.remove_repeatable_item = function (event) {
        var $repeatable_wrap = $(this).closest('.xbox-repeatable-wrap');
        if ($repeatable_wrap.find('.xbox-repeatable-item').length > 1) {
            $repeatable_wrap.trigger('xbox_before_remove_repeatable_item');
            var $item = $(this).closest('.xbox-repeatable-item');
            $item.slideUp(150, function () {
                $item.remove();
                $repeatable_wrap.trigger('sort_repeatable_items');
                $repeatable_wrap.trigger('xbox_after_remove_repeatable_item');
            });
        }
        return false;
    };

    xbox.sort_repeatable_items = function (event) {
        var $repeatable_wrap = $(event.target);
        var row_level = parseInt($repeatable_wrap.closest('[class*="xbox-row"]').data('row-level'));

        $repeatable_wrap.find('.xbox-repeatable-item').each(function (index, item) {
            xbox.update_attributes($(item), index, row_level);

            //Destroy WP Editors
            $(item)
                .find('.wp-editor-wrap')
                .each(function (index, el) {
                    xbox.destroy_wp_editor($(el));
                });
            xbox.update_fields_on_item_active($(item));
        });
    };

    xbox.new_group_item = function (event) {
        if ($(event.currentTarget).hasClass('xbox-duplicate-group-item')) {
            xbox.duplicate = true;
        } else {
            xbox.duplicate = false;
        }
        var $row = $(this).closest('.xbox-type-group').first();
        $row.trigger('xbox_before_add_group_item', [$(this), xbox.duplicate]);

        xbox.add_group_control_item($(this));
        xbox.add_group_item($(this));

        $row.trigger('xbox_after_add_group_item', [$(this), xbox.duplicate]);
        return false;
    };

    xbox.add_group_control_item = function ($btn) {
        var $group_control = $btn.closest('.xbox-type-group').find('.xbox-group-control').first();
        var $source_item = $group_control.find('.xbox-group-control-item').last();
        if (xbox.duplicate) {
            var control_index = $btn.closest('.xbox-group-control-item').index();
            $source_item = $group_control.children('.xbox-group-control-item').eq(control_index);
        }
        var index = parseInt($source_item.data('index'));
        var row_level = parseInt($source_item.closest('.xbox-row').data('row-level'));
        var $cloned = $source_item.clone();
        var $new_item = $('<li />', { class: $cloned.attr('class'), 'data-index': index + 1 });

        $new_item.html($cloned.html());
        $source_item.after($new_item);
        $new_item = $source_item.next('.xbox-group-control-item');
        $group_control.trigger('sort_group_control_items');

        if (xbox.duplicate === false && $new_item.find('.xbox-control-image').length) {
            $new_item.find('.xbox-control-image').css('background-image', 'url()');
        }
        if (xbox.duplicate === false) {
            var $input = $new_item.find('.xbox-inner input');
            if ($input.length) {
                var value = $group_control.data('control-name').toString();
                $input.attr('value', value.replace(/(#\d?)/g, '#' + (index + 2)));
                if ($btn.hasClass('xbox-custom-add')) {
                    $input.attr('value', $btn.text());
                }
            }
        }
    };

    xbox.add_group_item = function ($btn) {
        var $group_wrap = $btn.closest('.xbox-type-group').find('.xbox-group-wrap').first();
        var $source_item = $group_wrap.children('.xbox-group-item').last();
        if (xbox.duplicate) {
            var control_index = $btn.closest('.xbox-group-control-item').index();
            $source_item = $group_wrap.children('.xbox-group-item').eq(control_index);
        }
        var index = parseInt($source_item.data('index'));
        var row_level = parseInt($source_item.closest('.xbox-row').data('row-level'));
        var $cloned = $source_item.clone();
        var $cooked_item = xbox.cook_group_item($cloned, row_level, index);
        var $new_item = $('<div />', { class: $cloned.attr('class'), 'data-index': index + 1 });

        $new_item.html($cooked_item.html());
        $source_item.after($new_item);
        $new_item = $source_item.next('.xbox-group-item');
        $group_wrap.trigger('sort_group_items', [index + 1]);

        //Actualizar eventos
        xbox.reinit_js_plugins($new_item, $source_item);

        if (xbox.duplicate === false) {
            xbox.set_default_values($new_item, row_level);
        }

        //Active new item
        var $group_control = $btn.closest('.xbox-type-group').find('.xbox-group-control').first();
        $group_control
            .children('.xbox-group-control-item')
            .eq(index + 1)
            .trigger('click');

        return false;
    };

    xbox.cook_group_item = function ($group_item, row_level, prev_index) {
        var index = prev_index + 1;

        if (xbox.duplicate) {
            xbox.set_changed_values($group_item);
        } else {
            //No es duplicado, restaurar todo, eliminar items de grupos internos
            $group_item.find('.xbox-group-wrap').each(function (index, wrap_group) {
                $(wrap_group).find('.xbox-group-item').first().addClass('xbox-active').siblings().remove();
                $(wrap_group).prev('.xbox-group-control').children('.xbox-group-control-item').first().addClass('xbox-active').siblings().remove();
            });
            $group_item.find('.xbox-repeatable-wrap').each(function (index, wrap_repeat) {
                $(wrap_repeat).find('.xbox-repeatable-item').not(':first').remove();
            });
        }

        xbox.update_attributes($group_item, index, row_level);

        return $group_item;
    };

    xbox.set_changed_values = function ($new_item, field_type) {
        var $textarea, $input;
        $new_item.find('.xbox-field').each(function (iterator, item) {
            var type = field_type || $(item).closest('.xbox-row').data('field-type');
            switch (type) {
                case 'text':
                case 'number':
                case 'oembed':
                case 'file':
                case 'image':
                    $input = $(item).find('input.xbox-element');
                    $input.attr('value', $input.val());
                    break;
            }
        });
    };

    xbox.remove_group_item = function (event) {
        event.preventDefault();
        var $btn = $(this);

        $.xboxConfirm({
            title: XBOX_JS.text.remove_item_popup.title,
            content: XBOX_JS.text.remove_item_popup.content,
            confirm_class: 'xbox-btn-pink',
            confirm_text: XBOX_JS.text.popup.accept_button,
            cancel_text: XBOX_JS.text.popup.cancel_button,
            onConfirm: function () {
                setTimeout(function () {
                    xbox.remove_group_control_item($btn);
                    xbox._remove_group_item($btn);
                }, 150);
            },
        });
        return false;
    };

    xbox.remove_group_control_item = function ($btn) {
        var $group_control = $btn.closest('.xbox-group-control');
        if ($group_control.children('.xbox-group-control-item').length > 1) {
            var $item = $btn.closest('.xbox-group-control-item');
            $item.fadeOut(200, function () {
                $item.remove();
                $group_control.trigger('sort_group_control_items');
            });
        }
    };

    xbox._remove_group_item = function ($btn) {
        var $row = $btn.closest('.xbox-type-group');
        var $group_wrap = $row.find('.xbox-group-wrap').first();
        var $group_control = $btn.closest('.xbox-group-control');
        var index = $btn.closest('.xbox-group-control-item').index();
        if ($group_wrap.children('.xbox-group-item').length > 1) {
            $row.trigger('xbox_before_remove_group_item');
            var $item = $group_wrap.children('.xbox-group-item').eq(index);
            $item.fadeOut(200, function () {
                $item.remove();
                $group_wrap.trigger('sort_group_items', [index]);
                $group_control.children('.xbox-group-control-item').eq(0).trigger('click');
                $row.trigger('xbox_after_remove_group_item');
            });
        }
    };

    xbox.on_click_group_control_item = function (event) {
        var $control_item = $(this); //$(event.currentTarget);
        var $group_control = $control_item.parent();
        var index = $control_item.index();
        var $group_wrap = $group_control.closest('.xbox-type-group').find('.xbox-group-wrap').first();
        var $group_item = $group_wrap.children('.xbox-group-item').eq(index);

        $group_control.children('.xbox-group-control-item').removeClass('xbox-active');
        $control_item.addClass('xbox-active');

        $group_wrap.children('.xbox-group-item').removeClass('xbox-active');
        $group_item.addClass('xbox-active');

        xbox.update_fields_on_item_active($group_item);

        return false;
    };

    xbox.update_fields_on_item_active = function ($group_item) {
        //Init WP Editor
        $group_item.find('.wp-editor-wrap.init-wp-editor').each(function (index, el) {
            xbox.init_wp_editor($(el));
        });
    };

    xbox.sort_group_control_items = function (event) {
        var $group_control = $(event.target);
        var row_level = parseInt($group_control.closest('.xbox-row').data('row-level'));
        $group_control.children('.xbox-group-control-item').each(function (index, item) {
            xbox.update_group_control_item($(item), index, row_level);
        });
    };

    xbox.sort_group_items = function (event, start_index, end_index) {
        var $group_wrap = $(event.target);
        $group_wrap.trigger('xbox_before_sort_group');
        var row_level = parseInt($group_wrap.closest('.xbox-row').data('row-level'));
        end_index = end_index !== undefined ? parseInt(end_index) + 1 : undefined;

        var $items = $group_wrap.children('.xbox-group-item');
        var $items_to_sort = $items.slice(start_index, end_index);

        $items_to_sort.each(function (i, group_item) {
            var index = $group_wrap.find($(group_item)).index();
            xbox.update_attributes($(group_item), index, row_level);

            //Destroy WP Editors
            $(group_item)
                .find('.wp-editor-wrap')
                .each(function (index, el) {
                    xbox.destroy_wp_editor($(el));
                });
        });
        $group_wrap.trigger('xbox_after_sort_group');
    };

    xbox.update_group_control_item = function ($item, index, row_level) {
        $item.data('index', index).attr('data-index', index);
        $item.find('.xbox-info-order-item').text('#' + (index + 1));
        var value;
        if ($item.find('.xbox-inner input').length) {
            value = $item.find('.xbox-inner input').val();
            $item.find('.xbox-inner input').val(value.replace(/(#\d+)/g, '#' + (index + 1)));
        }

        //Cambiar names
        $item.find('*[name]').each(function (i, item) {
            xbox.update_name_ttribute($(item), index, row_level);
        });
    };

    xbox.update_attributes = function ($new_item, index, row_level) {
        $new_item.data('index', index).attr('data-index', index);

        $new_item.find('*[name]').each(function (i, item) {
            xbox.update_name_ttribute($(item), index, row_level);
        });

        $new_item.find('*[id]').each(function (i, item) {
            xbox.update_id_attribute($(item), index, row_level);
        });

        $new_item.find('label[for]').each(function (i, item) {
            xbox.update_for_attribute($(item), index, row_level);
        });

        $new_item.find('*[data-field-name]').each(function (i, item) {
            xbox.update_data_name_attribute($(item), index, row_level);
        });

        $new_item.find('*[data-editor]').each(function (i, item) {
            xbox.update_data_editor_attribute($(item), index, row_level);
        });

        $new_item.find('*[data-wp-editor-id]').each(function (i, item) {
            xbox.update_data_wp_editor_id_attribute($(item), index, row_level);
        });

        xbox.set_checked_inputs($new_item, row_level);
    };

    xbox.set_checked_inputs = function ($group_item, row_level) {
        $group_item.find('.xbox-field').each(function (iterator, item) {
            if ($(item).hasClass('xbox-has-icheck') || $(item).closest('.xbox-type-image_selector').length) {
                var $input = $(item).find('input[type="radio"], input[type="checkbox"]');
                $input.each(function (i, input) {
                    if ($(input).parent('div').hasClass('checked')) {
                        $(input).attr('checked', 'checked').prop('checked', true);
                    } else {
                        $(input).removeAttr('checked').prop('checked', false);
                    }
                    if ($(input).next('img').hasClass('xbox-active')) {
                        $(input).attr('checked', 'checked').prop('checked', true);
                    }
                });
            }
        });
    };

    xbox.update_name_ttribute = function ($el, index, row_level) {
        var old_name = $el.attr('name');
        var new_name = '';
        if (typeof old_name !== 'undefined') {
            new_name = xbox.nice_replace(/(\[\d+\])/g, old_name, '[' + index + ']', row_level);
            $el.attr('name', new_name);
        }
    };

    xbox.update_id_attribute = function ($el, index, row_level) {
        var old_id = $el.attr('id');
        var new_id = '';
        if (typeof old_id !== 'undefined') {
            new_id = xbox.nice_replace(/(__\d+__)/g, old_id, '__' + index + '__', row_level);
            $el.attr('id', new_id);
        }
    };

    xbox.update_for_attribute = function ($el, index, row_level) {
        var old_for = $el.attr('for');
        var new_for = '';
        if (typeof old_for !== 'undefined') {
            new_for = xbox.nice_replace(/(__\d+__)/g, old_for, '__' + index + '__', row_level);
            $el.attr('for', new_for);
        }
    };
    xbox.update_data_name_attribute = function ($el, index, row_level) {
        var old_data = $el.attr('data-field-name');
        var new_data = '';
        if (typeof old_data !== 'undefined') {
            new_data = xbox.nice_replace(/(\[\d+\])/g, old_data, '[' + index + ']', row_level);
            $el.attr('data-field-name', new_data);
        }
    };

    xbox.update_data_editor_attribute = function ($el, index, row_level) {
        var old_data = $el.attr('data-editor');
        var new_data = '';
        if (typeof old_data !== 'undefined') {
            new_data = xbox.nice_replace(/(__\d+__)/g, old_data, '__' + index + '__', row_level);
            $el.attr('data-editor', new_data);
        }
    };
    xbox.update_data_wp_editor_id_attribute = function ($el, index, row_level) {
        var old_data = $el.attr('data-wp-editor-id');
        var new_data = '';
        if (typeof old_data !== 'undefined') {
            new_data = xbox.nice_replace(/(__\d+__)/g, old_data, '__' + index + '__', row_level);
            $el.attr('data-wp-editor-id', new_data);
        }
    };

    xbox.set_default_values = function ($group, row_level) {
        var $input, array;
        $group.find('*[data-default]').each(function (iterator, item) {
            var $el = $(item);
            var default_value = $el.data('default');
            var default_value_low = default_value.toString().toLowerCase();

            //Input texto, number, colorpicker
            if ($el.closest('.xbox-type-text').length || $el.closest('.xbox-type-number').length || $el.closest('.xbox-type-colorpicker').length) {
                $el.find('input.xbox-element').attr('value', default_value);
                if ($el.closest('.xbox-type-colorpicker').length) {
                    $el.find('.xbox-colorpicker-color').attr('value', default_value).css('background-color', default_value);
                }
            }
            //Checkbox y radio buttons
            else if ($el.hasClass('xbox-has-icheck') && $el.find('.init-icheck').length) {
                $input = $el.find('input');
                if ($el.closest('.xbox-type-radio').length) {
                    if (is_empty($input.filter(':checked').val())) {
                        return;
                    }
                    if ($input.filter(':checked').val().toLowerCase() != default_value_low) {
                        $input
                            .filter(function (i) {
                                return $(this).val().toLowerCase() == default_value_low;
                            })
                            .iCheck('check');
                    }
                } else {
                    if (get_value_checkbox($input, ',').toLowerCase() != default_value_low) {
                        $input.iCheck('uncheck');
                        array = default_value_low.replace(/ /g, '').split(',');
                        $.each(array, function (index) {
                            $input
                                .filter(function (i) {
                                    return $(this).val().toLowerCase() == array[index];
                                })
                                .iCheck('check');
                        });
                    }
                }
            }
            //File, Oembed
            else if ($el.closest('.xbox-type-file').length || $el.closest('.xbox-type-oembed').length) {
                $el.find('input[type="text"]').attr('value', default_value);
                $el.find('.xbox-wrap-preview').html('');
            }
            //Image
            else if ($el.closest('.xbox-type-image').length) {
                $el.find('input.xbox-element').attr('value', default_value);
                $el.find('img.xbox-element-image').attr('src', default_value);
                if (is_empty(default_value)) {
                    $el.find('img.xbox-element-image').hide().next('.xbox-remove-preview').hide();
                }
            }
            //Selector de imagen
            else if ($el.closest('.xbox-type-image_selector').length) {
                $input = $el.find('input');
                var data_image_selector = $input.closest('.xbox-image-selector').data('image-selector');

                if (!data_image_selector.like_checkbox) {
                    if (is_empty($input.filter(':checked').val())) {
                        return;
                    }
                    if ($input.filter(':checked').val().toLowerCase() != default_value_low) {
                        $input
                            .filter(function (i) {
                                return $(this).val().toLowerCase() == default_value_low;
                            })
                            .trigger('click.img_selector');
                    }
                } else {
                    if (get_value_checkbox($input, ',').toLowerCase() != default_value_low) {
                        $input.first().trigger('img_selector_disable_all');
                        array = default_value_low.replace(/ /g, '').split(',');
                        $.each(array, function (index) {
                            $input
                                .filter(function (i) {
                                    return $(this).val().toLowerCase() == array[index];
                                })
                                .trigger('click.img_selector');
                        });
                    }
                }
            }
            //Select dropdown
            else if ($el.closest('.xbox-type-select').length) {
                var $dropdown = $el.find('.ui.selection.dropdown');
                var max_selections = parseInt($dropdown.data('max-selections'));
                $dropdown.dropdown('clear');
                if (max_selections > 1 && $dropdown.hasClass('multiple')) {
                    $dropdown.dropdown('set selected', default_value.split(','));
                } else {
                    $dropdown.dropdown('set selected', default_value);
                }
            }
            //Switcher
            else if ($el.closest('.xbox-type-switcher').length) {
                $input = $el.find('input');
                if ($input.val() !== default_value) {
                    if ($input.next().hasClass('xbox-sw-on')) {
                        $input.xboxSwitcher('set_off');
                    } else {
                        $input.xboxSwitcher('set_on');
                    }
                }
            }
            //WP editor
            if ($el.closest('.xbox-type-wp_editor').length) {
                $el.find('textarea').val(default_value);
            }
            //Textarea
            if ($el.closest('.xbox-type-textarea').length) {
                $el.find('textarea').text(default_value);
            }
            //Code editor
            if ($el.closest('.xbox-type-code_editor').length) {
                $el.find('textarea.xbox-element').text(default_value);
                var editor = ace.edit($el.find('.xbox-code-editor').attr('id'));
                editor.setValue(default_value);
            }
        });
    };

    xbox.nice_replace = function (regex, string, replace_with, row_level, offset) {
        offset = offset || 0;
        //http://stackoverflow.com/questions/10584748/find-and-replace-nth-occurrence-of-bracketed-expression-in-string
        var n = 0;
        string = string.replace(regex, function (match, i, original) {
            n++;
            return n === row_level + offset ? replace_with : match;
        });
        return string;
    };

    xbox.get_object_id = function () {
        return $('.xbox').data('object-id');
    };

    xbox.get_object_type = function () {
        return $('.xbox').data('object-type');
    };

    //Funciones privadas
    function is_empty(value) {
        return value === undefined || value === false || $.trim(value).length === 0;
    }

    function get_value_checkbox($elment, separator) {
        separator = separator || ',';
        if ($elment.attr('type') != 'checkbox') {
            return '';
        }
        var value = $elment
            .filter(':checked')
            .map(function () {
                return this.value;
            })
            .get()
            .join(separator);
        return value;
    }

    function viewport() {
        var e = window,
            a = 'inner';
        if (!('innerWidth' in window)) {
            a = 'client';
            e = document.documentElement || document.body;
        }
        return { width: e[a + 'Width'], height: e[a + 'Height'] };
    }

    //Document Ready
    $(function () {
        xbox.init();
    });

    return xbox;
})(window, document, jQuery);
