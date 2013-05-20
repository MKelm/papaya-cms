<?php
/**
* Page module for reading and showing foreign rss feeds.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
* GPL License:
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Rss
* @version $Id: ReaderPage.php 37760 2012-11-30 16:34:25Z weinert $
*/

/**
* Load necessary libraries
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module for reading and showing foreign rss feeds.
*
* @package Papaya-Modules
* @subpackage Free-Rss
*/
class RssReaderPage extends base_content {
  /**
  * Instance of the base class
  * @var RssReaderPageBase
  */
  private $_pageBaseObject = NULL;

  /**
  * Parameter namespace
  * @var string
  */
  public $paramName = 'ffr';

  /**
  * Edit fields
  * @var array
  */
  public $editFields = array(
    'Page',
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
    'text' => array('Text', 'isSomeText', FALSE, 'simplerichtext', 7),
    'RSS',
    'feedUrl' => array(
      'Rss feed url',
      'isHTTPX',
      TRUE,
      'input',
      255,
      'Rss feed url that should be parsed and shown.',
      'http://'
    ),
    'limit' => array(
      'Maximum count of items',
      'isNum',
      FALSE,
      'input',
      5,
      'leave empty if all received feed items should be displayed'
    )
  );

  /**
  * Get instance of the base class
  *
  * @return object instance of ReaderPageBase
  */
  public function getPageBaseObject() {
    if (!(isset($this->_pageBaseObject) && is_object($this->_pageBaseObject))) {
      include_once(dirname(__FILE__).'/ReaderPage/Base.php');
      $this->_pageBaseObject = new RssReaderPageBase($this);
      $this->_pageBaseObject->setConfiguration($this->papaya()->options);
    }
    return $this->_pageBaseObject;
  }

  /**
  * Set the instance of a base object to be used instead the original one.
  *
  * @param object $mock
  */
  public function setPageBaseObject($mock) {
    $this->_pageBaseObject = $mock;
  }

  /**
  * Get parsed data
  */
  public function getParsedData() {
    $this->setDefaultData();
    $this->initializeParams();
    $pageBaseObject = $this->getPageBaseObject();
    $pageBaseObject->setPageData($this->data);
    $pageBaseObject->setPageParams($this->params);
    return $pageBaseObject->getXML();
  }
}
?>