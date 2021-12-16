<?php

namespace Plugins\Pasien_Galleries;

use Systems\AdminModule;

class Admin extends AdminModule
{
    private $_thumbs = ['md' => 600, 'sm' => 300, 'xs' => 150];
    private $_uploads = UPLOADS.'/pasien_galleries';

    public function navigation()
    {
        return [
            'Manage' => 'manage',
        ];
    }

    /**
    * galleries manage
    */
    public function getManage()
    {

        $this->_addHeaderFiles();

        $assign = [];
        /*
        // list
        $rows = $this->db('mlite_pasien_galleries')->toArray();
        if (count($rows)) {
            foreach ($rows as $row) {
                $row['tag']    = $this->tpl->noParse('{$gallery.'.$row['slug'].'}');
                $row['editURL'] = url([ADMIN, 'pasien_galleries',  'edit', $row['id']]);
                $row['delURL']  = url([ADMIN, 'pasien_galleries', 'delete', $row['id']]);

                $assign[] = $row;
            }
        }

        return $this->draw('manage.html', ['galleries' => $assign]);
        */

        $perpage = '10';

        $totalRecords = $this->db('mlite_pasien_galleries')
          ->select('slug')
          ->toArray();
        $jumlah_data    = count($totalRecords);
  			$offset         = 10;
  			$jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        $rows = $this->db('mlite_pasien_galleries')
          ->desc('slug')
          ->offset(0)
          ->limit($perpage)
          ->toArray();

        foreach ($rows as $row) {
          $row['tag']    = $this->tpl->noParse('{$gallery.'.$row['slug'].'}');
          $row['editURL'] = url([ADMIN, 'pasien_galleries',  'edit', $row['id']]);
          $row['delURL']  = url([ADMIN, 'pasien_galleries', 'delete', $row['id']]);

          $assign[] = $row;

        }

        return $this->draw('manage.html', [
          'galleries' => $assign,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

    }

    public function anyDisplay()
    {
        $this->_addHeaderFiles();

        $perpage = '10';

        $totalRecords = $this->db('mlite_pasien_galleries')->select('slug')->toArray();
        $jumlah_data    = count($totalRecords);
  			$offset         = 10;
  			$jml_halaman    = ceil($jumlah_data / $offset);
        $halaman    = 1;

        if(isset($_POST['cari'])) {
          $rows = $this->db('mlite_pasien_galleries')
            ->like('slug', '%'.$_POST['cari'].'%')
            ->orLike('name', '%'.$_POST['cari'].'%')
            ->desc('slug')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
          $jumlah_data = count($rows);
    			$jml_halaman = ceil($jumlah_data / $offset);
        }elseif(isset($_POST['halaman'])){
    			$offset = (($_POST['halaman'] - 1) * $perpage);
          $rows = $this->db('mlite_pasien_galleries')
            ->desc('slug')
            ->offset($offset)
            ->limit($perpage)
            ->toArray();
          $halaman = $_POST['halaman'];
        }else{
          $rows = $this->db('mlite_pasien_galleries')
            ->desc('slug')
            ->offset(0)
            ->limit($perpage)
            ->toArray();
        }

        $assign = [];
        foreach ($rows as $row) {
          $row['tag']    = $this->tpl->noParse('{$gallery.'.$row['slug'].'}');
          $row['editURL'] = url([ADMIN, 'pasien_galleries',  'edit', $row['id']]);
          $row['delURL']  = url([ADMIN, 'pasien_galleries', 'delete', $row['id']]);

          $assign[] = $row;
        }

        echo $this->draw('display.html', [
          'galleries' => $assign,
          'halaman' => $halaman,
          'jumlah_data' => $jumlah_data,
          'jml_halaman' => $jml_halaman
        ]);

        exit();
    }

    public function getAjax()
    {
      header('Content-type: text/html');
      $show = isset($_GET['show']) ? $_GET['show'] : "";
      switch($show){
       default:
        break;

        case "pasien":
        $phrase = '';
        if(isset($_GET['s']))
          $phrase = $_GET['s'];

        $rows = $this->db('pasien')->like('no_rkm_medis', '%'.$phrase.'%')->orLike('nm_pasien', '%'.$phrase.'%')->toArray();
        foreach ($rows as $row) {
          $array[] = array(
              'no_rkm_medis' => $row['no_rkm_medis'],
              'nm_pasien'  => $row['nm_pasien']
          );
        }
        echo json_encode($array, true);
        break;

      }
      exit();
    }

    /**
    * add new gallery
    */
    public function anyAdd()
    {
        $location = [ADMIN, 'pasien_galleries', 'manage'];

        if (!empty($_POST['name'])) {
            $name = trim($_POST['name']);
            $pasien = $this->db('pasien')->where('no_rkm_medis', $name)->oneArray();
            if (!$this->db('mlite_pasien_galleries')->where('slug', $name)->count()) {
                $query = $this->db('mlite_pasien_galleries')->save(['name' => $pasien['nm_pasien'], 'slug' => $name]);

                if ($query) {
                    $id     = $this->db()->lastInsertId();
                    $dir    = $this->_uploads.'/'.$id;

                    if (mkdir($dir, 0755, true)) {
                        $this->notify('success', 'Sukses');
                        $location = [ADMIN, 'pasien_galleries', 'edit', $this->db()->lastInsertId()];
                    }
                } else {
                    $this->notify('failure', 'Gagal');
                }
            } else {
                $this->notify('failure', 'Sudah ada');
                $location = [ADMIN, 'pasien_galleries', 'edit', $this->db('mlite_pasien_galleries')->where('slug', $name)->oneArray()['id']];
            }
        } else {
            $this->notify('failure', 'Masih ada yg kosong');
        }

        redirect(url($location));
    }

    /**
    * remove gallery
    */
    public function getDelete($id)
    {
        $query = $this->db('mlite_pasien_galleries')->delete($id);

        deleteDir($this->_uploads.'/'.$id);

        if ($query) {
            $this->notify('success', 'Sukses');
        } else {
            $this->notify('failure', 'Gagal');
        }

        redirect(url([ADMIN, 'pasien_galleries', 'manage']));
    }

    /**
    * edit gallery
    */
    public function getEdit($id, $page = 1)
    {
        $assign = [];
        $assign['settings'] = $this->db('mlite_pasien_galleries')->oneArray($id);

        // pagination
        $totalRecords = $this->db('mlite_pasien_galleries_items')->select('id')->where('gallery', $id)->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'pasien_galleries', 'edit', $id, '%d']));
        $assign['pagination'] = $pagination->nav();
        $assign['page'] = $page;

        // items
        if ($assign['settings']['sort'] == 'ASC') {
            $rows = $this->db('mlite_pasien_galleries_items')->where('gallery', $id)
                    ->limit($pagination->offset().', '.$pagination->getRecordsPerPage())
                    ->asc('id')->toArray();
        } else {
            $rows = $this->db('mlite_pasien_galleries_items')->where('gallery', $id)
                    ->limit($pagination->offset().', '.$pagination->getRecordsPerPage())
                    ->desc('id')->toArray();
        }

        if (count($rows)) {
            foreach ($rows as $row) {
                $row['src'] = unserialize($row['src']);

                if (!isset($row['src']['sm'])) {
                    $row['src']['sm'] = isset($row['src']['xs']) ? $row['src']['xs'] : $row['src']['lg'];
                }

                $assign['images'][] = $row;
            }
        }

        $assign['id'] = $id;

        $this->core->addCSS(url('assets/jscripts/lightbox/lightbox.min.css'));
        $this->core->addJS(url('assets/jscripts/lightbox/lightbox.min.js'));
        $this->core->addJS(url('assets/jscripts/are-you-sure.min.js'));

        return $this->draw('edit.html', ['gallery' => $assign]);
    }

    /**
    * save gallery data
    */
    public function postSaveSettings($id)
    {
        if (checkEmptyFields(['sort'], $_POST)) {
            $this->notify('failure', 'Ada yang masih kosong');
            redirect(url([ADMIN, 'pasien_galleries', 'edit', $id]));
        }

        if ($this->db('mlite_pasien_galleries')->where($id)->save(['sort' => $_POST['sort']])) {
            $this->notify('success', 'Pengaturan sukses');
        }

        redirect(url([ADMIN, 'pasien_galleries', 'edit', $id]));
    }

    public function postSaveImages($id, $page)
    {
        foreach ($_POST['img'] as $key => $val) {
            $query = $this->db('mlite_pasien_galleries_items')->where($key)->save(['title' => $val['title']]);
        }

        if ($query) {
            $this->notify('success', 'Simpan sukses');
        }

        redirect(url([ADMIN, 'pasien_galleries', 'edit', $id, $page]));
    }

    /**
    * image uploading
    */
    public function postUpload($id)
    {
        $dir    = $this->_uploads.'/'.$id;
        $cntr   = 0;

        if (!is_uploaded_file($_FILES['files']['tmp_name'][0])) {
            $this->notify('failure', 'Tidak ada berkas');
        } else {
            foreach ($_FILES['files']['tmp_name'] as $image) {
                $img = new \Systems\Lib\Image();

                if ($img->load($image)) {
                    $imgName = time().$cntr++;
                    $imgPath = $dir.'/'.$imgName.'.'.$img->getInfos('type');
                    $src     = [];

                    // oryginal size
                    $img->save($imgPath);
                    $src['lg'] = str_replace(BASE_DIR.'/', null, $imgPath);

                    // generate thumbs
                    foreach ($this->_thumbs as $key => $width) {
                        if ($img->getInfos('width') > $width) {
                            $img->resize($width);
                            $img->save($thumbPath = "{$dir}/{$imgName}-{$key}.{$img->getInfos('type')}");
                            $src[$key] = str_replace(BASE_DIR.'/', null, $thumbPath);
                        }
                    }

                    $query = $this->db('mlite_pasien_galleries_items')->save(['src' => serialize($src), 'gallery' => $id]);
                } else {
                    $this->notify('failure', 'Exstensi berkas salah', 'jpg, png, gif');
                }
            }

            if ($query) {
                $this->notify('success', 'Sukses menambahkan gambar');
            };
        }

        redirect(url([ADMIN, 'pasien_galleries', 'edit', $id]));
    }

    /**
    * remove image
    */
    public function getDeleteImage($id)
    {
        $image = $this->db('mlite_pasien_galleries_items')->where($id)->oneArray();
        if (!empty($image)) {
            if ($this->db('mlite_pasien_galleries_items')->delete($id)) {
                $images = unserialize($image['src']);
                foreach ($images as $src) {
                    if (file_exists(BASE_DIR.'/'.$src)) {
                        if (!unlink(BASE_DIR.'/'.$src)) {
                            $this->notify('failure', 'Gagal dihapus');
                        } else {
                            $this->notify('success', 'Sukses dihapus');
                        }
                    }
                }
            }
        } else {
            $this->notify('failure', 'Gambar tidak ada');
        }

        redirect(url([ADMIN, 'pasien_galleries', 'edit', $image['gallery']]));
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/pasien_galleries/js/admin/pasien_galleries.js');
        exit();
    }

    private function _addHeaderFiles()
    {

        // CSS
        $this->core->addCSS(url('assets/css/jquery-ui.css'));
        //$this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));

        // JS
        $this->core->addJS(url('assets/jscripts/jquery-ui.js'), 'footer');
        //$this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'), 'footer');
        //$this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'), 'footer');

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'pasien_galleries', 'javascript']), 'footer');
    }

}
