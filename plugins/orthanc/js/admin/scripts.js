$(document).ready(function() {
    // Initialize DataTable
    $('#studiesTable').DataTable({
        "pageLength": 25,
        "order": [[ 2, "desc" ]], // Sort by Study Date descending
        "columnDefs": [
            { "orderable": false, "targets": 8 } // Disable sorting for Actions column
        ],
        "language": {
            "search": "Cari:",
            "lengthMenu": "Tampilkan _MENU_ entries",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entries",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });
});

function downloadStudy(studyId) {
    // Implement download functionality
    window.open('{?=url([ADMIN, "orthanc"])?}/download?study=' + studyId, '_blank');
}