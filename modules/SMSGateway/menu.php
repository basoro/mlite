<li class="<?php if ($params['module']=="SMSGateway") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">message</i>
        <span>SMS Gateway</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=SMSGateway&page=index">
                SMS Dashboard
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="masuk") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=SMSGateway&page=masuk">
                SMS Masuk
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="keluar") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=SMSGateway&page=keluar">
                SMS Keluar
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="kirim") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=SMSGateway&page=kirim">
                Kirim SMS
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="jadwal") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=SMSGateway&page=jadwal">
                SMS Terjadwal
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="auto") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=SMSGateway&page=auto">
                SMS Auto Responder
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="buku_telepon") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/?module=SMSGateway&page=buku_telepon">
                Buku Telepon
            </a>
        </li>
    </ul>
</li>
