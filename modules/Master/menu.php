<?php if($role == 'Admin' || $role == 'Manajemen')  { ?>
<li class="<?php if ($params['module']=="Master") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">folder</i>
        <span>Master</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Master" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Master&page=index">
                Mater INDEX
            </a>
        </li>
        <li class="<?php if ($params['module']=="Master" && $params['page']=="hello") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Master&page=hello">
                Master HELLO
            </a>
        </li>
        <li class="<?php if ($params['module']=="Master" && $params['page']=="world") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Master&page=world">
                Master WORLD
            </a>
        </li>
    </ul>
</li>
<?php } ?>
