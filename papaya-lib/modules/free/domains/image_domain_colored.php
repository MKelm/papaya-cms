<?php
/**
* Colorize and image depending on a domain specific configuration
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
* @subpackage Free-Domains
* @version $Id: image_domain_colored.php 32986 2009-11-10 17:31:00Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

/**
* Colorize and image depending on a domain specific configuration
*
* @package Papaya-Modules
* @subpackage Free-Domains
*/
class image_domain_colored extends base_dynamicimage {

  /**
  * Domain connector module identifier
  * @var string
  */
  var $domainConnectorGuid = '8ec0c5995d97c9c3cc9c237ad0dc6c0b';

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'Domain identifiers',
    'image_bgcolor_ident' =>
      array('Background color', 'isAlphaNum', FALSE,  'function', 'getIdentifierCombo', '', ''),
    'image_fgcolor_ident' =>
      array('Foreground color', 'isAlphaNum', FALSE,  'function', 'getIdentifierCombo', '', ''),
    'image_brightness_ident' =>
      array('Brightness', 'isAlphaNum', FALSE,  'function', 'getIdentifierCombo', '', ''),
    'Default colors',
    'image_bgcolor' =>
      array('Background color', 'isHTMLColor', TRUE, 'color', 7, '', '#0000FF'),
    'image_fgcolor' =>
      array('Foreground color', 'isHTMLColor', TRUE, 'color', 7, '', '#000000'),
    'Transparency',
    'image_bgcolor_alpha' =>
      array('Background', '(^(100|\d{1,2})%$)', TRUE, 'input', 4,
        '0% = opaque, 100% = transparent, only for png', '0%'),
    'image_fgcolor_alpha' =>
      array('Foreground', '(^(100|\d{1,2})%$)', TRUE, 'input', 4,
        '0% = opaque, 100% = transparent', '50%'),
    'Options',
    'image_copy_mode' => array(
      'Modus', 'isNum', TRUE, 'combo', array(0 => 'multiply', 1 => 'normal'), '', 0
    ),
    'image_brightness' => array(
      'Default brightness', '(^[+-]?(\d\d?|100)?$)', TRUE, 'input', '5', '-100 - +100', 0
    ),
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'image_guid' => array(
      'Image', 'isGuid', FALSE, 'imagefixed', 32, '', ''
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
    if (!empty($this->attributes['image_guid']) &&
        $image = &$controller->getMediaFileImage($this->attributes['image_guid'])) {
      $width = imagesx($image);
      $height = imagesy($image);
      if ($width > 0 && $height > 0) {
        $fields = array(
          'image_bgcolor' => $this->data['image_bgcolor_ident'],
          'image_fgcolor' => $this->data['image_fgcolor_ident'],
          'image_brightness' => $this->data['image_brightness_ident']
        );
        $colors = array(
          'image_bgcolor' => $this->data['image_bgcolor'],
          'image_fgcolor' => $this->data['image_fgcolor'],
          'image_brightness' => $this->data['image_brightness']
        );
        if (!(
              empty($this->data['image_bgcolor_ident']) &&
              empty($this->data['image_fgcolor_ident'])
            )) {
          include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
          $domainConnector = base_pluginloader::getPluginInstance(
            $this->domainConnectorGuid,
            $this
          );
          if (is_object($domainConnector)) {
            $colors = $domainConnector->readValues($fields, $colors);
          }
        }
        //create an image with the same size of the content image
        $result = imageCreateTrueColor($width, $height);
        //paint the background
        $bgColor = $controller->colorToRGB($colors['image_bgcolor']);
        if (defined('PAPAYA_THUMBS_FILETYPE') && PAPAYA_THUMBS_FILETYPE == 3 &&
            defined('PAPAYA_THUMBS_TRANSPARENT') && PAPAYA_THUMBS_TRANSPARENT) {
          $bgColorAlpha = round(substr($this->data['image_bgcolor_alpha'], 0, -1) * 1.27);
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
        if ($this->data['image_fgcolor_alpha'] == '100%') {
          imageCopy($result, $image, 0, 0, 0, 0, $width, $height);
        } elseif ($this->data['image_copy_mode'] == 1) {
          $this->imageCopyColor(
            $result,
            $image,
            $controller->colorToRGB($colors['image_fgcolor']),
            substr($this->data['image_fgcolor_alpha'], 0, -1),
            $colors['image_brightness']
          );
        } else {
          $this->imageCopyColorized(
            $result,
            $image,
            $controller->colorToRGB($colors['image_fgcolor']),
            substr($this->data['image_fgcolor_alpha'], 0, -1),
            $colors['image_brightness']
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

  /**
  * Get select box with available domain field identifiers
  * @param string $name
  * @param array $field
  * @param string $data
  * @return string
  */
  function getIdentifierCombo($name, $field, $data) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $domainConnector = base_pluginloader::getPluginInstance(
      $this->domainConnectorGuid,
      $this
    );
    if (is_object($domainConnector)) {
      return $domainConnector->getIdentifierCombo(
        $this->paramName.'['.$name.']',
        $data,
        TRUE
      );
    }
    return '';
  }

  /**
  * @see papaya-lib/system/base_dynamicimage#getCacheId($appendString)
  */
  function getCacheId($appendString = '') {
    if (isset($_SERVER['HTTP_HOST'])) {
      return parent::getCacheId($_SERVER['HTTP_HOST'].'_'.$appendString);
    } else {
      return parent::getCacheId();
    }
  }
}
?>