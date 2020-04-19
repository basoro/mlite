$("a.delete").click(function(e){
    if(!confirm('Anda yakin ingin menghapus?')){
        e.preventDefault();
        return false;
    }
    return true;
});
