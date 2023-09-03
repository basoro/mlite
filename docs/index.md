Instruksi Umum
==============

mLITE adalah sistem manajemen kesehatan yang sederhana, ringan dan cepat. Pertama kali dirilis pada Mei 2019. Versi gratis dari aplikasi ini dibagikan di bawah [lisensi](/lisensi) yang mengharuskan meninggalkan informasi tentang penulis dan tautan balik. Dengan dokumentasi ini Anda akan belajar cara menginstal, mengkonfigurasi, dan membuat modul dan tema Anda sendiri.

Dokumentasi dibagi menjadi beberapa bagian. Yang pertama adalah untuk instruksi umum, yang kedua untuk pengembang forntend dan yang terakhir untuk pengembang backend.


Persyaratan
-----------

Persyaratan sistem untuk mLITE sangat sederhana, jadi setiap server modern sudah mencukupi.

+ Apache 2.2+ or Nginx dengan `mod_rewrite`
+ PHP version 7.0+
+ MySQL Server 5.5+
+ Akses ke MySQL dan SQLite

Konfigurasi PHP harus memiliki ekstensi berikut:

+ dom
+ gd
+ mbstring
+ pdo
+ zip
+ cURL


Instalasi
---------

Pertama unduh versi terbaru [mLITE](https://github.com/basoro/khanza-lite).

Ekstrak semua file dari paket terkompresi dan kemudian transfer ke direktori lokal atau server. Untuk server cloud, sambungkan melalui klien (S)FTP, seperti program [FileZilla](https://filezilla-project.org) gratis. Biasanya, file harus diunggah ke `www`, `htdocs` atau `public_html`.

**Peringatan!** Pastikan file `.htaccess` juga ada di server. Tanpa itu mLITE tidak akan berfungsi.

Beberapa server mungkin memerlukan izin tambahan `chmod 777` untuk direktori dan file berikut:

+ tmp/
+ uploads/
+ admin/tmp/
+ systems/data/
+ webapps/

Buka browser Anda dan arahkan ke alamat tempat file mLITE berada. Tunggu beberapa saat sampai proses instalasi dibelakang layar (background) selesai.

Untuk masuk ke panel administrasi, tambahkan `/admin/` di akhir URL. **Login dan kata sandi awal adalah *"admin"*.** Ini harus diubah segera setelah login untuk alasan keamanan. Kami juga merekomendasikan mengganti nama direktori dengan panel administrasi. *(Anda perlu mengubah nilai konstanta dalam file definisi di config.php)*.


Konfigurasi
-----------

mLITE dapat dikonfigurasi dengan mengedit pengaturan di panel administrasi dan melalui file config.php. Namun, kami menyarankan mengubah beberapa konfigurasi dalam file jika Anda adalah orang yang tidak berpengalaman. Seperti informasi nama database, user, pengguna dan host.

### Panel Administrasi
Untuk mengubah konfigurasi dasar di panel admin, pilih tab `Pengaturan`. Anda dapat memasukkan nama instansi, deskripsi dan lain sebagainya, serta di tempat lain di template default, seperti di header. Anda juga dapat mengubah default tampilan beranda.

Anda juga bisa mengaktifkan modul yang belum aktif di tab yang sesuai dengan namanya.

### Konfigurasi file
Hal-hal lebih lanjut yang dapat Anda ubah di file `config.php`, yang berisi definisi variabel konstanta.

+ `ADMIN` — nama direktori yang berisi panel administrasi
+ `MULTI_APP` — mode beberapa aplikasi dengan pengaturan penyimpanan ke SqLITE
+ `THEMES` — path ke direktori yang berisi tema
+ `MODULES` — path ke direktori yang berisi modul
+ `UPLOADS` — path ke direktori yang berisi file yang diunggah
+ `FILE_LOCK` — membatasi untuk mengedit file melalui panel administrasi
+ `BASIC_MODULES` — daftar modul dasar yang tidak dapat dihapus
+ `DEV_MODE` — mode pengembang, di mana kesalahan dan catatan PHP ditampilkan


Pembaruan (Update)
------------------

Jika Anda ingin tetap up to date dengan semua berita terbaru, perbaikan bug dan masalah keamanan, Anda harus secara teratur memeriksa pembaruan mLITE. Anda dapat melakukannya di tab `Pengaturan -> Pembaruan`. Sistem akan memeriksa versi baru skrip dan secara otomatis mengunduh paket baru dari server Github dan memperbarui file dan modul inti.

Jika terjadi kesalahan atau kesulitan update otomatis, Anda dapat menggunakan mode update manual. Untuk melakukannya, unduh mLITE versi terbaru, unggah ke direktori aplikasi utama, lalu tambahkan parameter `&manual` di akhir URL bookmark pembaruan. CMS akan mendeteksi paket zip dan ketika Anda mengklik tombol update, proses mengekstrak dan menimpa file akan dilakukan.

Sebelum setiap pembaruan, mLITE membuat cadangan. Anda akan menemukannya di direktori skrip, di folder `backup/`. Jika pembaruan gagal, Anda dapat memulihkannya kapan saja.


Tema (Themes)
============

Struktur
--------

Struktur tema dalam mLITE sangat sederhana. Cukup buat folder baru di direktori `themes/` dan file-file berikut:

+ `index.html` — template default untuk subhalaman
+ `manifest.json` — informasi tema
+ `preview.png` — tangkapan layar yang menunjukkan tema *(opsional)*

Setiap sub halaman dapat menggunakan template lain, jadi selain file yang disebutkan, Anda juga dapat membuat yang lain, misalnya `xyz.html`. Pemilihan template tersedia di panel admin saat membuat halaman. Tidak ada aturan tentang file CSS dan JS. Ada kebebasan penuh.

Di folder tema Anda juga dapat membuat tampilan modul Anda sendiri. Untuk melakukan ini, Anda perlu membuat direktori `plugins/nama_plugin` dan file `*.html` dengan nama yang sesuai dengan nama tampilan asli. Misalnya, tampilan formulir kontak harus dimuat dalam jalur berikut: `themes/nama_tema/plugins/kontak/form.html`. mLITE secara otomatis mendeteksi tampilan baru dan menggunakannya sebagai ganti tampilan default modul.

Tag template
------------

mLITE menggunakan sistem template sederhana yang menyertakan tag berikut:

### Variables
```php
{$foo}        // simple variable
{$foo|e}      // HTML escape for variable
{$foo|cut:10} // content of the variable cut to 10 characters
{$foo.bar}    // array
```
Akses ke elemen array dilakukan oleh karakter titik.

### Conditions
```php
{if: $foo > 5}
    lorem
{elseif: $foo == 5}
    ipsum
{else}
    dolor
{/if}
```

### Loops
```html
<ul>
{loop: $foo}
    <li>{$key}, {$value}, {$counter}</li>
{/loop}

{loop: $foo as $bar}
    <li>{$key}, {$bar}, {$counter}</li>
{/loop}

{loop: $foo as $bar => $baz}
    <li>{$bar}, {$baz}, {$counter}</li>
{/loop}
</ul>
```
Tag loop memiliki 3 tahap ekspansi. Yang pertama adalah variabel array yang sistem template akan pecah menjadi tiga variabel bernama `$key`,` $value` dan `$counter`, yang menghitung iterasi berturut-turut mulai dari nol. Langkah kedua memungkinkan Anda menentukan nama variabel yang menyimpan nilai, dan langkah ketiga juga merupakan nama variabel indeks.

### Menyisipkan berkas template
```html
<html>
    <body>
    {template: header.html}
    <main>
        <p>Lorem ipsum dolor sit amet.</p>
    </main>
    {template: footer.html}
    </body>
</html>
```

### Menyisipkan kode PHP
```php
Today&grave;s date: {?= date('Y-m-d') ?}
```
Jika Anda membiarkan karakter `=`, kode hanya akan dijalankan dan tidak ada yang ditampilkan. Ini memungkinkan Anda, misalnya, untuk mendefinisikan variabel baru dalam template:
```php
{? $foo = 5 ?}
```

### Mengabaikan (disable) parsing
```
{noparse}Gunakan tag {$ contact.form} tuntuk menampiljan formulir kontak.{/noparse}
```
Tag apa pun di dalam ekspresi *noparse* akan tetap tidak berubah.

### Komentar (comments)
```
{* ini adalah komentar *}
```
Komentar tidak terlihat di file sumber setelah mengkompilasi template.

Variabel sistem
----------------
mLITE, seperti pluginnya, menyediakan banyak variabel *(biasanya array)* yang berfungsi untuk menampilkan setiap elemen halaman. Berikut adalah yang paling penting:

+ `{$settings.pole}` — elemen yang berisi nilai bidang pengaturan mLITE yang diberikan
+ `{$settings.moduł.pole}` — elemen yang berisi nilai bidang pengaturan modul
+ `{$mlite.path}` — menyimpan jalur tempat sistem berada
+ `{$mlite.notify}` — pemberitahuan terakhir
+ `{$mlite.notify.text}` - teks notifikasi
+ `{$mlite.notify.type}` - jenis pesan yang sesuai dengan kelas Bootstrap *(bahaya, sukses)*
+ `{$mlite.header}` — tag meta tambahan, skrip JS, dan lembar gaya CSS dimuat oleh modul
+ `{$mlite.footer}` — skrip JS tambahan dimuat oleh modul
+ `{$mlite.theme}` — menampilkan jalur ke tema aktif dengan host
+ `{$mlite.powered}` — menampilkan *Didukung oleh mLITE* dengan tautan ke situs resmi
+ `{$navigation.xyz}` — menampilkan daftar elemen navigasi `<li>`
+ `{$page.title}` — menampilkan nama subhalaman
+ `{$page.content}` — menampilkan konten subhalaman

Contoh
-------

### manifest.json

```
{
    "name": "Contoh",
    "version": "1.0",
    "author": "Basoro",
    "email": "contact@mlite.id",
    "thumb": "preview.png"
}
```

### index.html

```html
<!doctype html>

<html>
<head>
  <meta charset="utf-8">
  <title>{$page.title} - {$settings.title}</title>
  <meta name="description" content="{$settings.description}">
  <meta name="keywords" content="{$settings.keywords}">
  <link rel="stylesheet" href="{$mlite.theme}/styles.css">
  {loop: $mlite.header}{$value}{/loop}
</head>

<body>
    <nav>
        <ul>
            {$navigation.main}
        </ul>
    </nav>

    <main>
        <h1>{$page.title}</h1>
        {$page.content}
    </main>

    <footer>
        {$settings.footer} {$mlite.powered}
    </footer>

    <script src="{$mlite.theme}/scripts.js"></script>
    {loop: $mlite.footer}{$value}{/loop}
</body>
</html>
```

Plugins
=======

Struktur
--------

Setiap plugin, seperti tema, harus berada dalam folder terpisah yang dibuat di jalur `plugins/`. Harap dicatat bahwa direktori tidak mengandung huruf besar dan karakter khusus, seperti spasi.

Saat membuat plugin, Anda perlu memikirkan jenis plugin apa yang ingin Anda gunakan. Apakah seharusnya dikonfigurasi di panel admin atau hanya berfungsi di front-end? Karena pembagian ini, dalam mLITE kami membedakan tiga file plugin utama:

+ `Info.php` — berisi informasi tentang plugin, seperti nama, deskripsi, penulis, atau ikon
+ `Admin.php` — konten file ini dapat diakses melalui panel admin
+ `Site.php` — konten file ini akan tersedia untuk pengunjung situs ini

File keempat tetapi opsional adalah `ReadMe.md` yang seharusnya berisi informasi tambahan untuk pengguna mendatang di [Penurunan harga](https://en.wikipedia.org/wiki/Markdown), misalnya cara menggunakan plugin.

Jika Anda berencana untuk menulis plugin yang akan menggunakan HTML, sebaiknya pastikan kode PHP terpisah dari bahasa markup hypertext. Untuk melakukan ini, Anda perlu membuat direktori `views` di dalam folder modul. Sertakan file tampilan apa pun di dalamnya.

Struktur plugin akan terlihat seperti ini:
```
contoh/
|-- views/
|    |-- admin/
|    |    |-- bar.html
|    |-- foo.html
|-- Admin.php
|-- Info.php
|-- Site.php
+-- ReadMe.md
```

Membuat plugin
--------------

### Info file

File paling penting untuk setiap modul. Ini berisi informasi dasar dan instruksi selama instalasi dan penghapusan instalasi.

```php
<?php

    return [
        'name'          =>  'Contoh',
        'description'   =>  'Lorem ipsum....',
        'author'        =>  'Basoro',
        'version'       =>  '1.0',
        'compatibility' =>  '2022*',                  // Kesesuaian dengan versi mLITE
        'icon'          =>  'bolt',
        'pages'         =>  ['Contoh' => 'contoh'],   // Berfungsi sebagai halaman (opsional)
        'install'       =>  function() use($core)     // Perintah install
        {
            // lorem ipsum...
        },
        'uninstall'     =>  function() use($core)     // Perintah uninstall
        {
            // lorem ipsum...    
        }
    ];
```

Daftar ikon yang dapat Anda gunakan dalam file ini tersedia di [fontawesome.io](http://fontawesome.io/icons/). Pastikan untuk tidak memasukkan nama ikon dengan awalan `fa-`.

Mendaftarkan plugin sebagai halaman memungkinkan Anda untuk bebas menggunakan `route` dan memilihnya sebagai beranda.


### Admin file

Isi file ini akan digunakan di panel admin.

```php
<?php
    namespace Plugins\Contoh;

    use Systems\AdminModule;

    class Admin extends AdminModule
    {
        public function init()
        {
            // Prosedur dipanggil pada inisialisasi modul
        }

        public function navigation()
        {
            return [
                'Foo'   => 'foo',
                'Bar'   => 'bar',
            ];
        }

        public function getFoo($parm)
        {
            return "Foo $parm!";
        }

        public function postBar()
        {
            return "Bar!";
        }
    }
```

Dalam metode `navigasi`, sertakan baris dengan subhalaman modul. Setiap halaman harus diberi metode *(tanpa awalan)*. Item baris ini akan ditampilkan di menu panel administrasi.

Metode juga dapat menerima argumen yang diteruskan melalui URL. Misalnya, setelah memasukkan alamat `/contoh/foo/abc`, metode `getFoo` akan mengembalikan *"Foo abc!"*.

Seperti yang Anda lihat dalam daftar di atas, setiap metode yang mewakili subhalaman modul harus memiliki awalan yang menentukan jenis permintaan. Dalam kebanyakan kasus, kita akan menggunakan nomenklatur `getFoo`, dan pengiriman formulir `postFoo`. Jika metode mendukung semua jenis, metode tersebut harus mendahului awalan `any` *(misalnya, `anyFoo`)*. Ini penting karena halaman tanpa awalan tidak akan direspon. Metode yang didukung diterjemahkan oleh perutean dinamis sebagai berikut:

+ `getFoo()` — sebagai `/contoh/foo` untuk permintaan GET
+ `getFoo($parm)` — sebagai `/contoh/foo/abc` untuk permintaan GET
+ `postBar()` — sebagai `/contoh/bar` untuk permintaan POST *(pengiriman formulir)*
+ `anyFoo()` — sebagai `/contoh/foo` untuk setiap jenis permintaan

### Berkas (file) halaman umum (site)

File ini bertanggung jawab atas tampilah yang dilihat oleh pengguna. Jika plugins cukup komplek, yang baik adalah mendaftarkannya sebagai halaman dan menerapkan perutean.

```php
<?php

    namespace Plugins\Contoh;

    use Systems\SiteModule

    class Site extends SiteModule
    {
        public function init()
        {
            $this->_foo();
        }

        public function routes()
        {
            $this->route('contoh', 'mySite');
        }

        public function mySite()
        {
            $page = [
                'title' => 'Contoh judul halaman..',
                'desc' => 'Deskripsi halaman',
                'content' => 'Lorem ipsum dolor...'
            ];

            $this->setTemplate('index.html');
            $this->tpl->set('page', $page);
        }

        private function _foo()
        {            
            $this->tpl->set('bar', 'Bisa kan bro?');
        }
    }
```

Dalam contoh di atas, variabel template `bar` baru telah dibuat yang, dengan memanggil metode `_foo()` dalam penginisialisasi plugin, dapat digunakan dalam file tema sebagai `{$bar}`. Selain itu, metode `routes()` telah membuat subrutin `/contoh` yang menunjuk ke pemanggilan metode `mySite()`. Jika Anda membuka `http://contoh.com/contoh`, Anda akan memanggil metode `mySite()`.

Routing
-------

Perutean adalah proses memproses alamat permintaan yang diterima dan memutuskan apa yang harus dijalankan atau ditampilkan. Seharusnya memanggil metode/fungsi yang sesuai berdasarkan URL halaman. Anda harus menggunakan perutean di dalam metode `routes()` publik.

```php
void route(string $pattern, mixed $callback)
```

Parameter pertama dari metode `route` adalah ekspresi reguler. Beberapa ekspresi telah didefinisikan:

+ `:any` — any string
+ `:int` — integers
+ `:str` — string that is a slug

Parameter kedua adalah nama metode atau fungsi anonim yang melewati sejumlah argumen yang ditentukan dalam ekspresi reguler.

#### Contoh
```php
public function routes()
{
    // URL: http://contoh.com/news

    // - by calling the method inside the module:
    $this->route('news', 'importAllPosts');

    // - by calling an anonymous function:
    $this->route('news', function() {
        $this->importAllPosts();
    });

    // URL: http://contoh.com/news/2
    $this->route('news/(:int)', function($page) {
        $this->importAllPosts($page);
    });

    // URL: http://contoh.com/news/post/lorem-ipsum
    $this->route('news/post/(:str)', function($slug) {
        $this->importPost($slug);
    });

    // URL: http://contoh.com/news/post/lorem-ipsum/4
    $this->route('news/post/(:str)/(:int)', function($slug, $page) {
        $this->importPost($slug, $page);
    });
}
```


Methods
-------

Plugin memiliki trik khusus yang memfasilitasi akses ke metode skrip utama. Ini memungkinkan Anda untuk mempersingkat panggilan `$this->core->foo->bar`.

### db

```php
void mysql([string $table])
```

Memungkinkan Anda untuk beroperasi pada database. Rincian dijelaskan di bagian inti.

#### Argumen
+ `table` — Nama tabel database *(optional)*

#### Contoh
```php
$this->core->mysql('table')->where('age', 20)->delete();
```


### draw

```php
string draw(string $file [, array $variables])
```

Mengembalikan kode tampilan terkompilasi yang sebelumnya menggunakan tag sistem template. Ini juga memungkinkan Anda untuk mendefinisikan variabel dengan mengganti metode `set()`.

#### Argumen
+ `file` — nama file dengan tampilan di dalam plugin atau ke file di luarnya
+ `variabel` — definisi variabel yang dapat digunakan sebagai tag *(opsional)*

#### Contoh
```php
// Kompilasi tampilan di dalam modul
$this->draw('form.html', ['form' => $this->formFields]);

// Kompilasi tampilan di luar modul
$this->draw('../path/to/view.html', ['foo' => 'bar']);
```

### notifikasi

```php
void notify(string $type, string $text [, mixed $args [, mixed $... ]])
```

Ini memungkinkan Anda untuk memanggil pemberitahuan kepada pengguna.

#### Argumen
+ `type` — type of notification: *success* or *failure*
+ `text` — notyfication content
+ `args` — additional arguments *(optional)*

#### Contoh
```php
$foo = 'Bar';
$this->notify('success', 'This is %s!', $foo); // $this->core->setNotify('success', 'This is %s!', $foo);

```


### settings

```php
mixed settings(string $module [, string $field [, string $value]])
```

Mendapat atau menetapkan nilai pengaturan modul.

#### Argumen
+ `module` — nama modul dan bidang opsional yang dipisahkan oleh titik
+ `field` — nama kolom modul *(opsional)*
+ `value` — nilai bidang modul mana yang akan diubah *(opsional)*

#### Contoh
```php
// Pilih bidang "desc" dari plugin "website".
$this->settings('website.desc');    // $this->core->getSettings('website', 'desc');

// Pilih bidang "desc" dari plugin "situs web".
$this->settings('website', 'desc'); // $this->core->getSettings('website', 'desc');

// Setel konten bidang "desc" dari plugin "website".
$this->settings('website', 'desc', 'Lorem ipsum...');
```

### setTemplate

```php
void setTemplate(string $file)
```

Memungkinkan Anda untuk mengubah file template di bagian depan. Metode ini hanya berfungsi di Site class.

#### Argumen
+ `file` — Nama file template

#### Contoh
```php
$this->setTemplate('index.html'); // $this->core->template = 'index.html';
```

Database
--------

Basis data yang digunakan pada mLITE adalah MySQL dan SQLite versi 3. Untuk penggunaannya mLITE menggunakan class sederhana yang memudahkan untuk membangun query. Anda tidak perlu tahu SQL untuk dapat mengoperasikannya.

### SELECT

Pilih beberapa data:

```php

$rows = $this->core->mysql('table')->select('foo')->select('bar')->toArray();

```

Pilih satu data:
```php

$row = $this->core->mysql('table')->select('foo')->select('bar')->oneArray();

```

### WHERE

Pilih record dengan nomor yang ditentukan di kolom `id`:

```php
$row = $this->core->mysql('table')->oneArray(1);
// atau
$row = $this->core->mysql('table')->oneArray('id', 1);
// atau
$row = $this->core->mysql('table')->where(1)->oneArray();
// atau
$row = $this->core->mysql('table')->where('id', 1)->oneArray();
```

Kondisi kompleks:
```php
// Ambil baris yang nilai kolomnya 'foo' LEBIH BESAR dari 4
$rows = $this->core->mysql('table')->where('foo', '>', 4)->toArray();

// Ambil baris yang nilai kolomnya 'foo' LEBIH BESAR dari 4 dan LEBIH RENDAH dari 8
$rows = $this->core->mysql('table')->where('foo', '>', 4)->where('foo', '<', 8)->toArray();
```

OR WHERE:
```php
// Ambil baris yang nilai kolomnya 'foo' SAMA DENGAN 4 atau 8
$rows = $this->core->mysql('table')->where('foo', '=', 4)->orWhere('foo', '=', 8)->toArray();
```

WHERE LIKE:
```php
// Ambil baris yang kolomnya 'foo' BERISI string 'bar' ATAU 'basis'
$rows = $this->core->mysql('table')->like('foo', '%bar%')->orLike('foo', '%baz%')->toArray();
```

WHERE NOT LIKE:
```php
// Ambil baris yang kolomnya 'foo' TIDAK MENGANDUNG string 'bar' ATAU 'baz'
$rows = $this->core->mysql('table')->notLike('foo', '%bar%')->orNotLike('foo', '%baz%')->toArray();
```

WHERE IN:
```php
// Ambil baris yang nilai kolomnya 'foo' BERISI dalam baris [1,2,3] ATAU [7,8,9]
$rows = $this->core->mysql('table')->in('foo', [1,2,3])->orIn('foo', [7,8,9])->toArray();
```

WHERE NOT IN:
```php
// Ambil baris yang nilai kolomnya 'foo' TIDAK BERISI dalam baris [1,2,3] ATAU [7,8,9]
$rows = $this->core->mysql('table')->notIn('foo', [1,2,3])->orNotIn('foo', [7,8,9])->toArray();
```

Kondisi pengelompokan:
```php
// Ambil baris yang nilai kolomnya 'foo' adalah 1 atau 2 DAN statusnya adalah 1
$rows = $this->core->mysql('table')->where(function($st) {
            $st->where('foo', 1)->orWhere('foo', 2);
        })->where('status', 1)->toArray();
```

Operator pembanding yang diizinkan: `=`, `>`, `<`, `>=`, `<=`, `<>`, `!=`.


### JOIN

INNER JOIN:
```php
$rows = $this->core->mysql('table')->join('foo', 'foo.table_id = table.id')->toJson();
```

LEFT JOIN:
```php
$rows = $this->core->mysql('table')->leftJoin('foo', 'foo.table_id = table.id')->toJson();
```


### HAVING

```php
$rows = $this->core->mysql('table')->having('COUNT(*)', '>', 5)->toArray();
```

OR HAVING:
```php
$rows = $this->core->mysql('table')->orHaving('COUNT(*)', '>', 5)->toArray();
```


### INSERT

Metode `save` dapat menambahkan catatan baru ke tabel atau memperbarui yang sudah ada ketika memiliki kondisi. Ketika Anda menambahkan catatan baru, nomor identifikasi akan dikembalikan.

```php
// Tambahkan data baru
$id = $this->core->mysql('table')->save(['name' => 'Fulan bin Fulan', 'city' => 'Barabai']);
// Nilai pengembalian: nomor ID dari catatan baru

// Perbarui data yang ada
$this->core->mysql('table')->where('age', 50)->save(['name' => 'Fulan bin Fulan', 'city' => 'Barabai']);
// Nilai pengembalian: BENAR jika berhasil atau SALAH jika gagal
```


### UPDATE

Memperbarui catatan jika berhasil akan mengembalikan `TRUE`. Jika tidak, itu akan menjadi `FALSE`.

```php
// Mengubah satu kolom
$this->core->mysql('table')->where('city', 'Barabai')->update('name', 'Fulan');

// Mengubah beberapa kolom
$this->core->mysql('table')->where('city', 'Barabai')->update(['name' => 'Fulan', 'type' => 'Pasien']);
```


### SET

```php
$this->core->mysql('table')->where('age', 65)->set('age', 70)->set('name', 'Fulani Binti Fulan')->update();
```


### DELETE

Penghapusan catatan yang berhasil mengembalikan nomornya.

```php
// Hapus data dengan `id` sama dengan 1
$this->core->mysql('table')->delete(1);

// Penghapusan data dengan kondisi
$this->core->mysql('table')->where('age', 20)->delete();
```


### ORDER BY

Ascending:
```php
$this->core->mysql('table')->asc('created_at')->toArray();
```

Descending:
```php
$this->core->mysql('table')->desc('created_at')->toArray();
```

Kombinasi:
```php
$this->core->mysql('table')->desc('created_at')->asc('id')->toArray();
```


### GROUP BY

```php
$this->core->mysql('table')->group('city')->toArray();
```


### OFFSET, LIMIT

```php
// Ambil 5 catatan mulai dari kesepuluh
$this->core->mysql('table')->offset(10)->limit(5)->toArray();
```


### PDO

Tidak semua kueri dapat dibuat menggunakan metode di atas *(mis. membuat atau menghapus tabel)*, jadi Anda juga dapat menulis kueri menggunakan [PDO](http://php.net/manual/en/book.pdo.php):

```php
$this->core->mysql()->pdo()->exec("DROP TABLE `example`");
```

Sistem Template
---------------

Mengoperasikan sistem template itu mudah dan terutama didasarkan pada dua metode. Satu memungkinkan menetapkan variabel, sementara yang lain mengembalikan kode yang dikompilasi. Dalam beberapa kondisi, dua metode lainnya berguna.

### set

```php
void set(string $name, mixed $value)
```

Menetapkan nilai atau fungsi ke variabel yang dapat digunakan dalam tampilan.

#### Argumen
+ `name` — nama variabel
+ `value` — nilai variabel atau fungsi anonim

#### Contoh
```php
// Diletakkan pada array
$foo = ['bar', 'baz', 'qux'];
$this->tpl->set('foo', $foo);

// Diletakkan pada fungsi anonim
$this->tpl->set('bar', function() {
   return ['baz' => 'qux'];
})
```


### draw

```php
string draw(string $file)
```

Mengembalikan kode tampilan terkompilasi yang sebelumnya menggunakan tag sistem template.

#### Argumen
+ `file` — path berkas

#### Nilai return
Sebuah string, yaitu tampilan yang dikompilasi.

#### Contoh
```php
$this->tpl->draw(MODULES.'/pasien/view/admin/manage.html');
```


### noParse

```php
string noParse(string $text)
```

Melindungi dari kompilasi tag sistem template.

#### Argumen
+ `text` — string dibiarkan tidak berubah

#### Contoh
```php
$this->tpl->noParse('Letakkan tag ini di situs web: {$contact.form}');
```


### noParse_array

```php
array noParse_array(array $array)
```

Melindungi dari kompilasi tag sistem template di dalam array.

#### Arguments
+ `array` — array dibiarkan tidak berubah

#### Example
```php
$this->tpl->noParse_array(['{$no}', '{$changes}']);
```
