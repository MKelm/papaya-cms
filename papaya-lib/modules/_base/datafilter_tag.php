<?php
/**
* Tag data filter
*
* @package _base
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_datafilter.php');


class datafilter_tag extends base_datafilter {
  /**
  * PapayaContentPageTags object
  * @var object $tagObject
  */
  private $_tagObject = NULL;

  /**
  * Topic id
  * @var integer $topicId
  */
  public $topicId = 0;

  /**
  * Language id
  * @var integer $lngId
  */
  public $lngId = 0;

  /**
  * Set Page tags
  * @param PapayaContentPageTags $tagsObject
  */
  public function setPageTags(PapayaContentPageTags $tagsObject) {
    $this->_tagObject = $tagsObject;
  }

  /**
  * get Page tags
  * @return PapayaContentPageTags $tagObject
  */
  public function getPageTags() {
    if (NULL === $this->_tagObject) {
      $this->_tagObject = new PapayaContentPageTags();
    }
    return $this->_tagObject;
  }
	
  /**
  * Initialize tag object and variables
  * @param object &$contentObj object of content by base_datafilter_list
  * @return boolean status result
  */
  public function initialize($contentObj) {
    $this->getPageTags();
    if (isset($this->_tagObject) && is_object($this->_tagObject)) {
      $this->topicId = $contentObj->parentObj->topicId;
      $this->lngId = $contentObj->parentObj->topic['TRANSLATION']['lng_id'];
      if (isset($this->topicId) && isset($this->lngId)) {
        return TRUE;
      }
    }
    return FALSE;
  }
  
  public function prepareFilterData() {
    
  }

  /**
  * Get XML output for linked tags
  * @param array $parseParams parsing params 
  * @return string XML
  */
  function getFilterData($parseParams = NULL) {
    $tags = $this->getPageTags();
    if ($tags->load($this->topicId, $this->lngId)) {
      $dom = new PapayaXmlDocument();
      $node = $dom->appendElement('tags');
      foreach ($tags as $tag) {
        $node->appendElement("tag", array("id" => $tag['id']), $tag['title']);
      }
      return $node->saveXml();
    }
  }
}