<?php
/**
* Catalog page navigation
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
* @package Papaya-Modules
* @subpackage Free-Catalog
* @version $Id: actbox_catalog_limited_content.php 36191 2011-09-12 12:16:28Z kersken $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Function class base_catalog
*/
require_once(dirname(__FILE__).'/base_catalog.php');

/**
* Catalog page navigation - outputs a list of catalog
* catagories and links to a catalog content page
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class actionbox_catalog_limited_content extends base_actionbox {

  /**
  * Content edit fields
  * @todo Use a callback function to select the category id from
  * @var array $editFields
  */
  var $editFields = array(
    'categ_base_id' => array ('Catalog Id', 'isNum', TRUE, 'input', 10, '', 0),
    'catalog_limit' => array ('Number of categories', 'isNum', TRUE, 'input', 5, '', 0),
    'catalog_topic_unique' => array (
      'Unique topic links', 'isNum', FALSE, 'combo',
      array(0=>'Yes', 1=>'No'), 'Allow only unique topic links?'
    ),
    'link_mode' => array(
      'Link page name mode',
      'isNoHTML',
      TRUE,
      'combo',
      array('catalog' => 'Catalog titles', 'topic' => 'Topic titles'),
      '',
      'catalog'
    )
  );

  /**
  * base_catalog object
  * @var base_cataglog
  */
  var $_baseObject = NULL;

  /**
  * Return page XML
  *
  * @access public
  * @return string $str
  */
  function getParsedData() {
    $this->setDefaultData();
    $pageId = empty($this->data['catalog_id']) ? 0 : (int)$this->data['catalog_id'];
    $className = get_class($this->parentObj);
    $catalog = $this->getBaseObject();
    $catalog->tableTopics = $this->parentObj->tableTopics;
    $catalog->tableTopicsTrans = $this->parentObj->tableTopicsTrans;
    $categId = empty($this->data['categ_base_id']) ? 0 : (int)$this->data['categ_base_id'];
    $languageId = $this->parentObj->getContentLanguageId();
    $catalog->loadCatalogLinkList(
      $categId,
      $languageId,
      empty($this->data['catalog_limit']) ? 0 : (int)$this->data['catalog_limit'],
      empty($this->data['catalog_topic_unique'])
        ? FALSE : (bool)$this->data['catalog_topic_unique']
    );
    if (isset($catalog->linkList[$categId]) && is_array($catalog->linkList[$categId]) &&
        count($catalog->linkList[$categId]) > 0) {
      $str = '<subtopics>'.LF;
      if (isset($this->data['link_mode']) && $this->data['link_mode'] == 'topic') {
        $linkTitles = $catalog->getTopicTitles($languageId, $categId);
      }
      foreach ($catalog->linkList[$categId] as $link) {
        if (isset($this->data['link_mode']) && $this->data['link_mode'] == 'topic' &&
            isset($linkTitles[$link['topic_id']])) {
          $title = $linkTitles[$link['topic_id']];
        } else {
          $title = $link['cataloglink_title'];
        }
        $str .= sprintf(
          '<link title="%s" href="%s"/>'.LF,
          papaya_strings::escapeHtmlChars($link['cataloglink_title']),
          papaya_strings::escapeHtmlChars(
            $this->getWebLink(
              $link['topic_id'],
              NULL,
              NULL,
              NULL,
              NULL,
              $title,
              empty($this->data['catalog_id']) ? 0 : (int)$this->data['catalog_id']
            )
          )
        );
        $str .= '<subitem>';
        $str .= sprintf(
          '<title>%s</title>',
          papaya_strings::escapeHtmlChars($link['cataloglink_title'])
        );
        $str .= '</subitem>';
      }
      $str .= '</subtopics>'.LF;
      return $str;
    }
    return '';
  }

  /**
  * Get the base_catalog object
  *
  * @return base_catalog
  */
  function getBaseObject() {
    if (!is_object($this->_baseObject)) {
      $this->_baseObject = new base_catalog();
    }
    return $this->_baseObject;
  }
}
?>