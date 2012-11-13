$(function () {
    "use strict";

    $(document).on('submit', 'form.form-finalize', function (e) {
        var $form = $(this);
        $form.ajaxSubmit({
            context:$form,
            success:function (data, xhr, settings) {
                if (data.status === "KO") {
                    $form.replaceWith(data.form);
                } else if (data.status === "OK") {
                    $('.ajax-finalize').modal('hide');
                }
            }
        });
        return false;
    });

    $(document).on('click', 'a.set-post-status', function (e) {
        var $a = $(this);
        $.ajax({
            url:$a.attr('href'),
            success:function (data) {
                if (data.status === "OK") {
                    $a.parents('.list-status').replaceWith(data.statuses);
                } else if (data.status === "KO") {

                }
                else {
                }
            },
            error:function () {

            }
        });
        return false;
    });

    window.attach = function ($context) {
        $('textarea[data-wysiwyg="1"]:not(:disabled)', $context).redactor({
        });
    };

    window.attach($('body'));
});