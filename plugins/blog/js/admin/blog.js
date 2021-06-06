function insertEditor(type)
{
    var editor = $('.editor');

    if(type == 'wysiwyg')
    {
        if($('.markItUp').length)
        {
            editor.markItUpRemove();
            $('#textarea-tabs').removeClass('markItUp');
        }

        editor.summernote(
        {
            lang: 'id_indonesian',
            height: 335,
            callbacks:
            {
                onInit: function()
                {
                    var e = $(this);
                    $('.note-codable', e.next()).keyup(function()
                    {
                        e.val($(this).val());
                    });
                },
                onImageUpload: function(files)
                {
                    sendFile(files[0], this);
                },
                onChange: function()
                {
                    editor.parents('form').trigger('checkform.areYouSure');
                }
            }
        });
    }
    else
    {
        if($('.note-editor').length)
        {
            editor.each(function()
            {
                var isEmpty = $(this).summernote('isEmpty');
                $(this).summernote('destroy');
                if(isEmpty)
                    $(this).html('');
            });
        }

        var checkbox = $('input[name="markdown"]');
        editor.each(function()
        {
            var currentEditor = $(this);
            currentEditor.markItUp(checkbox.is(':checked') ? markItUp_markdown : markItUp_html).highlight();

            if($('.editor').data('editor') == 'html')
            {
                checkbox.change(function()
                {
                    currentEditor.markItUpRemove();
                    if (checkbox.is(':checked'))
                        currentEditor.markItUp(markItUp_markdown).highlight();
                    else
                        currentEditor.markItUp(markItUp_html).highlight();
                });
            }
        });
        $('#textarea-tabs').addClass('markItUp');
    }
}

function sendFile(file, editor)
{
    var formData = new FormData();
    formData.append('file', file);

    var fileData = URL.createObjectURL(file);
    $(editor).summernote('insertImage', fileData, function($image)
    {
        $.ajax({
            xhr: function()
            {
                var xhr = new window.XMLHttpRequest();

                $('input[type="submit"]').prop('disabled', true);
                var progress = $('.progress:first').clone();
                progress = (progress.fadeIn()).appendTo($('.progress-wrapper'));

                xhr.upload.addEventListener("progress", function(evt)
                {
                    if(evt.lengthComputable)
                    {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = parseInt(percentComplete * 100);
                        progress.children().css('width', percentComplete + '%');

                        if(percentComplete === 100)
                        {
                            progress.fadeOut();
                            progress.remove();
                            $('input[type="submit"]').prop('disabled', false);
                        }
                    }
                }, false);

                return xhr;
            },
            url: '{?=url([ADMIN, "blog", "editorUpload"])?}',
            data: formData,
            type: 'POST',
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data)
            {
                if(data.status == 'success')
                {
                    $image.remove();
                    $(editor).summernote('insertImage', data.result);
                }
                else if(data.status == 'failure')
                {
                    $image.remove();
                    bootbox.alert(data.result);
                }
            }
        });
    });
}

function markdown()
{
    var checkbox = $('input[name="markdown"]');
    if ($('.editor').data('editor') == 'wysiwyg')
    {
        checkbox.change(function()
        {
            if($(this).is(':checked'))
                insertEditor('html');
            else
                insertEditor('wysiwyg');
        });

        if(checkbox.is(':checked'))
            insertEditor('html');
        else
            insertEditor('wysiwyg');
    }
    else
        insertEditor('html');
}

$(document).ready(function()
{
    markdown();

    $('form').areYouSure({ 'message': 'Ada perubahan belum disimpan! Yakin ingin meninggalkan halaman ini?' });

    var $tags = $('select[name="tags[]"]');

    if($tags.hasClass('selectator'))
        $tags.selectator('destroy');

    $tags.selectator({
        height: '39px',
        useSearch: true,
        useOnMobile: true,
        showAllOptionsOnFocus: true,
        load: function (search, callback) {
            if (search.length < this.minSearchLength) return callback();
            $.ajax({
                url: '{?= url([ADMIN, "blog", "jsontags", "' + encodeURIComponent(search) + '"]) ?}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    callback(data.slice(0, 10));
                },
                error: function() {
                    callback();
                }
            });
        },
        placeholder: 'tambah tag...',
        delay: 200,
        minSearchLength: 2,
        valueField: 'name',
        textField: 'name',
        render: {
            option: function (_item, escape) {
                var html = '';
                html += '<div class="selectator_option_title">';
                html += ((typeof _item.name !== 'undefined') ? _item.name.replace(/[`~!@#$%^&*()_|+\-=?;:'",.<>\{\}\[\]\\\/]/g, '') : '');
                html += '</div>';
                return html;
            }
        }
    });
});
