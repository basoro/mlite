<?php
	use Workerman\Worker;
	require_once __DIR__ . '/vendor/autoload.php' ;
	
	# port 3892 terserah mau diganti apa
	$ws = new Worker('websocket://0.0.0.0:3892'); 
	
	// Jika ada yang terhubung
	$ws->onConnect = function($connection){
		$remote_ip = $connection->getRemoteIp();
		
		$connection->onWebSocketConnect = function($connection) use ($remote_ip){
			print("$remote_ip - Berhasil terhubung\n");
			unset($remote_ip);
		};
	};
	
	
	// Jika ada yang mengirim data
	$ws->onMessage = function($connection, $data) use($ws){
		
		// Broadcast datanya ke semua yang terhubung
		foreach($ws->connections as $connection_sub){
			$connection_sub->send($data);
		}
		
	};
	
	// Jika terputus
	$ws->onClose = function($connection){
		$remote_ip = $connection->getRemoteIp();
		print("$remote_ip - Telah terputus!\n");
	};
	
	
	Worker::runAll();
	
?>