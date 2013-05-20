<?php
/**
* Module showing several possible field controls
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
* @subpackage Sample
* @version $Id: content_fields_sample.php 37561 2012-10-16 18:25:45Z weinert $
*/

/**
* page modules inherit from the base_content super class
* This does not include any database access, because not any module needs it.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Module showing several possible field controls
*
* @package Papaya-Modules
* @subpackage Sample
*/
class content_fields_sample extends base_content {

  var $editFields = array(
    'counted' => array(
      'Counted input', 'isSomeText', FALSE, 'input_counter', 500, '', ''
    ),
    'page' => array(
      'Page Id', 'isSomeText', FALSE, 'pageid', 500, '', ''
    ),
    'color' => array(
      'Color', 'isSomeText', FALSE, 'color', 500, '', ''
    ),
    'geopos' => array(
      'Geo Position', 'isSomeText', FALSE, 'geopos', 500, '', ''
    ),
    'image' => array(
      'Image', 'isSomeText', FALSE, 'imagefixed', 500, '', ''
    ),
    'image_resized' => array(
      'Image Resized', 'isSomeText', FALSE, 'image', 500, '', ''
    ),
    'mediafile' => array(
      'Media File', 'isSomeText', FALSE, 'mediafile', 500, '', ''
    ),
    'date' => array(
      'Date', 'isSomeText', FALSE, 'date', 500, '', ''
    ),
    'datetime' => array(
      'Date and Time', 'isSomeText', FALSE, 'datetime', 500, '', ''
    ),
    'select' => array(
      'Select', 'isSomeText', FALSE, 'combo', array(1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 'foo', 'foobar')
    )
  );

}
?>
