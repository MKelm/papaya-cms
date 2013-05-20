<?php
/**
* papaya Wiki, media upload page
*
* @copyright 2002-2008 by papaya Software GmbH - All rights reserved.
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
* @subpackage Beta-Wiki
* @version $Id: content_wiki_upload.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Base class base_content
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Wiki base class
*/
require_once(dirname(__FILE__).'/base_wiki.php');

/**
* papaya Wiki, standard page
*
* @package Papaya-Modules
* @subpackage Beta-Wiki
*/
class content_wiki_upload extends base_content {
  /**
  * Parameter namespace
  * @var string
  */
  public $paramName = 'wiki';

  /**
  * Edit fields
  * @var array
  */
  public $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 100, '', 'Wiki upload page'),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 7),
    'directory' => array('Upload folder', 'isNum', TRUE, 'mediafolder', NULL),
    'page_article' => array('Article page', 'isNum', FALSE, 'pageid', 10, '', 0),
    'Captions',
    'caption_article' => array('Article', 'isNoHTML', TRUE, 'input', 100, '', 'Article'),
    'caption_go' => array('Search article', 'isNoHTML', TRUE, 'input', 100, '', 'Go'),
    'caption_file' => array('File', 'isNoHTML', TRUE, 'input', 100, '', 'Upload file'),
    'caption_filename' => array(
      'File name',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Alternate file name'
    ),
    'caption_title' => array('File title', 'isNoHTML', TRUE, 'input', 100, '', 'Title'),
    'caption_description' => array(
      'File description',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Description'
    ),
    'caption_submit' => array('Submit button', 'isNoHTML', TRUE, 'input', 100, '', 'Upload'),
    'Messages',
    'success' => array('Success', 'isNoHTML', TRUE, 'input', 100, '', 'Successfully uploaded.'),
    'message_success' => array(
      'Success message',
      'isNoHTML',
      TRUE,
      'textarea',
      6,
      'Use %s as the filename placeholder',
      'Use the file in your wiki code as [[File:%s]].'
    ),
    'error_login' => array(
      'Login error',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'You need to be logged in.'
    ),
    'error_input' => array('Input error', 'isNoHTML', TRUE, 'input', 100, '', 'Input error.'),
    'error_no_file' => array('No file error', 'isNoHTML', TRUE, 'input', 100, '', 'No file.'),
    'error_size' => array('File size error', 'isNoHTML', TRUE, 'input', 100, '', 'File too large.'),
    'error_partial' => array(
      'Partial upload error',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'File was only uploaded partially.'
    ),
    'error_upload' => array('Upload error', 'isNoHTML', TRUE, 'input', 100, '', 'Upload error.'),
    'error_mediadb' => array(
      'Media DB error',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Media DB error.'
    ),
    'error_wiki_files' => array(
      'Wiki file error',
      'isNoHTML',
      TRUE,
      'input',
      100,
      '',
      'Wiki file error.'
    )
  );

  /**
  * base_wiki object
  * @var base_wiki
  */
  private $baseWiki = NULL;

  /**
  * Upload form
  * @var base_dialog
  */
  private $uploadForm = NULL;

  /**
  * File name set after successful upload
  * @var string
  */
  private $fileName = '';

  /**
  * Initialize wiki object
  *
  * @return base_wiki
  */
  public function getBaseWiki() {
    if (!is_object($this->baseWiki)) {
      $this->baseWiki = new base_wiki($this->msgs);
    }
    return $this->baseWiki;
  }

  /**
  * Handle a file upload, and on success store metadata in the wiki media table
  *
  * @return integer 0 on success, other values on error
  */
  function handleUpload() {
    $surfer = $this->papaya()->surfer;
    if (!$surfer->isValid) {
      return -2;
    }
    if (!isset($_FILES[$this->paramName]['tmp_name']['file'])) {
      return -1;
    }
    $upload = $_FILES[$this->paramName];
    if (!is_uploaded_file($upload['tmp_name']['file'])) {
      return -1;
    }
    if ($upload['error']['file'] != UPLOAD_ERR_OK) {
      return $upload['error']['file'];
    }
    $mediaDb = new base_mediadb_edit();
    $mediaId = $mediaDb->addFile(
      $upload['tmp_name']['file'],
      $upload['name']['file'],
      $this->data['directory'],
      $surfer->surferId,
      $upload['type']['file'],
      'uploaded_file'
    );
    if ($mediaId !== FALSE) {
      $baseWiki = $this->getBaseWiki();
      $success = $baseWiki->addFile($mediaId, $upload['name']['file'], $this->params);
      if ($success !== FALSE) {
        $this->fileName = $success;
        return 0;
      }
      return -4;
    }
    return -3;
  }

  /**
  * Upload a file, i.e. process the upload form
  *
  * @return string XML
  */
  public function uploadFile() {
    $result = '';
    $this->initializeUploadForm();
    if (is_object($this->uploadForm)) {
      if ($this->uploadForm->checkDialogInput()) {
        if (isset($_FILES[$this->paramName]['tmp_name']['file'])) {
          $upload = $_FILES[$this->paramName];
          $error = $this->handleUpload();
          switch ($error) {
          case 0:
            $result = $this->getMessageXml($this->data['success'], 'info');
            if (strstr($this->data['message_success'], '%s')) {
              $sucessMessage = sprintf(
                $this->data['message_success'].LF, $this->fileName
              );
            } else {
              $sucessMessage = $this->data['message_success'];
            }
            $result .= sprintf(
              '<info>%s</info>'.LF,
              papaya_strings::escapeHTMLChars($sucessMessage)
            );
            break;
          case -1:
          case UPLOAD_ERR_NO_FILE:
            $result = $this->getMessageXml($this->data['error_no_file']);
            break;
          case -2:
            $result = $this->getMessageXml($this->data['error_login']);
            break;
          case -3:
            $result = $this->getMessageXml($this->data['error_mediadb']);
            break;
          case -4:
            $result = $this->getMessageXml($this->data['error_wiki_files']);
            break;
          case UPLOAD_ERR_INI_SIZE:
          case UPLOAD_ERR_FORM_SIZE:
            $result = $this->getMessageXml($this->data['error_size']);
            break;
          case UPLOAD_ERR_PARTIAL:
            $result = $this->getMessageXml($this->data['error_partial']);
            break;
          }
          if ($error != 0) {
            $result .= $this->getUploadForm();
          }
        } else {
          $result = $this->getMessageXml($this->data['error_no_file']);
          $result .= $this->getUploadForm();
        }
      } else {
        $result = $this->getMessageXml($this->data['error_input']);
        $result .= $this->getUploadForm();
      }
    }
    return $result;
  }

  /**
  * Get the file upload form
  *
  * @return string form XML
  */
  public function getUploadForm() {
    $result = '';
    $this->initializeUploadForm();
    if (is_object($this->uploadForm)) {
      $result = $this->uploadForm->getDialogXML();
    }
    return $result;
  }

  /**
  * Initialize the file upload form
  */
  public function initializeUploadForm() {
    if (is_object($this->uploadForm)) {
      return;
    }
    $fields = array(
      'file' => array($this->data['caption_file'], 'isFile', FALSE, 'file', 255),
      'filename' => array($this->data['caption_filename'], 'isNoHTML', FALSE, 'input', 200),
      'title' => array($this->data['caption_title'], 'isNoHTML', FALSE, 'input', 200),
      'description' => array($this->data['caption_description'], 'isNoHTML', FALSE, 'textarea', 6)
    );
    $data = array();
    $hidden = array('upload' => 1);
    $this->uploadForm = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
    if (is_object($this->uploadForm)) {
      $this->uploadForm->uploadFiles = TRUE;
      $this->uploadForm->buttonTitle = $this->data['caption_submit'];
      $this->uploadForm->loadParams();
    }
  }

  /**
  * Get message XML
  *
  * @param string $message
  * @param string $type optional, default = 'error'
  * @return string XML
  */
  public function getMessageXml($message, $type = 'error') {
    return sprintf(
      '<message type="%s">%s</message>'.LF,
      papaya_strings::escapeHTMLChars($type),
      papaya_strings::escapeHTMLChars($message)
    );
  }

  /**
  * Get XML for the article selection form
  *
  * @return string XML
  */
  public function getArticleSelector() {
    $result = '';
    if ($this->data['page_article'] > 0) {
      $result .= sprintf(
        '<article-select href="%s">',
        $this->getWebLink($this->data['page_article'])
      );
      $result .= sprintf('<hidden param="%s[mode]"/>', $this->paramName);
      $result .= sprintf(
        '<field param="%s[article_field]" caption="%s" />',
        $this->paramName,
        papaya_strings::escapeHTMLChars($this->data['caption_article'])
      );
      $result .= sprintf(
        '<button caption="%s" />',
        papaya_strings::escapeHTMLChars($this->data['caption_go'])
      );
      $result .= '</article-select>';
    }
    return $result;
  }

  /**
  * Get parsed data
  *
  * Create XML content of the page
  *
  * @return string XML
  */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $result = sprintf(
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= $this->getArticleSelector();
    $surfer = $this->papaya()->surfer;
    if ($surfer->isValid) {
      $result .= sprintf('<text>%s</text>'.LF, $this->getXHTMLString($this->data['text']));
      if (isset($this->params['upload']) && $this->params['upload'] == 1) {
        $result = $this->uploadFile();
      } else {
        $result .= $this->getUploadForm();
      }
    } else {
      $result = $this->getMessageXml($this->data['error_login']);
    }
    return $result;
  }
}
?>