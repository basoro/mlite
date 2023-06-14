<?php

namespace Plugins\Website;

use Systems\AdminModule;

class Admin extends AdminModule
{
    private $_uploads = UPLOADS.'/website';
    private $assign = [];

    public function navigation()
    {
        return [
            'Index'    => 'manage',
            'Kelola Berita'    => 'managenews',
            'Tambah Berita'              => 'addnews',
            'Kelola Halaman'    => 'managepages',
            'Tambah Halaman'              => 'addpage',
            'Pengaturan Berita'                => 'settingsnews',
            'Pengaturan Website' => 'settingswebsite'
        ];
    }

    public function getManage()
    {
      $sub_modules = [
        ['name' => 'Kelola Berita', 'url' => url([ADMIN, 'website', 'managenews']), 'icon' => 'pencil-square', 'desc' => 'Kelola postingan website'],
        ['name' => 'Tambah Berita', 'url' => url([ADMIN, 'website', 'addnews']), 'icon' => 'pencil-square', 'desc' => 'Tambah postingan baru'],
        ['name' => 'Kelola Halaman', 'url' => url([ADMIN, 'website', 'managepages']), 'icon' => 'pencil-square', 'desc' => 'Kelola halaman website'],
        ['name' => 'Tambah Halaman', 'url' => url([ADMIN, 'website', 'addpage']), 'icon' => 'pencil-square', 'desc' => 'Tambah halaman baru'],
        ['name' => 'Pengaturan Berita', 'url' => url([ADMIN, 'website', 'settingsnews']), 'icon' => 'pencil-square', 'desc' => 'Pengaturan berita'],
        ['name' => 'Pengaturan Website', 'url' => url([ADMIN, 'website', 'settingswebsite']), 'icon' => 'pencil-square', 'desc' => 'Pengaturan website'],
      ];
      return $this->draw('manage.html', ['sub_modules' => $sub_modules]);
    }

    public function anyManageNews($page = 1)
    {
        if (isset($_POST['delete'])) {
            if (isset($_POST['post-list']) && !empty($_POST['post-list'])) {
                foreach ($_POST['post-list'] as $item) {
                    $row = $this->db('mlite_news')->where('id', $item)->oneArray();
                    if ($this->db('mlite_news')->delete($item) === 1) {
                        if (!empty($row['cover_photo']) && file_exists(UPLOADS."/website/news/".$row['cover_photo'])) {
                            unlink(UPLOADS."/website/news/".$row['cover_photo']);
                        }

                        $this->notify('success', 'Artikel berhasil dihapus.');
                    } else {
                        $this->notify('failure', 'Gagal menghapus artikel.');
                    }
                }

                redirect(url([ADMIN, 'website', 'managenews']));
            }
        }

        // pagination
        $totalRecords = count($this->db('mlite_news')->toArray());
        $pagination = new \Systems\Lib\Pagination($page, $totalRecords, 10, url([ADMIN, 'website', 'managenews', '%d']));
        $this->assign['pagination'] = $pagination->nav();

        // list
        $this->assign['newURL'] = url([ADMIN, 'website', 'addnews']);
        $this->assign['postCount'] = 0;
        $rows = $this->db('mlite_news')
                ->limit($pagination->offset().', '.$pagination->getRecordsPerPage())
                ->desc('published_at')->desc('created_at')
                ->toArray();

        $this->assign['posts'] = [];
        if ($totalRecords) {
            $this->assign['postCount'] = $totalRecords;
            foreach ($rows as $row) {
                $row['editURL'] = url([ADMIN, 'website', 'editnews', $row['id']]);
                $row['delURL']  = url([ADMIN, 'website', 'deletenews', $row['id']]);
                $row['viewURL'] = url(['news', 'post', $row['slug']]);


                $fullname = $this->core->getUserInfo('fullname', $row['user_id'], true);
                $username = $this->core->getUserInfo('username', $row['user_id'], true);
                $row['user'] = !empty($fullname) ? $fullname.' ('.$username.')' : $username;

                $row['comments'] = $row['comments'] ? 'Aktif' : 'Tidak Aktif';

                switch ($row['status']) {
                    case 0:
                        $row['type'] = 'Draft';
                        break;
                    case 1:
                        $row['type'] = 'Sembunyi';
                        break;
                    case 2:
                        $row['type'] = 'Terbit';
                        break;
                    default:
                        case 0:
                        $row['type'] = "Unknown";
                }

                $row['created_at'] = date("d-m-Y", $row['created_at']);
                $row['published_at'] = date("d-m-Y", $row['published_at']);

                $row = htmlspecialchars_array($row);
                $this->assign['posts'][] = $row;
            }
        }

        return $this->draw('manage.news.html', ['news' => $this->assign]);
    }

    public function getAddNews()
    {
        return $this->getEditNews(null);
    }

    public function getEditNews($id = null)
    {
        $this->assign['manageURL'] = url([ADMIN, 'website', 'managenews']);
        $this->assign['coverDeleteURL'] = url([ADMIN, 'website', 'deleteCoverNews', $id]);
        $this->assign['editor'] = $this->settings('settings.editor');
        $this->_addHeaderFiles();

        if ($id === null) {
            $news = [
                'id' => null,
                'title' => '',
                'content' => '',
                'slug' => '',
                'intro' => '',
                'user_id' => $this->core->getUserInfo('id'),
                'comments' => 1,
                'cover_photo' => null,
                'status' => 0,
                'markdown' => 0,
                'tags' => '',
                'published_at' => time(),
            ];
        } else {
            $news = $this->db('mlite_news')->where('id', $id)->oneArray();
        }

        if (!empty($news)) {
            $this->assign['form'] = htmlspecialchars_array($news);
            $this->assign['form']['content'] =  $this->tpl->noParse($this->assign['form']['content']);
            $this->assign['form']['date'] = date("Y-m-d\TH:i", $news['published_at']);

            $tags_array = $this->db('mlite_news_tags')->leftJoin('mlite_news_tags_relationship', 'mlite_news_tags.id = mlite_news_tags_relationship.tag_id')->where('mlite_news_tags_relationship.news_id', $news['id'])->select(['mlite_news_tags.name'])->toArray();

            $this->assign['form']['tags'] = $tags_array;
            $this->assign['users'] = $this->db('mlite_users')->toArray();
            $this->assign['author'] = $this->core->getUserInfo('id', $news['user_id'], true);

            $this->assign['title'] = ($news['id'] === null) ? 'Tambah baru' : 'Sunting Artikel';

            return $this->draw('form.html', ['news' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'website', 'managenews']));
        }
    }

    public function postSaveNews($id = null)
    {
        unset($_POST['save'], $_POST['files']);

        if (!empty($_POST['tags'])) {
            $tags = array_unique($_POST['tags']);
        } else {
            $tags = [];
        }

        unset($_POST['tags']);

        // redirect location
        if (!$id) {
            $location = url([ADMIN, 'website', 'addnews']);
        } else {
            $location = url([ADMIN, 'website', 'editnews', $id]);
        }

        if (checkEmptyFields(['title', 'content'], $_POST)) {
            $this->notify('failure', 'Isian kosong.');
            $this->assign['form'] = htmlspecialchars_array($_POST);
            $this->assign['form']['content'] = $this->tpl->noParse($this->assign['form']['content']);
            redirect($location);
        }

        // slug
        if (empty($_POST['slug'])) {
            $_POST['slug'] = createSlug($_POST['title']);
        } else {
            $_POST['slug'] = createSlug($_POST['slug']);
        }

        // check slug and append with iterator
        $oldSlug = $_POST['slug'];
        $i = 2;

        if ($id === null) {
            $id = 0;
        }

        while ($this->db('mlite_news')->where('slug', $_POST['slug'])->where('id', '!=', $id)->oneArray()) {
            $_POST['slug'] = $oldSlug.'-'.($i++);
        }

        // format conversion date
        $_POST['updated_at'] = strtotime(date('Y-m-d H:i:s'));
        $_POST['published_at'] = strtotime($_POST['published_at']);
        if (!isset($_POST['comments'])) {
            $_POST['comments'] = 0;
        }
        if (!isset($_POST['markdown'])) {
            $_POST['markdown'] = 0;
        }

        if (isset($_FILES['cover_photo']['tmp_name'])) {
            $img = new \Systems\Lib\Image;

            if ($img->load($_FILES['cover_photo']['tmp_name'])) {
                if ($img->getInfos('width') > 1000) {
                    $img->resize(1000);
                } elseif ($img->getInfos('width') < 600) {
                    $img->resize(600);
                }

                $_POST['cover_photo'] = $_POST['slug'].".".$img->getInfos('type');
            }
        }

        if (!$id) { // new
            $_POST['created_at'] = strtotime(date('Y-m-d H:i:s'));

            $query = $this->db('mlite_news')->save($_POST);
            $location = url([ADMIN, 'website', 'editnews', $this->db()->pdo()->lastInsertId()]);
        } else {    // edit
            $query = $this->db('mlite_news')->where('id', $id)->save($_POST);
        }

        // detach tags from post
        if ($id) {
            $this->db('mlite_news_tags_relationship')->delete('news_id', $id);
            $newsId = $id;
        } else {
            $newsId = $id ? $id : $this->db()->pdo()->lastInsertId();
        }


        // Attach or create new tag
        foreach ($tags as $tag) {
            if (preg_match("/[`~!@#$%^&*()_|+\-=?;:\'\",.<>\{\}\[\]\\\/]+/", $tag)) {
                continue;
            }

            $slug = createSlug($tag);
            if ($e = $this->db('mlite_news_tags')->like('slug', $slug)->oneArray()) {
                $this->db('mlite_news_tags_relationship')->save(['news_id' => $newsId, 'tag_id' => $e['id']]);
            } else {
                $tagId = $this->db('mlite_news_tags')->save(['name' => $tag, 'slug' => $slug]);
                $this->db('mlite_news_tags_relationship')->save(['news_id' => $newsId, 'tag_id' => $tagId]);
            }
        }

        if ($query) {
            if (!file_exists(UPLOADS."/website")) {
                mkdir(UPLOADS."/website", 0777, true);
            }

            if (!file_exists(UPLOADS."/website/news")) {
                mkdir(UPLOADS."/website/news", 0777, true);
            }

            if ($p = $img->getInfos('width')) {
                $img->save(UPLOADS."/website/news/".$_POST['cover_photo']);
            }

            $this->notify('success', 'Artikel berhasil disimpan.');
        } else {
            $this->notify('failure', 'Gagal menyimpan artikel.');
        }

        redirect($location);
    }

    public function getDeleteNews($id)
    {
        if ($post = $this->db('mlite_news')->where('id', $id)->oneArray() && $this->db('mlite_news')->delete($id)) {
            if ($post['cover_photo']) {
                unlink(UPLOADS."/website/news/".$post['cover_photo']);
            }
            $this->notify('success', 'Artikel berhasil dihapus.');
        } else {
            $this->notify('failure', 'Gagal menghapus artikel.');
        }

        redirect(url([ADMIN, 'website', 'managenews']));
    }

    public function getDeleteCoverNews($id)
    {
        if ($post = $this->db('mlite_news')->where('id', $id)->oneArray()) {
            unlink(UPLOADS."/website/news/".$post['cover_photo']);
            $this->db('mlite_news')->where('id', $id)->save(['cover_photo' => null]);
            $this->notify('success', 'Foto cover sudah dihapus.');

            redirect(url([ADMIN, 'website', 'editnews', $id]));
        }
    }


    /**
    * list of pages
    */
    public function getManagePages($page = 1)
    {
        // pagination
        $totalRecords = $this->db('mlite_pages')->toArray();
        $pagination = new \Systems\Lib\Pagination($page, count($totalRecords), 10, url([ADMIN, 'website', 'managepages', '%d']));
        $this->assign['pagination'] = $pagination->nav();
        // list
        $rows = $this->db('mlite_pages')
                ->limit($pagination->offset().', '.$pagination->getRecordsPerPage())
                ->toArray();

        $this->assign['list'] = [];
        if (count($rows)) {
            foreach ($rows as $row) {
                $row = htmlspecialchars_array($row);
                $row['editURL'] = url([ADMIN, 'website', 'editpage', $row['id']]);
                $row['delURL']  = url([ADMIN, 'website', 'deletepage', $row['id']]);
                $row['viewURL'] = url(['page',$row['slug']]);
                $row['desc'] = str_limit($row['desc'], 48);

                $this->assign['list'][] = $row;
            }
        }

        return $this->draw('manage.pages.html', ['pages' => $this->assign]);
    }

    /**
    * add new page
    */
    public function getAddPage()
    {
        $this->assign['editor'] = $this->settings('settings', 'editor');
        $this->_addHeaderFiles();

        // Unsaved data with failure
        if (!empty($e = getRedirectData())) {
            $this->assign['form'] = ['title' => isset_or($e['title'], ''), 'desc' => isset_or($e['desc'], ''), 'content' => isset_or($e['content'], ''), 'slug' => isset_or($e['slug'], '')];
        } else {
            $this->assign['form'] = ['title' => '', 'desc' => '', 'content' => '', 'slug' => '', 'markdown' => 0];
        }

        $this->assign['title'] = 'Halaman baru';
        $this->assign['templates'] = $this->_getTemplates(isset_or($e['template'], 'index.html'));
        $this->assign['manageURL'] = url([ADMIN, 'website', 'managepages']);

        return $this->draw('form.page.html', ['pages' => $this->assign]);
    }

    public function getEditPage($id)
    {
        $this->assign['editor'] = $this->settings('settings', 'editor');
        $this->_addHeaderFiles();

        $page = $this->db('mlite_pages')->where('id', $id)->oneArray();

        if (!empty($page)) {
            // Unsaved data with failure
            if (!empty($e = getRedirectData())) {
                $page = array_merge($page, ['title' => isset_or($e['title'], ''), 'desc' => isset_or($e['desc'], ''), 'content' => isset_or($e['content'], ''), 'slug' => isset_or($e['slug'], '')]);
            }

            $this->assign['form'] = htmlspecialchars_array($page);
            $this->assign['form']['content'] =  $this->tpl->noParse($this->assign['form']['content']);

            $this->assign['title'] = 'Edit halaman';
            $this->assign['templates'] = $this->_getTemplates($page['template']);
            $this->assign['manageURL'] = url([ADMIN, 'website', 'managepages']);

            return $this->draw('form.page.html', ['pages' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'website', 'managepages']));
        }
    }

    public function postSavePage($id = null)
    {
        unset($_POST['save'], $_POST['files']);

        if (!$id) {
            $location = url([ADMIN, 'website', 'addpage']);
        } else {
            $location = url([ADMIN, 'website', 'editpage', $id]);
        }

        if (checkEmptyFields(['title', 'template'], $_POST)) {
            $this->notify('failure', 'Masih ada isian yang kosong');
            redirect($location, $_POST);
        }

        $_POST['title'] = trim($_POST['title']);
        if (!isset($_POST['markdown'])) {
            $_POST['markdown'] = 0;
        }

        if (empty($_POST['slug'])) {
            $_POST['slug'] = createSlug($_POST['title']);
        } else {
            $_POST['slug'] = createSlug($_POST['slug']);
        }

        if ($id != null && $this->db('mlite_pages')->where('slug', $_POST['slug'])->where('id', '!=', $id)->oneArray()) {
            $this->notify('failure', 'Halaman sudah ada');
            redirect(url([ADMIN, 'website', 'editpage', $id]), $_POST);
        } elseif ($id == null && $this->db('mlite_pages')->where('slug', $_POST['slug'])->oneArray()) {
            $this->notify('failure', 'Halaman sudah ada');
            redirect(url([ADMIN, 'website', 'addpage']), $_POST);
        }

        if (!$id) {
            $_POST['date'] = date('Y-m-d H:i:s');
            $query = $this->db('mlite_pages')->save($_POST);
            $location = url([ADMIN, 'website', 'editpage', $this->db()->pdo()->lastInsertId()]);
        } else {
            $query = $this->db('mlite_pages')->where('id', $id)->save($_POST);
        }

        if ($query) {
            $this->notify('success', 'Halaman tersimpan');
        } else {
            $this->notify('failure', 'Gagal menyimpan halaman');
        }

        redirect($location);
    }

    public function getDeletePage($id)
    {
        if ($this->db('mlite_pages')->delete($id)) {
            $this->notify('success', 'Halaman telah dihapus');
        } else {
            $this->notify('failure', 'Gagal menghapus halaman');
        }

        redirect(url([ADMIN, 'website', 'managepages']));
    }

    private function _getTemplates($selected = null)
    {
        $theme = $this->settings('settings', 'theme');
        $tpls = glob(THEMES.'/'.$theme.'/*.html');

        $result = [];
        foreach ($tpls as $tpl) {
            if ($selected == basename($tpl)) {
                $attr = 'selected';
            } else {
                $attr = null;
            }
            $result[] = ['name' => basename($tpl), 'attr' => $attr];
        }
        return $result;
    }

    public function getSettingsNews()
    {
        $assign = htmlspecialchars_array($this->settings('website'));
        $assign['dateformats'] = [
            [
                'value' => 'd-m-Y',
                'name'  => '01-01-2016'
            ],
            [
                'value' => 'd/m/Y',
                'name'  => '01/01/2016'
            ],
            [
                'value' => 'd M Y',
                'name'  => '01 Januari 2016'
            ],
            [
                'value' => 'M d, Y',
                'name'  => 'Januari 01, 2016'
            ],
            [
                'value' => 'd-m-Y H:i',
                'name'  => '01-01-2016 12:00'
            ],
            [
                'value' => 'd/m/Y H:i',
                'name'  => '01/01/2016 12:00'
            ],
            [
                'value' => 'd M Y, H:i',
                'name'  => '01 Januari 2016, 12:00'
            ],
        ];
        return $this->draw('settings.news.html', ['settings' => $assign]);
    }

    public function postSaveSettingsNews()
    {
        foreach ($_POST['website'] as $key => $val) {
            $this->settings('website', $key, $val);
        }
        $this->notify('success', 'Pengaturan sudah disimpan.');
        redirect(url([ADMIN, 'website', 'settingsnews']));
    }

    public function getSettingsWebsite()
    {
        $this->assign['title'] = 'Pengaturan Website';
        $this->assign['website'] = htmlspecialchars_array($this->settings('website'));
        return $this->draw('settings.website.html', ['settings' => $this->assign]);
    }

    public function postSaveSettingsWebsite()
    {
        $dir    = $this->_uploads;
        $img = new \Systems\Lib\Image;
        if ($img->load(isset_or($_FILES['logo']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/logo_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_logo'] = 'website/logo_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['logo_icon']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/logo_icon_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_logo_icon'] = 'website/logo_icon_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['slider_bg']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/slider_bg_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_slider_bg'] = 'website/slider_bg_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_12']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_12_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_about_12'] = 'website/about_12_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_22']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_22_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_about_22'] = 'website/about_22_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_32']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_32_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_about_32'] = 'website/about_32_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_42']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_42_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_about_42'] = 'website/about_42_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['about_bg']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/about_bg_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_about_bg'] = 'website/about_bg_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['homepage_services_13']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/homepage_services_13'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_services_13'] = 'website/homepage_services_13'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['homepage_services_23']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/homepage_services_23_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_services_23'] = 'website/homepage_services_23_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['homepage_services_33']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/homepage_services_33_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_services_33'] = 'website/homepage_services_33_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['homepage_services_43']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/homepage_services_43_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_services_43'] = 'website/homepage_services_43_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['homepage_services_53']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/homepage_services_53_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_services_53'] = 'website/homepage_services_53_'.$imgName.'.'.$img->getInfos('type');
            }
        }
        if ($img->load(isset_or($_FILES['homepage_services_63']['tmp_name'], false))) {
            if (isset($img)) {
                $imgName = time().$cntr++;
                $imgPath = $dir.'/homepage_services_63_'.$imgName.'.'.$img->getInfos('type');
                $img->save($imgPath);
                $_POST['website']['homepage_services_63'] = 'website/homepage_services_63_'.$imgName.'.'.$img->getInfos('type');
            }
        }

        foreach ($_POST['website'] as $key => $val) {
            $this->settings('website', $key, $val);
        }
        $this->notify('success', 'Pengaturan telah disimpan');
        redirect(url([ADMIN, 'website', 'settingswebsite']));
    }

    public function postEditorUpload()
    {
        header('Content-type: application/json');
        $dir    = UPLOADS.'/website/news';
        $error    = null;

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if (isset($_FILES['file']['tmp_name'])) {
            $img = new \Systems\Lib\Image;

            if ($img->load($_FILES['file']['tmp_name'])) {
                $imgPath = $dir.'/'.time().'.'.$img->getInfos('type');
                $img->save($imgPath);
                echo json_encode(['status' => 'success', 'result' => url($imgPath)]);
            } else {
                $error = 'Tidak dapat memuat gambar. Mungkin format tidak didukung.';
            }

            if ($error) {
                echo json_encode(['status' => 'failure', 'result' => $error]);
            }
        }
        exit();
    }

    public function getJavascript()
    {
        header('Content-type: text/javascript');
        echo $this->draw(MODULES.'/website/js/admin/website.js');
        exit();
    }

    public function getJsonTags($query = null)
    {
        header('Content-type: application/json');

        if (!$query) {
            exit(json_encode([]));
        }

        $query = urldecode($query);
        $tags = $this->db('mlite_news_tags')->like('name', $query.'%')->toArray();

        if (array_search($query, array_column($tags, 'name')) === false) {
            $tags[] = ['id' => 0, 'slug' => createSlug($query), 'name' => $query];
        }

        exit(json_encode($tags));
    }

    private function _addHeaderFiles()
    {
        // WYSIWYG
        $this->core->addCSS(url('assets/jscripts/wysiwyg/summernote.min.css'));
        $this->core->addJS(url('assets/jscripts/wysiwyg/summernote.min.js'));
        $this->core->addJS(url('assets/jscripts/wysiwyg/lang/id_indonesian.js'));

        // HTML & MARKDOWN EDITOR
        $this->core->addCSS(url('/assets/jscripts/editor/markitup.min.css'));
        $this->core->addCSS(url('/assets/jscripts/editor/markitup.highlight.min.css'));
        $this->core->addCSS(url('/assets/jscripts/editor/sets/html/set.min.css'));
        $this->core->addCSS(url('/assets/jscripts/editor/sets/markdown/set.min.css'));
        $this->core->addJS(url('/assets/jscripts/editor/highlight.min.js'));
        $this->core->addJS(url('/assets/jscripts/editor/markitup.min.js'));
        $this->core->addJS(url('/assets/jscripts/editor/markitup.highlight.min.js'));
        $this->core->addJS(url('/assets/jscripts/editor/sets/html/set.min.js'));
        $this->core->addJS(url('/assets/jscripts/editor/sets/markdown/set.min.js'));

        // ARE YOU SURE?
        $this->core->addJS(url('assets/jscripts/are-you-sure.min.js'));

        // MODULE SCRIPTS
        $this->core->addJS(url([ADMIN, 'website', 'javascript']));

        // MODULE CSS
        $this->core->addCSS(url(MODULES.'/website/css/admin/website.css'));
    }
}
