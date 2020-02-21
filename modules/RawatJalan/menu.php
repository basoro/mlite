<?php if(FKTL == true) { ?>
<li class="<?php if ($params['module']=="RawatJalan") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">accessible</i>
        <span>Rawat Jalan</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="RawatJalan" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=RawatJalan&page=index">
                Pasien Rawat Jalan
            </a>
        </li>
        <li class="<?php if ($params['module']=="RawatJalan" && $params['page']=="rujuk_internal") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=RawatJalan&page=rujuk_internal">
                Rujukan Internal
            </a>
        </li>
        <li class="<?php if ($params['module']=="RawatJalan" && $params['page']=="pasien_lanjutan") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=RawatJalan&page=pasien_lanjutan">
              Pasien Lanjutan
            </a>
        </li>
    </ul>
</li>
<?php } ?>
