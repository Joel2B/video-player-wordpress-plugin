XBOX.events = (function (window, document, $) {
    'use strict';
    var xbox_events = {};

    xbox_events.init = function () {
        var $xbox = $('.xbox');

        xbox_events.on_change_colorpicker($xbox);

        xbox_events.on_change_code_editor($xbox);

        xbox_events.on_change_file($xbox);

        xbox_events.on_change_image_selector($xbox);

        xbox_events.on_change_number($xbox);

        xbox_events.on_change_oembed($xbox);

        xbox_events.on_change_radio($xbox);

        xbox_events.on_change_checkbox($xbox);

        xbox_events.on_change_switcher($xbox);

        xbox_events.on_change_select($xbox);

        xbox_events.on_change_text($xbox);

        xbox_events.on_change_textarea($xbox);

        xbox_events.on_change_wp_editor($xbox);
    };

    xbox_events.on_change_colorpicker = function ($xbox) {
        $xbox.on('change', '.xbox-type-colorpicker .xbox-element', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
    };

    xbox_events.on_change_code_editor = function ($xbox) {
        $xbox.find('.xbox-code-editor').each(function (index, el) {
            var editor = ace.edit($(el).attr('id'));
            editor.getSession().on('change', function (e) {
                $(el).trigger('xbox_changed_value', editor.getValue());
            });
        });
    };

    xbox_events.on_change_file = function ($xbox) {
        $xbox.on('change', '.xbox-type-file .xbox-element', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
        $xbox.find('.xbox-type-file .xbox-field').on('xbox_after_add_files', function (e, selected_files, media) {
            var value;
            if (!media.multiple) {
                $(selected_files).each(function (index, obj) {
                    value = obj.url;
                });
            } else {
                value = [];
                $(selected_files).each(function (index, obj) {
                    value.push(obj.url);
                });
            }
            $(this).find('.xbox-element').trigger('xbox_changed_value', [value]);
        });
    };

    xbox_events.on_change_image_selector = function ($xbox) {
        $xbox.on('imgSelectorChanged', '.xbox-type-image_selector .xbox-element', function () {
            if ($(this).closest('.xbox-image-selector').data('image-selector').like_checkbox) {
                var value = [];
                $(this)
                    .closest('.xbox-radiochecks')
                    .find('input[type=checkbox]:checked')
                    .each(function (index, el) {
                        value.push($(this).val());
                    });
                $(this).trigger('xbox_changed_value', [value]);
            } else {
                $(this).trigger('xbox_changed_value', $(this).val());
            }
        });
    };

    xbox_events.on_change_number = function ($xbox) {
        $xbox.on('input', '.xbox-type-number .xbox-element', function () {
            $(this).trigger('xbox_changed_value', parseInt($(this).val(), 10));
        });
        $xbox.find('.xbox-type-number .xbox-field').spinner('changing', function (e, newVal, oldVal) {
            $(this).trigger('xbox_changed_value', newVal);
        });
    };

    xbox_events.on_change_oembed = function ($xbox) {
        $xbox.on('change', '.xbox-type-oembed .xbox-element', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
    };

    xbox_events.on_change_radio = function ($xbox) {
        $xbox.on('ifChecked', '.xbox-type-radio .xbox-element', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
    };

    xbox_events.on_change_checkbox = function ($xbox) {
        $xbox.on('ifChanged', '.xbox-type-checkbox .xbox-element', function () {
            var value = [];
            $(this)
                .closest('.xbox-radiochecks')
                .find('input[type=checkbox]:checked')
                .each(function (index, el) {
                    value.push($(this).val());
                });
            $(this).trigger('xbox_changed_value', [value]);
        });
    };

    xbox_events.on_change_switcher = function ($xbox) {
        $xbox.on('statusChange', '.xbox-type-switcher .xbox-element', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
    };

    xbox_events.on_change_select = function ($xbox) {
        $xbox.find('.xbox-type-select .xbox-element').dropdown({
            onChange: function (value, text, $selectedItem) {
                $selectedItem.closest('.xbox-element').trigger('xbox_changed_value', value);
            },
        });
    };

    xbox_events.on_change_text = function ($xbox) {
        $xbox.on('input', '.xbox-type-text .xbox-element', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
    };

    xbox_events.on_change_textarea = function ($xbox) {
        $xbox.on('input', '.xbox-type-textarea .xbox-element', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
    };

    xbox_events.on_change_wp_editor = function ($xbox) {
        var $wp_editors = $xbox.find('.xbox-type-wp_editor textarea.wp-editor-area');
        $xbox.on('input', '.xbox-type-wp_editor textarea.wp-editor-area', function () {
            $(this).trigger('xbox_changed_value', $(this).val());
        });
        setTimeout(function () {
            $wp_editors.each(function (index, el) {
                var ed_id = $(el).attr('id');
                var wp_editor = tinymce.get(ed_id);
                if (wp_editor) {
                    wp_editor.on('change input', function (e) {
                        var value = wp_editor.getContent();
                        $(el).trigger('xbox_changed_value', wp_editor.getContent());
                    });
                }
            });
        }, 1000);
    };

    //Document Ready
    $(function () {
        xbox_events.init();
    });

    return xbox_events;
})(window, document, jQuery);
