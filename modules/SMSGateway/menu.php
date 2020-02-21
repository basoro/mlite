<li class="<?php if ($params['module']=="SMSGateway") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">message</i>
        <span>SMS Gateway</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=SMSGateway&page=index">
                Dashboard
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="inbox") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=SMSGateway&page=inbox">
                SMS Inbox
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="outbox") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=SMSGateway&page=outbox">
                SMS Outbox
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="sendsms") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=SMSGateway&page=sendsms">
                SMS Instant
            </a>
        </li>
        <li class="<?php if ($params['module']=="SMSGateway" && $params['page']=="listphone") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=SMSGateway&page=listphone">
                Phonebook
            </a>
        </li>
    </ul>
</li>
