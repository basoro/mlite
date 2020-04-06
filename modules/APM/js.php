<script type="text/javascript">
$('.dpjp').select2({
  placeholder: 'Pilih Dokter',
  ajax: {
    url: '<?php echo URL; ?>/modules/RawatInap/includes/select-dokter.php',
    dataType: 'json',
    delay: 250,
    processResults: function (data) {
      return {
        results: data
      };
    },
    cache: true
  },
  templateResult: formatData,
  minimumInputLength: 3
});
</script>
