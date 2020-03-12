<li class="<?php if ($params['module']=="APM") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">aspect_ratio</i>
        <span>APM</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="APM" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=APM&page=index">
                Display
            </a>
        </li>
        <li class="<?php if ($params['module']=="APM" && $params['page']=="data_antrian") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=APM&page=data_antrian">
                Data Antrian
            </a>
        </li>
    </ul>
</li>
