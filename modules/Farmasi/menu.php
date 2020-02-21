<li class="<?php if ($params['module']=="Farmasi") {echo "active"; } ?>">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="material-icons">shopping_cart</i>
        <span>Farmasi</span>
    </a>
    <ul class="ml-menu">
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="index") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=index">
                Dashboard
            </a>
        </li>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="data_resep") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=data_resep">
                Data Resep Elektronik
            </a>
        </li>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="laporan_harian") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=laporan_harian">
                Laporan Obat Harian
            </a>
        </li>
        <?php if(FKTL == true) { ?>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="laporan_ralan_ranap") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=laporan_ralan_ranap">
                Laporan Ralan Ranap
            </a>
        </li>
        <?php } ?>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="permintaan_resep") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=permintaan_resep">
                Permintaan Resep Dokter
            </a>
        </li>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="rekam_obat") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=rekam_obat">
                Rekam Pemberian Obat
            </a>
        </li>
        <?php if(FKTL == true) { ?>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="monitoring_obat") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=monitoring_obat">
                Monitoring Obat
            </a>
        </li>
        <?php } ?>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="stok_opname") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=stok_opname">
                Stok Opname Gudang
            </a>
        </li>
        <?php if(FKTL == true) { ?>
        <li class="<?php if ($params['module']=="Farmasi" && $params['page']=="obat_expired") {echo "active"; } ?>">
            <a href="<?php echo URL; ?>/index.php?module=Farmasi&page=obat_expired">
                Obat Expired
            </a>
        </li>
        <?php } ?>
    </ul>
</li>
