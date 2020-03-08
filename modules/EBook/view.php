<?php
include ('../../config.php');
$ebook = fetch_assoc(query("SELECT berkas FROM perpustakaan_ebook WHERE kode_ebook = '$_POST[kode]'"));
echo '<a class="media" href="'.URLSIMRS.'/ebook/'.$ebook['berkas'].'"></a>';
?>
<script type="text/javascript">
    $(document).ready(function(){
        xheight = $(document).height();
        ywidth = $(document).width();
        $('a.media').media({width:ywidth, height:xheight});
    });
</script>
