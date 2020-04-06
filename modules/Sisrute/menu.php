<li class="<?php if ($params['module']=="Sisrute") {
    echo "active";
} ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">accessible_forward</i>
        <span>Sisrute</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Sisrute" && $params['page']=="index") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Sisrute&page=index">
                Sisrute Ranap
            </a>
        </li>
        <li class="<?php if ($params['module']=="Sisrute" && $params['page']=="rajal") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Sisrute&page=rajal">
                Sisrute Rajal
            </a>
        </li>
    </ul>
</li>
