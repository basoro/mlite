<script type="text/javascript" src="http://malsup.github.com/jquery.media.js"></script>
<script>
    $(function () {
        $(document).on('click', '.view-ebook', function (e) {
            e.preventDefault();
            $("#myModal").modal('show');
            $.post('<?php echo URL; ?>/modules/EBook/view.php',
                    {kode: $(this).attr('data-id')},
            function (html) {
                $(".modal-body").html(html);
            }
            );
        });
    });
</script>
