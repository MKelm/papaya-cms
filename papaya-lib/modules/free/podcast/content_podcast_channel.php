<?php
/**
* Page module - PodcastChannel with image
*
* This module realizes podcast distribution capability to papaya.
* A podcast channel contains meta information about a podcast channel
* and has subpages, each representing media data files avaiable over
* this podcast channel.
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link      http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Podcast
* @version $Id: content_podcast_channel.php 38114 2013-02-12 16:37:37Z weinert $
*/

/**
* Base class for content
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Page module - PodcastChannel with image
*
* @package Papaya-Modules
* @subpackage Free-Podcast
*/
class content_podcast_channel extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'max' => array('Count', 'isNum', TRUE, 'input', 5, '', 10000),
    'timetolive' => array('Time To Live', 'isNum', TRUE, 'input', 5,
      'Indicates the amount of time (in minutes) how long a channel
       should be cached before refreshing from the source', 5),
    'sort' => array(
      'Sort',
      'isNum',
      TRUE,
      'translatedcombo',
      array(
        base_topiclist::SORT_WEIGHT_ASCENDING => 'Position Ascending',
        base_topiclist::SORT_WEIGHT_DESCENDING => 'Position Descending',
        base_topiclist::SORT_CREATED_ASCENDING => 'Created Ascending',
        base_topiclist::SORT_CREATED_DESCENDING => 'Created Descending',
        base_topiclist::SORT_PUBLISHED_ASCENDING => 'Modified/Published Ascending',
        base_topiclist::SORT_PUBLISHED_DESCENDING => 'Modified/Published Descending',
        base_topiclist::SORT_RANDOM => 'Random'
      ),
      '',
      0
    ),
    'Image',
    'image' => array('Image', 'isSomeText', FALSE, 'image', 400,
      'Supported formats are jpg and png', ''),
    'image_link' => array('Image Link', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'Texts',
    'title' => array('Title', 'isNoHTML', TRUE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'summary' => array('Summary', 'isNoHTML', FALSE, 'textarea', 8, '', ''),
    'description' => array('Description', 'isNoHTML', TRUE, 'textarea', 8,
      'Short text that describes the content of the article', ''),
    'copyright' => array('Copyright', 'isNoHTML', FALSE, 'input', 100, '', ''),
    'link' => array('Link', 'isNoHTML', FALSE, 'input', 200, '', ''),
    'author' => array('Author', 'isNoHTML', FALSE, 'input', 60, '', ''),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    $result .= sprintf(
      '<image>%s</image>'.LF,
      $this->getPapayaImageTag($this->data['image'])
    );
    $result .= sprintf(
      '<imagelink>%s</imagelink>'.LF,
      papaya_strings::escapeHTMLChars($this->data['image_link'])
    );
    $result .= sprintf(
      '<summary>%s</summary>',
      $this->getXHTMLString($this->data['summary'])
    );
    $result .= sprintf(
      '<description>%s</description>',
      $this->getXHTMLString($this->data['description'])
    );
    $result .= sprintf(
      '<timetolive>%d</timetolive>'.LF,
      (int)$this->data['timetolive']
    );
    $result .= sprintf(
      '<author>%s</author>',
      papaya_strings::escapeHTMLChars($this->data['author'])
    );
    $result .= sprintf(
      '<server>%s</server>',
      empty($_SERVER['SERVER_NAME']) ? '' : papaya_strings::escapeHTMLChars($_SERVER['SERVER_NAME'])
    );
    $result .= sprintf(
      '<link>%s</link>',
      papaya_strings::escapeHTMLChars($this->data['link'])
    );
    $result .= sprintf(
      '<copyright>%s</copyright>'.LF,
      $this->getXHTMLString($this->data['copyright'])
    );

    include_once(PAPAYA_INCLUDE_PATH.'system/base_topiclist.php');
    $topicList = new base_topiclist;
    $topicList->databaseURI = $this->parentObj->databaseURI;
    $topicList->databaseURIWrite = $this->parentObj->databaseURIWrite;
    $topicList->tableTopics = $this->parentObj->tableTopics;
    $topicList->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
    $topicClass = get_class($this->parentObj);
    $topicList->loadList(
      $this->parentObj->topicId,
      (int)$this->parentObj->getContentLanguageId(),
      is_a($this->parentObj, 'papaya_publictopic'),
      (int)$this->data['sort']
    );
    if (isset($this->data['max']) && $this->data['max'] < 1) {
      $this->data['max'] = 1000;
    }
    $subTopicString = papaya_strings::entityToXML(
      $topicList->getList($topicClass, (int)$this->data['max'])
    );
    $result .= $subTopicString;
    return $result;
  }

  /**
  * Get parsed teaser
  *
  * @access public
  * @return string
  */
  function getParsedTeaser() {
    $this->setDefaultData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    if (isset($this->data['description']) && trim($this->data['description'] != '')) {
      $result .= sprintf(
        '<description>%s</description>'.LF,
        $this->getXHTMLString($this->data['description'])
      );
    } elseif (isset($this->data['description']) &&
              trim($this->data['description']) != '') {
      $teaser = str_replace("\r\n", "\n", $this->data['description']);
      if (preg_match("/^(.+)([\n]{2})/sU", $teaser, $regs)) {
        $teaser = $regs[1];
      }
      $result .= sprintf(
        '<description>%s</description>'.LF,
        $this->getXHTMLString($teaser)
      );
    }
    $result .= sprintf(
      '<image>%s</image>'.LF,
      $this->getPapayaImageTag($this->data['image'])
    );

    $result .= sprintf(
      '<link>%s</link>',
      $this->getXHTMLString($this->data['link'])
    );
    return $result;
  }
}

?>