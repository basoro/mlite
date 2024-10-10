// let URL_WEBSOCKET = "ws://localhost:3892";
var ws = new WebSocket(URL_WEBSOCKET);
var baseURL = mlite.url + '/' + mlite.admin;

ws.onmessage = function(response){
  try{
    output = JSON.parse(response.data);
    if(output['action'] == 'simpan'){
      if(output['modul'] == 'rawat_jalan'){
        $("#rawat_jalan #display").show().load(baseURL + '/rawat_jalan/display?t=' + mlite.token);
      }
    }
  }catch(e){
    console.log(e);
  }
}


ws.onclose = function(){
  // Jika terputus dari websocket server, maka mencoba terhubung kembali.
  var interval_reconnect_ws = setInterval(function(){
    if(ws.readyState != 0){
      if(ws.readyState == 1){ // readyState = 1 (Open) , berarti sudah terhubung dengan websocket. Maka gak perlu interval lagi.
        clearInterval(interval_reconnect_ws);
      }else{
        ws = new WebSocket(URL_WEBSOCKET);	
      }
    }
    
  },5000);
}   