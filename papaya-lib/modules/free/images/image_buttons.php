<?php
/**
* Generate a button using a background image or color and a text
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
* @version $Id: image_buttons.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

/**
* Generate a button using a background image or color and a text
*
* image  plugins must be inherited from this superclass
*
* @package Papaya-Modules
* @subpackage Free-Images
*/
class image_buttons extends base_dynamicimage {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'image' => array('Image', 'isSomeText', FALSE, 'imagefixed', 400, '', ''),
    'image_color' => array('Background color', 'isHTMLColor', TRUE, 'color', 7, '', '#000000'),
    'Font',
    'font_type' => array('Font', 'isSomeText', FALSE, 'mediafile', 400, '', ''),
    'font_color' => array('Color', 'isHTMLColor', TRUE, 'color', 7, '', '#FFFFFF'),
    'font_size' => array('Size', 'isNum', TRUE, 'input', 4, '', 18),
    'Alignment',
    'align_align' => array(
      'Alignment', 'isNum', TRUE, 'combo', array(1 => 'left', 2 => 'center', 3 => 'right'), '', 1
    ),
    'align_vertical' => array('Vertical padding', 'isNum', TRUE, 'input', 5, '', 0),
    'align_horizontal' => array('Horizontal padding', 'isNum', TRUE, 'input', 5, '', 0)
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'image_text' => array('Text', 'isSomeText', FALSE, 'input', 30, '', '')
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
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $mediaDB = base_mediadb::getInstance();
    $imageText = empty($this->attributes['image_text']) ? '' : $this->attributes['image_text'];
    $fontSize = (int)$this->data['font_size'];
    $offsetV = (int)$this->data['align_vertical'];
    $offsetH = (int)$this->data['align_horizontal'];
    if (!($fontType = $mediaDB->getFileName($this->data['font_type']))) {
      $fontType = '';
    }
    //get image width and height from text
    if (is_file($fontType) && is_readable($fontType)) {
      $systemFont = FALSE;
      $textData = imagettfbbox($fontSize, 0, $fontType, $imageText);
      $height = max($textData[1], $textData[3]) - min($textData[5], $textData[7]);
      $width = max($textData[2], $textData[4]) - min($textData[0], $textData[6]);
    } else {
      $systemFont = 3;
      $height = imagefontheight($systemFont);
      $width = imagefontwidth($systemFont) * strlen($imageText);
    }

    //if image is set
    if (!empty($this->data['image']) &&
        $imageBG = &$controller->getMediaFileImage($this->data['image'])) {
      //create image in $result
      $result = imagecreate(imagesx($imageBG), imagesy($imageBG));
      //load background image into $result
      imagecopy($result, $imageBG, 0, 0, 0, 0, imagesx($imageBG), imagesy($imageBG));
      //if non image set
    } else {
      $createWidth = $width + ($offsetH * 2);
      $createHeight = $height + ($offsetV * 2);
      if ($createWidth < 1) {
        $createWidth = 1;
      }
      if ($createHeight < 1) {
        $createHeight = 1;
      }
      //create image in $result
      $result = imagecreatetruecolor($createWidth, $createHeight);
      //get background color index
      $bgColor = $controller->colorToRGB($this->data['image_color']);
      $bgColorIdx = imagecolorallocate(
        $result,
        $bgColor['red'],
        $bgColor['green'],
        $bgColor['blue']
      );
      //fill with background color
      imagefilledrectangle(
        $result,
        0,
        0,
        $createWidth,
        $createHeight,
        $bgColorIdx
      );
    }

    $imageHeight = imagesy($result);
    $imageWidth = imagesx($result);

    //get positions from align and offsets
    //align = center
    if ($this->data['align_align'] == 2) {
      $posX = ($imageWidth / 2) - ($width / 2);
      $posY = ($imageHeight / 2) + ($height / 2);
      //align = right
    } elseif ($this->data['align_align'] == 3) {
      $posX = $imageWidth - $width - $offsetH;
      $posY = ($imageHeight / 2) + ($height / 2);
      //align = left
    } else {
      $posX = $offsetH;
      $posY = ($imageHeight / 2) + ($height / 2);
    }

    //get text color index
    $fontColor = $controller->colorToRGB($this->data['font_color']);
    $fontColorIdx = imagecolorallocate(
      $result,
      $fontColor['red'],
      $fontColor['green'],
      $fontColor['blue']
    );

    //copy text into $result
    if ($systemFont) {
      imagestring(
        $result,
        $systemFont,
        $posX,
        $posY + 3,
        $imageText,
        $fontColorIdx
      );
    } else {
      imagettftext(
        $result,
        $fontSize,
        0,
        $posX,
        $posY,
        $fontColorIdx,
        $fontType,
        $imageText
      );
    }
    return $result;
  }
}
?>