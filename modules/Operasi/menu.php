<li class="<?php if ($params['module']=="Operasi") {
    echo "active";
} ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">airline_seat_flat</i>
        <span>Operasi</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Operasi" && $params['page']=="index") {
    echo "active";
} ?>">
            <a href="<?php echo URL; ?>/?module=Operasi&page=index">
                Operasi
            </a>
        </li>
    </ul>
</li>
