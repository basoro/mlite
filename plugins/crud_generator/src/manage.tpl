<div class="row gx-3">
    <div class="col-xxl-12 col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title">Kelola MODULE_NAME</h5>
            </div>
            <div class="card-body">
                <div class="row" style="padding-bottom: 10px;">
                    <div class='col-md-6 text-left'>
                      <div class="btn-group" role="group" aria-label="Toolbar">
                          <button id='lihat_data_NAMA_TABLE' class='btn btn-info' data-bs-toggle='tooltip' data-bs-placement='top' title='Lihat Data' {?=isset_or($disabled_menu.read)?}>
                          <i class='ri-eye-line' style='font-size: 15px;'></i><span class="hidden-xs"> Lihat</span> 
                          </button>
                          <button id='tambah_data_NAMA_TABLE' class='btn btn-success' data-bs-toggle='tooltip' data-bs-placement='top' title='Edit Data' {?=isset_or($disabled_menu.create)?}>
                          <i class='ri-add-circle-line' style='font-size: 15px;'></i><span class="hidden-xs"> Tambah</span>
                          </button>
                          <button id='edit_data_NAMA_TABLE' class='btn btn-primary' data-bs-toggle='tooltip' data-bs-placement='top' title='Edit Data' {?=isset_or($disabled_menu.update)?}>
                          <i class='ri-edit-circle-line' style='font-size: 15px;'></i><span class="hidden-xs"> Edit</span>
                          </button>
                          <button id='hapus_data_NAMA_TABLE' class='btn btn-danger' data-bs-toggle='tooltip' data-bs-placement='top' title='Hapus Data' {?=isset_or($disabled_menu.delete)?}>
                          <i class='ri-delete-bin-line' style='font-size: 15px;'></i><span class="hidden-xs"> Hapus</span>
                          </button>
                      </div>
                    </div>
                    <div class='col-md-6 text-right'>
                      <div class="input-group" style="width:100%">
                          <span class="input-group-text">Cari</span>
                          <span style="width:150px;padding:0 !important;background-color: #cccccc;border: 0px;text-align: left !important;">
                          <select class="form-select" id='search_field_NAMA_TABLE' name='search_field_NAMA_TABLE' style="margin: 0 !important;"> 
                              SEARCH_ISI
                          </select>
                          </span>
                          <input class='form-control' name='search_text_NAMA_TABLE' id='search_text_NAMA_TABLE' type='search' placeholder='Masukkan Kata Kunci Pencarian' />  
                          <span class="input-group-text" id='filter_search_NAMA_TABLE' data-toggle='tooltip' data-placement='top' title='Filter Pencarian'><i class="ri-search-line"></i></span>
                      </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="tbl_NAMA_TABLE" class="table table-striped display dataTable" style="width:100%">
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
</div>

<!-- Modal -->
<div id="modal_NAMA_TABLE" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span id="modal-title">Edit Data MODULE_NAME</span></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form name="form_NAMA_TABLE">
          <input type="hidden" class="form-control" id="typeact" /> 
          FORM_EDIT
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><i class="ri-close-line"></i> Batal </button>
        <button type="submit" id="simpan_data_NAMA_TABLE" class="btn btn-primary"><i class="ri-save-3-line"></i> Simpan </button>
        </form>
      </div>
    </div>
  </div>
</div>

<div id="modal_lihat_NAMA_TABLE" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><span id="modal-title">Lihat Data MODULE_NAME</span></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="forTable_NAMA_TABLE"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal"><i class="ri-close-line"></i> Tutup </button>
        <button type="submit" id="view_chart" class="btn btn-primary">
          <i class="ri-bar-chart-2-line"></i> Chart </button>
        <button type="submit" id="export_pdf" class="btn btn-danger">
          <i class="ri-file-pdf-2-line"></i> PDF </button>
        <button type="submit" id="export_xlsx" class="btn btn-success">
          <i class="ri-file-excel-2-line"></i> XLSX </button>
      </div>
    </div>
  </div>
</div>

<div id="modal_detail_NAMA_TABLE" class="modal fade" role="dialog" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      ...
    </div>
  </div>
</div>
