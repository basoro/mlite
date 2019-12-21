<li class="<?php if ($params['module']=="Umpeg") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">folder</i>
        <span>Umum Dan Kepegawaian</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Umpeg" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Umpeg&page=index">
                Data Pegawai
            </a>
        </li>
    </ul>
</li>
