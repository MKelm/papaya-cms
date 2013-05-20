<?php
/**
* Page module - Podcast Item
*
* A podcast item represents a file distributed over a podcast channel.
* It manages relevant meta information as well as a link to a media file
* stored in the media db. A podcast item could produce content but this
* is not the way it should be used. To create podcast content for a papaya
* page take a look to podcast-channel.
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
* @package Papaya-Modules
* @subpackage Free-Podcast
* @version $Id: content_podcast_item.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Base class for content
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - Podcast item
*
* @package Papaya-Modules
* @subpackage Free-Podcast
*/
class content_podcast_item extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'mediafile' => array('Mediafile', 'isSomeText', TRUE, 'mediafile', 200,
      'If your mediafile will be used in iTunes, note that the mediafile
       must be compatible (mp4, mov, m4v, mp3, m4a).', ''),
    'keywords' => array('Keywords', 'isNoHTML', FALSE, 'textarea', 8,
      'Maximum of 12 keywords, use commas to separate them', ''),
    'author' => array('Author', 'isNoHTML', FALSE, 'input', 60, '', ''),
    'Texts',
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'summary' => array('Summary', 'isNoHTML', FALSE, 'textarea', 10, '', ''),
    'description' => array('Description', 'isNoHTML', FALSE, 'textarea', 10, '', '')
  );

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string $result xml or ''
  */
  function getParsedData() {
    $this->setDefaultData();
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $this->mediaDB = new base_mediadb;
    if ($file = $this->mediaDB->getFile($this->data['mediafile'])) {
      $fileTitle = ($file['file_name'] != $file['file_id']) ? $file['file_name'] : '';
      $href = $this->getWebMediaLink($file['file_id'], 'download', $fileTitle);
      $result = sprintf(
        '<file download="%s" file_date="%s" file_size_human="%d"'.
        ' file_size="%d" file_type="%s" file_name="%s" file_id="%s"/>'.LF,
        papaya_strings::escapeHTMLChars($href),
        date('Y-m-d H:i:s', $file['file_date']),
        round((int)$file['file_size'] / 1024),
        (int)$file['file_size'],
        papaya_strings::escapeHTMLChars($file['mimetype']),
        papaya_strings::escapeHTMLChars($file['file_name']),
        papaya_strings::escapeHTMLChars($this->data['mediafile'])
      );
      $result .= sprintf(
        '<title encoded="%s">%s</title>'.LF,
        rawurlencode($this->data['title']),
        papaya_strings::escapeHTMLChars($this->data['title'])
      );
      $result .= sprintf(
        '<subtitle>%s</subtitle>'.LF,
        papaya_strings::escapeHTMLChars($this->data['subtitle'])
      );
      $result .= sprintf(
        '<summary>%s</summary>'.LF,
        $this->getXHTMLString($this->data['summary'], TRUE)
      );
      $result .= sprintf(
        '<description>%s</description>',
        $this->getXHTMLString($this->data['description'])
      );
      $result .= sprintf(
        '<keywords>%s</keywords>',
        papaya_strings::escapeHTMLChars($this->data['keywords'])
      );
      $result .= sprintf(
        '<author>%s</author>'.LF,
        papaya_strings::escapeHTMLChars($this->data['author'])
      );
      return $result;
    }
    return '';
  }

  /**
  * teaser of a podcast item page contains the same like the teaser
  *
  * @access public
  * @return string $result xml or ''
  */
  function getParsedTeaser() {
    return $this->getParsedData();
  }
}

?>