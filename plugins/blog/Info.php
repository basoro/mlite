<?php

return [
    'name'          =>  'Blog',
    'description'   =>  'Membuat artikel pada blog.',
    'author'        =>  'Basoro',
    'version'       =>  '1.3',
    'compatibility' =>  '2022',
    'icon'          =>  'pencil-square',
    'pages'         =>  ['Blog' => 'blog'],  
    'install'       =>  function () use ($core) {

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_blog` (
          `id` int(11) NOT NULL,
          `title` VARCHAR(225) NOT NULL,
          `slug` VARCHAR(225) NOT NULL,
          `user_id` int(11) NOT NULL,
          `content` text NOT NULL,
          `intro` text,
          `cover_photo` text,
          `status` int(11) NOT NULL,
          `comments` int(11) DEFAULT '1',
          `markdown` int(11) DEFAULT '0',
          `published_at` int(11) DEFAULT '0',
          `updated_at` int(11) NOT NULL,
          `created_at` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_blog_tags` (
          `id` int(11) NOT NULL,
          `name` VARCHAR(225),
          `slug` VARCHAR(225)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("CREATE TABLE IF NOT EXISTS `mlite_blog_tags_relationship` (
          `blog_id` int(11) NOT NULL,
          `tag_id` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_blog`
          ADD PRIMARY KEY (`id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_blog_tags`
          ADD PRIMARY KEY (`id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_blog_tags_relationship`
          ADD KEY `mlite_blog_tags_relationship_ibfk_1` (`blog_id`),
          ADD KEY `tag_id` (`tag_id`);");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_blog`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_blog_tags`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");

        $core->db()->pdo()->exec("ALTER TABLE `mlite_blog_tags_relationship`
          ADD CONSTRAINT `mlite_blog_tags_relationship_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `mlite_blog` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
          ADD CONSTRAINT `mlite_blog_tags_relationship_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `mlite_blog_tags` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;");

        $core->db()->pdo()->exec("INSERT INTO `mlite_settings`
                    (`module`, `field`, `value`)
                    VALUES
                    ('blog', 'perpage', '5'),
                    ('blog', 'disqus', ''),
                    ('blog', 'dateformat', 'M d, Y'),
                    ('blog', 'title', 'Blog'),
                    ('blog', 'desc', '... RS Masa Gitu ...'),
                    ('blog', 'latestPostsCount', '5')
        ");

        if (!is_dir(UPLOADS."/blog")) {
            mkdir(UPLOADS."/blog", 0777);
        }

        copy(MODULES.'/blog/img/default.jpg', UPLOADS.'/blog/default.jpg');
        copy(MODULES.'/blog/img/default.jpg', UPLOADS.'/blog/default2.jpg');
    },
    'uninstall'     =>  function () use ($core) {
        //$core->db()->pdo()->exec("DROP TABLE `mlite_blog_tags_relationship`");
        //$core->db()->pdo()->exec("DROP TABLE `mlite_blog_tags`");
        //$core->db()->pdo()->exec("DROP TABLE `mlite_blog`");
        $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'blog'");
        deleteDir(UPLOADS."/blog");
    }
];
