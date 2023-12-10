<?php

namespace Plugins\Website;

use Systems\SiteModule;

class Site extends SiteModule
{
    public function init()
    {
        $slug = parseURL();
        if (count($slug) == 3 && $slug[0] == 'news' && $slug[1] == 'post') {
            $row = $this->db('mlite_news')->where('status', '>=', 1)->where('published_at', '<=', time())->where('slug', $slug[2])->oneArray();
        }

        $this->tpl->set('latestPosts', function () {
            return $this->_getLatestPosts();
        });
        $this->tpl->set('allTags', function () {
            return $this->_getAllTags();
        });
    }

    public function routes()
    {
        $this->route('homepage', '_getHomepage');
        $this->route('booking/save', 'postSave');
        $this->route('news', '_importAllPosts');
        $this->route('news/(:int)', '_importAllPosts');
        $this->route('news/post/(:str)', '_importPost');
        $this->route('news/tag/(:str)', '_importTagPosts');
        $this->route('news/tag/(:str)/(:int)', '_importTagPosts');
        $this->route('news/feed/(:str)', '_generateRSS');
    }

    public function _getHomepage()
    {
      $assign = [
          'title' => $this->settings('website.title'),
          'desc' => $this->settings('website.desc'),
          'posts' => []
      ];

      $assign['notify'] = $this->core->getNotify();
      $assign['nama_instansi'] = $this->settings->get('settings.nama_instansi');
      $assign['alamat'] = $this->settings->get('settings.alamat');
      $assign['kota'] = $this->settings->get('settings.kota');
      $assign['propinsi'] = $this->settings->get('settings.propinsi');
      $assign['nomor_telepon'] = $this->settings->get('settings.nomor_telepon');
      $assign['email'] = $this->settings->get('settings.email');
      $assign['poliklinik'] = $this->db('poliklinik')->where('status', '1')->toArray();
      $assign['website'] = $this->settings('website');
      $assign['setting'] = $this->settings('settings');

      $this->setTemplate("homepage.html");
      $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc']]);
      $this->tpl->set('website', $assign);
    }

    public function postSave()
    {
        unset($_POST['save']);
        if(isset($_POST['daftar'])) {
            $max = $this->db('booking_periksa')
                ->select(['no_booking' => 'ifnull(MAX(CONVERT(RIGHT(no_booking,4),signed)),0)+1'])
                ->where('tanggal', $_POST['tanggal'])
                ->oneArray();
            $no_urut = "BP".str_replace('-','',$_POST['tanggal']).''.sprintf("%04s", $max['no_booking']);
            $query = $this->db('booking_periksa')->save([
                'no_booking' => $no_urut,
                'tanggal' => $_POST['tanggal'],
                'nama' => $_POST['nama'],
                'alamat' => $_POST['alamat'],
                'no_telp' => $_POST['no_telp'],
                'email' => $_POST['email'],
                'kd_poli' => $_POST['kd_poli'],
                'tambahan_pesan' => $_POST['tambahan_pesan'],
                'status' => 'Belum Dibalas',
                'tanggal_booking' => date('Y-m-d H:i:s')
            ]);
            if ($query) {
                $this->notify('success', '<center><h2>Booking pendaftaran pasien sukes!!</h2></center>');
            } else {
                $this->notify('failure', '<center><h2>Booking pendaftaran pasien gagal!!</h2></center>');
            }
        }
        redirect(url());
    }

    public function _getLatestPosts()
    {
        $limit = $this->settings('website.latestPostsCount');
        $rows = $this->db('mlite_news')
                ->leftJoin('mlite_users', 'mlite_users.id = mlite_news.user_id')
                ->where('status', 2)
                ->where('published_at', '<=', time())
                ->desc('published_at')
                ->limit($limit)
                ->select(['mlite_news.id', 'mlite_news.title', 'mlite_news.slug', 'mlite_news.intro', 'mlite_news.content', 'mlite_users.username', 'mlite_users.fullname'])
                ->toArray();

        foreach ($rows as &$row) {
            $this->filterRecord($row);
        }

        return $rows;
    }

    public function _getAllTags()
    {
        $rows = $this->db('mlite_news_tags')
                ->leftJoin('mlite_news_tags_relationship', 'mlite_news_tags.id = mlite_news_tags_relationship.tag_id')
                ->leftJoin('mlite_news', 'mlite_news.id = mlite_news_tags_relationship.news_id')
                ->where('mlite_news.status', 2)
                ->where('mlite_news.published_at', '<=', time())
                ->select(['mlite_news_tags.name', 'mlite_news_tags.slug', 'count' => 'COUNT(mlite_news_tags.name)'])
                ->group('mlite_news_tags.name')
                ->toArray();

        return $rows;
    }

    /**
    * get single post data
    */
    public function _importPost($slug = null)
    {
        $assign = [];
        if (!empty($slug)) {
            if ($this->core->loginCheck()) {
                $row = $this->db('mlite_news')->where('slug', $slug)->oneArray();
            } else {
                $row = $this->db('mlite_news')->where('status', '>=', 1)->where('published_at', '<=', time())->where('slug', $slug)->oneArray();
            }

            if (!empty($row)) {
                // get dependences
                $row['author'] = $this->db('mlite_users')->where('id', $row['user_id'])->oneArray();
                $row['author']['name'] = !empty($row['author']['fullname']) ? $row['author']['fullname'] : $row['author']['username'];
                $row['author']['avatar'] = url(UPLOADS.'/users/'.$row['author']['avatar']);
                $row['cover_url'] = url(UPLOADS.'/website/news/'.$row['cover_photo']).'?'.$row['published_at'];

                $row['url'] = url('news/post/'.$row['slug']);
                $row['disqus_identifier'] = md5($row['id'].$row['url']);

                // tags
                $row['tags'] = $this->db('mlite_news_tags')
                                    ->leftJoin('mlite_news_tags_relationship', 'mlite_news_tags.id = mlite_news_tags_relationship.tag_id')
                                    ->where('mlite_news_tags_relationship.news_id', $row['id'])
                                    ->toArray();
                if ($row['tags']) {
                    array_walk($row['tags'], function (&$tag) {
                        $tag['url'] = url('news/tag/'.$tag['slug']);
                    });
                }

                $this->filterRecord($row);
                $assign = $row;

                // Markdown
                if (intval($assign['markdown'])) {
                    $parsedown = new \Systems\Lib\Parsedown();
                    $assign['content'] = $parsedown->text($assign['content']);
                    $assign['intro'] = $parsedown->text($assign['intro']);
                }

                // Admin access only
                if ($this->core->loginCheck()) {
                    if ($assign['published_at'] > time()) {
                        $assign['content'] = '<div class="alert alert-warning">Artikel ini belum dipublikasi. Hanya admin yang dapat melihat ini.</div>'.$assign['content'];
                    }
                    if ($assign['status'] == 0) {
                        $assign['content'] = '<div class="alert alert-warning">Artikel ini dalam status <b>draft</b>. Hanya admin yang dapat melihat ini.</div>'.$assign['content'];
                    }
                }

                // date formatting
                $assign['published_at'] = (new \DateTime(date("YmdHis", $assign['published_at'])))->format($this->settings('website.dateformat'));
                $keys = array_keys(month());
                $vals = array_values(month());
                $assign['published_at'] = str_replace($keys, $vals, strtolower($assign['published_at']));

                $this->setTemplate("post.html");
                $this->tpl->set('page', ['title' => $assign['title'], 'desc' => trim(mb_strimwidth(htmlspecialchars(strip_tags(preg_replace('/\{(.*?)\}/', null, $assign['content']))), 0, 155, "...", "utf-8"))]);
                $this->tpl->set('post', $assign);
                $this->tpl->set('website', [
                    'title' => $this->settings('website.title'),
                    'desc' => $this->settings('website.desc')
                ]);
            } else {
                return $this->core->module->pages->get404();
            }
        }

        $this->core->append('<link rel="alternate" type="application/rss+xml" title="RSS" href="'.url(['news', 'feed']).'">', 'header');
        $this->core->append('<meta property="og:url" content="'.url(['news', 'post', $row['slug']]).'">', 'header');
        $this->core->append('<meta property="og:type" content="article">', 'header');
        $this->core->append('<meta property="og:title" content="'.$row['title'].'">', 'header');
        $this->core->append('<meta property="og:description" content="'.trim(mb_strimwidth(htmlspecialchars(strip_tags(preg_replace('/\{(.*?)\}/', null, $assign['content']))), 0, 155, "...", "utf-8")).'">', 'header');
        if (!empty($row['cover_photo'])) {
            $this->core->append('<meta property="og:image" content="'.url(UPLOADS.'/website/news/'.$row['cover_photo']).'?'.$row['published_at'].'">', 'header');
        }

        $this->core->append($this->draw('disqus.html', ['isPost' => true]), 'footer');
    }

    /**
    * get array with all posts
    */
    public function _importAllPosts($page = 1)
    {
        $page = max($page, 1);
        $perpage = $this->settings('website.perpage');
        $rows = $this->db('mlite_news')
                            ->where('status', 2)
                            ->where('published_at', '<=', time())
                            ->limit($perpage)->offset(($page-1)*$perpage)
                            ->desc('published_at')
                            ->toArray();

        $assign = [
            'title' => $this->settings('website.title'),
            'desc' => $this->settings('website.desc'),
            'posts' => []
        ];
        foreach ($rows as $row) {
            // get dependences
            $row['author'] = $this->db('mlite_users')->where('id', $row['user_id'])->oneArray();
            $row['author']['name'] = !empty($row['author']['fullname']) ? $row['author']['fullname'] : $row['author']['username'];
            $row['cover_url'] = url(UPLOADS.'/website/news/'.$row['cover_photo']).'?'.$row['published_at'];

            // tags
            $row['tags'] = $this->db('mlite_news_tags')
                                ->leftJoin('mlite_news_tags_relationship', 'mlite_news_tags.id = mlite_news_tags_relationship.tag_id')
                                ->where('mlite_news_tags_relationship.news_id', $row['id'])
                                ->toArray();

            if ($row['tags']) {
                array_walk($row['tags'], function (&$tag) {
                    $tag['url'] = url('news/tag/'.$tag['slug']);
                });
            }

            // date formatting
            $row['published_at'] = (new \DateTime(date("YmdHis", $row['published_at'])))->format($this->settings('website.dateformat'));
            $keys = array_keys(month());
            $vals = array_values(month());
            $row['published_at'] = str_replace($keys, $vals, strtolower($row['published_at']));

            // generating URLs
            $row['url'] = url('news/post/'.$row['slug']);
            $row['disqus_identifier'] = md5($row['id'].$row['url']);

            if (!empty($row['intro'])) {
                $row['content'] = $row['intro'];
            }

            if (intval($row['markdown'])) {
                if (!isset($parsedown)) {
                    $parsedown = new \Systems\Lib\Parsedown();
                }
                $row['content'] = $parsedown->text($row['content']);
            }

            $this->filterRecord($row);
            $assign['posts'][$row['id']] = $row;
        }

        $count = $this->db('mlite_news')->where('status', 2)->where('published_at', '<=', time())->count();

        if ($page > 1) {
            $prev['url'] = url('news/'.($page-1));
            $this->tpl->set('prev', $prev);
        }
        if ($page < $count/$perpage) {
            $next['url'] = url('news/'.($page+1));
            $this->tpl->set('next', $next);
        }

        $this->setTemplate("news.html");

        $this->tpl->set('page', ['title' => $assign['title'], 'desc' => $assign['desc']]);
        $this->tpl->set('news', $assign);

        $this->core->append('<link rel="alternate" type="application/rss+xml" title="RSS" href="'.url(['news', 'feed']).'">', 'header');
        $this->core->append($this->draw('disqus.html', ['isNews' => true]), 'footer');
    }

    /**
    * get array with all posts
    */
    public function _importTagPosts($slug, $page = 1)
    {
        $page = max($page, 1);
        $perpage = $this->settings('website.perpage');

        if (!($tag = $this->db('mlite_news_tags')->oneArray('slug', $slug))) {
            return $this->core->module->pages->get404();
        }

        $rows = $this->db('mlite_news')
                        ->leftJoin('mlite_news_tags_relationship', 'mlite_news_tags_relationship.news_id = mlite_news.id')
                        ->where('mlite_news_tags_relationship.tag_id', $tag['id'])
                        ->where('status', 2)->where('published_at', '<=', time())
                        ->limit($perpage)
                        ->offset(($page-1)*$perpage)
                        ->desc('published_at')
                        ->toArray();

        $assign = [
            'title' => '#'.$tag['name'],
            'desc' => $this->settings('website.desc'),
            'posts' => []
        ];
        foreach ($rows as $row) {
            // get dependences
            $row['author'] = $this->db('mlite_users')->where('id', $row['user_id'])->oneArray();
            $row['author']['name'] = !empty($row['author']['fullname']) ? $row['author']['fullname'] : $row['author']['username'];

            $row['cover_url'] = url(UPLOADS.'/website/news/'.$row['cover_photo']).'?'.$row['published_at'];

            // tags
            $row['tags'] = $this->db('mlite_news_tags')
                                ->leftJoin('mlite_news_tags_relationship', 'mlite_news_tags.id = mlite_news_tags_relationship.tag_id')
                                ->where('mlite_news_tags_relationship.news_id', $row['id'])
                                ->toArray();

            if ($row['tags']) {
                array_walk($row['tags'], function (&$tag) {
                    $tag['url'] = url('news/tag/'.$tag['slug']);
                });
            }

            // date formatting
            $row['published_at'] = (new \DateTime(date("YmdHis", $row['published_at'])))->format($this->settings('website.dateformat'));
            $keys = array_keys(month());
            $vals = array_values(month());
            $row['published_at'] = str_replace($keys, $vals, strtolower($row['published_at']));

            // generating URLs
            $row['url'] = url('news/post/'.$row['slug']);
            $row['disqus_identifier'] = md5($row['id'].$row['url']);

            if (!empty($row['intro'])) {
                $row['content'] = $row['intro'];
            }

            if (intval($row['markdown'])) {
                if (!isset($parsedown)) {
                    $parsedown = new \Systems\Lib\Parsedown();
                }
                $row['content'] = $parsedown->text($row['content']);
            }

            $this->filterRecord($row);
            $assign['posts'][$row['id']] = $row;
        }

        $count = $this->db('mlite_news')->leftJoin('mlite_news_tags_relationship', 'mlite_news_tags_relationship.news_id = mlite_news.id')->where('status', 2)->where('published_at', '<=', time())->where('mlite_news_tags_relationship.tag_id', $tag['id'])->count();

        if ($page > 1) {
            $prev['url'] = url('news/tag/'.$slug.'/'.($page-1));
            $this->tpl->set('prev', $prev);
        }
        if ($page < $count/$perpage) {
            $next['url'] = url('news/tag/'.$slug.'/'.($page+1));
            $this->tpl->set('next', $next);
        }

        $this->setTemplate("news.html");

        $this->tpl->set('page', ['title' => $assign['title'] , 'desc' => $assign['desc']]);
        $this->tpl->set('news', $assign);

        $this->core->append($this->draw('disqus.html', ['isNews' => true]), 'footer');
    }

    public function _generateRSS()
    {
        header('Content-type: application/xml');
        $this->setTemplate(false);

        $rows = $this->db('mlite_news')
                    ->where('status', 2)
                    ->where('published_at', '<=', time())
                    ->limit(5)
                    ->desc('published_at')
                    ->toArray();

        if (!empty($rows)) {
            foreach ($rows as &$row) {
                if (!empty($row['intro'])) {
                    $row['content'] = $row['intro'];
                }

                $row['content'] = preg_replace('/{(.*?)}/', '', html_entity_decode(strip_tags($row['content'])));
                $row['url'] = url('news/post/'.$row['slug']);
                $row['cover_url'] = url(UPLOADS.'/website/news/'.$row['cover_photo']).'?'.$row['published_at'];
                $row['published_at'] = (new \DateTime(date("YmdHis", $row['published_at'])))->format('D, d M Y H:i:s O');

                $this->filterRecord($row);
            }

            echo $this->draw('feed.xml', ['posts' => $rows]);
        }
    }

    protected function filterRecord(array &$post)
    {
        $post['title'] = htmlspecialchars($post['title']);
    }

}
