CREATE TABLE `website_categories` (
  `cat_id` int(8) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `cat_description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `website_categories` (`cat_id`, `cat_name`, `cat_description`) VALUES
(1, 'Tak Berkategori', 'Kategori tidak didefinisikan');

CREATE TABLE `website_posts` (
  `post_id` int(8) NOT NULL,
  `post_title` varchar(255) NOT NULL,
  `post_content` text NOT NULL,
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_cat_id` varchar(8) DEFAULT NULL,
  `post_author` varchar(100) NOT NULL,
  `post_type` varchar(100) NOT NULL,
  `post_image` varchar(255) DEFAULT NULL,
  `post_status` ENUM('draft','publish','trash','home') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `website_posts` (`post_id`, `post_title`, `post_content`, `post_date`, `post_cat_id`, `post_author`, `post_type`, `post_image`, `post_status`) VALUES
(1,	'Halo dunia!',	'Selamat datang di <strong>Atila CMS</strong>. Ini adalah pos pertama Anda. Edit atau hapus pos ini, lalu mulailah menulis!',	NOW(),	1,	1,	'post', '', 'publish'),
(2, 'Halaman Contoh', '<p>Ini adalah contoh laman. Ini berbeda dengan posting <strong>POST</strong> karena bersifat statik dan biasanya muncul dimenu navigasi. Kebanyakan orang memulai dengan laman Tentang Kami yang mengenalkannya ke calon pengunjung situs.</p>', '2018-07-04 07:49:59', 1, 1, 'page', '', 'publish');

ALTER TABLE `website_categories`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `cat_name_unique` (`cat_name`);

ALTER TABLE `website_posts`
  ADD PRIMARY KEY (`post_id`);

ALTER TABLE `website_categories`
  MODIFY `cat_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `website_posts`
  MODIFY `post_id` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
