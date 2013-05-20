<?php
/**
* Alias plugin - Redirect by surfer handle
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage _Base-Community
* @version $Id: alias_surferhandle.php 33591 2010-01-25 15:23:09Z weinert $
*/

/**
* Abstract alias plugin base class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_plugin_alias.php');

/**
* Alias plugin - Redirect by surfer handle
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class alias_surferhandle extends base_plugin_alias {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'page_found' => array(
      'User page', 'isNum', FALSE, 'pageid', 30, '', 0
    ),
    'page_unknown' => array(
      'Unknown user page', 'isNum', FALSE, 'pageid', 30, '', 0
    ),
  );

  /**
  * Redirect surfer by a handle in the given url.
  * @see papaya-lib/system/base_plugin_alias#redirect($urlPart)
  *
  * @return string|array|boolean absolute url, alias definition array,
  *                              handled (TRUE), not found (FALSE)
  */
  function redirect($urlPart) {
    $this->setDefaultData();
    if (!empty($urlPart)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $surfersObj = base_pluginloader::getPluginInstance('06648c9c955e1a0e06a7bd381748c4e4', $this);
      if ($surfersObj->isValidSurfer('user:'.$urlPart)) {
        return $this->getWebLink(
          $this->data['page_found'],
          NULL,
          NULL,
          array('surfer_handle' => $urlPart)
        );
      }
    }
    return $this->getWebLink(
      $this->data['page_unknown']
    );
  }

}
?>
