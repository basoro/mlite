<div class="card">
  <div class="header">
      <h2>Indikator Wajib</h2>
  </div>
  <div class="body">
    <div class="table-responsive">
      <table id="elemen" class="table datatable">
        <thead>
          <tr>
            <th>#</th>
            <th>Elemen</th>
            <th>Penjelasan</th>
            <th>RDOWS</th>
            <th>Skor</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $url = "modules/Sismadak/inc/instrument.json";
          $json = file_get_contents($url);
          $datas = (array)json_decode($json, true);
          foreach($datas as $data) {
              echo '<tr>';
              echo '<td>'.$data['id'].'</td>';
              echo '<td>'.$data['instrument'].'</td>';
              echo '<td>'.$data['name'].'</td>';
              echo '<td>'.nl2br($data['RDOWS']).'</td>';
              echo '<td>'.$data['score'].'</td>';
              echo '</tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
