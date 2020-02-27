<li class="<?php if ($params['module']=="Sismadak") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">insert_chart</i>
        <span>Sismadak</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Sismadak" && $params['page']=="imut_klinik") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Sismadak&page=imut_klinik">
                Indikator Area Klinik
            </a>
        </li>
        <li class="<?php if ($params['module']=="Sismadak" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Sismadak&page=index">
                Indikator Area Manajemen
            </a>
        </li>
        <li class="<?php if ($params['module']=="Sismadak" && $params['page']=="imut_wajib") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Sismadak&page=imut_wajib">
                Indikator Wajib
            </a>
        </li>
    </ul>
</li>
