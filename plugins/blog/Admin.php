<?php

namespace Plugins\Blog;

use Systems\AdminModule;

class Admin extends AdminModule
{
    private $assign = [];

    public function navigation()
    {
        return [
            'Index'    => 'index',
            'Kelola'    => 'manage',
            'Tambah baru'              => 'add',
            'Pengaturan'                => 'settings'
        ];
    }

    public function getIndex()
    {
      $sub_modules = [
        ['name' => 'Kelola', 'url' => url([ADMIN, 'blog', 'manage']), 'icon' => 'pencil-square', 'desc' => 'Kelola postingan blog'],
        ['name' => 'Tambah Baru', 'url' => url([ADMIN, 'blog', 'add']), 'icon' => 'pencil-square', 'desc' => 'Tambah postingan baru'],
        ['name' => 'Pengaturan', 'url' => url([ADMIN, 'blog', 'settings']), 'icon' => 'pencil-square', 'desc' => 'Pengaturan blog'],
      ];
      return $this->draw('index.html', ['sub_modules' => $sub_modules]);
    }

    public function anyManage($page = 1)
    {
        if (isset($_POST['delete'])) {
            if (isset($_POST['post-list']) && !empty($_POST['post-list'])) {
                foreach ($_POST['post-list'] as $item) {
                    $row = $this->db('mlite_blog')->where('id', $item)->oneArray();
                    if ($this->db('mlite_blog')->delete($item) === 1) {
                        if (!empty($row['cover_photo']) && file_exists(UPLOADS."/blog/".$row['cover_photo'])) {
                            unlink(UPLOADS."/blog/".$row['cover_photo']);
                        }

                        $this->notify('success', 'Artikel berhasil dihapus.');
                    } else {
                        $this->notify('failure', 'Gagal menghapus artikel.');
                    }
                }

                redirect(url([ADMIN, 'blog', 'manage']));
            }
        }

        // pagination
        $totalRecords = count($this->db('mlite_blog')->toArray());
        $pagination = new \Systems\Lib\Pagination($page, $totalRecords, 10, url([ADMIN, 'blog', 'manage', '%d']));
        $this->assign['pagination'] = $pagination->nav();

        // list
        $this->assign['newURL'] = url([ADMIN, 'blog', 'add']);
        $this->assign['postCount'] = 0;
        $rows = $this->db('mlite_blog')
                ->limit($pagination->offset().', '.$pagination->getRecordsPerPage())
                ->desc('published_at')->desc('created_at')
                ->toArray();

        $this->assign['posts'] = [];
        if ($totalRecords) {
            $this->assign['postCount'] = $totalRecords;
            foreach ($rows as $row) {
                $row['editURL'] = url([ADMIN, 'blog', 'edit', $row['id']]);
                $row['delURL']  = url([ADMIN, 'blog', 'delete', $row['id']]);
                $row['viewURL'] = url(['blog', 'post', $row['slug']]);


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

        return $this->draw('manage.html', ['blog' => $this->assign]);
    }

    public function getAdd()
    {
        return $this->getEdit(null);
    }

    public function getEdit($id = null)
    {
        $this->assign['manageURL'] = url([ADMIN, 'blog', 'manage']);
        $this->assign['coverDeleteURL'] = url([ADMIN, 'blog', 'deleteCover', $id]);
        $this->assign['editor'] = $this->settings('settings.editor');
        $this->_addHeaderFiles();

        if ($id === null) {
            $blog = [
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
            $blog = $this->db('mlite_blog')->where('id', $id)->oneArray();
        }

        if (!empty($blog)) {
            $this->assign['form'] = htmlspecialchars_array($blog);
            $this->assign['form']['content'] =  $this->tpl->noParse($this->assign['form']['content']);
            $this->assign['form']['date'] = date("Y-m-d\TH:i", $blog['published_at']);

            $tags_array = $this->db('mlite_blog_tags')->leftJoin('mlite_blog_tags_relationship', 'mlite_blog_tags.id = mlite_blog_tags_relationship.tag_id')->where('mlite_blog_tags_relationship.blog_id', $blog['id'])->select(['mlite_blog_tags.name'])->toArray();

            $this->assign['form']['tags'] = $tags_array;
            $this->assign['users'] = $this->db('mlite_users')->toArray();
            $this->assign['author'] = $this->core->getUserInfo('id', $blog['user_id'], true);

            $this->assign['title'] = ($blog['id'] === null) ? 'Tambah baru' : 'Sunting Artikel';

            return $this->draw('form.html', ['blog' => $this->assign]);
        } else {
            redirect(url([ADMIN, 'blog', 'manage']));
        }
    }

    public function postSave($id = null)
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
            $location = url([ADMIN, 'blog', 'add']);
        } else {
            $location = url([ADMIN, 'blog', 'edit', $id]);
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

        while ($this->db('mlite_blog')->where('slug', $_POST['slug'])->where('id', '!=', $id)->oneArray()) {
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

            $query = $this->db('mlite_blog')->save($_POST);
            $location = url([ADMIN, 'blog', 'edit', $this->db()->pdo()->lastInsertId()]);
        } else {    // edit
            $query = $this->db('mlite_blog')->where('id', $id)->save($_POST);
        }

        // detach tags from post
        if ($id) {
            $this->db('mlite_blog_tags_relationship')->delete('blog_id', $id);
            $blogId = $id;
        } else {
            $blogId = $id ? $id : $this->db()->pdo()->lastInsertId();
        }


        // Attach or create new tag
        foreach ($tags as $tag) {
            if (preg_match("/[`~!@#$%^&*()_|+\-=?;:\'\",.<>\{\}\[\]\\\/]+/", $tag)) {
                continue;
            }

            $slug = createSlug($tag);
            if ($e = $this->db('mlite_blog_tags')->like('slug', $slug)->oneArray()) {
                $this->db('mlite_blog_tags_relationship')->save(['blog_id' => $blogId, 'tag_id' => $e['id']]);
            } else {
                $tagId = $this->db('mlite_blog_tags')->save(['name' => $tag, 'slug' => $slug]);
                $this->db('mlite_blog_tags_relationship')->save(['blog_id' => $blogId, 'tag_id' => $tagId]);
            }
        }

        if ($query) {
            if (!file_exists(UPLOADS."/blog")) {
                mkdir(UPLOADS."/blog", 0777, true);
            }

            if ($p = $img->getInfos('width')) {
                $img->save(UPLOADS."/blog/".$_POST['cover_photo']);
            }

            $this->notify('success', 'Artikel berhasil disimpan.');
        } else {
            $this->notify('failure', 'Gagal menyimpan artikel.');
        }

        redirect($location);
    }

    public function getDelete($id)
    {
        if ($post = $this->db('mlite_blog')->where('id', $id)->oneArray() && $this->db('mlite_blog')->delete($id)) {
            if ($post['cover_photo']) {
                unlink(UPLOADS."/blog/".$post['cover_photo']);
            }
            $this->notify('success', 'Artikel berhasil dihapus.');
        } else {
            $this->notify('failure', 'Gagal menghapus artikel.');
        }

        redirect(url([ADMIN, 'blog', 'manage']));
    }

    public function getDeleteCover($id)
    {
        if ($post = $this->db('mlite_blog')->where('id', $id)->oneArray()) {
            unlink(UPLOADS."/blog/".$post['cover_photo']);
            $this->db('mlite_blog')->where('id', $id)->save(['cover_photo' => null]);
            $this->notify('success', 'Foto cover sudah dihapus.');

            redirect(url([ADMIN, 'blog', 'edit', $id]));
        }
    }

    public function getSettings()
    {
        $assign = htmlspecialchars_array($this->settings('blog'));
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
        return $this->draw('settings.html', ['settings' => $assign]);
    }

    public function postSaveSettings()
    {
        foreach ($_POST['blog'] as $key => $val) {
            $this->settings('blog', $key, $val);
        }
        $this->notify('success', 'Pengaturan sudah disimpan.');
        redirect(url([ADMIN, 'blog', 'settings']));
    }

    public function postEditorUpload()
    {
        header('Content-type: application/json');
        $dir    = UPLOADS.'/blog';
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
        echo $this->draw(MODULES.'/blog/js/admin/blog.js');
        exit();
    }

    public function getJsonTags($query = null)
    {
        header('Content-type: application/json');

        if (!$query) {
            exit(json_encode([]));
        }

        $query = urldecode($query);
        $tags = $this->db('mlite_blog_tags')->like('name', $query.'%')->toArray();

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
        $this->core->addJS(url([ADMIN, 'blog', 'javascript']));

        // MODULE CSS
        $this->core->addCSS(url(MODULES.'/blog/css/admin/blog.css'));
    }
}
