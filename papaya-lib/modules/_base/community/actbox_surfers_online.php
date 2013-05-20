<?php
/**
* Actionbox - Surfers currently online
*
* Displaying surfers online list
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
* @version $Id: actbox_surfers_online.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Surfer class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');

/**
* Surfer admin class
*/
require_once(dirname(__FILE__).'/base_surfers.php');


/**
* Actionbox - Surfers currently online
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class actionbox_surfers_online extends base_actionbox {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'Settings',
    'detail_page' => array(
      'Detail page', 'isNum', TRUE, 'pageid', 30, '', 0
    ),
    'Captions',
    'caption_online_list' => array(
      'Direct contact', 'isNoHTML', TRUE, 'input', 50, '', 'Users currently online'
    )
  );

  /**
  * Database table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    $result = '';

    // Create a surfer admin object
    $surferAdmin = new surfer_admin($this->msgs);
    $onlineSurfers = $surferAdmin->getOnlineSurfers();
    if (isset($this->data['caption_online_list'])) {
      $title = $this->data['caption_online_list'];
    } else {
      $title = '';
    }

    $result .= sprintf(
      '<surfers-online count="%d" title="%s">',
      (int)$onlineSurfers['count'],
      papaya_strings::escapeHTMLChars($title)
    );
    foreach ($onlineSurfers['list'] as $surferId => $surferHandle) {
      $result .= sprintf(
        '<surfer link="%s">%s</surfer>',
        papaya_strings::escapeHTMLChars(
          $this->getWeblink(
            empty($this->data['detail_page']) ? NULL : $this->data['detail_page'],
            NULL,
            NULL,
            array('surfer_handle' => $surferHandle),
            'cnt'
          )
        ),
        papaya_strings::escapeHTMLChars($surferHandle)
      );
    }
    $result .= '</surfers-online>';

    return $result;
  }

}

?>
