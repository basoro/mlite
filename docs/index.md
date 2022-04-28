Instruksi Umum
==============

mLITE adalah sistem manajemen kesehatan yang sederhana, ringan dan cepat. Pertama kali dirilis pada Mei 2019. Versi gratis dari aplikasi ini dibagikan di bawah [lisensi](/lisensi) yang mengharuskan meninggalkan informasi tentang penulis dan tautan balik. Dengan dokumentasi ini Anda akan belajar cara menginstal, mengkonfigurasi, dan membuat modul dan tema Anda sendiri.

Dokumentasi dibagi menjadi beberapa bagian. Yang pertama adalah untuk instruksi umum, yang kedua untuk pengembang forntend, dan yang terakhir untuk pengembang backend.


Persyaratan
-----------

Persyaratan sistem untuk mLITE sangat sederhana, jadi setiap server modern sudah mencukupi.

+ Apache 2.2+ with `mod_rewrite`
+ PHP version 5.6+
+ Access to SQLite

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
+ inc/data/
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

The most important file for each module. It contains basic information and instructions during installation and uninstallation.

```php
<?php

    return [
        'name'          =>  'Example',
        'description'   =>  'Lorem ipsum....',
        'author'        =>  'Robin',
        'version'       =>  '1.0',
        'compatibility' =>  '1.3.*',                    // Compatibility with mLITE version
        'icon'          =>  'bolt',

        'pages'         =>  ['Example' => 'example'],   // Registration as a page (optional)

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

A list of icons that you can use in this file is available at [fontawesome.io](http://fontawesome.io/icons/). Be sure not to enter the icon name with the `fa-` prefix.

Registering a module as a page allows you to freely use the routing and select it as a homepage.


### Admin file

The contents of this file will be launched in the admin panel.

```php
<?php
    namespace Inc\Modules\Example;

    use Inc\Core\AdminModule;

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

In the `navigation` method, include array with the subpages of the module. Each page should be assigned a method *(without a prefix)*. Items of this array will be displayed in the administration panel menu.

Methods can also accept arguments that are passed through the URL. For example, after entering the `/example/foo/abc` address, the `getFoo` method will return *"Foo abc!"*.

As you can see in the above listing, each method representing the subpage of the module should have a prefix specifying the type of the request. In most cases we will use the `getFoo` nomenclature, and the `postFoo` form form submission. If the method supports all types, it should precede the `any` prefix *(for example, `anyFoo`)*. This is important because pages without prefix will not be handled. Supported methods are translated by dynamic routing as follows:

+ `getFoo()` — as `/example/foo` for a GET request
+ `getFoo($parm)` — as `/example/foo/abc` for a GET request
+ `postBar()` — as `example/bar` for POST requests *(form submission)*
+ `anyFoo()` — as `/example/foo` for each request type

### Site file

This file is responsible for the portion seen by visitors of the website. If the module is quite large, good practice is to register it as a page and apply routing.

```php
<?php

    namespace Inc\Modules\Example;

    use Inc\Core\SiteModule

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

In the above example, a new `bar` template variable has been created which, by calling the `_foo()` method in the module initializer, can be used in the theme files as `{$bar}`. In addition, the `routes()` method has created a `/example` subroutine that points to the `mySite()` method call. If you go to `http://example.com/example`, you will call the `mySite()` method.

### Language files

The module can contain language variables that can be used in classes and views. Language files have a `.ini` extension and are located in the` lang` directory of the module.
For example, if you want to add a language file containing English expressions for the administrative part of the `Example` module, you should create a new file in the `inc/modules/example/lang/admin /en_english.ini` path.
The content should resemble the following listing:

```
full_name           = "Firstname and surname"
email               = "E-mail"
subject             = "Subject"
message             = "Message"
send                = "Send"
send_success        = "Mail successfully sent. I will contact you soon."
send_failure        = "Unable to send a message. Probably mail() function is disabled on the server."
wrong_email         = "Submited e-mail address is incorrect."
empty_inputs        = "Fill all required fields to send a message."
antiflood           = "You have to wait a while before you will send another message."
```

Use the `$this->lang('subject')` construction in the module class and `{$lang.example.subject}` in view. For a class, we can leave the second parameter of the `lang` method, which is the name of the module.


Routing
-------

Routing is the process of processing a received request address and deciding what should be run or displayed. It's supposed to call the appropriate method/function based on the URL of the page. You must use routing inside public `routes()` method.

```php
void route(string $pattern, mixed $callback)
```

The first parameter of the `route` method is a regular expression. Some of the expressions have already been defined:

+ `:any` — any string
+ `:int` — integers
+ `:str` — string that is a slug

The second parameter is a method name or an anonymous function that passes any number of arguments defined in a regular expression.

#### Example
```php
public function routes()
{
    // URL: http://example.com/blog

    // - by calling the method inside the module:
    $this->route('blog', 'importAllPosts');

    // - by calling an anonymous function:
    $this->route('blog', function() {
        $this->importAllPosts();
    });

    // URL: http://example.com/blog/2
    $this->route('blog/(:int)', function($page) {
        $this->importAllPosts($page);
    });

    // URL: http://example.com/blog/post/lorem-ipsum
    $this->route('blog/post/(:str)', function($slug) {
        $this->importPost($slug);
    });

    // URL: http://example.com/blog/post/lorem-ipsum/4
    $this->route('blog/post/(:str)/(:int)', function($slug, $page) {
        $this->importPost($slug, $page);
    });
}
```


Methods
-------

Modules have special facades that facilitate access to the methods inside the core. This allows you to shorten the calls of `$this->core->foo->bar`.

### db

```php
void db([string $table])
```

Allows you to operate on a database. Details are described in the core section.

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

Returns a compiled view code that has previously used template system tags. It also allows you to define variables by replacing the `set()` method.

#### Arguments
+ `file` — filename with a view inside the module or path to a file outside of it
+ `variables` — an array of variable definitions that can be used as tags *(optional)*

#### Example
```php
// Compilation of the view inside the module
$this->draw('form.html', ['form' => $this->formFields]);

// Compilation of the view outside the module
$this->draw('../path/to/view.html', ['foo' => 'bar']);
```


### lang

```php
string lang(string $key [, string $module])
```

Returns the contents of the language array key from the current module or indicated by the second argument.

#### Arguments
+ `key` — the name of the language array key
+ `module` — the name of the module from which you want to select the key *(optional)*

#### Example
```php
// Reference to local translation
$this->lang('foo');                 // $this->core->lang['module-name']['foo'];

// Reference to general translation
$this->lang('cancel', 'general');   // $this->core->lang['general']['cancel'];

// Reference to the translation of "pages" module
$this->lang('slug', 'pages')        // $this->core->lang['pages']['slug'];
```


### notify

```php
void notify(string $type, string $text [, mixed $args [, mixed $... ]])
```

It allows you to call the notification to the user.

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

Gets or sets the value of the module settings.

#### Arguments
+ `module` — module name and optionally field separated by a period
+ `field` — module field name *(optional)*
+ `value` — the value to which module field will be changed *(optional)*

#### Example
```php
// Select the "desc" field from the "blog" module
$this->settings('blog.desc');    // $this->core->getSettings('blog', 'desc');

// Select the "desc" field from the "blog" module
$this->settings('blog', 'desc'); // $this->core->getSettings('blog', 'desc');

// Set the content of the "desc" field from the "blog" module
$this->settings('blog', 'desc', 'Lorem ipsum...');
```

### setTemplate

```php
void setTemplate(string $file)
```

Allows you to change the template file on the front. This method works only in the `Site` class.

#### Arguments
+ `file` — The name of the template file

#### Example
```php
$this->setTemplate('index.html'); // $this->core->template = 'index.html';
```


Core
====

This is the kernel/engine of mLITE, the most important part that is responsible for all its basic tasks. The core contains many definitions of constants, functions, and methods that you can use when writing modules.

Constants
---------

All definitions of constants are described in the first part of this documentation. To use them in a PHP file just call their names. Constants are particularly useful when building URLs and file paths.

#### Example
```php
echo MODULES.'/contact/view/form.html';

```


Functions
---------

mLITE has several built-in helper functions that facilitate the creation of modules.

### domain

```php
string domain([bool $with_protocol = true])
```

Returns the domain name with http(s) or without.

#### Arguments
+ `with_protocol` — it decides whether the address will be returned with or without protocol

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

Checks whether the array contains empty elements. It is useful while validating forms.

#### Arguments
+ `keys` — list of array items that the function has to check
+ `array` — source array

#### Return value
Returns `TRUE` when at least one item is empty. `FALSE` when all elements are completed.

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

Returns the current URL.

#### Arguments
+ `query` — it decides whether the address will be returned with or without query

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

Translates text in non-lingual characters, dashes to spaces, and removes special characters. Used to create slashes in URLs and variable names in the template system.

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

Recursive function that removes the directory and all its contents.

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

Returns the data passed to the session when using `redirect()`.

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

Replaces special characters from array elements into HTML entities.

#### Arguments
+ `array` — the array that will be converted

#### Return value
Returns the converted text.

#### Example
```php
$_POST = htmlspecialchars_array($_POST);
```


### isset_or

```php
mixed isset_or(mixed $var [, mixed $alternate = null ])
```

Replaces an empty variable with an alternate value.

#### Arguments
+ `var` — variable
+ `alternate` — replacement value of the variable *(optional)*

#### Return value
Returns an alternative value.

#### Example
```php
$foo = isset_or($_GET['bar'], 'baz');
```


### parseURL
```php
mixed parseURL([ int $key = null ])
```

Parses the current URL of the script.

#### Arguments
+ `key` — URL parameter number *(optional)*

#### Return value
An array or its individual element.

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

Redirect to the specified URL. It allows you to save data from the array to a session. It is useful to memorize unsaved data from forms.

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

Creates an absolute URL. The admin panel automatically adds a token.

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

In addition to functions, there are several important methods that speed up the process of creating new system functionality.

### addCSS

```php
void addCSS(string $path)
```

Imports the CSS file in the theme header.

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

Imports the JS file in the header or footer of the theme.

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
$this->core->append('<meta name="author" content="Bruce Wayne">', 'header');
```


### getModuleInfo

```php
array getModuleInfo(string $dir)
```

Returns module information. This method works only in the `Admin` class.

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

Gets the value of the module settings. By default these are the main mLITE settings.

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

Returns information about the logged in user or the user with the given ID. This method works only in the `Admin` class.

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

The database used in mLITE is SQLite version 3. For its use CMS uses a simple class that makes it easy to build queries. You do not need to know SQL to be able to operate it.

In addition, we recommend [phpLiteAdmin](https://phpliteadmin.com) tool for database management. This is a one-file PHP script similar to *phpMyAdmin*, where you can administer mLITE tables. This will allow you to familiarize yourself with the structure of existing tables.
The database file is located in `inc/data/database.sdb`.


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

The `save` method can add a new record to the table or update an existing one when it has a condition. When you add a new record, identification number will be returned.

```php
// Add a new record
$id = $this->core->db('table')->save(['name' => 'James Gordon', 'city' => 'Gotham']);
// Return value: ID number of new record

// Update an existing record
$this->core->db('table')->where('age', 50)->save(['name' => 'James Gordon', 'city' => 'Gotham']);
// Return value: TRUE on success or FALSE on failure
```


### UPDATE

Updating records in case of success will return `TRUE`. Otherwise it will be `FALSE`.

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

Successful deletion of records returns their number.

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

Not all queries can be created using the above methods *(e.g. creating or deleting a table)*, so you can also write queries using [PDO](http://php.net/manual/en/book.pdo.php):

```php
$this->core->db()->pdo()->exec("DROP TABLE `example`");
```


Template system
---------------

Operating the template system is easy and is based primarily on two methods. One allows assigning variables, while the other returns the compiled code. In exceptional situations, the other two methods are useful.

### set

```php
void set(string $name, mixed $value)
```

Assigns a value or function to a variable that can be used in views.

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

Returns a compiled view code that has previously used template system tags.

#### Arguments
+ `file` — file path

#### Return value
A string, i.e. a compiled view.

#### Example
```php
$this->tpl->draw(MODULES.'/galleries/view/admin/manage.html');
```


### noParse

```php
string noParse(string $text)
```

Protects against compiling template system tags.

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

Protects against compiling template system tags inside the array.

#### Arguments
+ `array` — array to be left unchanged

#### Example
```php
$this->tpl->noParse_array(['{$no}', '{$changes}']);
```
