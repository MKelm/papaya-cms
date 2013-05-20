<?php
/**
* Generate an progressbar using an background and a bar image
*
* image plugins must be inherited from this superclass
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
* @subpackage Free-Images
* @version $Id: image_progressbar.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

/**
* Generate an progressbar using an background and a bar image
*
* image  plugins must be inherited from this superclass
*
* @package Papaya-Modules
* @subpackage Free-Images
*/
class image_progressbar extends base_dynamicimage {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'limit_min' => array('Minimum (0%)', 'isNum', TRUE, 'input', 4, '', 0),
    'limit_max' => array('Maximum (100%)', 'isNum', TRUE, 'input', 4, '', 10),
    'Images',
    'image_fg' => array('Foreground', 'isSomeText', TRUE, 'imagefixed', 400, '', ''),
    'image_bg' => array('Background', 'isSomeText', FALSE, 'imagefixed', 400, '', ''),
    'Background',
    'padding' => array('Padding', 'isNum', TRUE, 'input', 4, '', 1),
    'color_bg' => array('Color', 'isHTMLColor', TRUE, 'color', 7, '', '#FFFFFF'),
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'position' => array('Current position', 'isNum', TRUE, 'input', 4, '', 0),
  );

  /**
  * generate the image
  *
  * @param object base_imagegenerator &$controller controller object
  * @access public
  * @return image $result resource image
  */
  function &generateImage(&$controller) {
    $this->setDefaultData();
    if ($imageFG = &$controller->getMediaFileImage($this->data['image_fg'])) {
      $steps = (int)$this->data['limit_max'] - (int)$this->data['limit_min'];
      if ($steps < 1) {
        $steps = 1;
      }

      //calc size and create image
      $padding = (int)$this->data['padding'];
      $height = imagesy($imageFG) + ($padding * 2);
      $width = ((imagesx($imageFG) + $padding) * $steps) + $padding;
      $result = imagecreatetruecolor($width, $height);

      // background color
      $bgColor = $controller->colorToRGB($this->data['color_bg']);
      $bgColorIdx = imagecolorallocate(
        $result, $bgColor['red'], $bgColor['green'], $bgColor['blue']
      );
      imagefilledrectangle($result, 0, 0, $width, $height, $bgColorIdx);

      // background image
      if ($imageBG = &$controller->getMediaFileImage($this->data['image_bg'])) {
        for ($x = $padding; ($x <= $width); $x += imagesx($imageBG) + $padding) {
          imagecopy($result, $imageBG, $x, $padding, 0, 0, imagesx($imageBG), imagesy($imageBG));
        }
      }

      $imgCount = (int)$this->attributes['position'] - (int)$this->data['limit_min'];

      // progress
      for ($i = 0; ($i < $imgCount && $i < $steps); $i++) {
        $x = ($i * (imagesx($imageFG) + $padding)) + (int)$this->data['padding'];
        imagecopy($result, $imageFG, $x, $padding, 0, 0, imagesx($imageFG), imagesy($imageFG));
      }

      return $result;
    } else {
      $this->lastError = 'Can not load foreground image';
    }
    $result = FALSE;
    return $result;
  }
}
?>