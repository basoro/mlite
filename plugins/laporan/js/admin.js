// JavaScript untuk Plugin Laporan

$(document).ready(function() {
    // Initialize date inputs with today's date if empty
    var today = new Date().toISOString().split('T')[0];
    
    $('input[name="tgl_awal"]').each(function() {
        if (!$(this).val()) {
            $(this).val(today);
        }
    });
    
    $('input[name="tgl_akhir"]').each(function() {
        if (!$(this).val()) {
            $(this).val(today);
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var tgl_awal = $('input[name="tgl_awal"]').val();
        var tgl_akhir = $('input[name="tgl_akhir"]').val();
        
        if (!tgl_awal || !tgl_akhir) {
            e.preventDefault();
            alert('Harap isi tanggal awal dan tanggal akhir!');
            return false;
        }
        
        if (tgl_awal > tgl_akhir) {
            e.preventDefault();
            alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir!');
            return false;
        }
    });
});

// Function to export table to Excel
function exportToExcel(tableId, filename) {
    var table = document.getElementById(tableId);
    if (!table) {
        alert('Tabel tidak ditemukan!');
        return;
    }
    
    var wb = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    XLSX.writeFile(wb, filename + '.xlsx');
}

// Function to print report
function printReport() {
    window.print();
}

// Function to show loading
function showLoading() {
    $('body').append('<div id="loading" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;"><div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:white;font-size:18px;">Memuat data...</div></div>');
}

// Function to hide loading
function hideLoading() {
    $('#loading').remove();
}