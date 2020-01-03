<li class="<?php if ($params['module']=="Master") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">folder</i>
        <span>Master</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Master" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Master&page=index">
                Dashboard
            </a>
        </li>
        <li class="<?php if ($params['module']=="Master" && $params['page']=="poliklinik") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Master&page=poliklinik">
                Master Poliklinik
            </a>
        </li>
        <li class="<?php if ($params['module']=="Master" && $params['page']=="dokter") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Master&page=dokter">
                Master Dokter
            </a>
        </li>
        <li class="<?php if ($params['module']=="Master" && $params['page']=="carabayar") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Master&page=carabayar">
                Master Cara Bayar
            </a>
        </li>
    </ul>
</li>
