<?php
/**
* Admin module for stickers
*
* @package Papaya-Modules
* @subpackage Free-Stickers
* @version $Id: edmodule_stickers.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic module class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_module.php');

/**
* Admin module for stickers
*
* @package Papaya-Modules
* @subpackage Free-Stickers
*/
class edmodule_stickers extends base_module {

  /**
  * @var array $permissions holds permissions available for this module package
  */
  var $permissions = array(
    1 => 'Manage',
  );

  /**
  * Initializes and executes the admin module.
  */
  function execModule() {
    if ($this->hasPerm(1, TRUE)) {
      include_once(dirname(__FILE__).'/admin_stickers.php');
      $stickers = new admin_stickers($this);
      $stickers->module = &$this;
      $stickers->images = &$this->images;
      $stickers->msgs = &$this->msgs;
      $stickers->layout = &$this->layout;
      $stickers->authUser = &$this->authUser;
      $stickers->initialize();
      $stickers->execute();
      $stickers->getXML();
    }
  }

}
?>
