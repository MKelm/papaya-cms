<?php
/**
* content import handling
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
* @package Papaya-Library
* @subpackage Controls
* @version $Id: base_import.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* database abstraction
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* content import handling
*
* @package Papaya-Library
* @subpackage Controls
*/
class base_import extends base_db {
  /**
  * Papaya database table import filter
  * @var string $tableImportFilter
  */
  var $tableImportFilter = PAPAYA_DB_TBL_IMPORTFILTER;
  /**
  * Papaya database table import filter links
  * @var string $tableImportFilterLinks
  */
  var $tableImportFilterLinks = PAPAYA_DB_TBL_IMPORTFILTER_LINKS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;

  /**
  * Table media links
  * @var string
  */
  var $tableMediaLinks = PAPAYA_DB_TBL_MEDIA_LINKS;

  /**
  * Single filter
  * @var array $filter
  */
  var $filter = NULL;

  /**
  * Imports a file
  *
  * @param string $tmpFile
  * @param string $fileName
  * @param imteger $pageId
  * @param integer $lngId
  * @param integer $viewId
  * @return boolean TRUE, on success, else FALSE
  */
  function importFile($tmpFile, $fileName, $pageId, $lngId, $viewId) {
    if ($this->filterObj = $this->getFilterByFileName($fileName)) {
      $this->filterObj->pageId = $pageId;
      $this->filterObj->languageId = $lngId;
      if ($this->loadFilterConfiguration($viewId, $this->filter['importfilter_id'])) {
        return $this->filterObj->import($tmpFile);
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('No import view configured for this file extension.'));
      }
    }
    return FALSE;
  }

  /**
  * Determines which filter to be used to import a file
  *
  * @param string $fileName
  * @return object Instance of the filter to be used to import a file
  */
  function &getFilterByFileName($fileName) {
    $result = NULL;
    if (preg_match('~\.(\w+)$~', $fileName, $regs)) {
      $extension = $regs[1];
      if ($this->loadFilterByExtension($extension)) {
        include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
        $result = &base_pluginloader::getPluginInstance(
          $this->filter['module_guid'],
          $this,
          NULL,
          $this->filter['module_class'],
          $this->filter['module_path'].$this->filter['module_file']);
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Unknown file extension.'));
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Invalid file extension.'));
    }
    return $result;
  }

  /**
  * Reads information about the filter to be used to import a file
  *
  * @param $extension
  * @return boolean TRUE, on success, else FALSE
  */
  function loadFilterByExtension($extension) {
    unset($this->filter);
    $sql = "SELECT im.importfilter_id, im.importfilter_ext, m.module_guid,
                   m.module_guid, m.module_path, m.module_file,
                   m.module_class, m.module_title
              FROM %s im
              LEFT OUTER JOIN %s m ON (m.module_guid = im.module_guid)
             WHERE im.importfilter_ext = '%s'";
    if ($res = $this->databaseQueryFmt(
          $sql,
          array($this->tableImportFilter, $this->tableModules, $extension))) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->filter = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Extracts the file to the tmp dir
  *
  * @param string $fileName
  * @param boolean $isUploadedFile
  * @return mixed Identifier of the extracted file
  */
  function extractFile($fileName, $isUploadedFile = TRUE) {
    $result = '';
    $this->initializeTemporaryDirectory();
    if (is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      if ($fileId = $this->copyFile($fileName, $isUploadedFile)) {
        $pkgName = $fileId.'.pkg';
        //extract package to directory
        include_once(PAPAYA_INCLUDE_PATH.'system/sys_zip.php');
        $archive = new sys_zip($pkgName);
        if ($archive->extract($fileId)) {
          $result = $fileId;
        }
        unlink($pkgName);
      }
    }
    return $result;
  }

  /**
  * Copies the given file to the tmp dir
  *
  * @param string $fileName
  * @param boolean $isUploadedFile
  * @return boolean TRUE, onsuccess, else FALSE
  */
  function copyFile($fileName, $isUploadedFile = TRUE) {
    $result = FALSE;
    $this->initializeTemporaryDirectory();
    if (is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      $fileId = $this->getTemporaryFileName(PAPAYA_PATH_CACHE.'tmp/');
      $pkgName = $fileId.'pkg';
      if (is_uploaded_file($fileName)) {
        $copied = move_uploaded_file($fileName, $pkgName);
      } elseif (is_file($fileName) && !($isUploadedFile)) {
        $copied = copy($fileName, $pkgName);
      } else {
        $copied = FALSE;
      }
      if ($copied) {
        chmod($pkgName, 0666);
        $result = $fileId;
      }
    }
    return $result;
  }

  /**
  * Generates a filename for temporary use
  *
  * @param string $path
  * @return string the given path etended by a randomized string
  */
  function getTemporaryFileName($path) {
    do {
      $randName = md5(uniqid(rand()));
    } while (file_exists($path.$randName) || file_exists($path.$randName.'.pkg'));
    return $path.$randName;
  }

  /**
  * Crreates the tmp dir if not existant
  *
  * @return boolean TRUE, on success, else FALSE
  */
  function initializeTemporaryDirectory() {
    if (!is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      umask(011);
      chdir(PAPAYA_PATH_CACHE);
      mkdir('tmp', 0777);
    }
    if (!is_dir(PAPAYA_PATH_CACHE.'tmp')) {
      $this->addMsg(MSG_ERROR, $this->_gt('Cannot find/create temporary path.'));
    }
  }

  /**
  * Deletes the given file from tmp directory
  *
  * @param string $fileName
  * @return boolean TRUE, on success, else FALSE
  */
  function deleteTemporaryFile($fileName) {
    if (isset($fileName) && is_dir($fileName) &&
        0 === strpos($fileName, PAPAYA_PATH_CACHE.'tmp/')) {
      if (unlink($fileName)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Removes previous extracted archive files from tmp dir
  *
  * @param string $directoryName
  * @return boolean TRUE, on success, else FALSE
  */
  function deleteExtractedFiles($directoryName) {
    if (isset($directoryName) && is_dir($directoryName) &&
        0 === strpos($directoryName, PAPAYA_PATH_CACHE.'tmp/')) {
      if ($dh = opendir($directoryName)) {
        while (FALSE !== ($file = readdir($dh))) {
          if ($file != '.' && $file != '..') {
            if (is_dir($directoryName.'/'.$file)) {
              $this->deleteExtractedFiles($directoryName.'/'.$file);
            } else {
              unlink($directoryName.'/'.$file);
            }
          }
        }
      }
      chdir(dirname($directoryName));
      rmdir(basename($directoryName));
    }
  }

  /**
  * Retrieves configuration information about the given filter
  *
  * @param integer $viewId
  * @param integer $filterId
  * @return booelan TRUE, on success, else FALSE
  */
  function loadFilterConfiguration($viewId, $filterId) {
    unset($this->filterLink);
    $sql = "SELECT fl.importfilter_data
              FROM %s fl
             WHERE fl.view_id = %d AND fl.importfilter_id = %d";
    $params = array($this->tableImportFilterLinks, $viewId, $filterId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        $this->filterObj->setData($row[0]);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Parses given xml string for errors
  *
  * @param string $xml
  * @param string $xslFile
  * @return boolean FALSE
  */
  function transformXML($xml, $xslFile) {
    if (file_exists($xslFile)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_xsl.php');
      $transformer = new sys_xsl($xslFile);
      $transformer->setXml($xml);
      if ($str = $transformer->parse()) {
        return $str;
      } else {
        $this->getXSLTTransFormErrors($transformer->lastError);
      }
    }
    return FALSE;
  }

  /**
  * Generates a XML string out of the passed errors and adds it to the current layout
  *
  * @param array $errors
  */
  function getXSLTTransFormErrors(&$errors) {
    if (isset($errors) && is_array($errors) && count($errors) > 0) {
      $result = sprintf(
        '<listview title="%s">',
        papaya_strings::escapeHTMLChars($this->_gt('Errors'))
      );
      $result .= '<items>';
      foreach ($errors as $error) {
        $result .= sprintf(
          '<listitem image="%s">',
          papaya_strings::escapeHTMLChars($this->images[65])
        );
        $result .= '<subitem>';
        $result .= sprintf(
          '<b>%d: %s</b>',
          (int)$error['code'],
          papaya_strings::escapeHTMLChars($error['msg'])
        );
        if ($error['file'] != '') {
          $fileName = $error['file'];
          $templateHandler = new PapayaTemplateXsltHandler();
          $templatePath = $templateHandler->getLocalPath();
          if (0 === strpos($fileName, $templatePath)) {
            $fileName = '~/'.substr($fileName, strlen($templatePath));
          }
          $result .= sprintf(
            '<br/>%s',
            papaya_strings::escapeHTMLChars($fileName)
          );
          $result .= ':'.(int)$error['line'];
        }
        $result .= '</subitem>';
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->add($result);
    }
  }

  /**
  * add file data string to media database
  *
  * @param integer $pageId
  * @param string $fileName
  * @param string $data
  * @access public
  * @return boolean
  */
  function addMediaFileData($pageId, $lngId, $fileName, $data) {
    if ($tempFileName = $this->getTemporaryFileName(PAPAYA_PATH_CACHE.'tmp')) {
      if ($fh = fopen($tempFileName, 'w')) {
        fwrite($fh, $data);
        fclose($fh);
        $result = $this->addMediaFile($pageId, $lngId, $fileName, $tempFileName);
        $this->deleteTemporaryFile($tempFileName);
        return $result;
      }
    }
    return FALSE;
  }

  /**
  * add a file to the media database
  *
  * @todo implement this
  * @param integer $pageId
  * @param string $fileName
  * @param string $tempFileName
  * @access public
  * @return boolean
  */
  function addMediaFile($pageId, $lngId, $fileName, $tempFileName = NULL) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb_edit.php');
    $mediaDB = base_mediadb_edit::getInstance();

    if (!isset($tempFileName)) {
      $tempFileName = $fileName;
    }
    $tempFileNameConv = NULL;
    $fileName = basename($fileName);
    if (@file_exists($tempFileName)) {
      list(,,$tempFileType) = @getimagesize($tempFileName);
      if ($pos = strrpos($fileName, '.')) {
        $ext = substr($fileName, $pos);
      } else {
        $ext = '';
      }

      $tempFileSize = @filesize($tempFileName);
      $surferId = $this->authUser->user['userId'];
      if ($fileId = $mediaDB->addFile(
        $tempFileName,
        $fileName,
        -2,
        $surferId,
        '',
        'local_file')) {
        $this->addMediaLinks($pageId, $lngId, $fileId);
        if (isset($tempFileNameConv)) {
          $this->deleteTemporaryFile($tempFileNameConv);
        }
        return $fileId;
      }
    }
    return FALSE;
  }

  /**
  * Adds a link to a media file to the database
  *
  * @param array $pageIds
  * @param array $lngIds
  * @param array $fileIds
  * @return boolean
  */
  function addMediaLinks($pageIds, $lngIds, $fileIds) {
    if (!is_array($pageIds)) {
      $pageIds = array($pageIds);
    }
    if (!is_array($lngIds)) {
      $lngIds = array($lngIds);
    }
    if (!is_array($fileIds)) {
      $fileIds = array($fileIds);
    }
    $data = array();
    foreach ($pageIds as $pageId) {
      foreach ($lngIds as $lngId) {
        foreach ($fileIds as $fileId) {
          $data[] = array(
            'file_id' => $fileId,
            'language_id' => $lngId,
            'page_id' => $pageId
          );
        }
      }
    }
    if (count($data) > 0) {
      return (FALSE !== $this->databaseInsertRecords($this->tableMediaLinks, $data));
    }
    return FALSE;
  }

  /**
  * Removes a link to a media file of a specific page and language
  *
  * @param integer $pageId
  * @param integer $lngId
  * @return boolean
  */
  function dropMediaLinksForPageId($pageId, $lngId = NULL) {
    $filter = array(
      'page_id' => (int)$pageId
    );
    if (isset($lngId)) {
      $filter['language_id'] = (int)$lngId;
    }
    return (FALSE !== $this->databaseDeleteRecord($this->tableMediaLinks, $filter));
  }

  /**
  * Removes a link to a media file of a specific fiel and language
  *
  * @param integer $fileId
  * @param integer $lngId
  * @return boolean
  */
  function dropMediaLinksForFileId($fileId, $lngId = NULL) {
    $filter = array(
      'file_id' => $fileId
    );
    if (isset($lngId)) {
      $filter['language_id'] = (int)$lngId;
    }
    return (FALSE !== $this->databaseDeleteRecord($this->tableMediaLinks, $filter));
  }
}
?>