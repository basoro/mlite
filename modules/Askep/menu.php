<li class="<?php if ($params['module']=="Askep") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">loyalty</i>
        <span>Asuhan Keperawatan</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Askep" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Askep&page=index">
                Index Asuhan
            </a>
        </li>
        <li class="<?php if ($params['module']=="Askep" && $params['page']=="pengkajian_awal") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Askep&page=pengkajian_awal">
                Pengkajian Awal
            </a>
        </li>
        <li class="<?php if ($params['module']=="Askep" && $params['page']=="rencana_asuhan") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Askep&page=rencana_asuhan">
                Rencana Asuhan
            </a>
        </li>
    </ul>
</li>
