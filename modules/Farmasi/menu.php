<li class="<?php if ($params['module']=="Farmasi") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">shopping_cart</i>
        <span>Farmasi</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Farmasi&page=index">
                Dashboard
            </a>
        </li>
    </ul>
</li>
