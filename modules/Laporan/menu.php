<li class="<?php if ($params['module']=="Laporan") {
    echo "active";
} ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">book</i>
        <span>Laporan</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Laporan" && $params['page']=="index") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Laporan&page=index">
                Laporan APS
            </a>
        </li>
        <li class="<?php if ($params['module']=="Laporan" && $params['page']=="ralan") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Laporan&page=ralan">
                Laporan Keluhan Penyakit
            </a>
        </li>
        <li class="<?php if ($params['module']=="Laporan" && $params['page']=="world") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Laporan&page=world">
                Laporan WORLD
            </a>
        </li>
    </ul>
</li>
