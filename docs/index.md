Instruksi Umum
==============

mLITE adalah sistem manajemen kesehatan yang sederhana, ringan dan cepat. Pertama kali dirilis pada Mei 2019. Versi gratis dari aplikasi ini dibagikan di bawah [lisensi](/lisensi) yang mengharuskan meninggalkan informasi tentang penulis dan tautan balik. Dengan dokumentasi ini Anda akan belajar cara menginstal, mengkonfigurasi, dan membuat modul dan tema Anda sendiri.

Dokumentasi dibagi menjadi beberapa bagian. Yang pertama adalah untuk instruksi umum, yang kedua untuk pengembang forntend, dan yang terakhir untuk pengembang backend.


Persyaratan
-----------

Persyaratan sistem untuk mLITE sangat sederhana, jadi setiap server modern sudah mencukupi.

+ Apache 2.2+ or Nginx with `mod_rewrite`
+ PHP version 5.6+
+ MySQL Server 5.5+
+ Access to MySQL and SQLite

Konfigurasi PHP harus memiliki ekstensi berikut:

+ dom
+ gd
+ mbstring
+ pdo
+ zip
+ cURL


Instalasi
---------

Pertama unduh versi terbaru [mLITE](https://github.com/basoro/khanza-lite/tree/mlite).

Ekstrak semua file dari paket terkompresi dan kemudian transfer ke direktori lokal atau server. Untuk server cloud, sambungkan melalui klien (S)FTP, seperti program [FileZilla](https://filezilla-project.org) gratis. Biasanya, file harus diunggah ke `www`, `htdocs` atau `public_html`.

**Peringatan!** Pastikan file `.htaccess` juga ada di server. Tanpa itu mLITE tidak akan berfungsi.

Beberapa server mungkin memerlukan izin tambahan `chmod 777` untuk direktori dan file berikut:

+ tmp/
+ uploads/
+ admin/tmp/
+ systems/data/
+ webapps/

Buka browser Anda dan arahkan ke alamat tempat file mLITE berada. Tunggu beberapa saat sampai proses instalasi dibelakang layar selesai.

Untuk masuk ke panel administrasi, tambahkan `/admin/` di akhir URL. **Login dan kata sandi awal adalah *"admin"*.** Ini harus diubah segera setelah login untuk alasan keamanan. Kami juga merekomendasikan mengganti nama direktori dengan panel administrasi. *(Anda perlu mengubah nilai konstanta dalam file definisi di config.php)*.


Konfigurasi
-------------

mLITE dapat dikonfigurasi dengan mengedit pengaturan di panel administrasi dan melalui file definisi. Namun, kami tidak menyarankan mengubah konfigurasi dalam file jika Anda adalah orang yang tidak berpengalaman.

### Panel Administrasi
Untuk mengubah konfigurasi dasar di panel admin, pilih tab `Pengaturan`. Anda dapat memasukkan nama instansi, deskripsi atau kata kunci di tag meta, serta di tempat lain di template default, seperti di header. Anda juga dapat mengubah default tampilan beranda.

Anda akan mengubah konfigurasi modul yang tersisa di tab yang sesuai dengan namanya.

### Defines file
More advanced things you can change in the `config.php` file, which contains definitions of constant variables.

+ `ADMIN` — the directory name that contains the administration panel
+ `MULTI_APP` — mode multiple app with storing setting to SqLITE
+ `THEMES` — path to the directory containing the themes
+ `MODULES` — path to the directory containing the modules
+ `UPLOADS` — path to the directory containing the uploaded files
+ `FILE_LOCK` — lock the ability to edit files through the administration panel
+ `BASIC_MODULES` — list of basic modules that can not be removed
+ `DEV_MODE` — developer mode, where PHP errors and notes are displayed


Update
------

Jika Anda ingin tetap up to date dengan semua berita terbaru, perbaikan bug dan masalah keamanan, Anda harus secara teratur memeriksa pembaruan mLITE. Anda dapat melakukannya di tab `Pengaturan -> Pembaruan`. Sistem akan memeriksa versi baru skrip dan secara otomatis mengunduh paket baru dari server kami dan memperbarui file dan modul inti.

Jika terjadi komplikasi, Anda dapat menggunakan mode manual. Untuk melakukannya, unduh mLITE versi terbaru, unggah ke direktori aplikasi utama, lalu tambahkan parameter `&manual` di akhir URL bookmark pembaruan. CMS akan mendeteksi paket zip dan ketika Anda mengklik tombol update, proses mengekstrak dan menimpa file akan dilakukan.

Sebelum setiap pembaruan, mLITE membuat cadangan. Anda akan menemukannya di direktori skrip, di folder `backup/`. Jika pembaruan gagal, Anda dapat memulihkannya kapan saja.


Themes
======

Struktur
--------

Struktur tema dalam mLITE sangat sederhana. Cukup buat folder baru di direktori `themes/` dan file-file berikut:

+ `index.html` — template default untuk subhalaman
+ `manifest.json` — informasi tema
+ `preview.png` — tangkapan layar yang menunjukkan tema *(opsional)*

Setiap subhalaman dapat menggunakan template lain, jadi selain file yang disebutkan, Anda juga dapat membuat yang lain, misalnya `xyz.html`. Pemilihan template tersedia di panel admin saat membuat halaman. Tidak ada aturan tentang file CSS dan JS. Ada kebebasan penuh.

Di folder tema Anda juga dapat membuat tampilan modul Anda sendiri. Untuk melakukan ini, Anda perlu membuat direktori `plugins/plugin_name` dan file `*.html` dengan nama yang sesuai dengan nama tampilan asli. Misalnya, tampilan formulir kontak harus dimuat dalam jalur berikut: `themes/theme_name/plugins/contact/form.html`. mLITE secara otomatis mendeteksi tampilan baru dan menggunakannya sebagai ganti tampilan default modul.

Tag template
-------------

mLITE menggunakan sistem templat sederhana yang menyertakan tag berikut:

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

### Include template files
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

### PHP code
```php
Today&grave;s date: {?= date('Y-m-d') ?}
```
Jika Anda membiarkan karakter `=`, kode hanya akan dijalankan dan tidak ada yang ditampilkan. Ini memungkinkan Anda, misalnya, untuk mendefinisikan variabel baru dalam template:
```php
{? $foo = 5 ?}
```

### Disable parsing
```
{noparse}Use the {$ contact.form} tag to display contact form.{/noparse}
```
Tag apa pun di dalam ekspresi *noparse* akan tetap tidak berubah.

### Comments
```
{* this is a comment *}
```
Komentar tidak terlihat di file sumber setelah mengkompilasi template.

Variabel sistem
----------------
mLITE, seperti pluginnya, menyediakan banyak variabel *(biasanya array)* yang berfungsi untuk menampilkan setiap elemen halaman. Berikut adalah yang paling penting:

+ `{$settings.pole}` — elemen larik yang berisi nilai bidang pengaturan mLITE yang diberikan
+ `{$settings.moduł.pole}` — elemen larik yang berisi nilai bidang pengaturan modul
+ `{$mlite.path}` — menyimpan jalur tempat sistem berada
+ `{$mlite.lang}` — menampilkan bahasa yang sedang digunakan
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

File keempat tetapi opsional adalah `ReadMe.md` yang seharusnya berisi informasi tambahan untuk pengguna mendatang di [Penurunan harga](https://en.wikipedia.org/wiki/Markdown), mis. cara menggunakan plugin.

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
        'compatibility' =>  '2022*',                    // Compatibility with mLITE version
        'icon'          =>  'bolt',

        'pages'         =>  ['Contoh' => 'contoh'],   // Registration as a page (optional)

        'install'       =>  function() use($core)       // Install commands
        {
            // lorem ipsum...
        },
        'uninstall'     =>  function() use($core)       // Uninstall commands
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
            // Procedures invoked at module initialization
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

Dalam metode `navigasi`, sertakan larik dengan subhalaman modul. Setiap halaman harus diberi metode *(tanpa awalan)*. Item larik ini akan ditampilkan di menu panel administrasi.

Metode juga dapat menerima argumen yang diteruskan melalui URL. Misalnya, setelah memasukkan alamat `/example/foo/abc`, metode `getFoo` akan mengembalikan *"Foo abc!"*.

Seperti yang Anda lihat dalam daftar di atas, setiap metode yang mewakili subhalaman modul harus memiliki awalan yang menentukan jenis permintaan. Dalam kebanyakan kasus, kita akan menggunakan nomenklatur `getFoo`, dan pengiriman formulir `postFoo`. Jika metode mendukung semua jenis, metode tersebut harus mendahului awalan `any` *(misalnya, `anyFoo`)*. Ini penting karena halaman tanpa awalan tidak akan ditangani. Metode yang didukung diterjemahkan oleh perutean dinamis sebagai berikut:

+ `getFoo()` — sebagai `/example/foo` untuk permintaan GET
+ `getFoo($parm)` — sebagai `/example/foo/abc` untuk permintaan GET
+ `postBar()` — sebagai `example/bar` untuk permintaan POST *(pengiriman formulir)*
+ `anyFoo()` — sebagai `/example/foo` untuk setiap jenis permintaan

### Site file

File ini bertanggung jawab atas tampilah yang dilihat oleh pengguna. Jika plugins cukup komplek, yang baik adalah mendaftarkannya sebagai halaman dan menerapkan perutean.

```php
<?php

    namespace Plugins\Example;

    use Systems\SiteModule

    class Site extends SiteModule
    {
        public function init()
        {
            $this->_foo();
        }

        public function routes()
        {
            $this->route('example', 'mySite');
        }

        public function mySite()
        {
            $page = [
                'title' => 'Sample title..',
                'desc' => 'Site description',
                'content' => 'Lorem ipsum dolor...'
            ];

            $this->setTemplate('index.html');
            $this->tpl->set('page', $page);
        }

        private function _foo()
        {            
            $this->tpl->set('bar', 'Why So Serious?');
        }
    }
```

Dalam contoh di atas, variabel template `bar` baru telah dibuat yang, dengan memanggil metode `_foo()` dalam penginisialisasi plugin, dapat digunakan dalam file tema sebagai `{$bar}`. Selain itu, metode `routes()` telah membuat subrutin `/example` yang menunjuk ke pemanggilan metode `mySite()`. Jika Anda membuka `http://example.com/example`, Anda akan memanggil metode `mySite()`.

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

#### Example
```php
public function routes()
{
    // URL: http://example.com/news

    // - by calling the method inside the module:
    $this->route('news', 'importAllPosts');

    // - by calling an anonymous function:
    $this->route('news', function() {
        $this->importAllPosts();
    });

    // URL: http://example.com/news/2
    $this->route('news/(:int)', function($page) {
        $this->importAllPosts($page);
    });

    // URL: http://example.com/news/post/lorem-ipsum
    $this->route('news/post/(:str)', function($slug) {
        $this->importPost($slug);
    });

    // URL: http://example.com/news/post/lorem-ipsum/4
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
void db([string $table])
```

Memungkinkan Anda untuk beroperasi pada database. Rincian dijelaskan di bagian inti.

#### Arguments
+ `table` — Database table name *(optional)*

#### Example
```php
$this->db('table')->where('age', 20)->delete();
```


### draw

```php
string draw(string $file [, array $variables])
```

Mengembalikan kode tampilan terkompilasi yang sebelumnya menggunakan tag sistem template. Ini juga memungkinkan Anda untuk mendefinisikan variabel dengan mengganti metode `set()`.

#### Argumen
+ `file` — nama file dengan tampilan di dalam plugin atau ke file di luarnya
+ `variabel` — definisi variabel yang dapat digunakan sebagai tag *(opsional)*

#### Example
```php
// Compilation of the view inside the module
$this->draw('form.html', ['form' => $this->formFields]);

// Compilation of the view outside the module
$this->draw('../path/to/view.html', ['foo' => 'bar']);
```

### notify

```php
void notify(string $type, string $text [, mixed $args [, mixed $... ]])
```

Ini memungkinkan Anda untuk memanggil pemberitahuan kepada pengguna.

#### Arguments
+ `type` — type of notification: *success* or *failure*
+ `text` — notyfication content
+ `args` — additional arguments *(optional)*

#### Example
```php
$foo = 'Bar';
$this->notify('success', 'This is %s!', $foo); // $this->core->setNotify('success', 'This is %s!', $foo);

```


### settings

```php
mixed settings(string $module [, string $field [, string $value]])
```

Mendapat atau menetapkan nilai pengaturan modul.

#### Arguments
+ `module` — module name and optionally field separated by a period
+ `field` — module field name *(optional)*
+ `value` — the value to which module field will be changed *(optional)*

#### Example
```php
// Select the "desc" field from the "website" plugin
$this->settings('website.desc');    // $this->core->getSettings('website', 'desc');

// Select the "desc" field from the "website" plugin
$this->settings('website', 'desc'); // $this->core->getSettings('website', 'desc');

// Set the content of the "desc" field from the "website" plugin
$this->settings('website', 'desc', 'Lorem ipsum...');
```

### setTemplate

```php
void setTemplate(string $file)
```

Memungkinkan Anda untuk mengubah file template di bagian depan. Metode ini hanya berfungsi di kelas `Site`.

#### Arguments
+ `file` — The name of the template file

#### Example
```php
$this->setTemplate('index.html'); // $this->core->template = 'index.html';
```


Systems
====

Ini adalah inti dari mLITE, bagian terpenting yang bertanggung jawab atas semua tugas dasarnya. Inti berisi banyak definisi konstanta, fungsi, dan metode yang dapat Anda gunakan saat menulis plugin.

Constants
---------

Semua definisi konstanta dijelaskan di bagian pertama dokumentasi ini. Untuk menggunakannya dalam file PHP, panggil saja namanya. Konstanta sangat berguna saat membuat URL.

#### Example
```php
echo MODULES.'/contact/view/form.html';

```


Functions
---------

mLITE memiliki beberapa fungsi pembantu bawaan yang memfasilitasi pembuatan plugin.

### domain

```php
string domain([bool $with_protocol = true])
```

Mengembalikan nama domain dengan http (s) atau tanpa.

#### Arguments
+ `with_protocol` — itu memutuskan apakah alamat akan dikembalikan dengan atau tanpa protokol

#### Return value
String with the domain name.

#### Example
```php
echo domain(false);
// Result: example.com
```


### checkEmptyFields

```php
bool checkEmptyFields(array $keys, array $array)
```

Memeriksa apakah array berisi elemen kosong. Ini berguna saat memvalidasi formulir.

#### Arguments
+ `keys` — list of array items that the function has to check
+ `array` — source array

#### Return value
Mengembalikan `TRUE` ketika setidaknya satu item kosong. `FALSE` ketika semua elemen selesai.

#### Example
```php
if(checkEmptyFields(['name', 'phone', 'email'], $_POST) {
    echo 'Fill in all fields!';
}
```


### currentURL

```php
string currentURL([bool $query = false])
```

Mengembalikan URL saat ini.

#### Arguments
+ `query` — itu memutuskan apakah alamat akan dikembalikan dengan atau tanpa permintaan

#### Example
```php
echo currentURL();
// Result: http://example.com/contact

echo currentURL(true);
// Result: http://example.com/contact?foo=bar
```


### createSlug

```php
string createSlug(string $text)
```

Menerjemahkan teks dalam karakter non-bahasa, tanda hubung ke spasi, dan menghapus karakter khusus. Digunakan untuk membuat garis miring di URL dan nama variabel dalam sistem template.

#### Arguments
+ `text` — text to convert

#### Return value
Returns the text in slug form.

#### Example
```php
echo createSlug('To be, or not to be, that is the question!');
// Result: to-be-or-not-to-be-that-is-the-question
```


### deleteDir

```php
bool deleteDir(string $path)
```

Fungsi rekursif yang menghapus direktori dan semua isinya.

#### Arguments
+ `path` — directory path

#### Return value
Returns `TRUE` for success or `FALSE` for failure.

#### Example
```php
deleteDir('foo/bar');
```


### getRedirectData
```php
mixed getRedirectData()
```

Mengembalikan data yang diteruskan ke sesi saat menggunakan `redirect()`.

#### Return value
An array or `null`.

#### Example
```php
$postData = getRedirectData();
```


### htmlspecialchars_array

```php
string htmlspecialchars_array(array $array)
```

Mengganti karakter khusus dari elemen array menjadi entitas HTML.

#### Arguments
+ `array` — the array that will be converted

#### Return value
Mengembalikan teks yang dikonversi.

#### Example
```php
$_POST = htmlspecialchars_array($_POST);
```


### isset_or

```php
mixed isset_or(mixed $var [, mixed $alternate = null ])
```

Menggantikan variabel kosong dengan nilai alternatif.

#### Arguments
+ `var` — variable
+ `alternate` — replacement value of the variable *(optional)*

#### Return value
Mengembalikan nilai alternatif.

#### Example
```php
$foo = isset_or($_GET['bar'], 'baz');
```


### parseURL
```php
mixed parseURL([ int $key = null ])
```

Parsing URL skrip saat ini.

#### Arguments
+ `key` — URL parameter number *(optional)*

#### Return value
Array atau elemen individualnya.

#### Example
```php
// URL: http://example.com/foo/bar/4

var_dump(parseURL())
// Result:
// array(3) {
//   [0] =>
//   string(3) "foo"
//   [1] =>
//   string(3) "bar"
//   [2] =>
//   int(4)
// }

echo parseURL(2);
// Result: "bar"
```


### redirect

```php
void redirect(string $url [, array $data = [] ])
```

Arahkan ulang ke URL yang ditentukan. Ini memungkinkan Anda untuk menyimpan data dari array ke sesi. Berguna untuk mengingat data yang belum disimpan dari formulir.

#### Arguments
+ `url` — address to redirect
+ `data` — an array that will be passed to the session *(optional)*

#### Example
```php
redirect('http://www.example.com/');

// Save the array to session:
redirect('http://www.example.com/', $_POST);
```


### url
```php
string url([ mixed $data = null ])
```

Membuat URL absolut. Panel admin secara otomatis menambahkan token.

#### Arguments
+ `data` — string or array

#### Return value
Absolute URL.

#### Example
```php
echo url();
// Result: http://example.com

echo url('foo/bar')
// Result: http://example.com/foo/bar

echo url('admin/foo/bar');
// Result: http://example.com/admin/foo/bar?t=[token]

echo url(['admin', 'foo', 'bar']);
// Result: http://example.com/admin/foo/bar?t=[token]
```


Methods
-------

Selain fungsi, ada beberapa metode penting yang mempercepat proses pembuatan fungsionalitas sistem baru.

### addCSS

```php
void addCSS(string $path)
```

Mengimpor file CSS di header tema.

#### Arguments
+ `path` — URL to file

#### Example
```php
$this->core->addCSS('http://example.com/style.css');
// Result: <link rel="stylesheet" href="http://example.com/style.css" />
```


### addJS

```php
void addJS(string $path [, string $location = 'header'])
```

Mengimpor file JS di header atau footer tema.

#### Arguments
+ `path` — URL to file
+ `location` — *header* or *footer* *(optional)*

#### Example
```php
$this->core->addJS('http://example.com/script.js');
// Result: <script src="http://example.com/script.js"></script>
```


### append

```php
void append(string $string, string $location)
```

Adds a string to the header or footer.

#### Arguments
+ `string` — character string
+ `location` — *header* or *footer*

#### Example
```php
$this->core->append('<meta name="author" content="Basoro">', 'header');
```


### getModuleInfo

```php
array getModuleInfo(string $dir)
```

Mengembalikan informasi modul. Metode ini hanya berfungsi di kelas `Admin`.

#### Arguments
+ `name` — module directory name

#### Return value
Array with informations.

#### Example
```php
$foo = $this->core->getModuleInfo('contact');
```


### getSettings

```php
mixed getSettings([string $module = 'settings', string $field = null])
```

Mendapatkan nilai dari pengaturan modul. Secara default ini adalah pengaturan mLITE utama.

#### Arguments
+ `module` — module name *(optional)*
+ `field` — field with definition of setting *(optional)*

#### Return value
Array or string.

#### Example
```php
echo $this->core->getSettings('blog', 'title');
```


### getUserInfo

```php
string getUserInfo(string $field [, int $id ])
```

Mengembalikan informasi tentang pengguna yang masuk atau pengguna dengan ID yang diberikan. Metode ini hanya berfungsi di kelas `Admin`.

#### Arguments
+ `field` — field name in the database
+ `id` — ID number *(opcjonalne)*

#### Return value
The string of the selected field.

#### Example
```php
// The currently logged in user
$foo = $this->core->getUserInfo('username');

// User with given ID
$foo = $this->core->getUserInfo('username', 1);
```


### setNotify

```php
void setNotify(string $type, string $text [, mixed $args [, mixed $... ]])
```

Generates notification.

#### Arguments
+ `type` — type of notification: *success* or *failure*
+ `text` — notyfication content
+ `args` — additional arguments *(optional)*

#### Example
```php
$foo = 'Bar';
$this->core->setNotify('success', 'This is %s!', $foo);
// Result: "This is Bar!"
```


Database
--------

Basis data yang digunakan pada mLITE adalah MySQL dan SQLite versi 3. Untuk penggunaannya mLITE menggunakan class sederhana yang memudahkan untuk membangun query. Anda tidak perlu tahu SQL untuk dapat mengoperasikannya.

### SELECT

Select multiple records:

```php
// JSON
$rows = $this->core->db('table')->toJson();

// Array
$rows = $this->core->db('table')->select('foo')->select('bar')->toArray();

// Object
$rows = $this->core->db('table')->select(['foo', 'b' => 'bar'])->toObject();
```

Select a single record:
```php
// JSON
$row = $this->core->db('table')->oneJson();

// Array
$row = $this->core->db('table')->select('foo')->select('bar')->oneArray();

// Object
$row = $this->core->db('table')->select(['foo', 'b' => 'bar'])->oneObject();
```


### WHERE

Select a record with the specified number in the `id` column:

```php
$row = $this->core->db('table')->oneArray(1);
// or
$row = $this->core->db('table')->oneArray('id', 1);
// or
$row = $this->core->db('table')->where(1)->oneArray();
// or
$row = $this->core->db('table')->where('id', 1)->oneArray();
```

Complex conditions:
```php
// Fetch rows whose column value 'foo' is GREATER than 4
$rows = $this->core->db('table')->where('foo', '>', 4)->toArray();

// Fetch rows whose column value 'foo' is GREATER than 4 and LOWER than 8
$rows = $this->core->db('table')->where('foo', '>', 4)->where('foo', '<', 8)->toArray();
```

OR WHERE:
```php
// Fetch rows whose column value 'foo' is EQUAL 4 or 8
$rows = $this->core->db('table')->where('foo', '=', 4)->orWhere('foo', '=', 8)->toArray();
```

WHERE LIKE:
```php
// Fetch rows whose column 'foo' CONTAINS the string 'bar' OR 'bases'
$rows = $this->core->db('table')->like('foo', '%bar%')->orLike('foo', '%baz%')->toArray();
```

WHERE NOT LIKE:
```php
// Fetch rows whose column 'foo' DOES NOT CONTAIN the string 'bar' OR 'baz'
$rows = $this->core->db('table')->notLike('foo', '%bar%')->orNotLike('foo', '%baz%')->toArray();
```

WHERE IN:
```php
// Fetch rows whose column value 'foo' CONTAINS in array [1,2,3] OR [7,8,9]
$rows = $this->core->db('table')->in('foo', [1,2,3])->orIn('foo', [7,8,9])->toArray();
```

WHERE NOT IN:
```php
// Fetch rows whose column value 'foo' DOES NOT CONTAIN in array [1,2,3] OR [7,8,9]
$rows = $this->core->db('table')->notIn('foo', [1,2,3])->orNotIn('foo', [7,8,9])->toArray();
```

Grouping conditions:
```php
// Fetch rows those column value 'foo' is 1 or 2 AND status is 1
$rows = $this->core->db('table')->where(function($st) {
            $st->where('foo', 1)->orWhere('foo', 2);
        })->where('status', 1)->toArray();
```

Allowed comparison operators: `=`, `>`, `<`, `>=`, `<=`, `<>`, `!=`.


### JOIN

INNER JOIN:
```php
$rows = $this->core->db('table')->join('foo', 'foo.table_id = table.id')->toJson();
```

LEFT JOIN:
```php
$rows = $this->core->db('table')->leftJoin('foo', 'foo.table_id = table.id')->toJson();
```


### HAVING

```php
$rows = $this->core->db('table')->having('COUNT(*)', '>', 5)->toArray();
```

OR HAVING:
```php
$rows = $this->core->db('table')->orHaving('COUNT(*)', '>', 5)->toArray();
```


### INSERT

Metode `save` dapat menambahkan catatan baru ke tabel atau memperbarui yang sudah ada ketika memiliki kondisi. Ketika Anda menambahkan catatan baru, nomor identifikasi akan dikembalikan.

```php
// Add a new record
$id = $this->core->db('table')->save(['name' => 'James Gordon', 'city' => 'Gotham']);
// Return value: ID number of new record

// Update an existing record
$this->core->db('table')->where('age', 50)->save(['name' => 'James Gordon', 'city' => 'Gotham']);
// Return value: TRUE on success or FALSE on failure
```


### UPDATE

Memperbarui catatan jika berhasil akan mengembalikan `TRUE`. Jika tidak, itu akan menjadi `FALSE`.

```php
// Changing one column
$this->core->db('table')->where('city', 'Gotham')->update('name', 'Joker');

// Changing multiple columns
$this->core->db('table')->where('city', 'Gotham')->update(['name' => 'Joker', 'type' => 'Villain']);
```


### SET

```php
$this->core->db('table')->where('age', 65)->set('age', 70)->set('name', 'Alfred Pennyworth')->update();
```


### DELETE

Penghapusan catatan yang berhasil mengembalikan nomornya.

```php
// Delete record with `id` equal to 1
$this->core->db('table')->delete(1);

// Deletion of record with condition
$this->core->db('table')->where('age', 20)->delete();
```


### ORDER BY

Ascending order:
```php
$this->core->db('table')->asc('created_at')->toJson();
```

Descending order:
```php
$this->core->db('table')->desc('created_at')->toJson();
```

Combine order:
```php
$this->core->db('table')->desc('created_at')->asc('id')->toJson();
```


### GROUP BY

```php
$this->core->db('table')->group('city')->toArray();
```


### OFFSET, LIMIT

```php
// Fetch 5 records starting at tenth
$this->core->db('table')->offset(10)->limit(5)->toJson();
```


### PDO

Tidak semua kueri dapat dibuat menggunakan metode di atas *(mis. membuat atau menghapus tabel)*, jadi Anda juga dapat menulis kueri menggunakan [PDO](http://php.net/manual/en/book.pdo.php):

```php
$this->core->db()->pdo()->exec("DROP TABLE `example`");
```


Template system
---------------

Mengoperasikan sistem template itu mudah dan terutama didasarkan pada dua metode. Satu memungkinkan menetapkan variabel, sementara yang lain mengembalikan kode yang dikompilasi. Dalam situasi luar biasa, dua metode lainnya berguna.

### set

```php
void set(string $name, mixed $value)
```

Menetapkan nilai atau fungsi ke variabel yang dapat digunakan dalam tampilan.

#### Arguments
+ `name` — variable name
+ `value` — variable value or anonymous function

#### Example
```php
// Assignment of the array
$foo = ['bar', 'baz', 'qux'];
$this->tpl->set('foo', $foo);

// Assign an anonymous function
$this->tpl->set('bar', function() {
   return ['baz' => 'qux'];
})
```


### draw

```php
string draw(string $file)
```

Mengembalikan kode tampilan terkompilasi yang sebelumnya menggunakan tag sistem template.

#### Arguments
+ `file` — file path

#### Return value
A string, i.e. a compiled view.

#### Example
```php
$this->tpl->draw(MODULES.'/pasien/view/admin/manage.html');
```


### noParse

```php
string noParse(string $text)
```

Melindungi dari kompilasi tag sistem template.

#### Arguments
+ `text` — string to be left unchanged

#### Example
```php
$this->tpl->noParse('Place this tag in website template: {$contact.form}');
```


### noParse_array

```php
array noParse_array(array $array)
```

Melindungi dari kompilasi tag sistem template di dalam larik.

#### Arguments
+ `array` — array to be left unchanged

#### Example
```php
$this->tpl->noParse_array(['{$no}', '{$changes}']);
```
