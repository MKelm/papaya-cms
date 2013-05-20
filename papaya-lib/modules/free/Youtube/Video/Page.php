<?php

/**
* Page module - Youtube video page.
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
* @subpackage Free-Youtube
*/

/**
* Base class for page modules.
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

class PapayaModuleYoutubeVideoPage extends base_content {
  
  /**
  * Parameter namespace
  * @var string
  */
  public $paramName = 'ytb';
  
  /**
  * Edit fields for page configuration
  * @var array
  */
  public $editGroups = array(
    array(
      "General",
      "categories-content",
      array(
        "Texts",
        "title" => array(
          "Title",
          "isNoHTML",
          TRUE,
          "input",
          255,
        ),
        "subtitle" => array(
          "Subtitle",
          "isNoHTML",
          FALSE,
          "input",
          100,
          '',
          ''
        ), 
        "teaser" => array(
          "Teaser",
          "isSomeText",
          FALSE,
          "simplerichtext",
          10,
          '',
          ''
        ),
        "image" => array(
          "Teaser image",
          "isSomeText",
          FALSE,
          "image",
          400,
          '',
          ''
        ),
        'imgalign' => array(
          'Image align',
          'isAlpha',
          TRUE,
          'combo',
          array(
            'left' => 'left',
            'right' => 'right',
            'center' => 'center'
          ),
          '',
          'left'
        ),
        'breakstyle' => array(
          'Text float',
          'isAlpha',
          TRUE,
          'combo',
          array(
             'none' => 'None',
            'side' => 'Side'
          ), 
          '',
          'none'
        ),
        "text" => array(
         "Text",
          "isSometext",
          FALSE,
          "richtext",
          30,
          '',
          '',
        ),
      )
    ),
    array(
      "Player settings",
      "categories-properties",
      array(
        "youtube_video_id" => array(
          "Youtube vide id",
          "isSomeText",
          TRUE,
          "input",
          100,
          'the id is the last charechters after the equel sign in the youtube url.',
          '',
        ),
        "youtube_url" => array(
          "Youtube url",
          "isSomeText",
          TRUE,
          "input",
          100,
          '',
          'http://www.youtube.com'
        ),
        "youtube_no_cookie_url" => array(
          "Youtube no cookie url",
          "isSomeText",
          TRUE,
          "input",
          100,
          '',
          'http://www.youtube-nocookie.com'
        ),
        "set_no_cookie" => array(
          "Set no cookie url",
          "isNum",
          TRUE,
          "yesno",
          NULL,
          '',
          0
        ),
        "video_format" => array(
          "Video format",
          "isNoHTML",
          TRUE,
          'combo',
          array(
            "16:9" => "16:9",
            "4:3" => "4:3"
          ),
          '',
          'none'
        ),
        "player_width" => array(
          "Player width",
          "isNum",
          TRUE,
          "input",
          3,
          '',
          '560'
        ),
        "autoplay" => array(
          "Autoplay",
          "isNum",
          TRUE,
          "yesno",
          NULL,
          '',
          0
        ),
        "related" => array(
          "show related videos when the video ends",
          "isNum",
          TRUE,
          "yesno",
          NULL,
          "",
          0
        ),
        "show_info" => array(
          "Show video info",
          "isNum",
          TRUE,
          "yesno",
          NULL,
          "",
          1
        ),
        "controls" => array(
          "Show player controls",
          "isNum",
          TRUE,
          "yesno",
          NULL,
          "",
          1
        )
      )
    )
  );
  
  /**
  * The PapayaModuleYoutubeVideoPageBase to be used
  * @var PapayaModuleYoutubeVideoPageBase
  */
  private $_pageVideoObject = NULL;
  
  /**
  * Set the PapayaModuleYoutubeVideoPageBase to be used
  * 
  * @param PapayaModuleYoutubeVideoPageBase $pageBaseObject
  */
  public function setVideoObject($pageVideoObject) {
    $this->_pageVideoObject = $pageVideoObject;
  }
  
  /**
  * Get (and, if necessary, initialize) the PapayaModuleYoutubeVideoPageBase object 
  * 
  * @return PapayaModuleYoutubeVideoPageBase $pageBaseObject
  */
  public function getVideoObject() {
    if (!is_object($this->_pageVideoObject)) {
      include_once (PAPAYA_INCLUDE_PATH.'modules/free/Youtube/Video.php');
      $this->_pageVideoObject = new PapayaModuleYoutubeVideo();
    }
    return $this->_pageVideoObject;
  }


  /**
  * Get the page's XML output
  *
  * @return string XML
  */
  public function getParsedData() {
    $pageBaseObject = $this->getVideoObject();
    $this->setDefaultData();
    $this->initializeParams();
    $pageBaseObject->setPageData($this->data);
    $pageBaseObject->setOwner($this);
    return $pageBaseObject->getPageXml();
  }
  
  /**
  * get the teaser's XML output
  *  
  * @return String XML
  */
  public function getParsedTeaser() {
    $pageBaseObject = $this->getVideoObject();
    $this->setDefaultData();
    $pageBaseObject->setPageData($this->data);
    $pageBaseObject->setOwner($this);
    return $pageBaseObject->getTeaserXml();
  }
  
}

?>
