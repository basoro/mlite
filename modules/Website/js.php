<!-- TinyMCE -->
<script src="<?php echo URL; ?>/modules/Website/tinymce/tinymce.min.js"></script>
<!-- Custom Js -->
<script src="<?php echo URL; ?>/modules/Website/assets/js/jquery-fancybox.js"></script>

<!-- Custom TiniMCE Js -->
<script>
tinymce.init({
    selector : 'textarea#tinymce',
    theme : 'modern',
    height: 300,
    plugins: [
        'advlist autolink lists link image charmap print preview hr anchor pagebreak',
        'searchreplace wordcount visualblocks visualchars code fullscreen',
        'insertdatetime media nonbreaking save table contextmenu directionality',
        'emoticons template paste textcolor colorpicker textpattern imagetools responsivefilemanager'
    ],
    toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media code',
    menubar : false,
    image_advtab: true,
    relative_urls: false,
    images_upload_url : '<?php echo URL; ?>/modules/Website/inc/upload.php',
    automatic_uploads : false,
    images_upload_handler : function(blobInfo, success, failure) {
      var xhr, formData;

      xhr = new XMLHttpRequest();
      xhr.withCredentials = false;
      xhr.open('POST', '<?php echo URL; ?>/modules/Website/inc/upload.php');

      xhr.onload = function() {
        var json;

        if (xhr.status != 200) {
          failure('HTTP Error: ' + xhr.status);
          return;
        }

        json = JSON.parse(xhr.responseText);

        if (!json || typeof json.file_path != 'string') {
          failure('Invalid JSON: ' + xhr.responseText);
          return;
        }

        success(json.file_path);
      };

      formData = new FormData();
      formData.append('file', blobInfo.blob(), blobInfo.filename());

      xhr.send(formData);
    },
    external_filemanager_path: "<?php echo URL; ?>/modules/Website/tinymce/plugins/filemanager/",
    filemanager_title: "Filemanager" ,
    external_plugins: { "filemanager" : "<?php echo URL; ?>/modules/Website/tinymce/plugins/filemanager/plugin.min.js"}
});
tinymce.suffix = "";
tinyMCE.baseURL = 'modules/Website/tinymce';
</script>

<script type="text/javascript">
$(function(){
$('.iframe-btn').fancybox({
  'width'	: 1024,
  'minHeight'	: 600,
  'type'	: 'iframe',
  'autoScale'   : true
 });
});
</script>

<script>
    function responsive_filemanager_callback(field_id){
        console.log(field_id);
        var url=jQuery('#'+field_id).val();
        //alert('update '+field_id+" with "+url);
        //your code
        $('#image_preview').attr('src',document.getElementById("post_image").value).show();
        parent.$.fancybox.close();
    }
</script>
