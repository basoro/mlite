<?php
if(!defined('IS_IN_MODULE')) { die("NO DIRECT FILE ACCESS!"); }

$module_directory   = 'SirsOnline';
$module_title       = 'Sirs Online';
$module_version     = '1.0';
$module_author      = 'drg. Faisol Basoro';
$module_url         = 'https://basoro.id';
$module_description = 'Modul Laporan dan Template Sirs Online di SIMKES Khanza.';
$module_type        = 'FKTL';

?>

<?php if(basename($_SERVER['SCRIPT_NAME']) == 'index.php') { ?>
  <?php if(FKTL == true) { ?>
  <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3">
    <a href="<?php echo URL; ?>/index.php?module=<?php echo $module_directory; ?>&page=index">
      <div class="image">
        <div class="icon">
          <i class="material-icons">assignment_turned_in</i>
        </div>
      </div>
      <div class="sname"><?php echo $module_title; ?></div>
    </a>
  </div>
  <?php } ?>
<?php } else { ?>
  <tr>
    <td>
      <div class="image-plugins">
        <div class="icon">
           <i class="material-icons">assignment_turned_in</i>
        </div>
      </div>
    </td>
    <td>
      <h4><?php echo $module_title; ?></h4>
      <?php echo $module_description; ?>
    </td>
    <td>
      <b><?php echo $module_type; ?></b>
    </td>
    <td>
      <b><?php echo $module_version; ?></b>
    </td>
    <td>
      <a href="<?php echo $module_url; ?>" alt="<?php echo $module_author; ?>"><?php echo $module_author; ?></a>
    </td>
    <td>
      <form class="rmdirmodule" method="post" action="">
        <input type="hidden" name="dirmodule" value="<?php echo $module_directory; ?>">
        <button type="submit" class="btn btn-danger"><i class="material-icons">delete</i> Delete</button>
      </form>
    </td>
  </tr>
<?php } ?>
