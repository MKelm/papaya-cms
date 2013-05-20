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
* @version $Id: image_colored.php 32598 2009-10-14 15:36:39Z weinert $
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
class image_colored extends base_dynamicimage {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'image_copy_mode' => array(
      'Modus', 'isNum', TRUE, 'combo', array(0 => 'multiply', 1 => 'normal'), '', 0
    )
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'image_guid' => array('Image', 'isGuid', FALSE, 'imagefixed', 32, '', ''),
    'Colors',
    'bgcolor' =>
      array('Background color', 'isHTMLColor', FALSE, 'color', 7, '', '#FFFFFF'),
    'fgcolor' =>
      array('Foreground color', 'isHTMLColor', FALSE, 'color', 7, '', '#000000'),
    'Transparency',
    'bgalpha' =>
      array('Background', '(^(100|\d{1,2})%$)', FALSE, 'input', 4,
        '0% = opaque, 100% = transparent, only for png', '0%'),
    'fgalpha' =>
      array('Foreground', '(^(100|\d{1,2})%$)', FALSE, 'input', 4,
        '0% = opaque, 100% = transparent', '0%'),
    'Brightness',
    'bright' => array(
      'Brightness', '(^[+-]?(\d\d?|100)?$)', FALSE, 'input', '5', '-100 - +100', 0
    )
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
    $result = NULL;
    foreach ($this->attributeFields as $field => $data) {
      if (empty($this->attributes[$field]) && is_array($data) && !empty($data[6])) {
        $this->attributes[$field] = $data[6];
      }
    }
    if (!empty($this->attributes['image_guid']) &&
        $image = &$controller->getMediaFileImage($this->attributes['image_guid'])) {
      $width = imagesx($image);
      $height = imagesy($image);
      if ($width > 0 && $height > 0) {
        //create an image with the same size of the content image
        $result = imageCreateTrueColor($width, $height);
        //paint the background
        $bgColor = $controller->colorToRGB($this->attributes['bgcolor']);
        if (defined('PAPAYA_THUMBS_FILETYPE') && PAPAYA_THUMBS_FILETYPE == 3 &&
            defined('PAPAYA_THUMBS_TRANSPARENT') && PAPAYA_THUMBS_TRANSPARENT) {
          $bgColorAlpha = round(substr($this->attributes['bgalpha'], 0, -1) * 1.27);
          imageSaveAlpha($result, TRUE);
        } else {
          $bgColorAlpha = 0;
          imageSaveAlpha($result, FALSE);
        }
        imageAlphaBlending($result, FALSE);
        $bgColor = imagecolorallocatealpha(
          $result,
          $bgColor['red'],
          $bgColor['green'],
          $bgColor['blue'],
          $bgColorAlpha
        );
        imageFill($result, 0, 0, $bgColor);

        imageAlphaBlending($result, TRUE);
        if ($this->attributes['fgalpha'] == '100%') {
          imageCopy($result, $image, 0, 0, 0, 0, $width, $height);
        } elseif ($this->data['image_copy_mode'] == 1) {
          $this->imageCopyColor(
            $result,
            $image,
            $controller->colorToRGB($this->attributes['fgcolor']),
            substr($this->attributes['fgalpha'], 0, -1),
            $this->attributes['bright']
          );
        } else {
          $this->imageCopyColorized(
            $result,
            $image,
            $controller->colorToRGB($this->attributes['fgcolor']),
            substr($this->attributes['fgalpha'], 0, -1),
            $this->attributes['bright']
          );
        }
      } else {
        $this->lastError = 'Invalid image size.';
      }
    } else {
      $this->lastError = 'Can not load image.';
    }
    return $result;
  }

  /**
  * Copy the image to the background after merging it with the color.
  *
  * @param resource $result
  * @param resource $image
  * @param array $color
  * @param integer $alpha
  * @param integer $brightness increase or decrease brightness (-100 to +100)
  * @access public
  * @return void
  */
  function imageCopyColorized($result, $image, $color, $alpha, $brightness = 0) {
    $width = imagesx($image);
    $height = imagesy($image);
    $imageAlpha = $alpha / 100;
    $overlayAlpha = 1 - $imageAlpha;
    $colorGray = max($color['red'], $color['green'], $color['blue']);
    $brightnessAlpha = (100 - abs($brightness)) / 100;
    $brightnessOverlay = 255 * (1 - $brightnessAlpha);
    for ($x = 0; $x < $width; $x++) {
      for ($y = 0; $y < $height; $y++) {
        $rgb = imagecolorat($image, $x, $y);
        $a = ($rgb >> 24) & 0xFF;
        if ($a < 127) {
          $r = ($rgb >> 16) & 0xFF;
          $g = ($rgb >> 8) & 0xFF;
          $b = $rgb & 0xFF;
          if ($colorGray > 0) {
            $gray = max($r, $g, $b) / $colorGray;
          } else {
            $gray = 0;
          }
          if ($imageAlpha > 0) {
            $r = (($color['red'] * $gray) * $overlayAlpha + $r * $imageAlpha);
            $g = (($color['green'] * $gray) * $overlayAlpha + $g * $imageAlpha);
            $b = (($color['blue'] * $gray) * $overlayAlpha + $b * $imageAlpha);
          } else {
            $r = $color['red'] * $gray;
            $g = $color['green'] * $gray;
            $b = $color['blue'] * $gray;
          }
          if ($brightness > 0) {
            $r = $r * $brightnessAlpha + $brightnessOverlay;
            $g = $g * $brightnessAlpha + $brightnessOverlay;
            $b = $b * $brightnessAlpha + $brightnessOverlay;
          } elseif ($brightness < 0) {
            $r *= $brightnessAlpha;
            $g *= $brightnessAlpha;
            $b *= $brightnessAlpha;
          }
          $idx = imagecolorallocatealpha($result, $r, $g, $b, $a);
          imagesetpixel($result, $x, $y, $idx);
        }
      }
    }
  }

  /**
  * Copy the image to the background after replacing it with the color
  * (but keepeing the alpha transparency).
  *
  * @param resource $result
  * @param resource $image
  * @param array $color
  * @param integer $alpha
  * @param integer $brightness increase or decrease brightness (-100 to +100)
  * @access public
  * @return void
  */
  function imageCopyColor($result, $image, $color, $alpha, $brightness = 0) {
    $width = imagesx($image);
    $height = imagesy($image);
    $imageAlpha = $alpha / 100;
    $overlayAlpha = 1 - $imageAlpha;
    $brightnessAlpha = (100 - abs($brightness)) / 100;
    $brightnessOverlay = 255 * (1 - $brightnessAlpha);
    for ($x = 0; $x < $width; $x++) {
      for ($y = 0; $y < $height; $y++) {
        $rgb = imagecolorat($image, $x, $y);
        $a = ($rgb >> 24) & 0xFF;
        if ($a < 127) {
          if ($imageAlpha > 0) {
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $r = $color['red'] * $overlayAlpha + $r * $imageAlpha;
            $g = $color['green'] * $overlayAlpha + $g * $imageAlpha;
            $b = $color['blue'] * $overlayAlpha + $b * $imageAlpha;
          } else {
            $r = $color['red'];
            $g = $color['green'];
            $b = $color['blue'];
          }
          if ($brightness > 0) {
            $r = $r * $brightnessAlpha + $brightnessOverlay;
            $g = $g * $brightnessAlpha + $brightnessOverlay;
            $b = $b * $brightnessAlpha + $brightnessOverlay;
          } elseif ($brightness < 0) {
            $r *= $brightnessAlpha;
            $g *= $brightnessAlpha;
            $b *= $brightnessAlpha;
          }
          $idx = imagecolorallocatealpha($result, $r, $g, $b, $a);
          imagesetpixel($result, $x, $y, $idx);
        }
      }
    }
  }
}
?>