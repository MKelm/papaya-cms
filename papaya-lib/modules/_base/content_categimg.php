<?php
/**
* Page module - category with image
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
* @subpackage _Base
* @version $Id: content_categimg.php 38177 2013-02-25 16:03:55Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');
/**
* Article list
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_topiclist.php');

/**
* Page module - category with image
*
* Display subordinate as page teaser as images
*
* @package Papaya-Modules
* @subpackage _Base
*/
class content_categimg extends base_content {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'max' => array('Count', 'isNum', TRUE, 'input', 5, '', 100),
    'columns' => array('Column count', 'isNum', TRUE, 'input', 1, '', 2),
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
    'image' => array('Image', 'isSomeText', FALSE, 'image', 400, '', ''),
    'imgalign' => array('Image align', 'isAlpha', FALSE, 'combo',
      array('left' => 'left', 'right' => 'right', 'top' => 'top', ), '', 'left'),
    'breakstyle' => array('Text float', 'isAlpha', TRUE, 'combo',
      array('none' => 'None', 'side' => 'Side', 'center' => 'Center'), '', 'none'),
    'Texts',
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Papaya will apply your linebreaks to the output page.',
      0),
    'title' => array('Title', 'isSomeText', FALSE, 'input', 400, '', ''),
    'subtitle' => array('Subtitle', 'isSomeText', FALSE, 'input', 400, '', ''),
    'teaser' => array('Teaser', 'isSomeText', FALSE, 'simplerichtext', 10, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 20, '', ''),
    'Thumbnails',
    'thumbwidth' => array('Width', 'isNum', TRUE, 'input', 5, '', 100),
    'thumbheight' => array('Height', 'isNum', TRUE, 'input', 5, '', 100),
    'resizemode' => array('Resize mode', 'isAlpha', TRUE, 'combo',
      array('max' => 'Maximal', 'min' => 'Minimum', 'mincrop' => 'Minimum crop',
        'abs' => 'Absolute'), '', 'max'),
  );




  /**
  * Get parsed data
  *
  * @access public
  * @param array | NULL $parseParams Parameters from output filter
  * @return string
  */
  function getParsedData($parseParams = NULL) {
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
    if (!empty($this->data['image'])) {
      $result .= sprintf(
        '<image align="%s" break="%s">%s</image>'.LF,
        papaya_strings::escapeHTMLChars($this->data['imgalign']),
        papaya_strings::escapeHTMLChars($this->data['breakstyle']),
        $this->getPapayaImageTag($this->data['image'])
      );
    }
    $result .= sprintf(
      '<teaser>%s</teaser>',
      $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
    );
    $result .= sprintf(
      '<text>%s</text>',
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    $result .= sprintf('<columns>%d</columns>'.LF, (int)$this->data['columns']);
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
    if (trim($subTopicString) != '') {
      $dom = new PapayaXmlDocument();
      $dom->loadXml($subTopicString);
      $thumbnails = new PapayaUiContentTeaserImages(
        $dom->documentElement,
        (int)$this->data['thumbwidth'],
        (int)$this->data['thumbheight'],
        $this->data['resizemode']
      );
      $result .= $thumbnails->getXml();
    }
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
      '<title>%s</title>'.LF,
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<subtitle>%s</subtitle>'.LF,
      papaya_strings::escapeHTMLChars($this->data['subtitle'])
    );
    if (isset($this->data['teaser']) && trim($this->data['teaser'] != '')) {
      $result .= sprintf(
        '<text>%s</text>',
        $this->getXHTMLString($this->data['teaser'], !((bool)$this->data['nl2br']))
      );
    } elseif (isset($this->data['text']) && trim($this->data['text']) != '') {
      $teaser = str_replace("\r\n", "\n", $this->data['text']);
      if (preg_match("/^(.+)([\n]{2})/sU", $teaser, $regs)) {
        $teaser = $regs[1];
      }
      $result .= sprintf(
        '<text>%s</text>'.LF,
        $this->getXHTMLString($teaser, !((bool)$this->data['nl2br']))
      );
    }
    if (!empty($this->data['image'])) {
      $result .= sprintf(
        '<image align="%s" break="%s">%s</image>'.LF,
        papaya_strings::escapeHTMLChars($this->data['imgalign']),
        papaya_strings::escapeHTMLChars($this->data['breakstyle']),
        $this->getPapayaImageTag($this->data['image'])
      );
    }
    return $result;
  }
}

?>