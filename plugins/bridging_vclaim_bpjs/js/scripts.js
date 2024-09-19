$(document).ready(function() {
    $('.--add-parameter').on('click', function(e) {
        e.preventDefault();

        var initial = $(this).attr('data-parameter');

        $(
            '<div class="row">' +
                '<div class="text-muted col-6 col-md-4">' +
                    '<div class="mb-3">' +
                        '<input type="text" name="' + initial + '_key[]" class="form-control form-control-sm param-' + initial + '-key" placeholder="Key" />' +
                    '</div>' +
                '</div>' +
                '<div class="text-muted col-6 col-md-6 ps-0">' +
                    '<div class="mb-3">' +
                        '<div class="input-group">' +
                            '<input type="text" name="' + initial + '_value[]" class="form-control form-control-sm param-' + initial + '-value" placeholder="Value" />' +
                            '<button type="button" class="btn btn-secondary btn-sm" onclick="$(this).closest(\'.row\').remove()">' +
                                '<i class="ri-close-line"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        )
        .insertBefore($(this))
    }),
    
    $('.--api-debug').on('submit', function(e) {
        e.preventDefault();

        $('.mdi.mdi-send').removeClass('mdi-send').addClass('mdi-loading mdi-spin');
        $('.response-result').trigger('click');
        
        if (! $(this).find('input[name=url]').val()) {
            $('.mdi.mdi-loading.mdi-spin').removeClass('mdi-loading mdi-spin').addClass('mdi-send');
            $('pre code').text(JSON.stringify({error: "No service URL are given"}, null, 4));
            Prism.highlightAll();
            
            return;
        }
        
        let header = {},
            body = {},
            method = $(this).find('select[name=method]').val(),
            parameter = new FormData(this);
        
        $('.param-header-key').each(function(num, value) {
            let key = $(this).val(),
                val = $('.param-header-value:eq(' + num + ')').val();
            if (val) {
                header[key] = val;
            }
        });
        
        $('.param-body-key').each(function(num, value) {
            let key = $(this).val(),
                val = $('.param-body-value:eq(' + num + ')').val();
            if (val) {
                body[key] = val;
            }
        });
        
        $.ajax({
            url: $(this).find('input[name=url]').val(),
            method: method,
            data: body,
            headers: header,
            beforeSend: function() {
                $('pre code').text('Requesting...'),
                $('.result-html').html('')
            }
        })
        .always(function(response, status, error) {
            if (typeof response !== 'object') {
                response = {
                    error: 'The response is not a valid object'
                };
            }
            
            $('.mdi.mdi-loading.mdi-spin').removeClass('mdi-loading mdi-spin').addClass('mdi-send');
            $('pre code').text(JSON.stringify((typeof response.responseJSON !== 'undefined' ? response.responseJSON : response), null, 4));
            Prism.highlightAll();

        })
    })
})
