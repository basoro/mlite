
    <div class="modal fade" id="ICTRSHD" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="smallModalLabel">Instalasi ICT RSHD Barabai</h4>
                </div>
                <div class="modal-body">
                    Ditetapkan sebagai Instalasi ICT dengan Surat Keputusan Direktur Rumah Sakit Umum Daerah H. Damanhuri pada tanggal 1 November 2017.
                    <ul style="list-style:none;margin-left:0;padding-left:0;"><br>
                        <li><b>Kepala Instalasi : <br>MasBas (drg. Faisol Basoro)</b></li><br>
                        <li>Anggota :
                            <ul style="list-style:none;margin-left:0;padding-left:0;">
                                <li>- Amat (Muhammad Ma'ruf, S.Kom)</li>
                                <li>- Aruf (Ma'ruf, S.Kom)</li>
                                <li>- Didi (Didi Andriawan, S.Kom)</li>
                                <li>- Adly (M. Adly Hidayat, S.Kom)</li>
                                <li>- Ridho (M. Alfian Ridho, S.Kom)</li>
                                <li>- Ijai (Zailani)</li>
                                <li>- Ina (Inarotut Darojah)</li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">TUTUP</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Jquery Core Js -->
    <script src="<?php echo URL; ?>/assets/plugins/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core Js -->
    <script src="<?php echo URL; ?>/assets/plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Select Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/bootstrap-select/js/bootstrap-select.js"></script>

    <!-- Slimscroll Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/jquery-slimscroll/jquery.slimscroll.js"></script>

    <!-- Waves Effect Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/node-waves/waves.js"></script>

    <!-- Jquery CountTo Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/jquery-countto/jquery.countTo.js"></script>

    <!-- Jquery DataTable Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/jquery.dataTables.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/export/buttons.flash.min.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/export/jszip.min.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/export/pdfmake.min.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/export/vfs_fonts.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/export/buttons.html5.min.js"></script>
    <script src="<?php echo URL; ?>/assets/plugins/jquery-datatable/extensions/export/buttons.print.min.js"></script>

    <!-- Chart Plugins Js -->
    <script src="<?php echo URL; ?>/assets/plugins/chartjs/Chart.bundle.js"></script>

    <!-- Sparkline Chart Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/jquery-sparkline/jquery.sparkline.js"></script>

    <!-- Jquery CountTo Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/jquery-countto/jquery.countTo.js"></script>

    <!-- Autosize Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/autosize/autosize.js"></script>

    <!-- Moment Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/momentjs/moment.js"></script>

    <!-- Bootstrap Material Datetime Picker Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js"></script>

    <!-- Jquery-UI Js -->
    <script src="<?php echo URL; ?>/assets/js/jquery-ui.min.js"></script>


	<script src="<?php echo URL; ?>/assets/plugins/sweetalert/sweetalert.min.js"></script>

    <!-- Select2 Js -->
    <script src="<?php echo URL; ?>/assets/js/select2.min.js"></script>

         <!-- Light Gallery Plugin Js -->
    <script src="<?php echo URL; ?>/assets/plugins/light-gallery/js/lightgallery-all.js"></script>

    <!-- Custom Js -->
    <script src="<?php echo URL; ?>/assets/js/admin.js"></script>
	  <script>

      var url = window.location.pathname; //sets the variable "url" to the pathname of the current window
      var activePage = url.substring(url.lastIndexOf('/') + 1); //sets the variable "activePage" as the substring after the last "/" in the "url" variable
      if($('.menu li a > .active').length > 0){
        $('.active').removeClass('active');//remove current active element if there's
      }
      $('.menu li a').each(function () { //looks in each link item within the primary-nav list
        var linkPage = this.href.substring(this.href.lastIndexOf('/') + 1); //sets the variable "linkPage" as the substring of the url path in each &lt;a&gt;
        if (activePage == linkPage) { //compares the path of the current window to the path of the linked page in the nav item
          $(this).parents('li').addClass('active');
          $(this).parent().addClass('active'); //if the above is true, add the "active" class to the parent of the &lt;a&gt; which is the &lt;li&gt; in the nav list
        }
      });

      $(document).ready(function() {
          if (location.hash) {
              $("a[href='" + location.hash + "']").tab("show");
          }
          $(document.body).on("click", "a[data-toggle='tab']", function(event) {
              location.hash = this.getAttribute("href");
          });
      });
      $(window).on("popstate", function() {
          var anchor = location.hash || $("a[data-toggle='tab']").first().attr("href");
          $("a[href='" + anchor + "']").tab("show");
      });

	  </script>

    <script>

        $('#datatable').dataTable( {
              "processing": true,
              "responsive": true,
              "oLanguage": {
                  "sProcessing":   "Sedang memproses...",
                  "sLengthMenu":   "Tampilkan _MENU_ entri",
                  "sZeroRecords":  "Tidak ditemukan data yang sesuai",
                  "sInfo":         "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                  "sInfoEmpty":    "Menampilkan 0 sampai 0 dari 0 entri",
                  "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                  "sInfoPostFix":  "",
                  "sSearch":       "Cari:",
                  "sUrl":          "",
                  "oPaginate": {
                      "sFirst":    "«",
                      "sPrevious": "‹",
                      "sNext":     "›",
                      "sLast":     "»"
                  }
              },
              "order": [[ 0, "asc" ]]
        } );

        $(document).ready(function() {
            $('.datepicker').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD',
                clearButton: true,
                weekStart: 1,
                time: false
            });
        });

        $('.count-to').countTo();

      	$('#aniimated-thumbnials').lightGallery({
          	thumbnail: true,
          	selector: 'a'
      	});
	  </script>

    <script type="text/javascript">
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#image_upload_preview').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#inputFile").change(function () {
        readURL(this);
    });

    function upload_berkas(){
        document.getElementById("inputFile").click();
    }


    </script>

<?php
if(isset($_GET['module'])) {
  if(file_exists('modules/'.$_GET['module'].'/js.php')) {
    include('modules/'.$_GET['module'].'/js.php');
  }
}
?>


</body>

</html>
