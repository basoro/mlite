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
