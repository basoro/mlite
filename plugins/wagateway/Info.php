<?php

    return [
        'name'          =>  'WA Gateway',
        'description'   =>  'Modul Whatsapp Gateway mLITE',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022',
        'icon'          =>  'whatsapp',
        'install'       =>  function () use ($core) {

          $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `autoreplies` (
            `id` bigint(20) UNSIGNED NOT NULL,
            `user_id` bigint(20) UNSIGNED NOT NULL,
            `device` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
            `keyword` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `type` enum('text','image','button','template') COLLATE utf8mb4_unicode_ci NOT NULL,
            `reply` json NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

          $core->mysql()->pdo()->exec("CREATE TABLE IF NOT EXISTS `numbers` (
            `id` bigint(20) UNSIGNED NOT NULL,
            `user_id` bigint(20) UNSIGNED NOT NULL,
            `body` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `webhook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `status` enum('Connected','Disconnect') COLLATE utf8mb4_unicode_ci NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

          $core->mysql()->pdo()->exec("ALTER TABLE `autoreplies`
            ADD PRIMARY KEY (`id`);");

          $core->mysql()->pdo()->exec("ALTER TABLE `numbers`
            ADD PRIMARY KEY (`id`);");

          $core->mysql()->pdo()->exec("ALTER TABLE `autoreplies`
            MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;");

          $core->mysql()->pdo()->exec("ALTER TABLE `numbers`
            MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;");

        },
        'uninstall'     =>  function() use($core)
        {
        }
    ];
