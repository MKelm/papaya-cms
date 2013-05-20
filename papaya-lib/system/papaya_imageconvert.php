<?php
/**
* image converter
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link      http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Administration
* @version $Id: papaya_imageconvert.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* papaya base class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_object.php');

/**
* image converter
*
* @package Papaya
* @subpackage Administration
* @version $Id: papaya_imageconvert.php 36224 2011-09-20 08:00:57Z weinert $
*/
class papaya_imageconvert extends base_object {


  /**
  * get a converter object
  *
  * @param $fileName
  * @access public
  * @return object imageconv_common
  */
  function &getConverter($fileName) {
    $result = NULL;
    $converters = array(
      'gd', 'netpbm', 'imagemagick','graphicsmagick'
    );
    if (is_file($fileName) && is_readable($fileName)) {
      if (defined('PAPAYA_IMAGE_CONVERTER') && trim(PAPAYA_IMAGE_CONVERTER) != '' &&
          in_array(trim(PAPAYA_IMAGE_CONVERTER), $converters)) {
        $converter = trim(PAPAYA_IMAGE_CONVERTER);
      } else {
        $converter = 'gd';
      }
      $convFileName = dirname(__FILE__).'/image/'.$converter.'.php';
      if ($converter != '' && file_exists($convFileName)) {
        include_once($convFileName);
        $className = 'imgconv_'.$converter;
        $result = new $className;
      }
    }
    return $result;
  }
}

?>