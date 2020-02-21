<li class="<?php if ($params['module']=="Odontogram") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">gradient</i>
        <span>Odontogram</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Odontogram" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Odontogram&page=index">
                Odontogram List
            </a>
        </li>
        <li class="<?php if ($params['module']=="Odontogram" && $params['page']=="history") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Odontogram&page=history">
                Odontogram History
            </a>
        </li>
    </ul>
</li>
