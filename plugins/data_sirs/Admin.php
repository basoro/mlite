<?php
    namespace Plugins\Data_Sirs;

    use Systems\AdminModule;

    class Admin extends AdminModule
    {

        public function navigation()
        {
            return [
                'Manage' => 'manage',
            ];
        }

        public function getManage()
        {
            $this->assign['yesterday'] = date('Y-m-d',strtotime("-1 days"));
            $today = date('Y-m-d');
            $yesterday = $this->assign['yesterday'];
            $namaObat = array(
                'B000010146' => 'Remdesivir Inj 100 mg',
                'B000010182' => 'Favipiravir 200 mg',
                'OBT000000056' => 'Vit C (Asam askorbat) inj 1000 mg',
                'B000001456' => 'Vit C (Asam askorbat) tab 250 mg',
                'OBT000000167' => 'Vit C (Asam askorbat) tab 500 mg',
                'B000009484' => 'Zinc sirup 20 mg / 5 ml',
                'OBT0418' => 'Zinc tab dispersible 20 mg',
                'OBT000000069' => 'Oseltamivir tab 75 mg',
                'OBT0055' => 'Azitromisin tab 500mg',
                'B000010270' => 'Azitromisin 500 mg Inj',
                'OBT0250' => 'Levofloxacin infus 5 mg/mL',
                'OBT0473' => 'Levofloxacin tab 750 mg',
                'OBT0249' => 'Levofloxacin tab 500 mg',
                'OBT0133' => 'Deksametason Inj 5 mg/mL',
                'B000001325' => 'Deksametason tab 0.5mg',
                'OBT0047' => 'N- Asetil Sistein kap 200 mg',
                'OBT0547' => 'Heparin Na inj 5.000 IU/mL (i.v./s.k.)',
                'B000001324' => 'Enoksaparin sodium inj 10.000 IU/mL',
                'B000009543' => 'Fondaparinux inj 2,5 mg/0,5 mL',
                'B000010173' => 'Fondaparinux inj 2,5 mg/0,5 mL'
            );

            $query = "SELECT SUM(gudangbarang.stok) as sum , databarang.kode_brng as kode , databarang.nama_brng as nama FROM gudangbarang JOIN databarang ON gudangbarang.kode_brng = databarang.kode_brng WHERE gudangbarang.kode_brng IN ('B000010146','B000010182','OBT000000056','B000001456','OBT000000167','B000009484','OBT0418','OBT000000069','OBT0055','B000010270','OBT0250','OBT0473','OBT0249','OBT0133','B000001325','OBT0047','OBT0547','B000001324','B000009543','B000010173','B000010147','B000010151','OBT0323','OBT0671','OBT0268','B000001172','OBT0677') AND gudangbarang.kd_bangsal != 'B0017' GROUP BY gudangbarang.kode_brng";
            $stmt = $this->db()->pdo()->prepare($query);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            $this->assign['sedia'] = [];
            foreach($rows as $row){
                if($namaObat[$row['kode']]){
                    $row['nama'] = $namaObat[$row['kode']];
                }else{
                    $row['nama'] = $row['nama'];
                }
                $query1 = "SELECT SUM(detail_pemberian_obat.jml) FROM detail_pemberian_obat , kamar_inap , reg_periksa , diagnosa_pasien WHERE detail_pemberian_obat.no_rawat = kamar_inap.no_rawat AND kamar_inap.no_rawat = reg_periksa.no_rawat AND reg_periksa.no_rawat = diagnosa_pasien.no_rawat AND diagnosa_pasien.kd_penyakit IN ('B34.2','Z03.8') AND detail_pemberian_obat.kode_brng = '".$row['kode']."' AND detail_pemberian_obat.tgl_perawatan = '$yesterday' GROUP BY detail_pemberian_obat.kode_brng";
                $stmt1 = $this->db()->pdo()->prepare($query1);
                $stmt1->execute();
                $rows1 = $stmt1->fetchColumn();
                $row['jml'] = $rows1;
                if($row['jml'] == ''){
                    $row['jml'] = '0';
                }else{
                    $row['jml'] = $rows1;
                }
                $this->assign['sedia'][] = $row;
            }

            return $this->draw('manage.html',['sirs' => $this->assign]);
        }

    }