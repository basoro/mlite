<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }
?>

<ol class="breadcrumb breadcrumb-bg-grey" style="padding:10px !important;">
    <li><a href="<?php echo URL; ?>">Home</a></li>
    <li><a href="<?php echo URL; ?>/?module=Master">Data Master</a></li>
    <li class="active">Index</li>
</ol>

<?php
class Master {
    function index() {
?>
<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=Master">
                <i class="material-icons">home</i> <span class="hidden-xs">INDEX</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=Master&page=hello">
                <i class="material-icons">face</i> <span class="hidden-xs">HELLO</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=Master&page=world">
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
    function hello() {
?>
<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=Master">
                <i class="material-icons">home</i> <span class="hidden-xs">INDEX</span>
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=Master&page=hello">
                <i class="material-icons">face</i> <span class="hidden-xs">HELLO</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=Master&page=world">
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
    function world() {
?>
<div class="body">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=Master">
                <i class="material-icons">home</i> <span class="hidden-xs">INDEX</span>
            </a>
        </li>
        <li role="presentation">
            <a href="<?php echo URL; ?>/?module=Master&page=hello">
                <i class="material-icons">face</i> <span class="hidden-xs">HELLO</span>
            </a>
        </li>
        <li role="presentation" class="active">
            <a href="<?php echo URL; ?>/?module=Master&page=world">
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
