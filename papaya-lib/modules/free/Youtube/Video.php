<?php

/**
* Youtube video page, page module base class
* Display youtube videos
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license papaya Commercial License (PCL)
*
* Redistribution of this script or derivated works is strongly prohibited!
* The Software is protected by copyright and other intellectual property
* laws and treaties. papaya owns the title, copyright, and other intellectual
* property rights in the Software. The Software is licensed, not sold.
*
* @package Papaya-Modules
* @subpackage Youtube
*/

/**
 * Simple youtube page module
 * Display youtube videos
 *
 * @package Papaya-Modules
 * @subpackage Youtube
 */
class PapayaModuleYoutubeVideo extends PapayaObject {

  /**
  * Page configuration data
  * @var array
  */
  private $_data = array();

  /**
  * Box configuration data
  * @var array
  */
  private $_boxData = array();

  /**
  * Page request parameters
  * @var array
  */
  private $_params = NULL;

  /**
  * Owner object
  * @var PapayaModuleYoutubeVideoPage
  */
  private $_owner = NULL;

  /**
  * The PapayaXmlDocument to be used
  * @var PapayaXmlDocument 
  */
  private $_papayaXmlDomObject = NULL;

  /**
  * Set owner object
  *
  * @param PapayaModuleYoutubeVideoPage $owner
  */
  public function setOwner($owner) {
    $this->_owner = $owner;
  }

  /**
  * Set page configuration data
  *
  * @param array $data
  */
  public function setPageData($data) {
    $this->_data = $data;
  }

  /**
  * set box configuration data
  *
  * @param array $boxData  
  */
  public function setBoxData($boxData) {
    $this->_boxData = $boxData;
  }

  /**
  * Set the PapayaXmlDocument to be used
  * 
  * @param PapayaXmlDcument $papayaXmlDomObject
  */
  public function setPapayaXmlDomObject($papayaXmlDomObject) {
    $this->_papayaXmlDomObject = $papayaXmlDomObject;
  }
  
  /**
  * Get thr PapayaXmlDocument object 
  * @return PapayaXmlDocument
  */
  public function getPapayaXmlDomObject() {
    if (!is_object($this->_papayaXmlDomObject)) {
      $this->_papayaXmlDomObject = new PapayaXmlDocument();
    }
    return $this->_papayaXmlDomObject;
  }

  /**
  * Get The page's XML output
  *
  * @return string XML  
  */
  public function getPageXml() {

    $papyaXmlDomObject = $this->getPapayaXmlDomObject();
    $content = $papyaXmlDomObject->appendElement("video");
    if ($this->_data['set_no_cookie'] == 1) {
      $url = $this->_data['youtube_no_cookie_url'];
    } else {
      $url = $this->_data['youtube_url'];
    }

    if ($this->_data['video_format'] == '16:9') {
      $playerHeight = $this->_data['player_width'] / 1.77;
    } else if ($this->_data['video_format'] == '4:3') {
      $playerHeight = $this->_data['player_width'] / 1.33;
    }

    $content->appendElement("title", array(), $this->_data['title']);
    $content->appendElement('subtitle', array(), $this->_data['subtitle']);
    $content->appendElement(
      "player", 
      array(
        "videoId" => $this->_data['youtube_video_id'],
        "width" => $this->_data['player_width'],
        "height" => round($playerHeight),
        "autoplay" => $this->_data['autoplay'],
        "rel" => $this->_data['related'],
        "info" => $this->_data['show_info'],
        "controls" => $this->_data['controls'],
        "url" => $url
      )
    );
    $teaser = $content->appendElement("teaser");
    $teaser->appendXml($this->_data['teaser']);
    $image = $content->appendElement(
      "image", 
      array(
        "align" => $this->_data['imgalign'],
        "break" => $this->_data['breakstyle']
      )
    );
    $image->appendXml(
      $this->_owner->getPapayaImageTag(
        $this->_data['image']
      )
    );
    $text = $content->appendElement("text");
    $text->appendXml($this->_data['text']);
    return $papyaXmlDomObject->saveXML($content);
  }
  
  /**
  * Get the page's teaser XML output 
  * @return string XML
  */
  public function getTeaserXml() {
    $papayaXmlDomObject = $this->getPapayaXmlDomObject();
    $teaser = $papayaXmlDomObject->appendElement('teaser');
    $teaser->appendElement('title', array(), $this->_data['title']);
    $teaser->appendElement('subtitle', array(), $this->_data['subtitle']);
    $text = $teaser->appendElement('text');
    $text->appendXml($this->_data['teaser']);
    $image = $teaser->appendElement(
      "image", 
      array(
        "align" => $this->_data['imgalign'],
        "break" => $this->_data['breakstyle']
      )
    );
    $image->appendXml(
      $this->_owner->getPapayaImageTag(
        $this->_data['image']
      )
    );
    return $teaser->saveFragment();
    
  }

  /**
  * Get The page's XML output
  *
  * @return string XML  
  */
  public function getBoxXml() {

    $papayaXmlDomObject = $this->getPapayaXmlDomObject();
    $content = $papayaXmlDomObject->appendElement("youtubebox");
    if ($this->_boxData['set_no_cookie'] == 1) {
      $url = $this->_boxData['youtube_no_cookie_url'];
    } else {
      $url = $this->_boxData['youtube_url'];
    }
    if ($this->_boxData['video_format'] == '16:9') {
      $playerHeight = $this->_boxData['player_width'] / 1.77;
    } else if ($this->_boxData['video_format'] == '4:3') {
      $playerHeight = $this->_boxData['player_width'] / 1.33;
    }

    $content->appendElement("title", array(), $this->_boxData['title']);
    $content->appendElement(
      "player", 
      array(
        "videoId" => $this->_boxData['youtube_video_id'],
        "width" => $this->_boxData['player_width'],
        "height" => round($playerHeight),
        "autoplay" => $this->_boxData['autoplay'],
        "rel" => $this->_boxData['related'],
        "info" => $this->_boxData['show_info'],
        "controls" => $this->_boxData['controls'],
        "url" => $url
      )
    );
    $text = $content->appendElement("text");
    $text->appendXml($this->_boxData['text']);
    return $papayaXmlDomObject->saveXML($content);
  }

}

?>
