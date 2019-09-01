
    <section>
        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <!-- User Info -->
            <div class="user-info">
                <?php $dataGet = fetch_array(query("(SELECT nm_dokter AS nama, jk FROM dokter WHERE kd_dokter = '{$_SESSION['username']}') UNION (SELECT nama AS nama, jk FROM pegawai WHERE nik = '{$_SESSION['username']}') UNION (SELECT nama AS nama, jk FROM petugas WHERE nip = '{$_SESSION['username']}')")); ?>
                <?php $dataGetBangsal = fetch_array(query("(SELECT nm_bangsal FROM bangsal WHERE kd_bangsal = '{$_SESSION['jenis_poli']}')")); ?>
                <div class="image">
                <?php
                if ($dataGet['1'] == 'L' || $dataGet['1'] == 'Pria') {
                    echo '<img src="'.URL.'/assets/images/pria.png" width="48" height="48" alt="User" />';
                } else if ($dataGet['1'] == 'P' || $dataGet['1'] == 'Wanita') {
                    echo '<img src="'.URL.'/assets/images/wanita.png" width="48" height="48" alt="User" />';
                }
                ?>
                </div>
                <div class="info-container">
                    <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $dataGet['nama']; ?></div>
                    <div class="email"><?php echo $_SESSION['username']; ?> | <?php echo $_SESSION['jenis_poli']; ?></div>
                    <div class="btn-group user-helper-dropdown">
                        <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                        <ul class="dropdown-menu pull-right">
                            <li><a href="<?php echo URL; ?>/profil.php"><i class="material-icons">person</i>Profile</a></li>
                            <li role="seperator" class="divider"></li>
                            <li><a href="<?php echo URL; ?>/login.php?action=logout"><i class="material-icons">input</i>Sign Out</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- #User Info -->
            <!-- Menu -->
            <div class="menu">
                <ul class="list">
                    <li class="header">MAIN NAVIGATION</li>
                    <li class="active">
                        <a href="<?php echo URL; ?>/index.php">
                            <i class="material-icons">home</i>
                            <span>Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">text_fields</i>
                            <span>Pasien</span>
                        </a>
                        <ul class="ml-menu">
		                    <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'RekamMedik')  { ?>
                            <li>
                                <a href="<?php echo URL; ?>/booking.php">Booking Pendaftaran</a>
                            </li>
							<?php } ?>
                          	<?php if($role !== 'Paramedis_Ranap')  { ?>
                          	<li>
                                <a href="<?php echo URL; ?>/pasien-igd.php">Pasien IGD</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/pasien-ralan.php">Pasien Rawat Jalan</a>
                            </li>
                          	<?php } ?>
                          	<?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'Medis' || $role == 'RekamMedik' || $role == 'Paramedis_Ranap')  { ?>
                            <li>
                                <a href="<?php echo URL; ?>/pasien-ranap.php">Pasien <?php if($role == 'Paramedis_Ranap') { echo $dataGetBangsal['nm_bangsal']; } else { echo 'Rawat Inap'; } ?></a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/asuhan-keperawatan.php">Asuhan Keperawatan</a>
                            </li>
                          	<?php } ?>
                            <li>
                                <a href="<?php echo URL; ?>/rekam-medik.php">Rekam Medik Pasien</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/pasien.php">Data Pasien</a>
                            </li>
                        </ul>
                    </li>
                    <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'Kasir')  { ?>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">attach_money</i>
                            <span>Pembayaran</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo URL; ?>/kasir-ranap.php">Kasir Ranap</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/kasir-ralan.php">Kasir Ralan</a>
                            </li>
                        </ul>
                    </li>
                    <?php } ?>
                    <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'Apotek')  { ?>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">widgets</i>
                            <span>Apotek</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo URL; ?>/data-resep.php">Data Resep</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/laporan-obat-harian.php">Laporan Harian</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/laporan-obat-ralan-ranap.php">Laporan Ralan - Ranap</a>
                            </li>
                        </ul>
                    </li>
                    <?php } ?>
                    <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'RekamMedik' || $_SESSION['jenis_poli'] == 'U0019')  { ?>
                      <li>
                          <a href="javascript:void(0);" class="menu-toggle">
                              <i class="material-icons">import_export</i>
                              <span>Bridging</span>
                          </a>
                          <ul class="ml-menu">
                              <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'RekamMedik') { ?>
                              <li>
                                  <a href="<?php echo URL; ?>/bridging/br-igd.php">IGD</a>
                              </li>
                              <?php } ?>
                              <?php if($_SESSION['jenis_poli'] == 'U0019' || $role == 'Admin' || $role == 'Manajemen' || $role == 'RekamMedik') { ?>
                              <li>
                                  <a href="<?php echo URL; ?>/bridging/br-ralan.php">Rawat Jalan</a>
                              </li>
                              <?php } ?>
                              <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'RekamMedik') { ?>
                              <li>
                                  <a href="<?php echo URL; ?>/bridging/br-ranap.php">Rawat Inap</a>
                              </li>
                              <li>
                                  <a href="<?php echo URL; ?>/bridging/br-cekpeserta.php">Cek Kepesertaan</a>
                              </li>
                              <li>
                                  <a href="<?php echo URL; ?>/bridging/pasien-batal-brid.php">Pasien Batal</a>
                              </li>
                              <?php } ?>
                          </ul>
                      </li>
                      <?php } ?>
                    <li class="header">MANAJEMEN</li>
                    <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'Medis' || $role == 'Apotek')  { ?>
                    <li>
                        <a href="<?php echo URL; ?>/surat.php">
                            <i class="material-icons">storage</i>
                            <span>Surat Menyurat</span>
                        </a>
                    </li>
                  	<?php if($role == 'Admin' || $role == 'Manajemen')  { ?>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">grade</i>
                            <span>Utilitas Instalasi</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo URL; ?>/includes/setkmr.php">Set Kamar</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/kesling.php">Surveilance</a>
                            </li>
                          	<li>
                                <a href="<?php echo URL; ?>/gizi.php">Data Diet Pasien</a>
                            </li>
                        </ul>
                    </li>
                  	<?php } ?>
                  	<li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">account_balance</i>
                            <span>Gudang Farmasi</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo URL; ?>/data-obat.php">Data Obat</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/stok-opname-gudang.php">Stok Opname Gudang</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/obat-expired.php">Obat Expired</a>
                            </li>
                          	<li>
                                <a href="<?php echo URL; ?>/rekam-obat.php">Rekam Pemberian Obat</a>
                            </li>
                        </ul>
                    </li>
                    <?php } ?>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">folder_open</i>
                            <span>Data Master</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="<?php echo URL; ?>/icd-10.php">ICD-10</a>
                            </li>
                            <li>
                                <a href="<?php echo URL; ?>/icd-9.php">ICD-9</a>
                            </li>
	                        </ul>
                    </li>
                    <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'RekamMedik')  { ?>
                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">update</i>
                            <span>Laporan-Laporan</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="javascript:void(0);" class="menu-toggle">
                                    <span>Data Dasar (RL 1)</span>
                                </a>
                                <ul class="ml-menu">
                                    <!--<li>
                                        <a href="<?php echo URL; ?>/laporan/rl-1-1.php">Laporan RL 1.1</a>
                                    </li>-->
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-1-2.php">Laporan RL 1.2</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-1-3.php">Laporan RL 1.3</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="menu-toggle">
                                    <span>Ketenagaan (RL 2)</span>
                                </a>
                                <ul class="ml-menu">
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-2.php">Laporan RL 2</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="menu-toggle">
                                    <span>Pelayanan (RL 3)</span>
                                </a>
                                <ul class="ml-menu">
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-1.php">Laporan RL 3.1</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-2.php">Laporan RL 3.2</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-3.php">Laporan RL 3.3</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-4.php">Laporan RL 3.4</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-5.php">Laporan RL 3.5</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-6.php">Laporan RL 3.6</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-7.php">Laporan RL 3.7</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-8.php">Laporan RL 3.8</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-9.php">Laporan RL 3.9</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-10.php">Laporan RL 3.10</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-11.php">Laporan RL 3.11</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-12.php">Laporan RL 3.12</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-13.php">Laporan RL 3.13</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-14.php">Laporan RL 3.14</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-3-15.php">Laporan RL 3.15</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="menu-toggle">
                                    <span>Morbiditas/Mortalitas (RL 4)</span>
                                </a>
                                <ul class="ml-menu">
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-4-a.php">Laporan RL 4.a</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-4-b.php">Laporan RL 4.b</a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="menu-toggle">
                                    <span>Pengunjung RS (RL 5)</span>
                                </a>
                                <ul class="ml-menu">
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-5-1.php">Laporan RL 5.1</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-5-2.php">Laporan RL 5.2</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-5-3.php">Laporan RL 5.3</a>
                                    </li>

                                    <li>
                                        <a href="<?php echo URL; ?>/laporan/rl-5-4.php">Laporan RL 5.4</a>
                                    </li>
                                </ul>
                            </li>
                          </ul>
                      </li>
                      <?php } ?>
                      <?php if($role == 'Admin' || $role == 'Manajemen')  { ?>
                    	<li>
                          <a href="javascript:void(0);" class="menu-toggle">
                              <i class="material-icons">mail</i>
                              <span>SMS Gateway</span>
                          </a>
                          <ul class="ml-menu">
                              <li>
                                  <a href="<?php echo URL; ?>/sms/sms-masuk.php">SMS Masuk</a>
                              </li>
                              <li>
                                  <a href="<?php echo URL; ?>/sms/sms-keluar.php">SMS Keluar</a>
                              </li>
                              <li>
                                  <a href="<?php echo URL; ?>/sms/sms-kirim.php">Kirim SMS</a>
                              </li>
                              <li>
                                  <a href="<?php echo URL; ?>/sms/sms-jadwal.php">Penjadwalan SMS</a>
                              </li>
                              <li>
                                  <a href="<?php echo URL; ?>/sms/sms-auto.php">Autorespon SMS</a>
                              </li>
                          </ul>
                      </li>
                    	<?php } ?>
                      <?php if($role == 'Admin')  { ?>
                      <li>
                          <a href="javascript:void(0);" class="menu-toggle">
                              <i class="material-icons">people</i>
                              <span>Pengguna</span>
                          </a>
                          <ul class="ml-menu">
                            <?php if($role == 'Admin' || $role == 'Manajemen' || $role == 'Kasir' || $role == 'Medis' || $role == 'Apotek' || $role == 'RekamMedik' || $role == 'Paramedis_Ranap'){?>
                              <li>
                                  <a href="<?php echo URL; ?>/profil.php">Profil</a>
                              </li>
                            <?php } ?>
                              <li>
                                  <a href="users.php">Data Pengguna</a>
                              </li>
                          </ul>
                      </li>
                      <?php } ?>
                </ul>
            </div>
            <!-- #Menu -->
            <!-- Footer -->
            <div class="legal">
                <div class="copyright">
                &copy; 2017 - <?php echo date('Y'); ?> <a href="#" data-toggle="modal" data-target="#ICTRSHD">Instalasi ICT RSHD</a>. v <?php echo VERSION; ?>
                </div>
            </div>
            <!-- #Footer -->
        </aside>
        <!-- #END# Left Sidebar -->
    </section>
