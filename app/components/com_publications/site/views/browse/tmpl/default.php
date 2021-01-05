<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 HUBzero Foundation, LLC.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$no_html = Request::getInt('no_html');

// No direct access
defined('_HZEXEC_') or die();

$database = App::get('db');

$this->css()
     ->css('intro')
     ->js()
    ->js('app');
?>

<?php if (!$no_html) { ?>

  <?php include_once Component::path('com_publications') . DS . 'site' . DS . 'views' . DS . 'publications' . DS . 'tmpl' . DS . 'intro.php';  ?>

  <section class="live-update">
    <div aria-live="polite" id="live-update-wrapper">
<?php } ?>

      

    <?php if (!$no_html) { ?>
        </div> <!-- .live-update-wrapper -->
      </section>
    <?php } ?>
