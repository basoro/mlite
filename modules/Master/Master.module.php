<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<div class="header">
    <h2>
        Hello World
    </h2>
</div>
<?php
class Master {
    function index() { // This is our index function. It is called if we do not have a function defined
?>
<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=HelloWorld">
                <i class="material-icons">home</i> <span class="hidden-xs">INDEX</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=HelloWorld&page=hello">
                <i class="material-icons">face</i> <span class="hidden-xs">HELLO</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=HelloWorld&page=world">
                <i class="material-icons">email</i> <span class="hidden-xs">WORLD</span>
            </a>
        </li>
    </ul>
    <div class="content m-t-30">
        <b>Index Content</b>
        <p>
            Lorem ipsum dolor sit amet, ut duo atqui exerci dicunt, ius impedit mediocritatem an. Pri ut tation electram moderatius.
            Per te suavitate democritum. Duis nemore probatus ne quo, ad liber essent aliquid
            pro. Et eos nusquam accumsan, vide mentitum fabellas ne est, eu munere gubergren
            sadipscing mel.
        </p>
    </div>
</div>
<?php
    }
    function hello() { // hello function called from modules.php?module=HelloWorld&page=hello
?>
<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=HelloWorld">
                <i class="material-icons">home</i> <span class="hidden-xs">INDEX</span>
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=HelloWorld&page=hello">
                <i class="material-icons">face</i> <span class="hidden-xs">HELLO</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=HelloWorld&page=world">
                <i class="material-icons">email</i> <span class="hidden-xs">WORLD</span>
            </a>
        </li>
    </ul>
    <div class="content m-t-30">
        <b>Hello Content</b>
        <p>
            Lorem ipsum dolor sit amet, ut duo atqui exerci dicunt, ius impedit mediocritatem an. Pri ut tation electram moderatius.
            Per te suavitate democritum. Duis nemore probatus ne quo, ad liber essent aliquid
            pro. Et eos nusquam accumsan, vide mentitum fabellas ne est, eu munere gubergren
            sadipscing mel.
        </p>
    </div>
</div>
<?php
    }
    function world() { // hello function called from modules.php?module=HelloWorld&page=world
?>
<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=HelloWorld">
                <i class="material-icons">home</i> <span class="hidden-xs">INDEX</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=HelloWorld&page=hello">
                <i class="material-icons">face</i> <span class="hidden-xs">HELLO</span>
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=HelloWorld&page=world">
                <i class="material-icons">email</i> <span class="hidden-xs">WORLD</span>
            </a>
        </li>
    </ul>
    <div class="content m-t-30">
        <b>World Content</b>
        <p>
            Lorem ipsum dolor sit amet, ut duo atqui exerci dicunt, ius impedit mediocritatem an. Pri ut tation electram moderatius.
            Per te suavitate democritum. Duis nemore probatus ne quo, ad liber essent aliquid
            pro. Et eos nusquam accumsan, vide mentitum fabellas ne est, eu munere gubergren
            sadipscing mel.
        </p>
    </div>
</div>
<?php
    }
}
?>
