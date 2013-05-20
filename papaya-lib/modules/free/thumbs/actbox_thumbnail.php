<?php
/**
* Action box shows a random thumbnail from the media db
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
* @subpackage Free-Media
* @version $Id: actbox_thumbnail.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
/**
* file manager
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');

/**
* Action box shows a random thumbnail from the media db
*
* @package Papaya-Modules
* @subpackage Free-Media
*/
class actionbox_thumbnail extends base_actionbox {

  /**
  * Preview possible
  * @var boolean $preview
  */
  var $preview = TRUE;

  /**
   * MediaDB Object Instance
   * @var base_mediadb $mediaDB
   */
  var $mediaDB = NULL;

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'directory' => array('Folder', 'isNum', TRUE, 'mediafolder', '', '', ''),
    'resize' => array('Resize mode', 'isAlpha', TRUE, 'combo',
      array('abs' => 'Absolute', 'max' => 'Maximum', 'min' => 'Minimum',
        'mincrop' => 'Minimum cropped'), '', 'max'),
    'thumbwidth' => array('Thumbnail width', 'isNum', TRUE, 'input', 5, '', 100),
    'thumbheight' => array('Thumbnail height', 'isNum', TRUE, 'input', 5, '', 100),
    'url' => array('Link target', 'isNoHTML', FALSE, 'pageid', 800,
      'Please input a relative or an absolute URL.', ''),
    'use_title' => array('Display title', 'isNum', TRUE, 'yesno', 1,'',''),
    'link_class' => array(
      'CSS class for the link', 'isAlpha', FALSE, 'input', 200, '', 'thumbnailLink'
    )
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';

    $this->mediaDB = &base_mediadb::getInstance();
    $file = $this->mediaDB->getRandomImages(
      $this->data['directory'],
      $this->parentObj->currentLanguage['lng_id']
    );
    if ($file) {
      // get thumbnail object for generating linked thumbnails
      include_once(PAPAYA_INCLUDE_PATH.'/system/base_thumbnail.php');
      $thumbnailObj = new base_thumbnail();
      $thumbSrc = $this->getWebMediaLink(
        $thumbnailObj->getThumbnail(
          $file['file_id'],
          NULL,
          $this->data['thumbwidth'],
          $this->data['thumbheight'],
          $this->data['resize']
        ),
        'thumb',
        empty($file['file_title']) ? '' : papaya_strings::escapeHTMLChars($file['file_title'])
      );
      list($thumbWidth, $thumbHeight) = $thumbnailObj->lastThumbSize;

      $result = sprintf(
        '<img src="%s" class="%s" style="width: %dpx; height: %dpx;" alt="%s" />',
        papaya_strings::escapeHTMLChars($thumbSrc),
        papaya_strings::escapeHTMLChars(
          defined('PAPAYA_MEDIA_CSSCLASS_IMAGE')
           ? PAPAYA_MEDIA_CSSCLASS_IMAGE : 'papayaImage'
        ),
        (int)$thumbWidth,
        (int)$thumbHeight,
        empty($file['file_title']) ? '' : papaya_strings::escapeHTMLChars($file['file_title'])
      );
      if (!empty($this->data['url'])) {
        $result = sprintf(
          '<a class="%s" href="%s" title="%s">%s</a>',
          $this->data['link_class'],
          papaya_strings::escapeHTMLChars($this->getAbsoluteURL($this->data['url'], '', TRUE)),
          papaya_strings::escapeHTMLChars($file['file_title']),
          $result
        );
      }
      if (isset($this->data['use_title']) && !empty($file['file_title'])) {
        $result = sprintf(
          '<div class="papayaImage">%s<div class="%s">%s</div></div>',
          $result,
          papaya_strings::escapeHTMLChars(
            defined('PAPAYA_MEDIA_CSSCLASS_SUBTITLE')
             ? PAPAYA_MEDIA_CSSCLASS_SUBTITLE : 'papayaSubTitle'
          ),
          papaya_strings::escapeHTMLChars($file['file_title'])
        );
      }
    }
    return $result;
  }

}

?>
