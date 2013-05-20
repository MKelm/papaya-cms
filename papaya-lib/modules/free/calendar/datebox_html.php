<?php
/**
* Calendar content HTML
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
* @subpackage Free:Calenda
* @version $Id: datebox_html.php 32407 2009-10-12 11:53:49Z feder $
*/

/**
* Basic class date box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_datebox.php');
/**
* Calendar content HTML
*
* @package Papaya-Modules
* @subpackage Free:Calenda
*/
class datebox_html extends base_datebox {
  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'text'=> array ('Text', 'isSomeText', FALSE, 'textarea', 15)
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    return '<text><![CDATA['.@$this->data['text'].']]></text>';
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    return '<text><![CDATA['.@$this->data['text'].']]></text>';
  }
}
?>