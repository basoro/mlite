$("a.delete").click(function(e){
    //if(!confirm('Anda yakin ingin menghapus?')){
    areYouSure({'message':'Ada perubahan belum disimpan! Yakin ingin meninggalkan halaman ini?'}) {
        e.preventDefault();
        return false;
    }
    return true;
});
