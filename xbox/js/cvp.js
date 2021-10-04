jQuery(document).ready(function () {
    toggleDisplayerOptions();

    setTimeout(function () {
        var $urlTrigger = jQuery('a[href="' + window.location.hash + '"]')[0];
        var $parent = jQuery($urlTrigger).parents('.xbox-tab-content');
        if ($parent.length > 0) {
            $parentTab = jQuery($parent[0]).data('tab');
            jQuery('a[href="' + $parentTab + '"]').trigger('click');
        }
        jQuery($urlTrigger).trigger('click');
    }, 0);

    function toggleDisplayerOptions() {
        jQuery('div[data-field-id]').each(function (e) {
            var fieldId = jQuery(this).data('field-id');
            if (fieldId.indexOf('displayed-when') > -1) {
                var data = fieldId.split(':');
                var displayer = { type: data[1], id: data[2], value: data[3] };
                if (displayer.type == 'switch') {
                    if (jQuery('#' + displayer.id).attr('value') == displayer.value) {
                        jQuery(this).slideDown('fast');
                    } else {
                        jQuery(this).slideUp('fast');
                    }
                }
                if (displayer.type == 'radio') {
                    if (
                        jQuery('div.checked')
                            .children('input[name="' + displayer.id + '"]')
                            .attr('value') == displayer.value
                    ) {
                        jQuery(this).slideDown('fast');
                    } else {
                        jQuery(this).slideUp('fast');
                    }
                }
            }
        });
    }

    function updateUrlHash(hash) {
        document.location.hash = hash;
    }

    jQuery(document).on('click', '.xbox-item a', function () {
        var hash = jQuery(this).attr('href');
        updateUrlHash(hash);
    });

    jQuery(document).on('click', '.xbox-sw-wrap', function () {
        toggleDisplayerOptions();
    });

    jQuery(document).on('click', 'label', function () {
        toggleDisplayerOptions();
    });
});
