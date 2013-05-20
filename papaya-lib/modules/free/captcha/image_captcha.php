<?php
/**
* Generate a captcha image and save the text value into the session
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
* @subpackage Free-Captcha
* @version $Id: image_captcha.php 38496 2013-05-17 10:32:04Z weinert $
*/

/**
* Base class for image plugins
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dynamicimage.php');

require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');

/**
* Generate a button using a background image or color and a text
*
* image  plugins must be inherited from this superclass
*
* @package Papaya-Modules
* @subpackage Free-Captcha
*/
class image_captcha extends base_dynamicimage {

  /**
  * @var boolean $cacheable it makes no sense to cache captchas, since they are random
  */
  var $cacheable = FALSE;

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'image' => array('Image', 'isSomeText', FALSE, 'imagefixed', 400, '', ''),
    'padding' => array('Padding', 'isNum', TRUE, 'input', 2, '', 20),
    'image_color' => array('Background color', 'isHTMLColor', TRUE, 'color', 7, '',
      '#FFFFFF'),
    'number_of_chars' => array('Number of chars', 'isNum', TRUE, 'input', 2, '', '2'),
    'line_width' => array('Line width', 'isNum', TRUE, 'input', 2, '', 2),
    'setting_chars' => array('Valid characters', 'isNoHTML', TRUE, 'input', 200, '',
      'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'),
    'Wave settings',
    'setting_waves_x' => array('Intensity (X)', 'isNum', TRUE, 'input', 4, '', 6),
    'setting_amplitude_x' => array('Amplitude (X)', 'isNum', TRUE, 'input', 4, '', 2),
    'setting_waves_y' => array('Intensity (Y)', 'isNum', TRUE, 'input', 4, '', 2),
    'setting_amplitude_y' => array('Amplitude (Y)', 'isNum', TRUE, 'input', 4, '', 1),
    'Font',
    'font_type' => array('Font', 'isSomeText', TRUE, 'mediafile', 400, '', ''),
    'font_size' => array('Size', 'isNum', TRUE, 'input', 4, '', 18),
    'font_color' => array('Base Color', 'isHTMLColor', TRUE, 'color', 7, '', '#FFAAAA')
  );

  /**
  * Attribute fields
  * @var array $attributeFields
  */
  var $attributeFields = array(
    'identifier' => array('Identifier', 'isGUID', TRUE, 'input', 32, '',
      '123456789012345678901234567890AF')
  );

  /**
  * Session index name
  * @var string
  */
  var $sessionIndex = '';

  /**
  * Chars
  * @var array $chars
  */
  var $chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'Q', 'J', 'K',
    'L', 'M', 'N', 'P', 'R', 'S', 'T', 'U', 'V', 'Y', 'W', '2', '3', '4', '5',
    '6', '7', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p',
    'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
  );

  /**
  * Captcha text
  * @var array $captchaText
  */
  var $captchaText = array();

  /**
  * Captcha String
  * @var string
  */
  var $captchaString = '';

  /**
  * cachable
  * @var boolean
  */
  var $cachable = FALSE;

  /**
  * image width
  * @var integer
  */
  var $imageWidth = 1;

  /**
  * image height
  * @var integer
  */
  var $imageHeight = 1;

  /**
  * text width
  * @var integer
  */
  var $textWidth = 30;

  /**
  * text height
  * @var integer
  */
  var $textHeight = 120;

  /**
  * max x move wave
  * @var integer
  */
  var $maxXMoveWave = 0;

  /**
  * max y move wave
  * @var integer
  */
  var $maxYMoveWave = 0;

  /**
  * font color
  * @var array
  */
  var $fontColor = array();

  /**
  * generate the captcha image
  *
  * @param object base_imagegenerator &$controller controller object
  * @access private
  * @return image $result resource id
  */
  function &generateImage(&$controller) {
    $this->setDefaultData();
    $this->chars = preg_split('/(?<=.)(?=.)/s', $this->data['setting_chars']);

    $sessionIndex = 'PAPAYA_SESS_CAPTCHA';
    $sessionData = $this->getSessionValue($sessionIndex);

    $this->_randomizedChangeOfFontColor($controller);

    $this->_generateRandomCaptchaString();

    $sessionData[$this->attributes['identifier']] = $this->captchaString;
    $this->setSessionValue($sessionIndex, $sessionData);

    // calcultae the maximum image sizes
    $this->_calculateImageSizes();

    //create image with the captcha chars and apply wave filter on it
    $imageString = $this->_generateImageString();
    $imageFinal = $this->_generateTransparentElements($imageString);
    $imageFinal = $this->imageFilterWaves($imageFinal, $controller);
    imageDestroy($imageString);

    $result = $this->_createBackground($controller);
    #$result = &$this->imageCreateAlpha();
    $this->textPos = $this->_generateTextPositionRandomized();

    // merge the filtered image onto the background
    imageCopy(
      $result,
      $imageFinal,
      $this->textPos['x'],
      $this->textPos['y'],
      0,
      0,
      $this->imageWidth,
      $this->imageHeight
    );
    return $result;
  }

  /**
  * image create alpha
  *
  * @access public
  * @return image resource id
  */
  function &imageCreateAlpha() {
    $result = imageCreateTrueColor($this->imageWidth, $this->imageHeight);
    imageSaveAlpha($result, TRUE);
    imageAlphaBlending($result, FALSE);
    $bgColor = imagecolorallocatealpha($result, 220, 220, 220, 127);
    imagefill($result, 0, 0, $bgColor);
    imageAlphaBlending($result, TRUE);
    return $result;
  }

  /**
  * randomize font color
  *
  * @param &$controller
  * @access private
  * @return nothing
  */
  function _randomizedChangeOfFontColor(&$controller) {
    $fColor = $controller->colorToRGB($this->data['font_color']);

    foreach ($fColor as $key => $value) {
      $fcPerc = 0.4 * $value;
      $change = rand(0, 2 * $fcPerc);
      $change -= $fcPerc;
      if (($value + $change) > 255) {
        $this->fontColor[$key] = 255;
      } elseif (($value + $change) < 1) {
        $this->fontColor[$key] = 0;
      } else {
        $this->fontColor[$key] = $value + $change;
      }
    }
  }

  /**
  * generate random captcha string
  *
  * @access private
  */
  function _generateRandomCaptchaString() {
    $this->captchaString = '';
    $this->captchaChars = array();
    $charCount = count($this->chars) - 1;
    for ($i = 0; $i < $this->data['number_of_chars']; $i++) {
      srand(((double)microtime() * 1000000));
      $chr = $this->chars[rand(0, $charCount)];
      $this->captchaChars[] = array('char' => $chr);
      $this->captchaString .= $chr;
    }
  }

  /**
  * calculate image sizes
  *
  * @access private
  * @return nothing
  */
  function _calculateImageSizes() {
    $mediaDB = base_mediadb::getInstance();
    $fontSize = (int)$this->data['font_size'];
    $padding = (int)$this->data['padding'];

    $fontType = $mediaDB->getFileName($this->data['font_type']);
    if ($fontType) {
      $totalWidth = 0;
      $totalHeight = array();
      foreach ($this->captchaChars as $idx => $data) {
        srand();
        $angle = rand(0, 40);
        $angle -= 20;
        $size = imagettfbbox($fontSize, $angle, $fontType, $data['char']);
        $this->captchaChars[$idx]['angle'] = $angle;
        $width = abs(min($size[0], $size[6])) + abs(max($size[2], $size[4]));
        $height = abs(min($size[1], $size[3])) + abs(max($size[5], $size[7]));
        $this->captchaChars[$idx]['width'] = $width;
        $this->captchaChars[$idx]['height'] = $height;
        $totalWidth += $width;
        $totalHeight[] = $height;
      }
      if (count($totalHeight) > 0) {
        $this->textHeight = max($totalHeight);
        $this->textWidth = $totalWidth;
      } else {
        $this->textHeight = 30;
        $this->textWidth = 120;
      }

      $this->imageWidth = $this->textWidth + ($padding * 2);
      $this->imageHeight = (int)$this->textHeight + ($padding * 2);
    }
    if ($this->imageWidth < 1) {
      $this->imageWidth = 1;
    }
    if ($this->imageHeight < 1) {
      $this->imageHeight = 1;
    }
  }

  /**
  * generate image string
  *
  * @param &$controller
  * @access private
  * @return image resource id
  */
  function _generateImageString() {
    $mediaDB = base_mediadb::getInstance();
    $fontSize = (int)$this->data['font_size'];
    $padding = (int)$this->data['padding'];

    $result = &$this->imageCreateAlpha();
    if ($fontType = $mediaDB->getFileName($this->data['font_type'])) {
      //get positions from padding
      $posX = $padding;
      $posY = $this->imageHeight - $padding;

      //get text color index
      $fontColorIdx = imagecolorallocate(
        $result,
        $this->fontColor['red'],
        $this->fontColor['green'],
        $this->fontColor['blue']
      );

      //copy text into $result
      foreach ($this->captchaChars as $idy => $data) {
        imagettftext(
          $result,
          $fontSize,
          $data['angle'],
          $posX,
          $posY,
          $fontColorIdx,
          $fontType,
          $data['char']
        );
        $posX += $data['width'];
      }
    }
    return $result;
  }

  /**
  * image filter waves
  *
  * @param $srcImage
  * @param &$controller
  * @access private
  * @return image resource id
  */
  function imageFilterWaves($srcImage, &$controller) {
    $imageHeight = imagesy($srcImage);
    $imageWidth = imagesx($srcImage);

    $result = &$this->imageCreateAlpha($imageWidth, $imageHeight);

    /* set color for controlling the waves
    $color = $controller->colorToRGB('#FF0000');
    $colorIdx = imagecolorallocate($resourceTarget,
      $color['red'], $color['green'], $color['blue']);
    */

    $waves = $this->data['setting_waves_x'];
    $amplitude = $this->data['setting_amplitude_x']; // 2 - 4
    $divisor = $imageWidth / $waves;
    for ($x = 0; $x < $imageWidth; $x++) {
      $ang = ($x / $divisor) * (2 * pi());
      $y = round(sin($ang) * $amplitude);
      $dotY = $imageHeight - $y - (pi() / 2 * $amplitude) - 1;

      $vertChange = $y + (pi() / 2 * $amplitude) + 1;
      if ($vertChange > $this->maxYMoveWave) {
        $this->maxYMoveWave = $vertChange;
      }

      $dstY = $dotY - $imageHeight;
      $width = 1;
      $height = $imageHeight;

      ImageCopy($result, $srcImage, $x, $dstY, $x, 0, $width, $height);
      //ImageSetPixel($result, $x, $dotY, $colorIdx);
    }

    $waves = $this->data['setting_waves_y'];
    $amplitude = $this->data['setting_amplitude_y'];
    $divisor = $imageHeight / $waves;
    for ($y = 0; $y < $imageHeight; $y++) {
      $ang = ($y / $divisor) * (2 * pi());
      $x = round(sin($ang) * $amplitude);
      $dotX = $imageWidth - $x - (pi() / 2 * $amplitude) - 1;

      $horiChange = $x + (pi() / 2 * $amplitude) + 1;
      if ($vertChange > $this->maxXMoveWave) {
        $this->maxXMoveWave = $vertChange;
      }

      $dstX = $dotX - $imageWidth;
      $width = $imageWidth;
      $height = 1;

      ImageCopy($result, $result, $dstX, $y, 0, $y, $width, $height);
      //ImageSetPixel($result, $dotX , $y, $colorIdx);
    }
    return $result;
  }

  /**
  * generate transparent elements
  *
  * @param $image
  * @param &$controller
  * @access private
  * @return image resource id
  */
  function _generateTransparentElements($image) {
    $transColor = imagecolorallocatealpha(
      $image,
      $this->fontColor['red'],
      $this->fontColor['green'],
      $this->fontColor['blue'],
      127
    );

    $vrtlH = round($this->imageHeight / 5);
    $vrtlW = round($this->imageWidth / 5);

    if (isset($this->data['line_width']) && $this->data['line_width'] > 0) {
      imageAlphaBlending($image, FALSE);

      $brush = imageCreateTrueColor($this->data['line_width'], $this->data['line_width']);
      imageSaveAlpha($brush, TRUE);
      imageAlphaBlending($brush, FALSE);
      imagefill($brush, 0, 0, $transColor);
      imagesetbrush($image, $brush);

      // diagonal top left to bottom right
      imageLine($image, 0, 0, $this->imageWidth, $this->imageHeight, IMG_COLOR_BRUSHED);
      // diagonal bottom left to top right
      imageLine($image, 0, $this->imageHeight, $this->imageWidth, 0, IMG_COLOR_BRUSHED);
      // vertical left
      imageLine($image, $vrtlW, 0, $vrtlW, $this->imageHeight, IMG_COLOR_BRUSHED);
      // vertical right
      $xPos = $this->imageWidth - $vrtlW;
      imageLine($image, $xPos, 0, $xPos, $this->imageHeight, IMG_COLOR_BRUSHED);
      imageLine($image, $xPos - 1, 0, $xPos - 1, $this->imageHeight, IMG_COLOR_BRUSHED);

      imagedestroy($brush);
      imageAlphaBlending($image, TRUE);
    }
    return $image;
  }

  /**
  * create background
  *
  * @param &$controller
  * @access private
  * @return image resource id
  */
  function _createBackground(&$controller) {
    $result = &$this->imageCreateAlpha();
    // load background into result
    if ($imgBG = &$controller->getMediaFileImage($this->data['image'])) {
      // fill Image with background
      $bgSrcWidth = imagesx($imgBG);
      $bgSrcHeight = imagesy($imgBG);
      $hRepeat = 1;
      $vRepeat = 1;

      if ($bgSrcWidth < $this->imageWidth || $bgSrcHeight < $this->imageHeight) {
        $result = $this->_getRepeatBackground($result, $imgBG);
      } elseif ($bgSrcWidth > $this->imageWidth ||
                $bgSrcHeight > $this->imageHeight) {
        $result = $this->_getRandomBackgroundCutout($result, $imgBG);
      } else {
        imagecopy($result, $imgBG, 0, 0, 0, 0, $this->imageWidth, $this->imageHeight);
      }
    } else {
      $color = $controller->colorToRGB($this->data['image_color']);
      $colorIdx = imagecolorallocate(
        $result, $color['red'], $color['green'], $color['blue']
      );
      imagefill($result, 1, 1, $colorIdx);
    }
    return $result;
  }

  /**
  * get repeat background
  *
  * @param $destImg
  * @param $imgBG
  * @access private
  * @return image resource id
  */
  function _getRepeatBackground($destImg, $imgBG) {
    $bgSrcWidth = imagesx($imgBG);
    $bgSrcHeight = imagesy($imgBG);
    $hRepeat = 1;
    $vRepeat = 1;
    if ($bgSrcWidth < $this->imageWidth) {
      $hRepeat = ceil($this->imageWidth / $bgSrcWidth);
    }
    if ($bgSrcHeight < $this->imageHeight) {
      $vRepeat = ceil($this->imageHeight / $bgSrcHeight);
    }

    $startX = 0;
    $startY = 0;

    for ($i = 0; $i < $hRepeat; $i++) {
      imagecopy($destImg, $imgBG, $startX, $startY, 0, 0, $bgSrcWidth, $bgSrcHeight);
      $startX += $bgSrcWidth;
    }
    $startX = 0;
    $startY = $bgSrcHeight;
    for ($i = 1; $i < $vRepeat; $i++) {
      imagecopy(
        $destImg,
        $destImg,
        $startX,
        $startY,
        0,
        0,
        $this->imageWidth,
        $this->imageHeight
      );
      $startY += $bgSrcHeight;
    }

    return $destImg;
  }

  /**
  * get random background cutout
  *
  * @param $destImage
  * @param $bgImage
  * @access private
  * @return image resource id
  */
  function _getRandomBackgroundCutout($destImage, $bgImage) {
    $bgSrcWidth = imagesx($bgImage);
    $bgSrcHeight = imagesy($bgImage);

    $destWidth = imagesx($destImage);
    $destHeight = imagesx($destImage);

    $selAreaWidth = $bgSrcWidth - $destWidth;
    $selAreaHeight = $bgSrcHeight - $destHeight;

    $selAreaX = rand(0, $selAreaWidth);
    $selAreaY = rand(0, $selAreaHeight);

    imagecopy(
      $destImage,
      $bgImage,
      0,
      0,
      $selAreaX,
      $selAreaY,
      $this->imageWidth,
      $this->imageHeight
    );
    return $destImage;
  }

  /**
  * get text position randomized
  *
  * @access private
  * @return array array('x' => pixel, 'y' => pixel)
  */
  function _generateTextPositionRandomized() {
    $padding = (int)$this->data['padding'];
    srand((double)microtime() * 1000000);

    $newX = rand($this->maxXMoveWave, $this->imageWidth - $this->textWidth);
    $newY = rand($this->maxYMoveWave, $this->imageHeight - $this->textHeight);

    return array(
      'x' => ($newX - $padding - $this->maxXMoveWave),
      'y' => ($newY - $padding - $this->maxYMoveWave)
    );
  }
}
?>
