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
* @version $Id: content_upload.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class content
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - download list.
*
* Expanded content upload contains additional code
* to allow the display of a javascript-based upload progress bar.
* This upload progress bar uses the pecl package at
* http://pecl.php.net/package/uploadprogress to monitor the
* progress of the upload via a javascript XML call to the executeUploadProgressRPC
* method of this object, which returns an XML document containing the
* progress information.
*
* @package Papaya-Modules
* @subpackage Free-Media
*/
class content_upload extends base_content {

  /**
  * @var string $id unique hex(32) id
  */
  var $id = NULL;

  /**
  * @var array $info contains upload progress info chunks
  */
  var $info = array();

  /**
  * instance ob base_surfer, need surfer as file owner
  * @var base_surfer $surferObj
  */
  var $surferObj = NULL;

  /**
  * @var boolean $validSurfer if the current surfer login and valid?
  */
  var $validSurfer = FALSE;

  /**
   * stored message
   * @var string $message
   */
  var $message = '';

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'Settings',
    'title' =>
      array('Title', 'isNoHTML', TRUE, 'input', 200, '', ''),
    'subtitle' =>
      array('Subtitle', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'text' =>
      array('Text', 'isSomeText', FALSE, 'textarea', 10, '', ''),
    'directory' =>
       array('Folder', 'isNum', TRUE, 'mediafolder', NULL, '', 0),
    'show_surfers_upload_list' =>
      array('Show uploaded files', 'isNum', TRUE, 'yesno', 1,
      'Disable this setting if you do not want displaying all files uploaded by the surfer', 1),
    'show_surfers_upload_newfile' =>
      array('Show new file upload', 'isNum', TRUE, 'yesno', 1,
      'Disable this setting if you do not want displaying the upload box', 1),
    'redirect_after_upload' =>
      array('Redirect after an upload', 'isNum', TRUE, 'pageid', 10, NULL, 0),

    'Captions',
    'caption_file_field' =>
      array('File field', 'isNoHTML', TRUE, 'input',
      100, '', 'Select a file'),
    'caption_title_field' =>
      array('Title field', 'isNoHTML', TRUE, 'input',
      100, '', 'Title'),
    'caption_descr_field' =>
      array('Description field', 'isNoHTML', TRUE, 'input',
      100, '', 'Description'),
    'caption_upload_button' =>
      array('Upload button', 'isNoHTML', TRUE, 'input',
      100, '', 'Upload'),
    'maximum_uploaded_files' =>
      array('Maximum uploaded files permitted', 'isNum', TRUE,
      'input', 10, '', 5),

    'Messages',
    'FILE_UPLOADED' =>
      array('Uploaded', 'isNoHTML', TRUE, 'input', 400, '',
      'File uploaded'),
    'Error Messages',
    'INPUT_ERROR' =>
      array('Input error', 'isNoHTML', TRUE, 'input', 400, '',
      'Please check your inputs'),
    'FILE_TO_LARGE' =>
      array('File to large', 'isNoHTML', TRUE, 'input', 400, '',
      'File is too large'),
    'NOT_LOGGED_ON' =>
      array('Not Logged On', 'isNoHTML', TRUE, 'input', 400, '',
      'Not logged in as a valid surfer'),
    'FILE_NOT_COMPLETE' =>
      array('File not complete', 'isNoHTML', TRUE, 'input',
      400, '', 'Uploaded file is not complete'),
    'FILE_NO_FILE' =>
      array('No file', 'isNoHTML', TRUE, 'input', 400, '',
      'No file uploaded'),
    'INVALID_FILE_TYPE' =>
      array('Invalid file type', 'isNoHTML', TRUE, 'input',
      400, '', 'Invalid file type'),
    'NO_TEMPORARY_PATH' =>
      array('no temporary path', 'isNoHTML', TRUE, 'input',
      400, '', 'No temporary path for file uploads - Please contact us.'),
    'MAXIMUM_FILES_UPLOADED' =>
      array('Maximum files uploaded', 'isNoHTML', TRUE, 'input',
      400, '', 'The maximum permitted amount of files uploaded has been exceeded.'),
      'Not logged in',
    'not_logged_in_message' =>
      array('Message', 'isNoHTML', TRUE, 'textarea', 10,
        'Can contain {%LOGIN%} and {%REGISTER%}.',
        'You are not logged in. Please click {%LOGIN%} to login first
           or {%REGISTER%} to register.'),
    'not_logged_in_login' =>
      array('Login page Id', 'isNum', FALSE, 'pageid', 10,
        'Needs community module login page.', ''),
    'not_logged_in_login_caption' =>
      array('Login caption', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'not_logged_in_register' =>
      array('Register page', 'isNum', FALSE, 'pageid', 10,
        'Needs community module registration page.', ''),
    'not_logged_in_register_caption' =>
      array('Registration caption', 'isNoHTML', FALSE, 'input', 200, '', '')
  );

  /**
  * Get parsed data
  *
  * @return string $result
  */
  function getParsedData() {
    $this->setDefaultData();

    include_once(PAPAYA_INCLUDE_PATH.'/system/base_surfer.php');
    $this->surferObj = &base_surfer::getInstance();

    // check if current surfer is valid (check about 32 chars should be enough in this
    // context)
    $this->validSurfer = $this->surferObj->isValid &&
      isset($this->surferObj->surferId) && strlen($this->surferObj->surferId) == 32;

    $result = '';

    if ($this->validSurfer) {

      // if a download ID has been passed to the server
      if (!empty($_GET['id'])) {
        // call the upload progress method to output the
        include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_rpc.php');
        $this->mediaDBRpc = new base_mediadb_rpc;
        $this->mediaDBRpc->executeUploadProgressRPC($_GET['id']);
        // status as XML.  NOTE : the upload progress method
        // detects several errors which require it to pass
        // an XML document, so don't test it in an if, and
        // ALWAYS exit after using it, otherwise any other output
         // may be sent to the XMLrpc call javascript.
      } else {
        /* once we have an uploaded file, we need to put it in the mediadb. */
        $result .= sprintf(
          '<title>%s</title>' . LF,
          papaya_strings::escapeHTMLChars($this->data['title'])
        );
        $result .= sprintf(
          '<subtitle>%s</subtitle>' . LF,
          papaya_strings::escapeHTMLChars($this->data['subtitle'])
        );
        $result .= sprintf(
          '<text>%s</text>'.LF,
          $this->getXHTMLString($this->data['text'], TRUE)
        );

        $folderId = (int)$this->data['directory'];
        if ($folderId > 0) {
          $this->initializeMediaDBEditObject();
          // this prevents anyone logged in on the front-end from
          // uploading to the desktop.
          // check if anything has been uploaded, if so, output message
          if (isset($_FILES[$this->paramName]) &&
              is_array($_FILES[$this->paramName]) &&
              $this->execUpload()) {
            $this->message = $this->data['FILE_UPLOADED'];
            // perform a redirect to another page
            if (isset($this->data['redirect_after_upload']) &&
                !empty($this->data['redirect_after_upload'])) {
              $GLOBALS['PAPAYA_PAGE']->sendHeader(
                'X-Papaya-Status: redirecting after successful file upload'
              );
              $GLOBALS['PAPAYA_PAGE']->sendHeader(
                'Location: '.$this->getWebLink($this->data['redirect_after_upload'])
              );
            }
          } elseif (isset($_POST['delup']) && $this->execDelete()) {
            // check if anyone has pressed the delete button, if so, delete file
            // and output message
            $this->message = $this->data['FILE_DELETED'];
          }
          // display form template
          $result .= $this->getUploadFormXML();
        }
      }
    } else {
      $values['LOGIN'] = sprintf(
        '<a href="%s">%s</a>',
        $this->getWebLink((int)$this->data['not_logged_in_login']),
        papaya_strings::escapeHTMLChars($this->data['not_logged_in_login_caption'])
      );
      $values['REGISTER'] = sprintf(
        '<a href="%s">%s</a>',
        $this->getWebLink((int)$this->data['not_logged_in_register']),
        papaya_strings::escapeHTMLChars($this->data['not_logged_in_register_caption'])
      );

      include_once(PAPAYA_INCLUDE_PATH.'system/base_simpletemplate.php');
      $template = new base_simpletemplate();
      $message = $template->parse(
        $this->data['not_logged_in_message'],
        $values
      );

      $result = sprintf(
        '<title>%s</title>'.LF,
        papaya_strings::escapeHTMLChars($this->data['title'])
      );
      $result .= sprintf(
        '<subtitle>%s</subtitle>'.LF,
        papaya_strings::escapeHTMLChars($this->data['subtitle'])
      );
      $result .= sprintf(
        '<text>%s</text>'.LF,
        $this->getXHTMLString($message, TRUE)
      );
    }
    return $result;
  }


  /**
  * execute upload
  *
  * @access public
  * @return void
  */
  function execDelete() {
    $surferFiles = $this->mediaDB->getFilesBySurferId($this->surferObj->surferId);

    //$delfiles = array();
    foreach ($_POST as $key => $value) {
      if ($key != 'delup' && $value == 'on' && isset($surferFiles[$key])) {
        // delete thingy here
        $this->mediaDB->deleteFile($key);
        //$delfiles[]=$ret[$key]['file_name'];
      }
    }
  }

  /**
  * execute upload - move a valid file into the uploaded file area
  *
  * @access public
  * @return boolean
  */
  function execUpload() {
    $result = FALSE;        // set default failure condition
    $this->message = NULL;  // no message to start off with.

    // have we received anything in the superglobal?
    if (!empty($_FILES[$this->paramName]['tmp_name']['uploadfile'])) {

      // deindex the particular section of files to do with our form.
      $uploadData = $_FILES[$this->paramName];

      // permitted type of file?
      if ($this->checkUploadFile($uploadData)) {
        // check if the user is logged in.
        if ($this->validSurfer) {
          // if user is logged on, has a valid folder and hasn't uploaded too many files
          // then we add the file to the media db.
          $fileId = $this->mediaDB->addFile(
            $uploadData['tmp_name']['uploadfile'],
            $uploadData['name']['uploadfile'],
            $this->data['directory'],
            $this->surferObj->surferId,
            $uploadData['type']['uploadfile'],
            'uploaded_file'
          );

          // if successfully added
          if ($fileId) {
            // check file metadata into database
            if ((isset($this->params['title']) && $this->params['title'] != '') ||
                (isset($this->params['description']) && $this->params['description'] != '')) {
              $data = array(
                'file_id' => $fileId,
                'file_title' => empty($this->params['title'])
                  ? '' : (string)$this->params['title'],
                'file_description' => empty($this->params['description'])
                  ? '' : (string)$this->params['description'],
                'lng_id' => (int)$this->parentObj->getContentLanguageId(),
              );

              $rtn = $this->mediaDB->databaseInsertRecord(
                $this->mediaDB->tableFilesTrans, NULL, $data);
            }
            unset($this->params);
            $result = TRUE;
          } else {
            // failed : report failure
            $this->message = $this->data['FILE_NO_FILE'];
          }
        } else {
          $this->message = $this->data['NOT_LOGGED_ON'];
        }
      }
    } else {
      $this->message = $this->data['FILE_NO_FILE'];
    }
    return $result;
  }

  /**
  * get upload form xml
  *
  * @access public
  * @return string $result xml
  */
  function getUploadFormXML() {
    $result = '';

    // get only file upload box if allowed
    if ($this->data['show_surfers_upload_newfile'] > 0) {

      // dynamic progress bar flag for xslt sheet
      $progressBar = 'no';
      // check for the upload function
      if (function_exists('uploadprogress_get_info')) {
        // set to yes only when php has the pecl extension
        // http://pecl.php.net/package/uploadprogress
        $progressBar = 'yes';
      }

      // check to see if the user has uploaded too many files before
      // displaying the upload form.
      if ($this->getFileCount() <= $this->data['maximum_uploaded_files']) {
        $result .= sprintf(
          '<uploaddialog action="%s" progressbar="%s">',
          papaya_strings::escapeHTMLChars($this->baseLink),
          papaya_strings::escapeHTMLChars($progressBar)
        );
        $result .= sprintf(
          '<input type="hidden" name="%s[upload]" value="1" />'.LF,
          papaya_strings::escapeHTMLChars($this->paramName)
        );
        $result .= sprintf(
          '<input type="hidden" name="MAX_FILE_SIZE" value="%s" />' . LF,
          papaya_strings::escapeHTMLChars($this->mediaDB->getMaxUploadSize())
        );
        // self-imposed size of files.
        if (!empty($this->message)) {
          $result .= sprintf(
            '<message>%s</message>',
            papaya_strings::escapeHTMLChars($this->message)
          );
        }

        $result .= '<element id="file">';
        $result .= sprintf(
          '<label>%s</label>',
          papaya_strings::escapeHTMLChars($this->data['caption_file_field'])
        );
        $result .= sprintf(
          '<input type="file" id="upload" name="%s[uploadfile]"/>',
          papaya_strings::escapeHTMLChars($this->paramName)
        );
        $result .= '</element>';

        $error = (isset($this->inputError['title'])) ? ' error="error"' : '';
        $result .= '<element id="title"'.$error.'>';
        $result .= sprintf(
          '<label>%s</label>',
          papaya_strings::escapeHTMLChars($this->data['caption_title_field'])
        );
        $result .= sprintf(
          '<input type="text" name="%s[title]" value="%s"/>',
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars(
            empty($this->params['title']) ? '' : $this->params['title']
          )
        );
        $result .= '</element>';

        $error = (isset($this->inputError['description'])) ? ' error="error"' : '';
        $result .= '<element id="description"'.$error.'>';
        $result .= sprintf(
          '<label>%s</label>',
          papaya_strings::escapeHTMLChars($this->data['caption_descr_field'])
        );
        $result .= sprintf(
          '<textarea type="text" name="%s[description]">%s</textarea>',
          papaya_strings::escapeHTMLChars($this->paramName),
          papaya_strings::escapeHTMLChars(
            empty($this->params['description']) ? '' : $this->params['description']
          )
        );
        $result .= '</element>';
        $result .= sprintf(
          '<button type="submit" id="upsub" name="upsub">%s</button>',
          papaya_strings::escapeHTMLChars(
          $this->data['caption_upload_button'])
        );
        $result .= '</uploaddialog>';
      } else {
        $this->message = $this->data['MAXIMUM_FILES_UPLOADED'];
      }
    }

    if (!empty($this->message)) {
      $result .= sprintf(
        '<message>%s</message>',
        papaya_strings::escapeHTMLChars($this->message)
      );
    }

    $result .= $this->getFileList();
    return $result;
  }

  /**
  * check upload file
  *
  * @param array $fileData
  * @access public
  * @return mixed boolean FALSE or array file information
  */
  function checkUploadFile($fileData) {
    $result = FALSE;

    if (isset($fileData) &&
        is_array($fileData) &&
        isset($fileData['error']['uploadfile'])) {
      switch ($fileData['error']) {  // check if error encountered
      case 1:                     // exceeded max file size
      case 2:                     // exceeded max post size
        $this->message = $this->data['FILE_TO_LARGE'];
        break;
      case 3:
        $this->message = $this->data['FILE_NOT_COMPLETE'];
        break;
      case 6:
        $this->message = $this->data['NO_TEMPORARY_PATH'];
        break;
      case 4:
        $this->message = $this->data['FILE_NO_FILE'];
        break;
      case 0:
      default:
        $tempFileName = (string)$fileData['tmp_name']['uploadfile'];
        break;
      }
    }

    if (isset($tempFileName) && @file_exists($tempFileName)
        && is_uploaded_file($tempFileName)) {
      $tempFileSize = @filesize($tempFileName);
      list(, , $tempFileType) = @getimagesize($tempFileName);

      if ($tempFileSize <= 0) {
        $this->message = $this->data['FILE_NO_FILE'];
      } elseif ($tempFileSize >= $this->mediaDB->getMaxUploadSize()) {
        $this->message = $this->data['FILE_TO_LARGE'];
      } else {
        $result = TRUE;
      }
    }

    return $result;
  }

  /**
  * this method will initialize the media db object
  * if object already initialized, it will be returned
  *
  * @return base_mediadb_edit
  */
  function initializeMediaDBEditObject() {
    if (!(
          isset($this->mediaDB) &&
          is_object($this->mediaDB) &&
          is_a($this->mediaDB, 'base_mediadb_edit')
        )) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
      $this->mediaDB = new base_mediadb_edit;
    }
    return $this->mediaDB;
  }

  /**
  * getFileCount
  *
  * return the number of files owned by a surfer.
  *
  * @access private
  * @return int
  */
  function getFileCount() {
    $this->initializeMediaDBEditObject();

    if ($this->validSurfer) {
      $surferFiles = $this->mediaDB->getFilesBySurferId($this->surferObj->surferId);
      return count($surferFiles);
    }

    return 0;
  }

  /**
  * getFileList
  *
  * return files owned by a surfer as an xml document
  *
  * @access private
  * @return string
  */

  function getFileList() {
    // set up empty string variable for XML result string
    $result = '';

    // exit this method if setting disallow listing
    if ($this->data['show_surfers_upload_list'] < 1) {
      return $result;
    }

    if ($this->validSurfer) {
      $this->initializeMediaDBEditObject();
      // get the files belonging to the user
      $surferFiles = $this->mediaDB->getFilesBySurferId($this->surferObj->surferId);
    } else {
      // invalid surfer
      return $result;
    }

    if (count($surferFiles) > 0) { // if there are any files for this user
      // start building the xml document
      $result .= '<uploadedFiles>'.LF;
      foreach ($surferFiles as $fileId => $file) {
        // get any title and description info for the document for the current language
        $translation = $this->mediaDB->getFileTrans(
          $fileId, $this->parentObj->currentLanguage['lng_id']
        );

        $fileTitle = $file['file_name'];
        $fileDescription = '';
        if (is_array($translation) && isset($translation[$fileId])) {
          // parse the title and description info from trans into the xml document,
          // avoiding duplication of the fileId
          if (isset($translation[$fileId])) {
            if (!empty($translation[$fileId]['file_title'])) {
              $fileTitle = $translation[$fileId]['file_title'];
            }
            if (!empty($translation[$fileId]['file_description'])) {
              $fileDescription = $translation[$fileId]['file_description'];
            }
          }
        }

        // get each file's details out of the array
        $result .= sprintf(
          '<filedata download="%s">'.LF,
          $this->getWebMediaLink($file['file_id'], 'download', $fileTitle, $file['mimetype_ext'])
        );

        if (!empty($fileTitle) && $fileTitle != $file['file_name']) {
          $result .= sprintf(
            '<title>%s</title>',
            papaya_strings::escapeHTMLChars($fileDescription)
          );
        }
        if (!empty($fileDescription)) {
          $result .= sprintf(
            '<description>%s</description>',
            papaya_strings::escapeHTMLChars($fileDescription)
          );
        }
        $detailFields = array(
          'file_name', 'file_date', 'file_size', 'mimetype'
        );
        if (is_array($file)) {
          // loop through the detail field names
          foreach ($detailFields as $name) {
            if (isset($file[$name])) {
              switch ($name) {
              case 'file_date':
                $result .= sprintf(
                  '<file_date>%s</file_date>'.LF,
                  date('Y-m-d H:i:s', (int)$file[$name])
                );
                break;
              default:
                $result .= '<'.papaya_strings::escapeHTMLChars($name).'>';
                $result .= papaya_strings::escapeHTMLChars($file[$name]);
                $result .= '</'.papaya_strings::escapeHTMLChars($name).'>'.LF;
                break;
              }
            }
          }
        }
        $result .= '  </filedata>'.LF;    // close file data
      }
      $result .= '</uploadedFiles>'.LF;
    }
    return $result;
  }
}
?>