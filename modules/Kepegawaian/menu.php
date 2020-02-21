<li class="<?php if ($params['module']=="Kepegawaian") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">assignment_ind</i>
        <span>Kepegawaian</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Kepegawaian" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Kepegawaian&page=index">
                Data Pegawai
            </a>
        </li>
        <li class="<?php if ($params['module']=="Kepegawaian" && $params['page']=="tambah") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Kepegawaian&page=tambah">
                Tambah Pegawai
            </a>
        </li>
    </ul>
</li>
