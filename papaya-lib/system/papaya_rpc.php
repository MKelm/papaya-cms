<?php
/**
* Control particular activities (example: for flash)
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
* @package Papaya
* @subpackage Administration
* @version $Id: papaya_rpc.php 37746 2012-11-29 16:29:01Z weinert $
*/

/**
* Basic class base_topic_edit
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_topic_edit.php');
/**
* Control particular activities (example: for flash)
*
* @package Papaya
* @subpackage Administration
*/
class papaya_rpc extends base_object {

  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = "rpc";
  /**
  * Parameters
  * @var array $params
  */
  var $params;

  /**
  * Topic tree
  * @var object $topicTree papaya_topic_tree
  */
  var $topicTree = NULL;
  /**
  * XML buffer
  * @var string $xmlBuffer
  */
  var $xmlBuffer = NULL;

  /**
  * papaya tag pattern
  * @var string $papayaTagPattern
  */
  var $papayaTagPattern = '/<(papaya|ndim):([a-z]\w+)\s?([^>]*)\/?>(<\/(\1):(\2)>)?/ims';

  /**
  * papaya parameters pattern
  * @var string $papayaParamsPattern
  */
  var $papayaParamsPattern =
    '/(?:^|\s)([a-z]\w+)(?:=)(?:(?:\'([^\']+)\')|(?:"([^"]*)")|([^\s,]+))/i';

  /**
  * Initialize parameters
  *
  * @param mixed $paramName optional, default value NULL
  * @access public
  */
  function initialize($paramName = NULL) {
    if (isset($paramName)) {
      $this->paramName = $paramName;
    }
    $this->initializeParams();
  }

  /**
  * Execute - basic function for handling parameters
  *
  * @access public
  */
  function execute() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
    $this->lngSelect = &base_language_select::getInstance();
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_topic_tree.php');
    $this->topicTree = new papaya_topic_tree;
    $this->topicTree->images = $this->images;
    $this->topicTree->msgs = &$this->msgs;
    $this->topicTree->authUser = &$this->authUser;
    if (isset($this->params['cmd'])) {
      switch($this->params['cmd']) {
      case 'topic_detail':
        $this->sessionParamName = 'PAPAYA_SESS_tt';
        $this->sessionParams = $this->getSessionValue($this->sessionParamName);
        $this->xmlBuffer = $this->getTopicDetails();
        break;
      case 'publish':
        $this->sessionParamName = 'PAPAYA_SESS_tt';
        $this->sessionParams = $this->getSessionValue($this->sessionParamName);
        $this->publishTopic();
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        break;
      case 'mv':
      case 'cp':
        if (!empty($_GET['p_id'])) {
          $this->topicTree->initialize($_GET['p_id']);
        }
        if (!empty($_POST['p_id'])) {
          $this->topicTree->initialize($_POST['p_id']);
        }
        if (!empty($_GET['page_id'])) {
          $this->topicTree->initialize($_GET['page_id']);
        }
        if (!empty($_POST['page_id'])) {
          $this->topicTree->initialize($_POST['page_id']);
        }
        $this->topicTree->params = $this->params;
        $this->topicTree->execute();
        break;
      case 'strings':
        switch ($this->params['module']) {
        case 'sitemap':
          $this->xmlBuffer = $this->getSitemapTranslations();
          break;
        }
        break;
      case 'strings_navigation' :
        switch ($this->params['module']) {
        case 'sitemap':
          $this->xmlBuffer = $this->getNavigationTranslations();
          break;
        }
        break;
      case 'select':
        $this->sessionParamName = 'PAPAYA_SESS_tt';
        $this->sessionParams = $this->getSessionValue($this->sessionParamName);
        $this->sessionParams['page_id'] = empty($this->params['page_id'])
          ? 0 : (int)$this->params['page_id'];
        $this->setSessionValue($this->sessionParamName, $this->sessionParams);
        $this->xmlBuffer = '<dummy>'.$this->params['page_id'].'</dummy>';
        break;
      case 'imageconf_form':
        $this->xmlBuffer = '<response>';
        $this->xmlBuffer .= '<method>showImageConfDialog</method>';
        $data = $this->getImageConfDialog(
          empty($this->params['image_ident']) ? '' : $this->params['image_ident']
        );
        $this->xmlBuffer .= '<data><![CDATA['.
          papaya_strings::escapeHTMLChars($data).']]></data>';
        $this->xmlBuffer .= '</response>';
        break;
      case 'addon_form':
        $this->xmlBuffer = '<response>';
        $this->xmlBuffer .= '<method>showAddOnDialog</method>';
        $xmlData = $this->getAddOnDialog(
          empty($this->params['addon_guid']) ? '' : $this->params['addon_guid']
        );
        $this->xmlBuffer .= '<params>'.$xmlData.'</params>';
        $this->xmlBuffer .= '</response>';
        break;
      case 'addon_data':
        $this->xmlBuffer = '<response>';
        $this->xmlBuffer .= '<method>setAddOnData</method>';
        $xmlData = $this->getAddOnData(
          empty($this->params['addon_guid']) ? '' : $this->params['addon_guid']
        );
        $this->xmlBuffer .= '<data>'.$xmlData.'</data>';
        $this->xmlBuffer .= '</response>';
        break;
      case 'image_data':
        $this->xmlBuffer = '<response>';
        $this->xmlBuffer .= '<method>rpcSetImageData</method>';
        $xmlData = $this->getImageData(
          empty($this->params['image_conf']) ? '' : $this->params['image_conf'],
          empty($this->params['thumbnail']) ? FALSE : TRUE
        );
        $this->xmlBuffer .= '<data>'.$xmlData.'</data>';
        $this->xmlBuffer .= '</response>';
        break;
      case 'media_data':
        $this->xmlBuffer = '<response>';
        $this->xmlBuffer .= '<method>rpcSetMediaData</method>';
        $xmlData = $this->getMediaData(
          empty($this->params['media_id']) ? '' : $this->params['media_id']
        );
        $this->xmlBuffer .= '<data>'.$xmlData.'</data>';
        $this->xmlBuffer .= '</response>';
        break;
      case 'page_data':
        $this->xmlBuffer = '<response>';
        $this->xmlBuffer .= '<method>rpcSetPageData</method>';
        $xmlData = $this->getPageData(
          empty($this->params['page_id']) ? 0 : $this->params['page_id']
        );
        $this->xmlBuffer .= '<data>'.$xmlData.'</data>';
        $this->xmlBuffer .= '</response>';
        break;
      }
    }
  }

  /**
  * Get XML
  *
  * @access public
  * @return string xml
  */
  function getXML() {
    if (isset($this->msgs->errorList) && is_array($this->msgs->errorList)) {
      return $this->msgs->get();
    } elseif (isset($this->xmlBuffer)) {
      return $this->xmlBuffer;
    } else {
      $this->topicTree->loadSimplyAll($this->lngSelect->currentLanguageId);
      return $this->topicTree->getXML();
    }
  }

  /**
  * Publish topic
  *
  * @access public
  */
  function publishTopic() {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_topic.php');
    $topic = new papaya_topic();
    $topic->params = $this->params;
    //$topic->msgs = &$this->msgs;
    $topic->authUser = &$this->authUser;
    $topic->load(
      $this->params['page_id'],
      empty($this->lngSelect->currentLanguageId) ? 0 : (int)$this->lngSelect->currentLanguageId
    );
    if (isset($topic->topic) && is_array($topic->topic)) {
      if ($topic->hasPermUser(PERM_WRITE, $this->authUser) &&
          $topic->editable($this->authUser) &&
          $this->authUser->hasPerm(PapayaAdministrationPermissions::PAGE_VERSION_MANAGE) &&
          $this->authUser->hasPerm(PapayaAdministrationPermissions::PAGE_PUBLISH) &&
          isset($this->params['commit_message'])) {
        $this->sessionParams['last_publish_message'] = $this->params['commit_message'];
        if ($topic->publishTopic()) {
          $this->addMsg(MSG_INFO, $this->_gt('Page published.'));
          $this->logMsg(
            MSG_INFO,
            PAPAYA_LOGTYPE_PAGES,
            sprintf(
              'Page "%s (%d)" published.',
              papaya_strings::escapeHTMLChars(
                $topic->topic['TRANSLATION']['topic_title']
              ),
              $topic->topicId
            )
          );
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t publish this page.'));
        }
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t publish this page.'));
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t load this page.'));
    }
  }

  /**
  * Get translations
  *
  * @param $strs
  * @access public
  * @return string $result xml
  */
  function getTranslations($strs) {
    $result = '<phrases>';
    foreach ($strs as $id=>$str) {
      $result .= sprintf(
        '<phrase id="%s">%s</phrase>',
        papaya_strings::escapeHTMLChars($id),
        papaya_strings::escapeHTMLChars($this->_gt($str, 'sitemap'))
      );
    }
    $result .= '</phrases>';
    return $result;
  }

  /**
  * Get sitemap translation
  *
  * @see papaya_rpc::getTranslations
  * @access public
  * @return string
  */
  function getSitemapTranslations() {
    $strs = array(
      'str_publish' => 'Publish',
      'str_publishquestion' => 'Publish page "%s" (%s)?',
      'str_copy' => 'Copy',
      'str_copyquestion' => 'Copy page "%s" (%d) to "%s" (%d)?',
      'str_move' => 'Move',
      'str_movequestion' => 'Move page "%s" (%d) to "%s" (%d)?',
      'str_info' => 'Information',
      'str_warning' => 'Warning',
      'str_error' => 'Error',
      'str_ok' => 'OK',
      'str_cancel' => 'Cancel',
      'str_close' => 'Close',
      'str_loading' => 'Loading'
    );
    return $this->getTranslations($strs);
  }

  /**
  * Get navigation translations
  *
  * @see papaya_rpc::getTranslations
  * @access public
  * @return string
  */
  function getNavigationTranslations() {
    $strs = array(
      'str_base' => 'Base',
      'str_current' => 'Current',
      'str_offset' => 'Offset',
      'str_depth' => 'Depth',
      'str_focus' => 'Focus'
    );
    return $this->getTranslations($strs);
  }

  /**
  * Get topic details
  *
  * @access public
  * @return string $result xml
  */
  function getTopicDetails() {
    $result = NULL;
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_topic.php');
    $topic = new papaya_topic();
    $topic->params = $this->params;
    $topic->msgs = &$this->msgs;
    $topic->authUser = &$this->authUser;
    $topic->load($this->params['page_id'], $this->lngSelect->currentLanguageId);
    if (isset($topic->topic) && is_array($topic->topic)) {
      if ($topic->hasPermUser(PERM_WRITE, $this->authUser) &&
          $topic->editable($this->authUser) &&
          $this->authUser->hasPerm(PapayaAdministrationPermissions::PAGE_VERSION_MANAGE) &&
          $this->authUser->hasPerm(PapayaAdministrationPermissions::PAGE_PUBLISH)) {
        $topic->loadTranslationsInfo();
        if (isset($this->sessionParams['last_publish_message']) &&
            trim($this->sessionParams['last_publish_message']) != '') {
          $publishVersionMessage = $this->sessionParams['last_publish_message'];
        } else {
          $publishVersionMessage = '';
        }
        $result .= sprintf(
          '<item id="%d" commitmsg="%s">',
          $topic->topicId,
          papaya_strings::escapeHTMLChars($publishVersionMessage)
        );
        if (isset($this->lngSelect->languages) &&
            is_array($this->lngSelect->languages)) {
          foreach ($this->lngSelect->languages as $lngId => $lng) {
            $selected = '';
            if ($this->lngSelect->currentLanguageId == $lngId) {
              $selected = ' checked="checked"';
            }
            $topicData = &$topic->topic['TRANSLATIONINFOS'][$lngId];
            if (isset($topicData['topic_trans_published']) &&
              $topicData['topic_trans_published'] >=
              $topicData['topic_trans_modified']) {
              break;
            }
            $lngTitle = $this->lngSelect->languages[$lngId]['lng_title'].' ('.
              $this->lngSelect->languages[$lngId]['lng_short'].')';
            $result .= sprintf(
              '<translation lng_title="%s" lng_id="%s" title="%s" %s/>',
              papaya_strings::escapeHTMLChars($lngTitle),
              (int)$lngId,
              papaya_strings::escapeHTMLChars($topicData['topic_title']),
              $selected
            );
          }
        }
        $result .= '</item>';
        return $result;
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t publish this page.'));
      }
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Couldn\'t load this page.'));
    }
  }

  /**
  * Get image configuration dialog
  *
  * @param $imageIdent
  * @access public
  * @return string $result
  */
  function getImageConfDialog($imageIdent) {
    $result = '';
    include_once(PAPAYA_INCLUDE_PATH.'/system/papaya_imagegenerator.php');
    $imgGenerator = new papaya_imagegenerator();
    $imgGenerator->loadByIdent($imageIdent);
    if (isset($imgGenerator->imageConf) &&
        isset($imgGenerator->imageConf['module_guid'])) {
      $parent = NULL;
      include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
      $moduleObj = &base_pluginloader::getPluginInstance(
        $imgGenerator->imageConf['module_guid'],
        $parent,
        NULL,
        $imgGenerator->imageConf['module_class'],
        $imgGenerator->imageConf['module_path'].$imgGenerator->imageConf['module_file']
      );
      if (isset($moduleObj) && is_object($moduleObj)) {
        $moduleObj->images = &$imgGenerator->images;
        $moduleObj->setData($imgGenerator->imageConf['image_data']);
        $fields = $data = $hidden = NULL;
        include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
        $dialog = new base_dialog(
          $this, $this->paramName, $fields, $data, $hidden);
        $dialog->msgs = &$this->msgs;
        $dialog->paramName = 'img';
        $dialog->dialogId = 'image';
        if (isset($moduleObj->attributeFields) &&
            is_array($moduleObj->attributeFields) &&
            count($moduleObj->attributeFields) > 0) {
          foreach ($moduleObj->attributeFields as $fieldName => $field) {
            $result .= sprintf(
              '<label>%s</label>',
              empty($field[0]) ? '' : papaya_strings::escapeHTMLChars($this->_gt($field[0]))
            );
            $result .= $dialog->getDlgElement(
              $fieldName, $field, empty($field[6]) ? '' : $field[6]
            );
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get add on dialog
  *
  * @param string $moduleGuid GUID
  * @access public
  * @return string $result
  */
  function getAddOnDialog($moduleGuid) {
    $result = '';
    $parent = NULL;
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $moduleObj = &base_pluginloader::getPluginInstance($moduleGuid, $parent);
    if (isset($moduleObj) && is_object($moduleObj)) {
      $fields = $data = $hidden = NULL;
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $dialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden);
      $dialog->msgs = &$this->msgs;
      $dialog->paramName = 'mod';
      $dialog->dialogId = 'addon';
      if (method_exists($moduleObj, 'getAttributeFields')) {
        $attributeFields = $moduleObj->getAttributeFields();
      } elseif (isset($moduleObj->attributeFields)) {
        $attributeFields = $moduleObj->attributeFields;
      } else {
        $attributeFields = NULL;
      }
      if (is_array($attributeFields) &&
          count($attributeFields) > 0) {
        $dialogData = '';
        foreach ($attributeFields as $fieldName => $field) {
          $dialogData .= sprintf(
            '<label>%s</label>',
            empty($field[0]) ? '' : papaya_strings::escapeHTMLChars($this->_gt($field[0]))
          );
          $dialogData .= $dialog->getDlgElement(
            $fieldName, $field, empty($field[6]) ? '' : $field[6]
          );
        }
        $result .= sprintf(
          '<param name="dialog">%s</param>', papaya_strings::escapeHTMLChars($dialogData)
        );
      }
      if (method_exists($moduleObj, 'getAttributePopup')) {
        $popupData = $moduleObj->getAttributePopup();
        if (!empty($popupData)) {
          foreach ($popupData as $name => $value) {
            $result .= sprintf(
              '<param name="popup_%s">%s</param>',
              papaya_strings::escapeHTMLChars($name),
              papaya_strings::escapeHTMLChars($value)
            );
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get add on data
  *
  * @param string $moduleGuid GUID
  * @access public
  * @return string $result
  */
  function getAddOnData($moduleGuid) {
    $result = '';
    $parent = NULL;
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $moduleObj = &base_pluginloader::getPluginInstance($moduleGuid, $parent);
    if (isset($moduleObj) && is_object($moduleObj)) {
      $attr = $moduleObj->getPapayaTagData($this->params);
      if (isset($attr) && is_array($attr)) {
        foreach ($attr as $key => $value) {
          if (is_array($value)) {
            $result .= sprintf(
              '<param name="%s" value="%s">',
              papaya_strings::escapeHTMLChars($key),
              papaya_strings::escapeHTMLChars(isset($value['value']) ? $value['value'] : '')
            );
            if (isset($value['options']) &&
                is_array($value['options'])) {
              $result .= '<options>';
              foreach ($value['options'] as $optionKey => $optionTitle) {
                $result .= sprintf(
                  '<option value="%s" caption="%s" />',
                  papaya_strings::escapeHTMLChars($optionKey),
                  papaya_strings::escapeHTMLChars($optionTitle)
                );
              }
              $result .= '</options>';
            }
            $result .= '</param>';
          } else {
            $result .= sprintf(
              '<param name="%s" value="%s" />',
              papaya_strings::escapeHTMLChars($key),
              papaya_strings::escapeHTMLChars($value)
            );
          }
        }
      }
    }
    return $result;
  }

  /**
  * Get image data
  *
  * @param array $imageConf selected image configuration data
  * @access public
  * @return string $result
  */
  function getImageData($imageConf, $createThumbnail = FALSE) {
    $params = array();
    if (preg_match($this->papayaTagPattern, $imageConf, $regs)) {
      if (preg_match_all($this->papayaParamsPattern, $regs[3], $subRegs, PREG_SET_ORDER)) {
        foreach ($subRegs as $match) {
          $paramName = $match[1];
          $paramValue = empty($match[2]) ? '' : $match[2];
          $paramValue .= empty($match[3]) ? '' : $match[3];
          $paramValue .= empty($match[4]) ? '' : $match[4];
          $params[$paramName] = $paramValue;
        }
      }
      return $regs[0];
    } elseif (preg_match('~^([^.,]+(\.\w+)?)(,(\d+)(,(\d+)(,(\w+))?)?)?$~i', $imageConf, $regs)) {
      $params = array(
        'src' => $regs[1],
        'width' => empty($regs[4]) ? 0 : (int)$regs[4],
        'height' => empty($regs[6]) ? 0 : (int)$regs[6],
      );
      if (isset($regs[8]) && in_array($regs[8], array('max', 'min', 'mincrop', 'abs'))) {
        $params['resize'] = $regs[8];
      } else {
        $params['resize'] = 'max';
      }
    }
    if (!empty($params['src'])) {
      $mediaId = $params['src'];
      include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
      $mediaDB = new base_mediadb;
      if ($file = $mediaDB->getFile($mediaId)) {
        $params['name'] = $file['file_name'];
        $params['org_width'] = $file['width'];
        $params['org_height'] = $file['height'];
        $params['mimetype'] = $file['mimetype'];
        $params['type'] = $file['FILETYPE'];
        include_once(PAPAYA_INCLUDE_PATH.'system/base_language_select.php');
        $lngSelect = &base_language_select::getInstance();
        $translations = $mediaDB->getFilesById(array($mediaId), $lngSelect->currentLanguageId);
        if (!empty($translations[$mediaId])) {
          $params['title'] = $translations[$mediaId]['file_title'];
          $params['description'] = PapayaUtilStringHtml::stripTags(
            $translations[$mediaId]['file_description']
          );
        }

        if ($createThumbnail) {
          include_once(PAPAYA_INCLUDE_PATH.'system/base_thumbnail.php');
          $thumbnail = new base_thumbnail;
          $thumbFileName = $thumbnail->getThumbnail(
            $file['file_id'], $file['current_version_id'], 48, 48
          );
          $params['thumbnail_src'] = $this->getWebMediaLink($thumbFileName, 'thumb');
          $params['thumbnail_width'] = $thumbnail->lastThumbSize[0];
          $params['thumbnail_height'] = $thumbnail->lastThumbSize[1];
        }
      }
    }
    $result = '';
    foreach ($params as $key => $value) {
      $result .= sprintf(
        '<param name="%s" value="%s" />',
        papaya_strings::escapeHTMLChars($key),
        papaya_strings::escapeHTMLChars($value)
      );
    }
    return $result;
  }

  /**
  * Get data for media item
  * @param string $mediaId
  * @return string
  */
  function getMediaData($mediaId) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
    $mediaDB = new base_mediadb;
    if ($file = $mediaDB->getFile(substr($mediaId, 0, 32))) {
      if (preg_match('~(.+)\.([\w\d])$~', $file['file_name'], $match)) {
        $fileName = $match[1];
        $fileExt = $match[2];
      } else {
        $fileName = $file['file_name'];
        $fileExt = $file['mimetype_ext'];
      }
      $params = array(
        'src' => $file['file_id'],
        'dyn_src' => $this->getWebMediaLink(
          $file['file_id'], 'media', $file['file_name'], $fileExt
        ),
        'dyn_title' => empty($file['file_title']) ? $file['file_name'] : $file['file_title'],
        'dyn_file' => $file['file_name'],
        'dyn_size' => $file['file_size'],
        'dyn_mimetype' =>  $file['mimetype'],
        'dyn_orgwidth' => (int)$file['width'],
        'dyn_orgheight' => (int)$file['height'],
        'dyn_type' => $mediaDB->mimeToInteger($file['mimetype'])
      );
      $result = '';
      foreach ($params as $key => $value) {
        $result .= sprintf(
          '<param name="%s" value="%s" />',
          papaya_strings::escapeHTMLChars($key),
          papaya_strings::escapeHTMLChars($value)
        );
      }
      return $result;
    }
    return '';
  }

  /**
  * Get data for page
  * @param integer $pageId
  * @return string
  */
  function getPageData($pageId) {
    $pageId = (int)$pageId;
    $lngSelect = &base_language_select::getInstance();
    $lngId = $lngSelect->currentLanguageId;
    include_once(PAPAYA_INCLUDE_PATH.'system/base_topic_edit.php');
    $topic = new base_topic_edit;
    $topic->loadTranslatedData($pageId, $lngId);
    if (isset($topic->topic) && isset($topic->topic['TRANSLATION'])) {
      $params = array(
        'topic' => $pageId,
        'dyn_title' => empty($topic->topic['TRANSLATION']['topic_title'])
          ? '' : (string)$topic->topic['TRANSLATION']['topic_title']
      );
      $result = '';
      foreach ($params as $key => $value) {
        $result .= sprintf(
          '<param name="%s" value="%s" />',
          papaya_strings::escapeHTMLChars($key),
          papaya_strings::escapeHTMLChars($value)
        );
      }
      return $result;
    }
    return '';
  }
}
?>
