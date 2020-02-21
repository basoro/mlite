<?php if(FKTL == true) { ?>
  <li class="<?php if ($params['module']=="BridgingBPJS") {echo "active"; } ?>">
      <a href="javascript:void(0);" class="menu-toggle">
          <i class="material-icons">autorenew</i>
          <span>BPJS</span>
      </a>
      <ul class="ml-menu">
          <li class="<?php if ($params['module']=="BridgingBPJS" && $params['page']=="index") {echo "active"; } ?>">
              <a href="<?php echo URL; ?>/index.php?module=BridgingBPJS&page=index">
                  Bridging
              </a>
          </li>
          <li class="<?php if ($params['module']=="BridgingBPJS" && $params['page']=="data_sep") {echo "active"; } ?>">
              <a href="<?php echo URL; ?>/index.php?module=BridgingBPJS&page=data_sep">
                  Data SEP
              </a>
          </li>
          <li class="<?php if ($params['module']=="BridgingBPJS" && $params['page']=="pasien_batal") {echo "active"; } ?>">
              <a href="<?php echo URL; ?>/index.php?module=BridgingBPJS&page=pasien_batal">
                  Pasien Batal
              </a>
          </li>
          <li class="<?php if ($params['module']=="BridgingBPJS" && $params['page']=="cek_kepesertaan") {echo "active"; } ?>">
              <a href="<?php echo URL; ?>/index.php?module=BridgingBPJS&page=cek_kepesertaan">
                  Cek Kepesertaan
              </a>
          </li>
      </ul>
  </li>
<?php } ?>
