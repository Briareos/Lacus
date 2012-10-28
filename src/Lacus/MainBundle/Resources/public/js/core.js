$(function () {
    "use strict";

    $(document).on('submit', 'form.form-finalize', function (e) {
        var $form = $(this);
        $form.ajaxSubmit({
            context:$form,
            success:function (data, xhr, settings) {
                if (data.status === 'invalid') {
                    $form.replaceWith(data.form);
                } else if (data.status === 'success') {
                    $('.ajax-finalize').modal('hide');
                }
            }
        });
        return false;
    });

    window.attach = function ($context) {
        $('textarea[data-wysiwyg]', $context).wysihtml5({
            stylesheets:false,
            image:false,
            color:false,
            tags: {
            }
        });
    };
});