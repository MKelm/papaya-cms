<?php
/**
* Action box for FlowPlayer integration
*
* The flowplayer has to be uploaded to the mediaDB
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
* @subpackage Free-Include
* @version $Id: content_flowplayer.php 34957 2010-10-05 15:57:41Z weinert $
* @link http://flowplayer.sourceforge.net/
*/

/**
* Basic class action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Action box for FlowPlayer integration
*
* @package Papaya-Modules
* @subpackage Free-Include
*/
class content_flowplayer extends base_content {

  /**
  * Preview ?
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'), 'Apply linebreaks from input to the HTML output.', 0),
    'title' => array('Title', 'isSomeText', FALSE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 5, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, '', ''),

    'Files',
    'flashfile' => array('Flowplayer file', '#^[a-z\d]{32}$#', TRUE, 'mediafile', 100,
      'Flowplayer media ID', ''),
    'flashmovie' =>
      array('Movie file (flv)', '#^[a-z\d]{32}$#', TRUE, 'mediafile', 100,
        'Flash movie media ID', ''),
    'General',
    'width' => array('Width', 'isNum', FALSE, 'input', 5, '', ''),
    'height' => array('Height', 'isNum', FALSE, 'input', 5, '', ''),
    'quality' => array('Quality', 'isAlpha', FALSE, 'combo',
      array('high' => 'High', 'medium' => 'Medium', 'low' =>'Low'), '', 'high'),
    'Flowplayer options',
    'baseURL' => array('Base URL', 'isHTTP', FALSE, 'input', 100),
    'autoPlay' => array('Start movie automatically', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'No', 1 => 'Yes'), '', 1),
    'autoBuffering' => array('Automatic buffering', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'No', 1 => 'Yes'), '', 1),
    'bufferLength' => array('Buffer length', 'isNum', FALSE, 'input', 3,
      'in Percent (%)', 5),
    'loop' => array('Loop Movie', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'No', 1 => 'Yes'), '', 1),
    'backgroundColor' => array('Background color', 'isNoHTML', TRUE,
      'input', 7, '#RRGGBB', '#FFFFFF'),
    'progressBarColor1' => array('Progress bar color 1', '/0x[0-9A-F]{6}/i', TRUE,
      'input', 8, '0xRRGGBB', '0xCCCCCC'),
    'progressBarColor2' => array('Progress bar color 2', '/0x[0-9A-F]{6}/i', TRUE,
      'input', 8, '0xRRGGBB', '0x999999'),
    'videoHeight' => array('Video height', 'isNum', FALSE, 'input', 5, '', ''),
    'hideControls' => array('Hide controls', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'No', 1 => 'Yes'), '', 0),
    'hideBorder' => array('Hide borders', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'No', 1 => 'Yes'), '', 1),
    'skinImagesBaseURL' => array('Skin images base URL', 'isHTTP', FALSE,
      'input', 100),
    'useEmbeddedButtonImages' => array('Use embedded button images', 'isNum', TRUE,
      'translatedcombo', array(0 => 'No', 1 => 'Yes'),
      'Switch to "No" only if skinImagesBaseURL is true and images exist!', 1),
    'logoFile' => array('Logo file', 'isNoHTML', FALSE, 'input', 100, '', ''),
    'splashImageFile' => array('Splash image file', 'isNoHTML', FALSE, 'imagefixed', 100, '', ''),
    'scaleSplash' => array('Scale splash image', 'isNum', TRUE, 'translatedcombo',
      array(0 => 'No', 1 => 'Yes'), '', 0),
    'Alternative texts',
    'noflash' => array('No Flash', 'isSomeText', FALSE, 'textarea', 10, '')
  );

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string $result
  */
  function getParsedTeaser() {
    $result .= sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      "<text>%s</text>".LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    return $result;
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $result .= sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      "<text>%s</text>".LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $mediaDB = base_mediadb::getInstance();
    $result = '';
    $fileName = '';
    if (preg_match('#^[a-z\d]{32}\.swf$#', $this->data['flashfile']) &&
        ($mediaFile = $mediaDB->getFile($this->data['flashfile'])) &&
        in_array($mediaFile['FILETYPE'], array(4, 13)) &&
        file_exists($mediaFile['FILENAME'])) {
      $fileName = $this->getWebMediaLink(
        $mediaFile['file_id'], 'media', $mediaFile['file_name'], $mediaFile['mimetype_ext']
      );
      if (empty($this->data['width'])) {
        $this->data['width'] = $mediaFile['WIDTH'];
      }
      if (empty($this->data['height'])) {
        $this->data['height'] = $mediaFile['HEIGHT'];
      }
    } elseif (file_exists(getenv('DOCUMENT_ROOT').'/'.$this->data['flashfile'])) {
      $fileName = $this->data['flashfile'];
    } elseif (checkit::isHTTPX($this->data['flashfile'], TRUE) &&
              (strtolower(substr($this->data['flashfile'], -4)) == '.swf')) {
      $fileName = $this->data['flashfile'];
    } else {
      $fileName = PAPAYA_PATH_WEB.'papaya-script/FlowPlayer.swf';
    }
    if ($movie = $mediaDB->getFile($this->data['flashmovie'])) {
      include_once(PAPAYA_INCLUDE_PATH.'system/papaya_swfobject.php');
      $swfObject = new papaya_swfobject();
      $swfObject->setNoFlashMessage(
        $this->getXHTMLString($this->data['noflash'])
      );
      $flashVars = array(
        'baseURL' => $this->data['baseURL'],
        'videoFile' => $this->getWebMediaLink(
          $movie['FILENAME'], 'media', $movie['file_name'], $movie['mimetype_ext']
        ),
        'autoplay' => $this->data['autoPlay'] ? 'true' : 'false',
        'autoBuffering' => $this->data['autoBuffering'] ? 'true' : 'false',
        'bufferLength' => $this->data['bufferLength'],
        'loop' => $this->data['loop'] ? 'true' : 'false',
        'progressBarColor1' => $this->data['progressBarColor1'],
        'progressBarColor1' => $this->data['progressBarColor1'],
        'videoHeight' => $this->data['videoHeight'],
        'hideControls' => $this->data['hideControls'] ? 'true' : 'false',
        'skinImagesBaseURL' => $this->data['skinImagesBaseURL'],
        'skinImagesBaseURL' => $this->data['skinImagesBaseURL'],
        'useEmbeddedButtonImages' => $this->data['useEmbeddedButtonImages'] ? 'true' : 'false',
        'logoFile' => $this->data['logoFile']
      );
      if ($splashImage = $mediaDB->getFile($this->data['splashImageFile'])) {
        $flashVars['splashImageFile'] = $this->getWebMediaLink(
          $splashImage['FILENAME'],
          'media',
          $splashImage['file_name'],
          $splashImage['mimetype_ext']
        );
        $flashVars['scaleSplash'] = $this->data['scaleSplash'] ? 'true' : 'false';
      }
      $swfObject->setFlashVars($flashVars);
      $swfObject->setSWFParam('bgcolor', $this->data['backgroundColor']);
      $swfObject->setSWFParam(
        'salign',
        str_replace('m', '', $this->data['svalign'].$this->data['shalign'])
      );
      $swfObject->setSWFParam('scale', $this->data['scale']);
      $swfObject->setSWFParam('quality', $this->data['quality']);

      $result .= '<videoplayer>';
      $result .= $swfObject->getXHTML(
        $fileName, $this->data['width'], $this->data['height']
      );
      $result .= '</videoplayer>';
    }
    return $result;
  }
}
?>