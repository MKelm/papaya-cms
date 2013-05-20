<?php
/**
* Base class for rss reader page module.
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
* @version $Id: Base.php 37762 2012-11-30 17:53:05Z weinert $
*/

/**
* Base class for rss reader page module.
*
* @package Papaya-Modules
* @subpackage Free-Rss
*/
class RssReaderPageBase {

  /**
  * owner object
  * @var object
  */
  private $_owner = NULL;

  /**
  * Rss reader object.
  * @var RssReader
  */
  private $_rssReaderObject = NULL;

  /**
  * Configuration
  * @var PapayaConfiguration
  */
  private $_configuration = NULL;

  /**
  * Page configuration data
  * @var array
  */
  private $_data = array();

  /**
  * Page parameters
  * @var array
  */
  private $_params = array();

  /**
  * Messages
  * @var array
  */
  private $_messages = array();

  /***************************************************************************/
  /** Methods                                                                */
  /***************************************************************************/

  /**
  * Constructor of the class
  *
  * NOTE:
  * The calling object should be an instance of base_plugin or at leased an inheritance.
  *
  * @param object $owner Caller object
  */
  public function __construct($owner = NULL) {
    $this->_owner = $owner;
  }

  /**
  * Returns the parsed rss feed xml for output.
  *
  * @return string xml
  */
  public function getXML() {
    $result = sprintf(
      '<title>%s</title>',
      PapayaUtilStringHtml::escapeStripped($this->_data['title'])
    );
    if (isset($this->_data['text']) && !empty($this->_data['text'])) {
      $result .= sprintf(
        '<text>%s</text>',
        $this->_owner->getXHTMLString($this->_data['text'])
      );
    }
    try {
      $reader = $this->getReaderObject();
      $feed = $reader->parseFeed($this->_data['feedUrl']);
      $result .= $this->getParsedRssFeedXML($feed);
    } catch (LogicException $e) {
      $this->addMessage($e->getMessage(), 'error');
    }
    $result .= $this->getMessagesXML();
    return $result;
  }

  /**
  * Retrieves the feed's output xml by given data.
  *
  * @param array $feed
  * @return string xml output
  */
  public function getParsedRssFeedXML($feed) {
    $result = '';
    if (!empty($feed)) {
      $logo = (isset($feed['logo']) && !empty($feed['logo'])) ?
        sprintf('logo="%s"', $feed['logo']) :
        '';
      $result .= sprintf('<feed last-modified="%s" %s>', $feed['updated'], $logo);
      $result .= sprintf(
        '<title>%s</title>',
        PapayaUtilStringHtml::escapeStripped($feed['title'])
      );
      $counter = 0;
      foreach ($feed['entries'] as $id => $entry) {
        $result .= sprintf(
          '<entry published="%s" href="%s">',
          $entry['published'],
          $entry['link']
        );
        if (isset($entry['title']) && !empty($entry['title'])) {
          $result .= sprintf(
            '<title>%s</title>',
            $this->_owner->getXHTMLString($entry['title'])
          );
        }
        if (isset($entry['author']) && !empty($entry['author'])) {
          $result .= sprintf(
            '<author>%s</author>',
            $this->_owner->getXHTMLString($entry['author'])
          );
        }
        $result .= sprintf(
          '<content>%s</content>',
          $this->_owner->getXHTMLString($entry['content'])
        );
        $result .= '</entry>';
        $counter++;
      }
      $result .= '</feed>';
    }
    return $result;
  }

  /**
  * Collects messages for output.
  * @param string $message
  * @param string $type info|error|warning
  */
  public function addMessage($message, $type) {
    $this->_messages[$type][] = $message;
  }

  /**
  * Shows collected messages.
  * @return string xml output
  */
  public function getMessagesXML() {
    $result = '';
    if (count($this->_messages) > 0) {
      $result .= '<messages>';
      foreach ($this->_messages as $type => $messages) {
        foreach ($messages as $message) {
          $result .= sprintf(
            '<message type="%s">%s</message>',
            PapayaUtilStringXml::escape($type),
            PapayaUtilStringXml::escape($message)
          );
        }
      }
      $result .= '</messages>';
    }
    return $result;
  }

  /***************************************************************************/
  /** Helper / instances                                                     */
  /***************************************************************************/

  /**
  * Set the current configuration
  *
  * @param PapayaConfiguration instance
  */
  public function setConfiguration($configuration) {
    $this->_configuration = $configuration;
  }

  /**
  * Set the page configuration data
  *
  * @param array $data configuration data
  */
  public function setPageData($data) {
    $this->_data = $data;
  }

  /**
  * Set the page request parameters
  *
  * @param array $params request parameters
  */
  public function setPageParams($params) {
    $this->_params = $params;
  }

  /**
  * Retrieves the base reader object.
  * @return RssReader
  */
  public function getReaderObject() {
    if (!(isset($this->_rssReaderObject) && is_object($this->_rssReaderObject))) {
      include_once(dirname(__FILE__).'/../Reader.php');
      $this->_rssReaderObject = new RssReader();
    }
    return $this->_rssReaderObject;
  }

  /**
  * Retrieves the base reader object.
  * @return RssReader
  */
  public function setReaderObject($readerObject) {
    $this->_rssReaderObject = $readerObject;
  }

  /**
  * Adds a domain/url prefix to given url if needed.
  * @param string $link
  * @param string $prefix domain / url
  */
  public function validateLink($link, $prefix) {
    $reader = $this->getReaderObject();
    return $reader->validateURL($link, $prefix);
  }

}
?>