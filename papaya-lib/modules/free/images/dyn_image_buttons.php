<?php
/**
* Generate an button using a background, a left and a right image and a text
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
* @version $Id: dyn_image_buttons.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

/**
* Generate an button using a background, a left and a right image and a text
*
* image  plugins must be inherited from this superclass
*
* @package Papaya-Modules
* @subpackage Free-Images
*/
class dyn_image_buttons extends base_dynamicimage {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'Images',
    'images_background' => array(
      'Background image', 'isSomeText', FALSE, 'imagefixed', 400, '', ''
    ),
    'images_left' => array('Left image', 'isSomeText', TRUE, 'imagefixed', 400, '', ''),
    'images_right' => array('Right image', 'isSomeText', TRUE, 'imagefixed', 400, '', ''),
    'images_color' => array('Background color', 'isHTMLColor', TRUE, 'color', 7, '', '#FFFFFF'),
    'Font',
    'font_type' => array('Font', 'isSomeText', TRUE, 'mediafile', 400, '', ''),
    'font_color' => array('Color', 'isHTMLColor', TRUE, 'color', 7, '', '#FFFFFF'),
    'font_size' => array('Size', 'isNum', TRUE, 'input', 4, '', 18),
    'Vertical alignment',
    'align_vertical' => array(
      'Alignment', 'isNum', TRUE, 'combo', array(1 => 'top', 2 => 'middle', 3 => 'bottom'), '', 1
    ),
    'align_top_offset' => array('Top padding', 'isNum', TRUE, 'input', 5, '', 0),
    'align_bottom_offset' => array('Bottom padding', 'isNum', TRUE, 'input', 5, '', 0),
    'Horizontal alignment',
    'align_horizontal' => array(
      'Alignment', 'isNum', TRUE, 'combo', array(1 => 'left', 2 => 'center', 3 => 'right'), '', 1
    ),
    'align_left_offset' => array('Left padding', 'isNum', TRUE, 'input', 5, '', 0),
    'align_right_offset' => array('Right padding', 'isNum', TRUE, 'input', 5, '', 0)
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'image_text' => array('Text', 'isSomeText', FALSE, 'input', 100, '', '')
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
    $offsetLH = (int)$this->data['align_left_offset'];
    $offsetRH = (int)$this->data['align_right_offset'];
    $offsetTV = (int)$this->data['align_top_offset'];
    $offsetBV = (int)$this->data['align_bottom_offset'];
    if (!($fontType = $mediaDB->getFileName($this->data['font_type']))) {
      $fontType = '';
    }
    //get image width and height from text
    $textData = imagettfbbox($fontSize, 0, $fontType, $imageText);
    $height = max($textData[1], $textData[3]) - min($textData[5], $textData[7]);
    $width = max($textData[2], $textData[4]) - min($textData[0], $textData[6]);

    $imageLeft = &$controller->getMediaFileImage($this->data['images_left']);
    $imageRight = &$controller->getMediaFileImage($this->data['images_right']);
    $imageBg = &$controller->getMediaFileImage($this->data['images_background']);

    if ($imageBg && $imageLeft && $imageRight) {
      //get maximum image size
      $createWidth = $width + ($offsetLH + $offsetRH);
      $createHeight = imagesy($imageBg);
      if ($createHeight < imagesy($imageLeft)) {
        $createHeight = imagesy($imageLeft);
      }
      if ($createHeight < imagesy($imageRight)) {
        $createHeight = imagesy($imageRight);
      }
      if ($createWidth < 1) {
        $createWidth = 1;
      }
      if ($createHeight < 1) {
        $createHeight = 1;
      }

      //create image in $result
      $result = imagecreatetruecolor($createWidth, $createHeight);
      //get background color index
      $bgColor = $controller->colorToRGB($this->data['images_color']);
      $bgColorIdx = imagecolorallocate(
        $result, $bgColor['red'], $bgColor['green'], $bgColor['blue']
      );
      //fill with background color
      imagefilledrectangle($result, 0, 0, $createWidth, $createHeight, $bgColorIdx);
      //copy left image into $result
      $posY = ($createHeight / 2) - (imagesy($imageLeft) / 2);
      imagecopy($result, $imageLeft, 0, $posY, 0, 0, imagesx($imageLeft), imagesy($imageLeft));
      $posX = imagesx($imageLeft);
      $lengthX = imagesx($imageBg);
      if (isset($imageBg)) {
        $bgCount = (int)ceil(
          ($createWidth - imagesx($imageLeft) - imagesx($imageRight)) / imagesx($imageBg)
        );
        $posY = ($createHeight / 2) - (imagesy($imageBg) / 2);
        for ($i = 0; $i < $bgCount; $i++) {
          //cut background image before right image
          if (($lengthX + $posX) > ($createWidth - imagesx($imageRight))) {
            if ($createWidth > ($posX + imagesx($imageBg))) {
              $lengthX = $lengthX - (
                imagesx($imageRight) - ($createWidth - $posX - imagesx($imageBg))
              );
            } else {
              $lengthX = $lengthX - imagesx($imageRight) -
                (($posX + imagesx($imageBg)) - $createWidth);
            }
          }
          imagecopy($result, $imageBg, $posX, $posY, 0, 0, $lengthX, imagesy($imageBg));
          $posX += imagesx($imageBg);
        }
      }
      //copy right image into $result
      $posX = $createWidth - imagesx($imageRight);
      $posY = ($createHeight / 2) - (imagesy($imageRight) / 2);
      imagecopy(
        $result, $imageRight, $posX, $posY, 0, 0, imagesx($imageRight), imagesy($imageRight)
      );

      //get horizontal positions from align and offsets
      //align = center
      if ($this->data['align_horizontal'] == 2) {
        $posX = ($createWidth / 2) - ($width / 2);
        //align = right
      } elseif ($this->data['align_horizontal'] == 3) {
        $posX = $createWidth - $width - $offsetRH;
        //align = left
      } else {
        $posX = $offsetLH;
      }
      //get vertical positions from align and offsets
      //align = middle
      if ($this->data['align_vertical'] == 2) {
        $posY = ($createHeight / 2) + ($height / 2);
        //align = bottom
      } elseif ($this->data['align_vertical'] == 3) {
        $posY = $createHeight - $height - $offsetBV;
        //align = top
      } else {
        $posY = $offsetTV + $height;
      }

      //get text color index
      $fontColor = $controller->colorToRGB($this->data['font_color']);
      $fontColorIdx = imagecolorallocate(
        $result, $fontColor['red'], $fontColor['green'], $fontColor['blue']
      );

      //copy text into $result
      imagettftext($result, $fontSize, 0, $posX, $posY, $fontColorIdx, $fontType, $imageText);
    } else {
      $this->lastError = 'Can not load one or more images';
      $result = FALSE;
    }
    return $result;
  }
}
?>
