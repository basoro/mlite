<article class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Kelola</h3>
            </div>
            <div class="panel-body">
                <div class="row" style="padding-bottom: 10px;">
                    <div class='col-md-6 text-left'>
                      <div class="btn-group" role="group" aria-label="Toolbar">
                          <button id='lihat_data_NAMA_TABLE' class='btn btn-info' data-toggle='tooltip' data-placement='top' title='Lihat Data NAMA_TABLE' style='color:white;'>
                          <i class='fa fa-eye' style='font-size: 15px;'></i><span class="hidden-xs"> Lihat</span> </button>
                          <button id='tambah_data_NAMA_TABLE' class='btn btn-success' data-toggle='tooltip' data-placement='top' title='Edit Data'>
                          <i class='fa fa-plus-square' style='font-size: 15px;'></i><span class="hidden-xs"> Tambah</span>
                          </button>
                          <button id='edit_data_NAMA_TABLE' class='btn btn-primary' data-toggle='tooltip' data-placement='top' title='Edit Data'>
                          <i class='fa fa-edit' style='font-size: 15px;'></i><span class="hidden-xs"> Edit</span>
                          </button>
                          <button id='hapus_data_NAMA_TABLE' class='btn btn-danger' data-toggle='tooltip' data-placement='top' title='Hapus Data'>
                          <i class='fa fa-trash' style='font-size: 15px;'></i><span class="hidden-xs"> Hapus</span>
                          </button>
                          <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                              <span class="hidden-xs">More</span>
                              <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                              <li><a href="#" id="lihat_detail_NAMA_TABLE">View Detail</a></li>
                              <li><a href="#">Other link</a></li>
                            </ul>
                          </div>
                      </div>
                    </div>
                    <div class='col-md-6 text-right'>
                      <div class="input-group" style="width:100%">
                          <span class="input-group-addon">Cari :</span>
                          <span class="input-group-addon" style="width:150px;padding:0 !important;background-color: #cccccc;border: 0px;text-align: left !important;">
                          <select class="form-control" id='search_field_NAMA_TABLE' name='search_field_NAMA_TABLE' style="margin: 0 !important;"> 
                              SEARCH_ISI
                          </select>
                          </span>
                          <input class='form-control' name='search_text_NAMA_TABLE' id='search_text_NAMA_TABLE' type='search' placeholder='Masukkan Kata Kunci Pencarian' />  
                          <span class="input-group-addon">
                          <span id='searchclear_NAMA_TABLE' data-toggle='tooltip' data-placement='top' title='Clear'><i class="fa fa-times text-danger"></i></span>
                          </span>
                      </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tbl_NAMA_TABLE" class="table table-bordered table-striped display dataTable" style="width:100%">
                        <thead>
                        <tr> 
                            HEAD_TABLE
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>        
            </div>
        </div>
    </div>
</article>

<!-- Modal -->
<div id="modal_NAMA_TABLE" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span id="modal-title">Edit Data MODULE_NAME</span>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </h4>
      </div>
      <div class="modal-body">
        <form name="form_NAMA_TABLE">
          <input type="hidden" class="form-control" id="typeact" /> 
          FORM_EDIT
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" type="button" data-dismiss="modal">Close</button>
        <button type="submit" id="simpan_data_NAMA_TABLE" class="btn btn-primary">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="modal_lihat_NAMA_TABLE" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span id="modal-title">Lihat Data MODULE_NAME</span>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </h4>
      </div>
      <div class="modal-body">
        <div id="forTable_NAMA_TABLE"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-default" type="button" data-dismiss="modal">Close</button>
        <button type="submit" id="export_pdf" class="btn btn-primary">
          <i class="fa fa-file-pdf-o"></i>&nbsp;PDF </button>
        <button type="submit" id="export_xlsx" class="btn btn-primary">
          <i class="fa fa-file-excel-o"></i>&nbsp;XLSX </button>
      </div>
    </div>
  </div>
</div>

<div id="modal_detail_NAMA_TABLE" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      ...
    </div>
  </div>
</div>
