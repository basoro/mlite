# Khanza-Lite
SIMKES Khanza versi Ringan. Jalan di sisi server sebagai aplikasi web dan bersifat mobile first (responsive)

Update FROM Version 1.x - 2.0 to Version 2.1 please update roles table

ALTER TABLE `roles` ADD `module` TEXT NOT NULL AFTER `cap`;
