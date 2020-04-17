<li class="<?php if ($params['module']=="Website") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">language</i>
        <span>Website</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Website" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Website&page=index">
                Dashboard
            </a>
        </li>
        <li class="<?php if ($params['module']=="Website" && $params['page']=="posts") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Website&page=posts">
                Data Postingan
            </a>
        </li>
        <li class="<?php if ($params['module']=="Website" && $params['page']=="categories") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Website&page=categories">
                Kategori Postingan
            </a>
        </li>
        <li class="<?php if ($params['module']=="Website" && $params['page']=="pages") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Website&page=pages">
                Data Halaman
            </a>
        </li>
        <li class="<?php if ($params['module']=="Website" && $params['page']=="media") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Website&page=media">
                Data Media
            </a>
        </li>
    </ul>
</li>
