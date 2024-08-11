let URL_WEBSOCKET = "ws://localhost:3892";
let ws = new WebSocket(URL_WEBSOCKET);

ws.onmessage = function(response){
  try{
    output = JSON.parse(response.data);
    if(output['action'] == 'insert'){
      if(output['modul'] == 'pengguna'){
        let id = output['data']['id'];
        let username = output['data']['username'];
        let fullname = output['data']['fullname'];
        let description = output['data']['description'];
        let avatar = output['data']['avatar'] ? '<img src="' + mlite.url + '/uploads/users/' + output['data']['avatar'] +'" width="25px">' : '<img src="' + mlite.url + '/plugins/pengguna/img/default.png" width="25px">';
        let email = output['data']['email'];
        let role = output['data']['role'];
        let cap = output['data']['cap'];
        let access = output['data']['access'];
        $("#tbl_mlite_users").DataTable().destroy();
        $("#tbl_mlite_users").DataTable().row.add([id, username, fullname, description, avatar, email, role, cap, access]).draw(false).node();
      }
      if(output['modul'] == 'bahasa'){
        let id_bahasa = output['data']['id'];
        let nama_bahasa = output['data']['nama_bahasa'];
        $("#tbl_bahasa_pasien").DataTable().destroy();
        $("#tbl_bahasa_pasien").DataTable().row.add([id_bahasa, nama_bahasa]).draw(false).node();
      }
    }
  }catch(e){
    console.log(e);
  }
}


ws.onclose = function(){
  // Jika terputus dari websocket server, maka mencoba terhubung kembali.
  let interval_reconnect_ws = setInterval(function(){
    if(ws.readyState != 0){
      if(ws.readyState == 1){ // readyState = 1 (Open) , berarti sudah terhubung dengan websocket. Maka gak perlu interval lagi.
        clearInterval(interval_reconnect_ws);
      }else{
        ws = new WebSocket(URL_WEBSOCKET);	
      }
    }
    
  },5000);
  
}   