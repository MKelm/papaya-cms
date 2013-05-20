<?php
/**
* Page module - download list.
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
* @version $Id: content_download.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Basic class content
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Page module - download list.
*
* @package Papaya-Modules
* @subpackage Free-Media
*/
class content_download extends base_content {

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.',
      0
    ),
    'title' => array('Title', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 5, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10, '', ''),
    'Downloads',
    'directory' => array('Folder', 'isNum', TRUE, 'mediafolder', NULL, '', ''),
    // currently only the first level of subdirectories is supported
    'recursive' => array('Files from subdirectories', 'isNum', TRUE, 'yesno', '',
      'This will show files from the selected folder and all direct subfolders (only first level).',
      0
    ),
    'sort' => array('Sort', 'isNum', TRUE, 'combo',
      array(
        0 => 'Alphabetical ascending',
        1 => 'Alphabetical descending',
        2 => 'Created ascending',
        3 => 'Created descending',
        4 => 'Sort ascending',
        5 => 'Sort descending'
      ),
      '',
      0
    ),
    'limit' => array('Files per page', 'isNum', FALSE, 'input', 2, '', 20),

    'Captions',
    'caption_file_name' => array('File name', 'isNoHTML', FALSE, 'input', 50, '', 'Name'),
    'caption_file_size' => array('File size', 'isNoHTML', FALSE, 'input', 50, '', 'Size'),
    'caption_file_date' => array('File date', 'isNoHTML', FALSE, 'input', 50, '', 'Date'),
    'caption_download' => array('Download', 'isNoHTML', FALSE, 'input', 50, '', 'Download'),
  );

  var $paramName = 'dwn';

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string $result
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = '';
    $result .= sprintf(
      '<title>%s</title>'.LF,
      $this->getXHTMLString($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      $this->getXHTMLString($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    return $result;
  }


  /**
  * Get parsed data
  *
  * @access public
  * @return string $result
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '';
    $this->initializeParams();
    $result .= sprintf(
      '<title>%s</title>'.LF,
      $this->getXHTMLString($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      $this->getXHTMLString($this->data['subtitle'])
    );
    $result .= sprintf(
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    // set captions for downloads
    $result .= sprintf(
      '<captions>'.LF.
        '<file_name>%s</file_name>'.LF.
        '<file_size>%s</file_size>'.LF.
        '<file_date>%s</file_date>'.LF.
        '<download>%s</download>'.LF.
        '</captions>'.LF,
      papaya_strings::escapeHTMLChars($this->data['caption_file_name']),
      papaya_strings::escapeHTMLChars($this->data['caption_file_size']),
      papaya_strings::escapeHTMLChars($this->data['caption_file_date']),
      papaya_strings::escapeHTMLChars($this->data['caption_download'])
    );

    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $this->mediaDB = &base_mediadb::getInstance();
    if (isset($this->params['file_id'])) {
      $result .= $this->getFileInformationXML($this->params['file_id']);
    } else {
      $result .= $this->getFileListXML();
    }
    return $result;
  }

  /**
  * This method generates XML with detailed information on a single file.
  *
  * @param string $fileId the file id
  * @return string $result file details XML
  */
  function getFileInformationXML($fileId) {
    $result = '';
    if ($file = $this->mediaDB->getFile($fileId)) {
      $fileTrans = $this->mediaDB->getFileTrans(
        $fileId,
        $this->parentObj->getContentLanguageId()
      );
      if (isset($fileTrans) && isset($fileTrans[$fileId])) {
        $fileTrans = $fileTrans[$fileId];
      } else {
        $fileTrans = array(
          'file_title' => '',
          'file_description' => ''
        );
      }
      $fileTitle = ($file['file_name'] != $file['file_id']) ? $file['file_name'] : '';

      $href = $this->getWebMediaLink($file['file_id'], 'download', $fileTitle);

      $result .= sprintf(
        '<file download="%s" file_id="%s" file_name="%s" '.
          'file_size="%s kB" file_type="%s" file_date="%s" file_title="%s">%s</file>',
        papaya_strings::escapeHTMLChars($href),
        papaya_strings::escapeHTMLChars($file['file_id']),
        papaya_strings::escapeHTMLChars($fileTitle),
        round($file['file_size'] / 1024),
        papaya_strings::escapeHTMLChars($file['mimetype']),
        date('Y-m-d H:i:s', $file['file_date']),
        papaya_strings::escapeHTMLChars($fileTrans['file_title']),
        $this->getXHTMLString($fileTrans['file_description'])
      );
    }
    return $result;
  }

  /**
  * This method generates the list of files in a folder or optionally with the files in
  * the first level of subfolders (this could be extended to allow arbitrary recursion depth).
  *
  * @return string $result list of files and folders
  */
  function getFileListXML() {
    $result = '';
    if (isset($this->params['offset']) && $this->params['offset'] > 0) {
      $offset = (int)$this->params['offset'];
    } else {
      $offset = 0;
    }

    $sort = $this->getSort();
    $order = $this->getOrder();
    $languageId = $this->parentObj->getContentLanguageId();

    if (isset($this->data['recursive']) && $this->data['recursive']) {
      $folders = $this->mediaDB->getSubFolders($languageId, $this->data['directory']);
      $parentDir = $this->mediaDB->getFolders($languageId, $this->data['directory']);
      if (isset($parentDir[$this->data['directory']])) {
        $folders[$this->data['directory']] = $parentDir[$this->data['directory']];
        $folder = array_keys($folders);
      }
      // add the parent folder to the list of folders to retrieve file from
      $folder[] = $this->data['directory'];
    } else {
      $folder = $this->data['directory'];
    }
    $files = $this->mediaDB->getFilesTranslated(
      $this->parentObj->getContentLanguageId(),
      $folder,
      (int)$this->data['limit'],
      $offset,
      $sort,
      $order
    );

    if (isset($files)) {
      $result .= sprintf(
        '<files count="%d" limit="%d" offset="%d">',
        (int)$this->mediaDB->absCount,
        (int)$this->data['limit'],
        (int)$offset
      );
      foreach ($files as $id => $file) {
        $href = $this->getWebLink(
          NULL,
          NULL,
          NULL,
          array(
            'file_id' => $file['file_id'],
            'offset' => $offset
          ),
          $this->paramName
        );

        $fileTitle = (isset($files[$file['file_id']]['file_title']))
          ? $files[$file['file_id']]['file_title'] : $file['file_name'];

        $result .= sprintf(
          '<file href="%s" file_id="%s" file_name="%s" file_size="%s kB" folder_id="%d" '.
            'file_type="%s" file_date="%s" file_title="%s" download="%s">%s</file>',
          $href,
          papaya_strings::escapeHTMLChars($file['file_id']),
          papaya_strings::escapeHTMLChars($file['file_name']),
          round($file['file_size'] / 1024),
          $file['folder_id'],
          papaya_strings::escapeHTMLChars($file['mimetype']),
          date('Y-m-d H:i:s', $file['file_date']),
          papaya_strings::escapeHTMLChars($fileTitle),
          $this->getWebMediaLink($file['file_id'], 'download', $fileTitle, $file['mimetype_ext']),
          empty($file['file_description']) ? '' : $this->getXHTMLString($file['file_description'])
        );
      }
      $result .= '</files>';
      if (isset($folders) && is_array($folders) && count($folders) > 0) {
        $result .= '<folders>'.LF;
        foreach ($folders as $folderId => $folder) {
          $parentFolder = ($folderId == $this->data['directory']) ? ' parent="1" ' : '';
          $result .= sprintf(
            '<folder id="%d" name="%s" %s/>'.LF,
            $folderId,
            papaya_strings::escapeHTMLChars($folder['folder_name']),
            $parentFolder
          );
        }
        $result .= '</folders>'.LF;
      }
    }
    return $result;
  }

  /**
  * This method returns the sort criterion based on the value of $this->data[sort]
  *
  * @return string $sort name, title, date or sort
  */
  function getSort() {
    $sort = 'name';
    if (isset($this->data['sort'])) {
      switch ($this->data['sort']) {
      case 0:
      case 1:
        $sort = 'title';
        break;
      case 2 :
      case 3 :
        $sort = 'date';
        break;
      case 4 :
      case 5 :
        $sort = 'sort';
        break;
      }
    }
    return $sort;
  }

  /**
  * This method returns the sort order based on the value of $this->data[sort]
  *
  * @return string $order ASC|DESC
  */
  function getOrder() {
    $order = 'ASC';
    if (isset($this->data['sort'])) {
      switch ($this->data['sort']) {
      case 0:
      case 2 :
      case 4 :
        $order = 'ASC';
        break;
      case 1:
      case 3 :
      case 5 :
        $order = 'DESC';
        break;
      }
    }
    return $order;
  }
}
?>