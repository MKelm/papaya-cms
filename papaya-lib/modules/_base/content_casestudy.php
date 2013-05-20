<?php
/**
* Page module casestudy
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
* @subpackage _Base
* @version $Id: content_casestudy.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* file manager
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
/**
* Page module casestudy
*
* combines article and image gallery
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_casestudy extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Papaya will apply your linebreaks to the output page.'),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 60),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 300),
    'teaser' => array('Teaser', 'isSomeText', TRUE, 'simplerichtext', 10),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 30),
    'Box',
    'fb_title' => array('Title', 'isSomeText', FALSE, 'input', 300),
    'fb_text' => array('Text', 'isSomeText', FALSE, 'simplerichtext', 10),
    'Images',
    'directory' => array('Folder', 'isNum', TRUE, 'function', 'callbackFolders', '',
      0),
    'width' => array('Width', 'isNum', TRUE, 'input', 5, '', 390),
    'height' => array('Height', 'isNum', TRUE, 'input', 5, '', 292),
    'Thumbnail',
    'thumbwidth' => array('Width', 'isNum', TRUE, 'input', 5, '', 100),
    'thumbheight' => array('Height', 'isNum', TRUE, 'input', 5, '', 100),
    'maxperline' => array('Per line', 'isNum', TRUE, 'input', 2, '', 3),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';
    $result .= sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<teaser>%s</teaser>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    $result .= sprintf(
      '<factbox title="%s">%s</factbox>'.LF,
      papaya_strings::escapeHTMLChars($this->data['fb_title']),
      $this->getXHTMLString($this->data['fb_text'], !((bool)$this->data['nl2br']))
    );
    $result .= $this->getThumbs();
    return $result;
  }

  /**
  * Get thumbs
  *
  * @access public
  * @return string
  */
  function getThumbs() {
    $result = '';
    $selected = '';
    if (isset($this->parentObj->topicId)) {
      $this->baseLink = $this->getBaseLink($this->parentObj->topicId);
    }

    $mediaDB = new base_mediadb;
    $files = array_values($mediaDB->getFiles($this->data['directory']));
    $count = $mediaDB->absCount;

    $min = 0;
    $max = (6 < $count) ? 5 : $count - 1;
    if (!isset($this->data['maxperline'])) {
      $this->data['maxperline'] = 3;
    }
    if (isset($this->params['mode']) && isset($this->params['img']) &&
        ($this->params['mode'] == 'max') && ($this->params['img'] >= 0)) {
      // full view
      $result .= '<image>';
      $file = $files[$this->params['img']];
      $fileTrans = $mediaDB->getFileTrans(
        $file['file_id'],
        $this->parentObj->currentLanguage['lng_id']
      );
      $result .= sprintf(
        '<papaya:media src="%s" width="%d" height="%d" align="center"/>'.LF,
        papaya_strings::escapeHTMLChars($file['file_id']),
        (int)$this->data['width'],
        (int)$this->data['height']
      );
      $result .= '</image>';
      if (!empty($fileTrans[$file['file_id']]['file_title'])) {
        $result .= sprintf(
          '<imagetitle>%s</imagetitle>',
          papaya_strings::escapeHTMLChars($fileTrans[$file['file_id']]['file_title'])
        );
      }
      if (!empty($fileTrans[$file['file_id']]['file_description'])) {
        $result .= sprintf(
          '<imagecomment>%s</imagecomment>',
          $this->getXHTMLString($fileTrans[$file['file_id']]['file_description'], TRUE)
        );
      }
      $result .= '<navigation>'.LF;
      $result .= sprintf(
        '<navlink dir="index" href="%s" />'.LF,
        $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array(
            'idx' => empty($this->params['idx']) ? 0: (int)$this->params['idx']
          )
        )
      );
      $result .= '</navigation>'.LF;
    }
    $result .= '<thumbnails>'.LF;
    $result .= '<line>'.LF;
    // get thumbnail object for generating linked thumbnails
    include_once(PAPAYA_INCLUDE_PATH.'/system/base_thumbnail.php');
    $thumbnailObj = new base_thumbnail();

    for ($i = $min; $i <= $max; $i++) {
      if ($file = $files[$i]) {
        if (isset($this->params['img']) && $this->params['img'] == $i) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $href = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array(
            'mode' => 'max',
            'idx' => empty($this->params['idx']) ? 0 : (int)$this->params['idx'],
            'img' => $i
          ),
          $this->paramName
        );
        $forHref = sprintf(
          'media.thumb.%s',
          $thumbnailObj->getThumbnail(
            $file['file_id'],
            NULL,
            $this->data['width'],
            $this->data['height']
          )
        );
        $result .= sprintf(
          '<thumb for="%s" %s><papaya:media href="%s" src="%s" width="%d" '.
            'height="%d" align="center" lspace="0" tspace="0" rspace="0" bspace="0" />'.
            '</thumb>'.LF,
          papaya_strings::escapeHTMLChars($forHref),
          $selected,
          papaya_strings::escapeHTMLChars($href),
          papaya_strings::escapeHTMLChars($file['file_id']),
          (int)$this->data['thumbwidth'],
          (int)$this->data['thumbheight']
        );
        $lineEnd = (
          isset($this->data['maxperline']) &&
          (($i + 1) % $this->data['maxperline']) == 0
        );
        if ($lineEnd && ($i < ($max))) {
          $result .= '</line><line>'.LF;
        }
      }
    }
    $result .= '</line>'.LF;
    $result .= '</thumbnails>'.LF;
    return $result;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = '';
    $result .= sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    return $result;
  }

  /** Get folders select box control
  *
  * @param string $name
  * @param array $field
  * @param string $data
  * @return string
  */
  function callbackFolders($name, $field, $data) {
    $mediaDB = new base_mediadb;
    $result = '';
    $folders = $mediaDB->getFolderComboArray(
      $this->parentObj->currentLanguage['lng_id']
    );

    $result .= sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">'.LF,
      $this->paramName,
      $name
    );
    foreach ($folders as $folderId => $folderName) {
      if (isset($data) && $data == $folderId) {
        $selected = ' selected="selected"';
      } else {
        $selected = '';
      }
      $result .= sprintf(
        '<option value="%s"%s>%s</option>'.LF,
        (int)$folderId,
        $selected,
        papaya_strings::escapeHTMLChars($folderName)
      );
    }
    $result .= '</select>'.LF;

    return $result;
  }
}

?>