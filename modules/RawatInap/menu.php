<?php if(FKTL == true) { ?>
<li class="<?php if ($params['module']=="RawatInap") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">hotel</i>
        <span>Rawat Ipan</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="RawatInap" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=RawatInap&page=index">
                Pasien Rawat Inap
            </a>
        </li>
        <li class="<?php if ($params['module']=="RawatInap" && $params['page']=="cppt") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=RawatInap&page=cppt">
              CPPT
            </a>
        </li>
        <li class="<?php if ($params['module']=="RawatInap" && $params['page']=="diet_pasien") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=RawatInap&page=diet_pasien">
                Diet Pasien
            </a>
        </li>
    </ul>
</li>
<?php } ?>
