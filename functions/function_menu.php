<?php
function buat_menu($link, $ikon, $judul,  $leveluser=array()){
	$module = isset($_GET['module'])?$_GET['module']:null;
	foreach($leveluser as $level){ 
		if(userroles('role')==$level) echo'<li'.(($module==$link)?' class="active"':"").'><a href="?module='.$link.'"><i class="material-icons">'.$ikon.'</i> <span>'.$judul.'</span></a></li>';
	}
}

function buat_submenu($link, $judul, $leveluser=array()){
	$module = isset($_GET['module'])?$_GET['module']:null;
	foreach($leveluser as $level){
		if(userroles('role')==$level) echo'<li'.(($module==$link)?' class="active"':"").'><a href="?module='.$link.'"> '.$judul.'</a></li>';
	}
}

function buka_dropdown($ikon, $judul, $page=array(), $leveluser=array()){
	$module = isset($_GET['module'])?$_GET['module']:null;
	foreach($leveluser as $level){
		if(userroles('role')==$level)
			echo'<li'.((in_array($module, $page))?' class="active"':"").'><a href="javascript:void(0);" class="menu-toggle">
			<i class="material-icons">'.$ikon.'</i> <span>'.$judul.'</span></a>
			<ul class="ml-menu">';
	}
}

function tutup_dropdown($leveluser=array()){
	foreach($leveluser as $level){
		if(userroles('role')==$level) echo'</ul></li>';
	}
}

function buat_menu_dashboard($link, $ikon, $judul,  $leveluser=array()){
	foreach($leveluser as $level){
		if(userroles('role')==$level) {
			echo '<div class="col-xs-6 col-sm-3 col-md-3 col-lg-3">
				<a href="?module='.$link.'">
					<div class="image">
						<div class="icon">
							<i class="material-icons">'.$ikon.'</i>
						</div>
					</div>
					<div class="sname">'.$judul.'</div>
				</a>
			</div>';
		}
	}
}

?>
