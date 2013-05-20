<?php
/**
* Action box for richtext -- login-dependent
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
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
* @version $Id: actbox_richtext_surfer.php 32626 2009-10-15 13:22:25Z siddi $
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Action box for richtext -- login-dependent
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class actionbox_richtext_surfer extends base_actionbox {

  /**
  * Preview allowed?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0),
    'text_loggedin' => array(
      'Text logged-in',
      'isSomeText', FALSE,
      'richtext',
      15,
      'Text for logged-in surfers',
      '',
    ),
    'text_loggedout' => array(
      'Text logged-out',
      'isSomeText', FALSE,
      'richtext',
      15,
      'Text for logged-out surfers',
      ''
    )
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $surferObj = &base_surfer::getInstance();
    if ($surferObj->isValid) {
      return $this->getXHTMLString(
        $this->data['text_loggedin'],
        !((bool)$this->data['nl2br'])
      );
    } else {
      return $this->getXHTMLString(
        $this->data['text_loggedout'],
        !((bool)$this->data['nl2br'])
      );
    }
  }
}
?>