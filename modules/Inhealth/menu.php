<li class="<?php if ($params['module']=="Inhealth") {
    echo "active";
} ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">accessible_forward</i>
        <span>Inhealth</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Inhealth" && $params['page']=="index") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Inhealth&page=index">
                Inhealth Ranap
            </a>
        </li>
        <li class="<?php if ($params['module']=="Inhealth" && $params['page']=="rajal") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Inhealth&page=rajal">
                Inhealth Rajal
            </a>
        </li>
    </ul>
</li>
