<?php
/**
* Page module - Community surfer avatar image upload module.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage _Base-Community
* @version $Id: content_avatarupload.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Media db (for avatar upload)
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');

/**
* Basic class check conditions
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_checkit.php');

/**
* Basic class surfer administration
*/
require_once(dirname(__FILE__).'/base_surfers.php');

/**
* Basic class to generate dialog forms.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');


/**
* Page module - Community surfer avatar image upload module.
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class content_avatarupload extends base_content {

  /**
   * Basic community amdmin object
   * @var object $surferAdmin
   */
  var $surferAdmin = NULL;

  /**
   * Handle to media database
   * @var object $mediaDB
   */
  var $mediaDB = NULL;

  /**
   * Form definition array.
   * @var $editFields
   */
  var $editFields = array(
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200, ''),
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.'),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 10),
    'MediaDB',
    'directory' => array('Image folder', 'isNum', TRUE, 'mediafolder'),
    'perm_upload_avatar' => array('Upload permission', 'isNum', TRUE, 'function',
      'getPermsCombo', '', 30),
    'Captions',
    'caption_upload_image' => array('Upload image text', 'isNoHtml', TRUE, 'input', 200, '',
      'Avatar image'),
    'caption_upload_button' => array('Upload button caption', 'isNoHtml', TRUE, 'input', 200, '',
      'upload'),
    'Success messages',
    'success_upload' => array('Success upload', 'isNoHtml', TRUE, 'input', 200,'',
      'Avatar successfully uploaded'),
    'Error messages',
    'error_not_authenticated' => array('Not authorized', 'isNoHtml', TRUE, 'input', 200,'',
      'Please log in first.'),
    'error_no_permission' => array('No upload permission', 'isNoHtml', TRUE, 'input', 200, '',
      'You do not have the necessary permission to upload images.'),
    'error_upload' => array('Error during upload', 'isNoHtml', TRUE, 'input', 200,'',
      'Error during upload'),
    'error_nofile' => array('No file uploaded', 'isNoHtml', TRUE, 'input', 200,'',
      'No file has been uploaded.'),
    'error_file_too_large' => arraY('File too large', 'isNoHtml', TRUE,'input', 200, '',
      'File too large'),
    'error_file_incomplete' => array('File incomplete', 'isNoHtml', TRUE, 'input', 200,'',
      'File is incomplete'),
    'error_no_temporary_path' => array('No temporary path', 'isNoHtml', TRUE, 'input', 200, '',
      'No temporary path'),
    'error_file_type' => array('Invalid file type', 'isNoHtml', TRUE, 'input', 200, '',
      'Invalid file type'),
    'error_database' => array('Database error', 'isNoHtml', TRUE, 'input', 200, '',
      'Database error'),
    'error_community' => array('Surfer not registered', 'isNoHtml', TRUE, 'input', 200, '',
      'The surfer has not registered for an account.'),
  );

  /**
   * Returns the xml document with the contents of the avatar upload form page.
   */
  function getParsedData() {
    $result = sprintf(
      '<title>%s</title>'.LF.
      '<text>%s</text>'.LF,
      $this->getXHTMLString($this->data['title']),
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    include_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();
    $this->_initSurferAdmin();

    if (isset($this->surferObj) && is_object($this->surferObj) && $this->surferObj->isValid) {
      if ($this->checkSurferPerm($this->data['perm_upload_avatar'])) {
        $this->initializeMediaDBEditObject();
        $surferData = $this->surferAdmin->loadSurfer($this->surferObj->surferId, TRUE);

        if (isset($this->params['upload']) && $this->params['upload'] == 1) {
          if (@isset($_FILES[$this->paramName]['tmp_name']['surfer_avatar'])) {
            $uploadData = $_FILES[$this->paramName];
            switch ($uploadData['error']) {  // check if error encountered
            case 1:                        // exceeded max file size
            case 2:                        // exceeded max post size
              $result .= $this->getErrorMessageXml($this->data['error_file_too_large']);
              break;
            case 3:
              $result .= $this->getErrorMessageXml($this->data['error_file_incomplete']);
              break;
            case 6:
              $result .= $this->getErrorMessageXml($this->data['error_no_temporary_path']);
              break;
            case 4:
              $result .= $this->getErrorMessageXml($this->data['error_nofile']);
              break;
            case 0:
            default:
              $tempFileName = (string)$uploadData['tmp_name']['surfer_avatar'];
              break;
            }

            if (isset($tempFileName) &&
                @file_exists($tempFileName) &&
                is_uploaded_file($tempFileName)) {
              $tempFileSize = @filesize($tempFileName);
              list(,,$tempFileType) = @getimagesize($tempFileName);

              if ($tempFileSize <= 0) {
                $result .= $this->getErrorMessageXml($this->data['error_nofile']);
              } elseif ($tempFileSize >= $this->mediaDB->getMaxUploadSize()) {
                $result .= $this->getErrorMessageXml($this->data['error_file_too_large']);
              } elseif ($tempFileType == NULL || $tempFileType < 1 || $tempFileType > 3) {
                $result .= $this->getErrorMessageXml($this->data['error_file_type']);
              } else {
                $fileId = $this->mediaDB->addFile(
                  $uploadData['tmp_name']['surfer_avatar'],
                  $uploadData['name']['surfer_avatar'],
                  $this->data['directory'],
                  $this->surferObj->surferId,
                  $uploadData['type']['surfer_avatar'],
                  'uploaded_file'
                );

                if (!$fileId) {
                  $result .= $this->getErrorMessageXml($this->data['error_database']);
                } else {
                  $data = array(
                    'file_id' => $fileId,
                    'file_title' => 'Surfer Avatar -- '.date('Y-m-d H:i:s', time()),
                    'file_description' => '',
                    'lng_id' => (int)$this->parentObj->currentLanguage['lng_id'],
                  );

                  if ($this->mediaDB->databaseInsertRecord(
                    $this->mediaDB->tableFilesTrans,
                    NULL,
                    $data
                  ) == FALSE) {
                    $result .= $this->getErrorMessageXml($this->data['error_database']);
                  } else {
                    $mediaId = $fileId;
                  }
                }
              }
            }

            if (isset($mediaId) && !empty($mediaId) && checkit::isGUID($mediaId, TRUE)) {
              if (!empty($surferData) && is_array($surferData)) {
                $surferData['surfer_avatar'] = $mediaId;
                unset($surferData['surfergroup_title']);
                $this->surferAdmin->saveSurfer($surferData);
                $result .= $this->getSuccessMessageXml($this->data['success_upload']);
              } else {
                $result .= $this->getErrorMessageXml($this->data['error_community']);
              }
            } else {
              $result .= $this->getErrorMessageXml($this->data['error_upload']);
            }
          } else {
            $result .= $this->getErrorMessageXml($this->data['error_nofile']);
          }
        }

        $fields = array(
          'surfer_avatar' => array($this->data['caption_upload_image'], 'isFile', TRUE, 'file', 50)
        );

        $hidden = array('upload' => 1);

        $data = array();
        $uploadDialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
        $uploadDialog->escape = FALSE;
        $uploadDialog->translate = FALSE;
        $uploadDialog->msgs = &$this->msgs;
        $uploadDialog->buttonTitle = $this->data['caption_upload_button'];
        $uploadDialog->uploadFiles = TRUE;
        $uploadDialog->loadParams();

        $result .= $uploadDialog->getDialogXml();
      } else {
        $result .= $this->getErrorMessageXml($this->data['error_no_permission']);
      }
    } else {
      $result .= $this->getErrorMessageXml($this->data['error_not_authenticated']);
    }

    $result .= sprintf(
      '<avatar src="%s" />'.LF,
      $this->surferAdmin->getAvatar($this->surferObj->surferId, 0, TRUE, 'max')
    );

    return $result;
  }

  /**
  * Initializes the mediadb access object $this->mediaDB. mediaDB is of type
  * base_mediadb_edit.
  */
  function initializeMediaDBEditObject() {
    if (!isset($this->mediaDB) || !is_object($this->mediaDB) ||
        get_class($this->mediaDB) != 'base_mediadb_edit') {
      $this->mediaDB = new base_mediadb_edit;
    }
  }

  /**
  * Error message creation function.
  *
  * @param string $message the error message.
  * @return string Returns an xml message element containing an error message.
  */
  function getErrorMessageXml($message) {
    return $this->getMessage($message, 'error');
  }

  /**
  * Success message creation function.
  *
  * @param string $message the message text.
  * @returns string Returns an xml message element containing a success message.
  */
  function getSuccessMessageXml($message) {
    return $this->getMessage($message, 'success');
  }

  /**
  * Message element creation function.
  *
  * @param string $message the message text.
  * @param string $type message type: ('success'|'error')
  */
  function getMessage($message, $type) {
    return sprintf(
      '<message type="%s">%s</message>'.LF,
      $type,
      papaya_strings::escapeHTMLChars($message)
    );
  }

  /**
  * Internal helper function to create a surfer admin instance
  *
  * @access private
  */
  function _initSurferAdmin() {
    if (!(isset($this->surferAdmin) && is_object($this->surferAdmin))) {
      $this->surferAdmin = surfer_admin::getInstance($this->msgs);
    }
  }
}

?>
