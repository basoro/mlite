<div class="card">
  <div class="header">
      <h2>Instrument SNARS 1.1</h2>
  </div>
  <div class="body">
    <div class="table-responsive">
      <table id="elemen" class="table datatable">
        <thead>
          <tr>
            <th>#</th>
            <th>Elemen</th>
            <th>Penjelasan</th>
            <th>Cek List</th>
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
              echo '<td>';
              if(!empty($data['R'])) {
                echo '<b>R:</b> <div class="m-l-20 m-t-10">'.nl2br($data['R']).'</div>';
              }
              if(!empty($data['D'])) {
                echo '<b>D:</b> <div class="m-l-20">'.nl2br($data['D']).'</div>';
              }
              if(!empty($data['O'])) {
                echo '<b>O:</b> <div class="m-l-20">'.nl2br($data['O']).'</div>';
              }
              if(!empty($data['W'])) {
                echo '<b>W:</b> <div class="m-l-20">'.nl2br($data['W']).'</div>';
              }
              if(!empty($data['S'])) {
                echo '<b>S:</b> <div class="m-l-20">'.nl2br($data['S']).'</div>';
              }
              echo '</td>';
              echo '<td>'.$data['score'].'</td>';
              echo '</tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
