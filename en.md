General
=======

Batflat is a Polish content management system that is simple, light and fast. It was first released in May 2016. The free version of the application is shared under a [license](/license) that requires leaving information about the authors and backlinks. Batflat works great when creating small websites such as business identity, portfolios, blogs or home pages. With this documentation you will learn how to install, configure and create your own modules and themes.

The documentation is divided into several sections. The first is for general instructions, the second is for web designers, and the last two are for web developers.


Requirements
------------

System requirements for Batflat are modest, so every modern server should suffice.

+ Apache 2.2+ with `mod_rewrite`
+ PHP version 5.6+
+ Access to SQLite

PHP configuration must have the following extensions:

+ dom
+ gd
+ mbstring
+ pdo
+ zip
+ cURL


Installation
------------

First download the latest version of [Batflat](http://feed.sruu.pl/batflat/download/latest).

Extract all files from the compressed package and then transfer them to the local directory or remote server. In the case of a remote server, connect to it via a (S)FTP client, such as the free [FileZilla](https://filezilla-project.org) program. Usually, files should be uploaded to `www`, `htdocs` or `public_html`.

**Warning!** Make sure the `.htaccess` file is also on the server. Without it the CMS will not work.

Some servers may require additional permissions `chmod 777` for the following directories and files:

+ tmp/
+ uploads/
+ admin/tmp/
+ inc/data/
+ inc/data/database.sdb

Open your browser and navigate to the address where the Batflat's files are located. You should see a default template with sample content.

To go to the administration panel, add `/admin/` at the end of the URL. **The initial login and password are *"admin"*.** It should be changed right after login for security reasons. We also recommend rename the directory with the administration panel. *(you need to then change the constant value in the defines file)*.


Configuration
-------------

CMS can be configured by editing the settings in the administration panel and through the definition file. However, we do not recommend changing the configuration in the file if you are an inexperienced person.

### Administration panel
To change the basic configuration in the admin panel, select the `Settings` tab. You can enter a page name, description or keywords in the meta tags, as well as elsewhere in the default template, such as in the header. You can also change the homepage, default language *(separately for the website and the panel)*, define the footer content, and choose the editor *(HTML or WYSIWYG)* that will be available when editing subpages and blog posts.

You will change the configuration of the remaining modules in the tabs corresponding to their names.

### Defines file
More advanced things you can change in the `inc/core/defines.php` file, which contains definitions of constant variables. 

+ `ADMIN` — the directory name that contains the administration panel
+ `THEMES` — path to the directory containing the themes
+ `MODULES` — path to the directory containing the modules
+ `UPLOADS` — path to the directory containing the uploaded files
+ `FILE_LOCK` — lock the ability to edit files through the administration panel
+ `BASIC_MODULES` — list of basic modules that can not be removed
+ `HTML_BEAUTY` — nice HTML formatting after parsing
+ `DEV_MODE` — developer mode, where PHP errors and notes are displayed


Update
------

If you want to keep up to date with all the latest news, bug fixes and security issues, you should regularly check for Batflat updates. You can do this in the `Settings -> Updates` tab. The system will check for a new version of the script and automatically download a new package from our server and update the core files and modules.

In case of complications you can use manual mode. To do this, download the latest version of Batflat, upload it to the main application directory, and then add the `&manual` parameter to the end of the update's bookmark URL. The CMS should detect a zipped package and when you click on the update button, the process of extracting and overwriting the files will be performed.

Before each update, Batflat creates a backup. You will find it in the script directory, in the `backup/` folder. If an update has failed, you can restore it at any time.


Themes
======

Structure
---------

The structure of themes in Batflat is very simple. Just create a new folder in the `themes/` directory and the following files:

+ `index.html` — default template for subpages
+ `manifest.json` — theme informations
+ `preview.png` — screenshot showing the theme *(optional)*

Each subpage can use another template, so besides the mentioned file, you can also create another, eg `xyz.html`. Template selection is available in the admin panel while creating a page. There are no rules about CSS and JS files. There is full freedom.

In the theme folder you can also create your own module views. To do this, you need to create a directory `modules/module_name` and `*.html` files with names corresponding to the names of the original views. For example, the view of the contact form should be contained in the following path: `themes/theme_name/modules/contact/form.html`. Batflat automatically detects a new view and uses it instead of the module default view.


Template tags
-------------

CMS uses a simple template system that includes the following tags:

### Variables
```php
{$foo}        // simple variable
{$foo|e}      // HTML escape for variable
{$foo|cut:10} // content of the variable cut to 10 characters
{$foo.bar}    // array
```
Access to the elements of the array is done by a dot character.

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
The loop tag has 3 stages of expansion. The first is an array variable that the template system will break into three variables named `$key`,` $value` and `$counter`, which counts successive iterations starting from zero. The second step allows you to specify the name of the variable that holds the value, and the third is also the name of the index variable.

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
If you leave the character `=`, the code will just execute and nothing will display. This allows you, for example, to define new variables within a template:
```php
{? $foo = 5 ?}
```

### Disable parsing
```
{noparse}Use the {$ contact.form} tag to display contact form.{/noparse}
```
Any tags inside the *noparse* expression will remain unchanged.

### Comments
```
{* this is a comment *}
```
Comments are not visible in the source file after compiling the template.

### Languages
```
{lang: pl_polski}
    Witaj świecie!
{/lang}
{lang: en_english}
    Hello world!
{/lang}
```
If you want to customize the template elements for a particular language, use the tags above.


System variables
----------------
Batflat, like its modules, provides many variables *(usually arrays)* that serve to display each page element. Here are the most important ones:

+ `{$settings.pole}` — an array element containing the value of the given Batflat settings field
+ `{$settings.moduł.pole}` — an array element containing the value of the module settings field
+ `{$bat.path}` — stores the path where the system resides
+ `{$bat.lang}` — displays currently used language
+ `{$bat.notify}` — the last notification
+ `{$bat.notify.text}` - notification text
+ `{$bat.notify.type}` - message type corresponding to the Bootstrap classes *(danger, success)*
+ `{$bat.header}` —  additional meta tags, JS scripts and CSS style sheets loaded by modules
+ `{$bat.footer}` — additional JS scripts loaded by modules
+ `{$bat.theme}` — displays the path to the active theme with the host
+ `{$bat.powered}` — displays *Powered by Batflat* with a link to the official site
+ `{$navigation.xyz}` — displays a list of `<li>` navigation elements
+ `{$page.title}` — displays the name of the subpage
+ `{$page.content}` — displays the contents of the subpage

Example
-------

### manifest.json

```
{
    "name": "Example",
    "version": "1.0",
    "author": "Bruce Wayne",
    "email": "contact@waynecorp.com",
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
  <link rel="stylesheet" href="{$bat.theme}/styles.css">
  {loop: $bat.header}{$value}{/loop}
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
        {$settings.footer} {$bat.powered}
    </footer>
   
    <script src="{$bat.theme}/scripts.js"></script>
    {loop: $bat.footer}{$value}{/loop}
</body>
</html>
```

Modules
=======

Structure
---------

Each module, like the themes, must be in a separate folder created in the `inc/modules/` path. Please note that the directory does not contain uppercase and special characters, such as spaces.

Podczas tworzenia modułu należy zastanowić się nad tym, jakiego typu ma być to moduł. Czy ma być konfiguralny w panelu administracyjnym, czy może ma działać wyłącznie po stronie gości odwiedzających stronę? Ze względu na taki podział, w Batflacie wyróżniamy trzy główne pliki modułu:

When creating a module, you need to think about what type of module you want to use. Is it supposed to be configured in the admin panel or is it supposed to work only on the front-end? Due to this division, in Batflat we distinguish three main module files:

+ `Info.php` — contains information about the module, such as name, description, author or icon
+ `Admin.php` — content of this file will be accessible through the admin panel
+ `Site.php` — content of this file will be available for visitors of this site

The fourth but optional file is `ReadMe.md` which should contain additional information for the future user in [Markdown](https://en.wikipedia.org/wiki/Markdown), e.g. how to use the module.

If you are planning to write a module that will use HTML, it would be good to make sure the PHP code is separate from the hypertext markup language. To do this, you need to create a directory `views` inside the module folder. Include any view files in it.

The problem with multilingualism of the module is similar. Just create language files with the `ini` extension inside the `lang` directory.

The structure of the module should look something like this:
```
example/
|-- lang/
|    |-- admin/
|    |    |-- en_english.ini
|    |    |-- pl_polski.ini
|    |-- en_english.ini
|    |-- pl_polski.ini
|-- views/
|    |-- admin/
|    |    |-- bar.html
|    |-- foo.html
|-- Admin.php
|-- Info.php
|-- Site.php
+-- ReadMe.md
```

Creating a module
-----------------

### Info file

The most important file for each module. It contains basic information and instructions during installation and uninstallation.

```php
<?php

    return [
        'name'          =>  'Example',
        'description'   =>  'Lorem ipsum....',
        'author'        =>  'Robin',
        'version'       =>  '1.0',
        'compatibility' =>  '1.3.*',                    // Compatibility with Batflat version
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

This is the kernel/engine of Batflat, the most important part that is responsible for all its basic tasks. The core contains many definitions of constants, functions, and methods that you can use when writing modules.

Constants
---------

All definitions of constants are described in the first part of this documentation. To use them in a PHP file just call their names. Constants are particularly useful when building URLs and file paths.

#### Example
```php
echo MODULES.'/contact/view/form.html';

```


Functions
---------

Batflat has several built-in helper functions that facilitate the creation of modules.

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

Gets the value of the module settings. By default these are the main Batflat settings.

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

The database used in Batflat is SQLite version 3. For its use CMS uses a simple class that makes it easy to build queries. You do not need to know SQL to be able to operate it.

In addition, we recommend [phpLiteAdmin](https://github.com/sruupl/batflat-pla) tool for database management. This is a one-file PHP script similar to *phpMyAdmin*, where you can administer Batflat tables. This will allow you to familiarize yourself with the structure of existing tables.
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

Languages
---------

All language files are located in the `lang` directories inside the modules and under the `inc/lang` path.
In this last path there are folders corresponding to the language names in the following format: `en_english`. The first part is the abbreviation of the language and the second is the full name.
Within the directory is the `general.ini` file, which contains general language variables for the system.
After creating a new language folder, Batflat automatically detects the added language and allows it to be selected in the admin panel. Note that the procedure for creating a new language should be repeated for each module.
