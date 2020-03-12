<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<?php
class EBook {
    function index() {
?>
      <div class="modal fade modal-fullscreen" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 100%;height: 100%;margin: 0;top: 0;left: 0;">
          <div class="modal-dialog modal-lg">
              <div class="modal-content">
                  <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                      <h4 class="modal-title" id="myModalLabel">Baca e-Book</h4>
                  </div>
                  <div class="modal-body" style="padding:0;">
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      <button type="button" class="simpan btn btn-primary">Save changes</button>
                  </div>
              </div>
          </div>
      </div>
      <div class="card">
        <div class="header">
            <h2>Koleksi e-Book</h2>
        </div>
        <div class="body">
          <table id="datatable" class="table table-bordered table-striped table-hover display nowrap" width="100%">
            <thead>
              <tr>
                <th>Judul</th>
                <th>Ringkasan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $query = query("SELECT perpustakaan_ebook.*, perpustakaan_pengarang.nama_pengarang FROM perpustakaan_ebook, perpustakaan_pengarang WHERE perpustakaan_ebook.kode_pengarang = perpustakaan_pengarang.kode_pengarang");
              while ($row = fetch_array($query)) {
                echo '<tr>';
                echo '<td>'.SUBSTR($row['judul_ebook'], 0, 30).' ...</td>';
                echo '<td>';
                echo '<ul class="list-unstyled">';
                echo '<li>Kode: '.$row['kode_ebook'].'</li>';
                echo '<li>Judul: '.$row['judul_ebook'].'</li>';
                echo '<li>Halaman : '.$row['jml_halaman'].'</li>';
                echo '<li>Pengarang: '.$row['nama_pengarang'].'</li>';
                echo '</ul>';
                echo '</td>';
                echo '<td><a href="#" class="view-ebook" data-id="'.$row['kode_ebook'].'"><i class="material-icons">library_books</i></a></a></td>';
                echo '</tr>';
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
<?php
    }
}
?>
