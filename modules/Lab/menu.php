<li class="<?php if ($params['module']=="Lab") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">toc</i>
        <span>Lab</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Lab" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=Lab&page=index">
                Permintaan Lab
            </a>
        </li>
    </ul>
</li>
