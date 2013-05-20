<?php
/**
* Mailto Box
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
* @subpackage Free-Mail
* @version $Id: actbox_sendpage_mailto.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Mailto Box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');
/**
* Mailto Box
*
* @package Papaya-Modules
* @subpackage Free-Mail
*/
class actionbox_sendpage_mailto extends base_actionbox {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'title' => array('Link Title', 'isNoHTML', FALSE, 'input', 200, '', 'send page'),
    'subject' => array('Subject template of email', 'isNoHTML', FALSE, 'input', 200, '',
      'someone sent you this webpage'),
    'body' => array('Body template of email', 'isNoHTML', FALSE, 'textarea', 20, '',
      "{%title%}\n\n{%teaser%}\n\n{%link%}"),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result xml
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = '<mailto>'.LF;
    $result .= sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      $this->getXHTMLString($this->data['title'])
    );
    $result .= sprintf(
      '<mailtolink>%s</mailtolink>'.LF,
      $this->getXHTMLString($this->getMailtoLink())
    );
    $result .= '</mailto>'.LF;
    return $result;
  }

  /**
  * get mailto link
  *
  * linkes topic object from id and fetches data by using simple_xmltree
  * @uses simple_xmltree:create()
  * @uses email:getMailtoLink()
  *
  * @access public
  * @return string mailto link e.g. mailto:?subject=something%20as%20title&body=something%20else
  */
  function getMailtoLink() {
    $this->setDefaultData();
    $content = array(
      'title' => '',
      'teaser' => '',
      'link' => ''
    );

    $topicId = $this->parentObj->topicId;
    $className = get_class($this->parentObj);
    $this->linkedTopic = new $className();
    if ($this->linkedTopic->topicExists($topicId) &&
        $this->linkedTopic->loadOutput($topicId, $this->parentObj->getContentLanguageId())) {
      if ($GLOBALS['PAPAYA_PAGE']->validateAccess($topicId)) {
        if ($str = $this->linkedTopic->parseContent(FALSE)) {
          $xmlTree = &simple_xmltree::createFromXML($str, $this);
          if (is_object($xmlTree) && $xmlTree->documentElement->hasChildNodes()) {
            for ($idx = 0; $idx < $xmlTree->documentElement->childNodes->length; $idx++) {
              $node = &$xmlTree->documentElement->childNodes->item($idx);
              if ($node->nodeType == XML_ELEMENT_NODE) {
                switch ($node->nodeName) {
                case 'text':
                case 'title':
                  $templateValues[$node->nodeName] = $node->valueOf();
                  break;
                }
              }
            }

            include_once(PAPAYA_INCLUDE_PATH.'system/sys_email.php');
            $this->emailObj = new email();

            $templateValues['link'] = $this->getAbsoluteURL($this->getWebLink());

            $this->emailObj->setSubject($this->data['subject'], $templateValues);
            $this->emailObj->setBody($this->data['body'], $templateValues);
            return $this->emailObj->getMailtoLink();
          }
        }
      }
    }
    return '';
  }
}
?>