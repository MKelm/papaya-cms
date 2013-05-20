<?php
/**
* Catalog base module
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
* @version $Id: base_catalog.php 36816 2012-03-09 13:56:00Z yurtsever $
*/

/**
* Basicclass for database access
*/
require_once(PAPAYA_INCLUDE_PATH.'system/sys_base_db.php');

/**
* Catalog base module
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class base_catalog extends base_db {

  /**
  * list points (characters)
  * @var string
  */
  var $azListPoints = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ_';

  /**
  * Catalog parent path for all show able elements
  * @var string $catalogParentPath
  */
  var $catalogParentPath = ';0;';

  /**
  * List of displayed catalogs
  * @var array $catalogList
  */
  var $catalogList = NULL;

  /**
  * List of displayed links
  * @var array $linkList
  */
  var $linkList = NULL;

  /**
  * List of displayed link synonyms
  * @var array $linkListSynonyms
  */
  var $linkListSynonyms = NULL;

  /**
  * Output dialog for module
  * @var object base_dialog $catalogDialog
  */
  var $catalogDialog = NULL;

  /**
  * Language selected via left side choice
  * @var integer $lngSelect
  */
  var $lngSelect = NULL;

  /**
  * List of open catalogs
  * @var array $catalogsOpen
  */
  var $catalogsOpen = NULL;

  /**
  * List of topics
  * @var array $topicList
  */
  var $topicList = NULL;

  /**
  * Database table of topics
  * @var string $tableTopics
  */
  var $tableTopics = PAPAYA_DB_TBL_TOPICS;

  /**
  * Database table of topic translation
  * @var string $tableTopicsTrans
  */
  var $tableTopicsTrans = PAPAYA_DB_TBL_TOPICS_TRANS;

  /**
  * Detail data to topic
  * @var array $topicDetail
  */
  var $topicDetail = NULL;

  /**
  * Group of topics under thier language ids
  * @var $topicDetailLngGroups
  */
  var $topicDetailLngGroups = NULL;

  /**
  * Database table for catalog
  * @var string $tableCatalog
  */
  var $tableCatalog = '';

  /**
  * Database table for catalog types
  * @var string $tableCatalogTypes
  */
  var $tableCatalogTypes = '';

  /**
  * Database table for links
  * @var string $tableCatalogLinks
  */
  var $tableCatalogLinks = '';

  /**
  * Database table for translations
  * @var string $tableCatalogTrans
  */
  var $tableCatalogTrans = '';

  /**
  * Catalogs array
  * @var array $catalogs
  */
  var $catalogs = NULL;

  /**
  * Catalog
  * @var array $catalog
  */
  var $catalog = NULL;

  /**
  * Catalog detail
  * @var array $catalogDetail
  */
  var $catalogDetail = NULL;

  /**
  * Catalog details
  * @var array $catalogDetails
  */
  var $catalogDetails = NULL;

  /**
  * Catalog tree
  * @var array $catalogTree
  */
  var $catalogTree = NULL;

  /**
  * Constructor initializes some class variables
  *
  * @param string $paramName optional, default value 'ldb'
  * @access public
  */
  function __construct($paramName = 'catalog') {
    $this->paramName = $paramName;
    $this->sessionParamName = 'PAPAYA_SESS_'.$paramName;
    $this->tableCatalog = PAPAYA_DB_TABLEPREFIX.'_catalog';
    $this->tableCatalogTrans = PAPAYA_DB_TABLEPREFIX.'_catalog_trans';
    $this->tableCatalogLinks = PAPAYA_DB_TABLEPREFIX.'_catalog_links';
    $this->tableCatalogTypes = PAPAYA_DB_TABLEPREFIX.'_catalog_types';
    $this->tableCatalogSynonyms = PAPAYA_DB_TABLEPREFIX.'_catalog_synonyms';
    $this->tableLng = PAPAYA_DB_TABLEPREFIX.'_lng';
  }

  /**
  * Initialisize parameters
  *
  * @access public
  */
  function initialize() {
    $this->initializeParams();
  }

  /**
  * Calculate preview path
  *
  * @param integer $catalogId
  * @access public
  * @return string
  */
  function calcPrevPath($catalogId) {
    if ($catalogId > 0 && isset($this->catalogs[$catalogId])) {
      $catalog = $this->catalogs[$catalogId];
      if (!isset($catalog['newpath'])) {
        if ($catalog['catalog_parent'] > 0) {
          if (isset($this->catalogs[$catalogs['catalog_parent']])) {
            $newPath = $this->calcPrevPath($catalog['catalog_parent']).
              $catalog['catalog_parent'].';';
          } else {
            $newPath = ";0;";
          }
        } else {
          $newPath = ";0;";
        }
        $this->catalogs[$catalogId]['newpath'] = $newPath;
        return $newPath;
      } else {
        return $catalog['newpath'];
      }
    }
    return '';
  }

  // ------------------------       Output       -------------------------

  /**
  * Function which returns the page xml
  * Note: splitted into subfunctions to improve readability
  *
  * @param integer $lngId
  * @param integer $catalogId mixed, default NULL
  * @param string $linkMode optional, default 'catalog'
  * @access public
  * @return string $result XML
  */
  function getOutput($lngId, $catalogId = NULL, $linkMode = 'catalog') {
    // acquire catalog_id
    if (isset($catalogId) && (int)$catalogId > 0) {
      $catalogId = @(int)$catalogId;
    } elseif (isset($GLOBALS['PAPAYA_PAGE']->requestData['categ_id']) &&
              $GLOBALS['PAPAYA_PAGE']->requestData['categ_id'] > 0) {
      $catalogId = $GLOBALS['PAPAYA_PAGE']->requestData['categ_id'];
    } elseif (isset($_GET['catalog']) && $_GET['catalog'] > 0) {
      $catalogId = @(int)$_GET['catalog'];
    } else {
      $catalogId = @(int)$this->module->data['categ'];
    }
    $result = '';
    if ($this->loadCatalog($catalogId, @(int)$this->module->data['categ']) &&
        $this->loadCatalogDetail($catalogId, $lngId, 1)) {
      $result .= '<catalog>'.LF;
      $result .= $this->getXMLTranslations($catalogId, $lngId);
      $result .= $this->getXMLCatalogList($catalogId, $lngId);
      $result .= $this->getXMLLetterList();
      $result .= $this->getXMLCategoryList($catalogId, $lngId);
      $result .= $this->getXMLLinkList($catalogId, $linkMode);
      $result .= '</catalog>'.LF;
    }
    return $result;
  }

  /**
  * Function for translations of the page
  *
  * @param integer $catalogId
  * @param integer $currentLngId
  * @access public
  * @return string $result XML
  */
  function getXMLTranslations($catalogId, $currentLngId) {
    $linkCount = $this->checkCategHasLinks($catalogId);
    $topicObj = &$this->module->parentObj;
    $topicObj->loadTranslationsData($currentLngId);
    $catalogTranslations = $this->loadCatalogTranslations($catalogId);
    $result = '<translations>';
    if (isset($topicObj->topicTranslations) &&
        is_array($topicObj->topicTranslations) &&
        is_array($catalogTranslations) && count($catalogTranslations) > 0) {
      foreach ($topicObj->topicTranslations as $lngId=>$translation) {
        if (isset($linkCount[$catalogId][$lngId]) &&
            $linkCount[$catalogId][$lngId] > 0 &&
            isset($catalogTranslations[$lngId])) {
          $selected = ($translation['lng_id'] == $currentLngId)
            ? ' selected="selected"' : '';
          $href = $this->getWebLink(
            $topicObj->topicId,
            $translation['lng_ident'],
            NULL,
            NULL,
            NULL,
            $catalogTranslations[$lngId],
            $catalogId
          );
          $result .= sprintf(
            '<translation lng_short="%s" lng_title="%s" href="%s" entries="%d" %s>%s</translation>',
            papaya_strings::escapeHTMLChars($translation['lng_short']),
            papaya_strings::escapeHTMLChars($translation['lng_title']),
            papaya_strings::escapeHTMLChars($href),
            (int)$linkCount[$catalogId][$lngId],
            $selected,
            papaya_strings::escapeHTMLChars($catalogTranslations[$lngId])
          );
        }
      }
    }
    $result .= '</translations>';
    return $result;
  }

  /**
  * Function wich gets the catalog list, categories
  *
  * @param integer $catalogId
  * @param integer $lngId
  * @access public
  * @return string $result XML
  */
  function getXMLCatalogList($catalogId, $lngId) {
    $result = '';
    $offset = (@$this->module->data['catalog_offset'] > 0)
      ? (int)$this->module->data['catalog_offset'] : 1;
    $levels = (@$this->module->data['catalog_levels'] > 0)
      ? (int)$this->module->data['catalog_levels'] : 1;
    if ($catalogId == @(int)$this->module->data['categ'] &&
        ($offset + $levels > 2)) {
      //load subcategories of specified offset and level count
      $rootPath = ($catalogId > 0)
        ? $this->catalog['catalog_parent_path'].$this->catalog['catalog_id'].';' : ';0;';
      $this->loadCatalogTree($lngId, $rootPath, $offset, $levels);
    } else {
      //load direct subcategories
      $this->loadCatalogList($catalogId, $lngId);
      $catalogLinkCategs = array($catalogId => TRUE);
      if (isset($this->catalogList) && is_array($this->catalogList)) {
        foreach ($this->catalogList as $category) {
          if ($category['catalogtype_loadlinks']) {
            $catalogLinkCategs[(int)$category['catalog_id']] = TRUE;
          }
        }
      }
      $this->loadCatalogLinkList(array_keys($catalogLinkCategs), $lngId, 0, FALSE);
      $this->loadLinkListSynonyms($catalogId, $lngId);
      $result .= sprintf(
        '<category title="%s" type="%s">'.LF,
        papaya_strings::escapeHtmlChars($this->catalogDetail['catalog_title']),
        papaya_strings::escapeHtmlChars($this->catalogDetail['catalogtype_name'])
      );
      $result .= sprintf(
        '<glyph>%s</glyph>'.LF,
        $this->module->getXHTMLString($this->catalogDetail['catalog_glyph'])
      );
      $image = sprintf(
        '<papaya:media src="%s" width="%d" height="%d" resize="%s"/>',
        papaya_strings::escapeHtmlChars($this->catalogDetail['catalog_image']),
        @(int)$this->module->data['cat_img_width'],
        @(int)$this->module->data['cat_img_height'],
        @(string)$this->module->data['cat_img_resize']
      );
      $result .= sprintf('<image>%s</image>'.LF, $image);
      $result .= sprintf(
        '<text>%s</text>'.LF,
        $this->module->getXHTMLString($this->catalogDetail['catalog_text'])
      );
      $result .= '</category>'.LF;
    }
    return $result;
  }

  /**
  * Function checks if the category has links
  *
  * @param $catalogLinkCategs
  * @param integer $lngId optional, default value 0
  * @access public
  * @return array $result
  */
  function checkCategHasLinks($catalogLinkCategs, $lngId = 0) {
    $result = array();
    $filter = $this->databaseGetSQLCondition('c.catalog_id', $catalogLinkCategs);
    $concat = $this->databaseGetSQLSource(
      'CONCAT',
      'c.catalog_parent_path',
      FALSE,
      'c.catalog_id',
      FALSE,
      ';%%'
    );
    $lngFilter = ($lngId > 0) ? ' AND cl.lng_id = '.(int)$lngId : '';
    $sql = "SELECT c.catalog_id, cl.lng_id, count(*) as counted
              FROM %s AS c, %s AS c2, %s AS cl, %s AS tt
             WHERE $filter
               AND (c2.catalog_id = c.catalog_id OR
                   c2.catalog_parent = c.catalog_id OR
                   c2.catalog_parent_path LIKE $concat)
               AND cl.catalog_id = c2.catalog_id
               AND tt.topic_id = cl.topic_id
               AND tt.lng_id = cl.lng_id
               $lngFilter
             GROUP BY c.catalog_id, cl.lng_id";
    $params = array(
      $this->tableCatalog,
      $this->tableCatalog,
      $this->tableCatalogLinks,
      $this->tableTopicsTrans
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['counted'] > 0) {
          $result[$row['catalog_id']][$row['lng_id']] = $row['counted'];
        }
      }
    }
    return $result;
  }

  /**
  * Function get a list of letters from a to z
  *
  * @access public
  * @return string $result XML
  */
  function getXMLLetterList() {
    $result = '';
    $result .= '<letters>';
    for ($i = 0; $i < strlen($this->azListPoints); $i++) {
      $letter = $this->azListPoints{$i};
      $result .= sprintf('<letter title="%s" />'.LF, $letter);
    }
    $result .= '</letters>';
    return $result;
  }

  /**
  * Function gets XML list of categories
  *
  * @param integer $catalogId
  * @param integer $lngId
  * @access public
  * @return string $result XML
  */
  function getXMLCategoryList($catalogId, $lngId) {
    $result = '';
    if (isset($this->catalogList) && is_array($this->catalogList)) {
      $result .= '<categories>'.LF;
      $categories = $this->getCategoriesWithSynonyms();
      foreach ($categories as $category) {
        $categoryKeys[] = $category['catalog_id'];
      }
      $linkCount = $this->checkCategHasLinks(array_unique($categoryKeys), $lngId);
      foreach ($categories as $category) {
        if (isset($linkCount[$category['catalog_id']][$lngId])) {
          $result .= sprintf(
            '<category title="%s" type="%s" chargroup="%s" href="%s" id="%d" parent="%s">'.LF,
            papaya_strings::escapeHtmlChars($category['catalog_title']),
            papaya_strings::escapeHtmlChars($category['catalogtype_name']),
            papaya_strings::escapeHtmlChars(
              $this->getFirstLetterNormalized($category['catalog_title'])
            ),
            papaya_strings::escapeHtmlChars(
              $this->getWebLink(
                NULL,
                '',
                NULL,
                NULL,
                NULL,
                $category['catalog_title'],
                $category['catalog_id']
              )
            ),
            (int)$category['catalog_id'],
            papaya_strings::escapeHtmlChars($category['catalog_parent'])
          );
          $result .= sprintf(
            '<glyph>%s</glyph>',
            $this->module->getXHTMLString($category['catalog_glyph'])
          );
          $image = sprintf(
            '<papaya:media src="%s" width="%d" height="%d" resize="%s"/>',
            papaya_strings::escapeHtmlChars(@$category['catalog_image']),
            @(int)$this->module->data['cat_img_width'],
            @(int)$this->module->data['cat_img_height'],
            papaya_strings::escapeHtmlChars(@(string)$this->module->data['cat_img_resize'])
          );
          $result .= sprintf('<image>%s</image>', $image);
          $result .= sprintf(
            '<teaser>%s</teaser>'.LF,
            $this->module->getXHTMLString((string)@$category['catalog_text'])
          );
          $result .= $this->getCategorySynonyms($category);
          if (isset($this->linkList[$category['catalog_id']]) &&
              is_array($this->linkList[$category['catalog_id']])) {
            $result .= '<links>'.LF;
            foreach ($this->linkList[$category['catalog_id']] as $link) {
              $result .= sprintf(
                '<link href="%s" title="%s" chargroup="%s" module="%s" guid="%s" />'.LF,
                papaya_strings::escapeHtmlChars(
                  $this->getWebLink(
                    $link['topic_id'],
                    '',
                    NULL,
                    NULL,
                    NULL,
                    $link['cataloglink_title'],
                    NULL
                  )
                ),
                papaya_strings::escapeHtmlChars($link['cataloglink_title']),
                papaya_strings::escapeHtmlChars(
                  $this->getFirstLetterNormalized($link['cataloglink_title'])
                ),
                papaya_strings::escapeHtmlChars(
                  @(string)$moduleInfos[$link['topic_id']]['module_class']
                ),
                papaya_strings::escapeHtmlChars(
                  @(string)$moduleInfos[$link['topic_id']]['module_guid']
                )
              );
            }
            $result .= '</links>'.LF;
          }
          $result .= '</category>'.LF;
        }
      }
      $result .= '</categories>'.LF;
    }
    return $result;
  }

  /**
  * get merge of categories with synoyms
  *
  * uses $this->catalogList and $this->catalogSynonyms to
  * generate $this->categoriesWithSynonyms
  *
  * @access public
  * @return string array
  */
  function &getCategoriesWithSynonyms() {
    unset($this->categoriesWithSynonyms);
    $this->categoriesWithSynonyms = $this->catalogList;
    if (isset($this->catalogSynonyms)) {
      foreach ($this->catalogSynonyms as $catalogId => $synonyms) {
        foreach ($synonyms as $synId => $synonym) {
          $synCatId = sprintf('%d-%d', $catalogId, $synId);
          $this->categoriesWithSynonyms[$synCatId] = $synonym;
        }
      }
      usort(
        $this->categoriesWithSynonyms,
        array('base_catalog', 'callbackSortByTitle')
      );
    }
    return $this->categoriesWithSynonyms;
  }


  /**
  * Get a XML list of links
  *
  * @param integer $catalogId
  * @param string $linkMode optional, default 'catalog'
  * @access public
  * @return string $result XML
  */
  function getXMLLinkList($catalogId, $linkMode = 'catalog') {
    $result = '';
    if (isset($this->linkList[$catalogId]) && is_array($this->linkList[$catalogId])) {
      $this->loadCatalogTeaserList($catalogId);
      //$filterIds is the list of all topics linked with the catalog category
      //specified by $catalogId.
      foreach ($this->linkList[$catalogId] as $link) {
        $filterIds[@(int)$link['topic_id']] = TRUE;
      }
      if ($linkMode == 'topic') {
        $linksByLanguage = array();
        foreach ($this->linkList[$catalogId] as $link) {
          if (!isset($linksByLanguage[$link['lng_id']])) {
            $linksByLanguage[$link['lng_id']] = array();
          }
          $linksByLanguage[$link['lng_id']][] = $link['topic_id'];
        }
        $titles = array();
        foreach ($linksByLanguage as $language => $links) {
          $titles[$language] = $this->getTopicTitles($language, 0, $links);
        }
      }
      $moduleInfos = $this->getModuleInformation(
        array_keys($filterIds), $this->module->parentObj
      );
      $result .= '<links>'.LF;
      foreach ($this->linkList[$catalogId] as $link) {
        if (isset($this->topicTeaser[$link['topic_id']][$link['lng_id']])) {
          $teaser = $this->topicTeaser[$link['topic_id']][$link['lng_id']];
        } else {
          $teaser = '';
        }
        if ($linkMode == 'topic' && isset($titles[$link['lng_id']][$link['topic_id']])) {
          $title = $titles[$link['lng_id']][$link['topic_id']];
        } else {
          $title = $link['cataloglink_title'];
        }
        $result .= sprintf(
          '<link href="%s" title="%s" chargroup="%s" module="%s" guid="%s">'.LF,
          papaya_strings::escapeHtmlChars(
            $this->getWebLink(
              $link['topic_id'],
              '',
              NULL,
              NULL,
              NULL,
              $title,
              NULL
            )
          ),
          papaya_strings::escapeHtmlChars($link['cataloglink_title']),
          papaya_strings::escapeHtmlChars(
            $this->getFirstLetterNormalized($link['cataloglink_title'])
          ),
          papaya_strings::escapeHtmlChars(
           $moduleInfos[$link['topic_id']]['module_class']
          ),
          papaya_strings::escapeHtmlChars($moduleInfos[$link['topic_id']]['module_guid'])
        );
        $result .= sprintf(
          '<teaser>%s</teaser>'.LF,
          $this->getXHTMLString(
            $this->topicTeaser[$link['topic_id']][$link['lng_id']]
          )
        );
        if (isset($this->linkListSynonyms[$link['topic_id']]) &&
            is_array($this->linkListSynonyms[$link['topic_id']])) {
          foreach ($this->linkListSynonyms[$link['topic_id']] as $synonym) {
            if ($synonym['cataloglink_title'] != $link['cataloglink_title']) {
              $result .= sprintf(
                '<synonym title="%s" />'.LF,
                papaya_strings::escapeHtmlChars($synonym['cataloglink_title'])
              );
            }
          }
        }
        $result .= '</link>'.LF;
      }
      $result .= '</links>'.LF;
      $result .= $this->getImageResizedTeaser(
        $result,
        @$this->module->data['img_width'],
        @$this->module->data['img_height'],
        @$this->module->data['img_resize']
      );
    }
    return $result;
  }

  /**
  * Get a resized image of the teaser image
  *
  * @param string $xml
  * @param integer $width
  * @param integer $height
  * @param string $resize
  * @access public
  * @return string $result XML
  */
  function getImageResizedTeaser($xml, $width, $height, $resize) {
    $result = '';
    if (trim($xml) != '') {
      include_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');
      if ($subTopicTree = &simple_xmltree::createFromXML($xml, $this)) {
        for ($idx1 = 0; $idx1 < $subTopicTree->documentElement->childNodes->length; $idx1++) {
          $linkNode = &$subTopicTree->documentElement->childNodes->item($idx1);
          if ($linkNode->nodeType == XML_ELEMENT_NODE && $linkNode->nodeName == 'link') {
            for ($idx2 = 0; $idx2 < $linkNode->childNodes->length; $idx2++) {
              $teaserNode = $linkNode->childNodes->item($idx2);
              if ($teaserNode->nodeType == XML_ELEMENT_NODE && $teaserNode->nodeName == 'teaser') {
                for ($idx3 = 0; $idx3 < $teaserNode->childNodes->length; $idx3++) {
                  $subTopicNode = &$teaserNode->childNodes->item($idx3);
                  if ($subTopicNode->nodeType == XML_ELEMENT_NODE &&
                      $subTopicNode->nodeName == 'subtopic' &&
                      $subTopicNode->hasChildNodes()) {
                    for ($idx4 = 0; $idx4 < $subTopicNode->childNodes->length; $idx4++) {
                      $imageNode = &$subTopicNode->childNodes->item($idx4);
                      if ($imageNode->nodeType == XML_ELEMENT_NODE &&
                          $imageNode->nodeName == 'image' &&
                          $imageNode->hasChildNodes()) {
                        for ($idx5 = 0; $idx5 < $imageNode->childNodes->length; $idx5++) {
                          $papayaNode = &$imageNode->childNodes->item($idx5);
                          if ($papayaNode->nodeType == XML_ELEMENT_NODE &&
                              in_array($papayaNode->nodeName, array('papaya:media', 'media'))) {
                            $thumbs[@(int)$subTopicNode->getAttribute('no')] = sprintf(
                              '<papaya:media src="%s" width="%d" height="%d" resize="%s" />',
                              papaya_strings::escapeHtmlChars(
                                $papayaNode->getAttribute('src')
                              ),
                              (int)$width,
                              (int)$height,
                              papaya_strings::escapeHtmlChars($resize)
                            );
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
        simple_xmltree::destroy($subTopicTree);
        if (isset($thumbs) && is_array($thumbs)) {
          $result .= '<subtopicthumbs>';
          foreach ($thumbs as $no=>$thumb) {
            $result .= '<thumb topic="'.(int)$no.'">'.$thumb.'</thumb>';
          }
          $result .= '</subtopicthumbs>';
        }
      }
    }
    return $result;
  }

  /**
  * get list of synonyms for a category
  *
  * @param array $category a category
  * @access public
  * @return string $result XML string of synonyms
  */
  function getCategorySynonyms($category) {
    $catalogId = $category['catalog_id'];
    $result = '';
    if (isset($category) &&
        isset($category['catalogsysnonym_id']) &&
        isset($this->catalogSynonyms[$catalogId])) {
      $synonyms = $this->catalogSynonyms[$catalogId];
      if (isset($synonyms) && is_array($synonyms) && count($synonyms) > 0) {
        foreach ($synonyms as $synId => $synonym) {
          if ($category['catalogsynonym_id'] != $synId) {
            $synonymList[$synId] = $synonym['catalog_title'];
          }
        }
        if (isset($category['catalogsynonym_id']) &&
            $category['catalogsynonym_id'] != '') {
          $synonymList[] = $this->catalogList[$category['catalog_id']]['catalog_title'];
        }
        usort($synonymList, array('base_catalog', 'callbackSortByTitle'));
        $result .= '<synonyms>';
        foreach ($synonymList as $id => $title) {
          $result .= sprintf(
            '<synonym>%s</synonym>',
            papaya_strings::escapeHtmlChars($title)
          );
        }
        $result .= '</synonyms>';
      }
    }
    return $result;
  }

  /**
  * Load Catalog
  *
  * @param integer $id catalog_id
  * @param integer $baseId optional, default value 0
  * @access public
  * @return boolean
  */
  function loadCatalog($id, $baseId = 0) {
    unset($this->catalog);
    $sql = "SELECT catalog_id, catalog_parent_path, catalog_parent
              FROM %s
             WHERE catalog_id = '%d'";
    $params = array($this->tableCatalog, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($baseId == 0 || $id == $baseId
          || (strpos($row['catalog_parent_path'], ';'.$baseId.';') !== FALSE)) {
            $this->catalog = $row;
            return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Load catalog details
  *
  * @param integer $id
  * @param integer $lngId
  * @param integer $catMustSet optional, default value 0
  * @access public
  * @return boolean
  */
  function loadCatalogDetail($id, $lngId, $catMustSet = 0) {
    unset($this->catalogDetail);
    if (isset($this->catalog) || $catMustSet == 0) {
      $sql = "SELECT ct.lng_id, ct.catalog_id, ct.catalog_title,
                     ct.catalog_glyph, ct.catalog_image, ct.catalog_text,
                     t.catalogtype_id, t.catalogtype_name
                FROM (%s AS c INNER JOIN %s AS ct USING(catalog_id))
                LEFT OUTER JOIN %s AS t ON (t.catalogtype_id = c.catalogtype_id)
               WHERE c.catalog_id = '%d'
                 AND ct.lng_id = '%d'";
      $params = array($this->tableCatalog,
                      $this->tableCatalogTrans,
                      $this->tableCatalogTypes,
                      $id,
                      $lngId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->catalogDetail = $row;
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
  * Load translations of catalog
  *
  * @param integer $catalogId
  * @access public
  * @return array $result
  */
  function loadCatalogTranslations($catalogId) {
    $result = array();
    $sql = "SELECT ct.lng_id, ct.catalog_title
              FROM %s AS c, %s AS ct
             WHERE c.catalog_id = '%d'
               AND ct.catalog_id = c.catalog_id";
    $params = array($this->tableCatalog, $this->tableCatalogTrans, $catalogId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['lng_id']] = $row['catalog_title'];
      }
    }
    return $result;
  }

  /**
  * Load catalog list - also loads synoyms
  *
  * @param integer $id
  * @param integer $lngId
  * @access public
  */
  function loadCatalogList($id, $lngId) {
    $this->loadCatalogSynonyms($lngId, $id);
    unset($this->catalogList);
    $sql = "SELECT c.catalog_id, c.catalog_parent, ct.catalog_text,
                   ct.catalog_title, ct.catalog_glyph, ct.catalog_image,
                   t.catalogtype_name, t.catalogtype_loadlinks, t.catalogtype_loadteaser
              FROM (%s AS c INNER JOIN %s AS ct USING(catalog_id))
              LEFT OUTER JOIN %s AS t ON (t.catalogtype_id = c.catalogtype_id)
             WHERE c.catalog_parent = '%d'
               AND ct.lng_id = '%d'
             ORDER BY ct.catalog_title, c.catalog_id";
    $params = array($this->tableCatalog, $this->tableCatalogTrans,
      $this->tableCatalogTypes, $id, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogList[$row['catalog_id']] = $row;
      }
    }
  }

  /**
  * Load catalog list(?) - also loads synoyms
  *
  * @param integer $lngId
  * @param string $rootPath
  * @param integer $offset
  * @param integer $levels
  * @access public
  */
  function loadCatalogTree($lngId, $rootPath, $offset, $levels) {
    $this->loadCatalogSynonyms($lngId);
    if (preg_match_all('~\d+~', $rootPath, $regs, PREG_PATTERN_ORDER)) {
      $startLevel = count($regs[0]) + $offset - 1;
      $endLevel = $startLevel + $levels;
    }
    $sql = "SELECT c.catalog_id, c.catalog_parent, c.catalog_parent_path,
                   ct.catalog_title, ct.catalog_glyph,
                   t.catalogtype_name, t.catalogtype_loadlinks, t.catalogtype_loadteaser
              FROM (%s AS c INNER JOIN %s AS ct USING(catalog_id))
              LEFT OUTER JOIN %s AS t ON (t.catalogtype_id = c.catalogtype_id)
             WHERE c.catalog_parent_path LIKE '$rootPath%%'
               AND ct.lng_id = '%d'
             ORDER BY ct.catalog_title, c.catalog_id";
    $params = array($this->tableCatalog,
                    $this->tableCatalogTrans,
                    $this->tableCatalogTypes,
                    $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if (preg_match_all('~\d+~', $row['catalog_parent_path'], $regs, PREG_PATTERN_ORDER)) {
          $currentLevel = count($regs[0]);
          if ($currentLevel >= $startLevel && $currentLevel < $endLevel) {
            $this->catalogList[$row['catalog_id']] = $row;
          }
        }
      }
    }
  }

  /**
  * loads catalog synonyms by lngId and (optional) parentId; fills $this->catalogSynonyms
  *
  * @param integer $lngId lanugage ID optional, default value = NULL
  * @param integer $parentId parent ID
  * @access public
  */
  function loadCatalogSynonyms($lngId, $parentId = NULL) {
    $filter = ($parentId !== NULL && $parentId != '') ?
      sprintf(" AND c.catalog_parent = '%d' ", $parentId) : '';
    $sql = "SELECT cs.catalogsynonym_title AS catalog_title,
                   cs.catalog_id, cs.catalogsynonym_id,
                   c.catalog_parent, c.catalog_parent_path, ct.catalog_glyph,
                   t.catalogtype_name, t.catalogtype_loadlinks, t.catalogtype_loadteaser
              FROM %s AS cs
              LEFT OUTER JOIN %s AS c ON (c.catalog_id = cs.catalog_id)
              LEFT OUTER JOIN %s AS ct ON (ct.catalog_id = cs.catalog_id)
              LEFT OUTER JOIN %s AS t ON (t.catalogtype_id = c.catalogtype_id)
             WHERE cs.lng_id = '%d' $filter
          ORDER BY catalogsynonym_title ASC";
    $params = array(
      $this->tableCatalogSynonyms,
      $this->tableCatalog,
      $this->tableCatalogTrans,
      $this->tableCatalogTypes,
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->catalogSynonyms[$row['catalog_id']][$row['catalogsynonym_id']] = $row;
      }
    }
  }

  /**
  * orders arrays by arr['catalog_title']
  *
  * @param array $a
  * @param array $b
  * @access public
  * @return integer $result
  */
  function callbackSortByTitle($a, $b) {
    if ($a['catalog_title'] == $b['catalog_title']) {
      $result = 0;
    } elseif ($a['catalog_title'] < $b['catalog_title']) {
      $result = -1;
    } else {
      $result = 1;
    }
    return $result;
  }

  /**
  * Load link list synonyms
  *
  * @param integer $catalogId
  * @param integer $lngId
  * @access public
  */
  function loadLinkListSynonyms($catalogId, $lngId) {
    unset($this->linkListSynonyms);
    if (isset($this->linkList[$catalogId]) && is_array($this->linkList[$catalogId])) {
      foreach ($this->linkList[$catalogId] as $link) {
        $filterIds[@(int)$link['topic_id']] = TRUE;
      }
      $filter = '('.implode(',', array_keys($filterIds)).')';
      $sql = "SELECT topic_id, cataloglink_id, cataloglink_title, lng_id
                FROM %s
               WHERE topic_id IN $filter
                 AND catalog_id = '%d'
                 AND lng_id = '%d'
               ORDER BY cataloglink_title";
      $params = array($this->tableCatalogLinks, $catalogId, $lngId);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $this->linkListSynonyms[$row['topic_id']][$row['cataloglink_id']] = $row;
        }
      }
    }
  }

  /**
  * Load related catalog list
  *
  * @param integer $topicId
  * @param integer $lngId
  * @param integer $baseCatalogId
  * @access public
  */
  function loadRelatedCatalogList($topicId, $lngId, $baseCatalogId) {
    unset($this->catalogList);
    $sql = "SELECT ct.catalog_id, ct.catalog_title, ct.catalog_glyph ,
                   c.catalog_parent_path, c.catalog_parent
              FROM %s AS cl, %s AS ct, %s c
             WHERE cl.topic_id = '%d'
               AND cl.lng_id = '%d'
               AND c.catalog_id = cl.catalog_id
               AND cl.catalog_id = ct.catalog_id
               AND ct.lng_id = cl.lng_id
             ORDER BY ct.catalog_title";
    $params = array(
      $this->tableCatalogLinks,
      $this->tableCatalogTrans,
      $this->tableCatalog,
      $topicId,
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($baseCatalogId != $row['catalog_id']) {
          $this->catalogList[$row['catalog_id']] = $row;
        } else {
          $baseIdParentPath = $row['catalog_parent'];
        }
      }
    }
  }

  /**
  * Load catalog details per ids
  *
  * @param array &$list
  * @param integer $lngId
  * @access public
  */
  function loadCatalogDetailPerIds(&$list, $lngId) {
    if (is_array($list)) {
      $tmp = array_keys($list);
      $filter = implode(",", $tmp);
      $sql = "SELECT ct.catalog_id, ct.catalog_title, ct.catalog_glyph, cy.catalogtype_id
                FROM %s AS ct
                LEFT OUTER JOIN %s AS c ON (ct.catalog_id = c.catalog_id)
                LEFT OUTER JOIN %s AS cy ON (c.catalogtype_id = cy.catalogtype_id)
               WHERE ct.lng_id='%d'
                 AND ct.catalog_id IN ($filter)
               ORDER BY catalog_title";
      $params = array(
        $this->tableCatalogTrans,
        $this->tableCatalog,
        $this->tableCatalogTypes,
        $lngId
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $list[$row['catalog_id']] = $row;
        }
      } else {
        $list = array();
      }
    }
  }

  //  ---------------------------------------- azlist ----------------------------

  /**
  * load catalog on parent path
  *
  * @param integer $lngId
  * @access public
  */
  function loadCatalogsOnPrevPath($lngId) {
    unset($this->catalogList);
    $sql = "SELECT DISTINCT tt.topic_id, l.cataloglink_title
              FROM %s c, %s l, %s tt
             WHERE l.catalog_id = c.catalog_id
               AND l.lng_id='%d'
               AND c.catalog_parent_path LIKE('%s%%')
               AND tt.topic_id = l.topic_id
               AND tt.lng_id = l.lng_id
             ORDER BY l.cataloglink_sort, l.cataloglink_title";
    $params = array(
      $this->tableCatalog,
      $this->tableCatalogLinks,
      $this->tableTopicsTrans,
      $lngId,
      $this->catalog['catalog_parent_path'].$this->catalog['catalog_id'].';'
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $letter = $this->getFirstLetterNormalized($row['cataloglink_title']);
        if (FALSE !== strpos($this->azListPoints, $letter)) {
          $this->catalogList[$letter][] = $row;
          $this->catalogSynonymList[$row['topic_id']][] = $row;
        } else {
          $this->catalogList['_'][] = $row;
        }
      }
    }
  }

  /**
  * normalized first letter to latin
  *
  * @param string $str
  * @todo pruefen ob das so richtig ist an Thomas
  * @access public
  * @return string $letter
  */
  function getFirstLetterNormalized($str) {
    $letter = papaya_strings::substr($str, 0, 1);
    $letter = papaya_strings::normalizeString($letter);
    $letter = substr($letter, 0, 1);
    $letter = strtoupper($letter);
    return $letter;
  }

  /**
  * Get az list output
  *
  * @param integer $categBaseId
  * @param integer $lngId
  * @access public
  * @return string XML
  */
  function getAzListOutput($categBaseId, $lngId) {
    $result = '';
    $this->loadCatalog((int)@$categBaseId);
    $this->loadCatalogSynonyms($lngId, (int)@$categBaseId);
    if (isset($this->catalog) && is_array($this->catalog)) {
      $this->loadCatalogsOnPrevPath($lngId);
    }
    if (isset($this->catalogList) && is_array($this->catalogList)) {
      $result = '<azlist>'.LF;
      $topicIds = array_keys(@$this->catalogSynonymList);
      $moduleInfos = $this->getModuleInformation($topicIds, $this->module->parentObj);
      for ($i = 0; $i < strlen($this->azListPoints); $i++) {
        $letter = $this->azListPoints{$i};
        if (isset($this->catalogList[$letter]) && is_array($this->catalogList[$letter])) {
          $result .= sprintf(
            '<letter title="%s" href="%s" >'.LF,
            papaya_strings::escapeHtmlChars($letter),
            papaya_strings::escapeHtmlChars('#'.$letter)
          );
          foreach ($this->catalogList[$letter] as $catalog) {
            $result .= sprintf(
              '<link title="%s" href="%s" module="%s" guid="%s">'.LF,
              papaya_strings::escapeHtmlChars($catalog['cataloglink_title']),
              papaya_strings::escapeHtmlChars(
                $this->getWebLink(
                  $catalog['topic_id'],
                  NULL,
                  NULL,
                  NULL,
                  NULL,
                  $catalog['cataloglink_title']
                )
              ),
              papaya_strings::escapeHtmlChars(
                @$moduleInfos[$catalog['topic_id']]['module_class']
              ),
              papaya_strings::escapeHtmlChars(
                @$moduleInfos[$catalog['topic_id']]['module_guid']
              )
            );
            if (isset($this->catalogSynonymList[$catalog['topic_id']]) &&
                is_array($this->catalogSynonymList[$catalog['topic_id']])) {
              foreach ($this->catalogSynonymList[$catalog['topic_id']] as $synonym) {
                if ($synonym['cataloglink_title'] != $catalog['cataloglink_title']) {
                  $result .= sprintf(
                    '<synonym title="%s"/>'.LF,
                    papaya_strings::escapeHtmlChars($synonym['cataloglink_title'])
                  );
                }
              }
            }
            $result .= '</link>'.LF;
          }
          $result .= '</letter>'.LF;
        } else {
          $result .= sprintf(
            '<letter title="%s" />'.LF,
            papaya_strings::escapeHtmlChars($letter)
          );
        }
      }
      $result .= '</azlist>'.LF;
    }
    return $result;
  }

  /**
  * Get information of topic module (topics)
  *
  * @param array $topicIds
  * @param object base_topic &$topicObj topic object
  * @access public
  * @return array $result
  */
  function getModuleInformation($topicIds, &$topicObj) {
    $result = array();
    $filter = $this->databaseGetSQLCondition('tt.topic_id', $topicIds);
    $sql = "SELECT tt.topic_id, v.module_guid, m.module_class
              FROM %s AS v, %s AS tt, %s AS m
             WHERE $filter
               AND tt.lng_id = '%d'
               AND tt.view_id = v.view_id
               AND v.module_guid = m.module_guid";
    $params = array(
      $topicObj->tableViews,
      $topicObj->tableTopicsTrans,
      $topicObj->tableModules,
      $topicObj->getContentLanguageId()
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $result[$row['topic_id']] = $row;
      }
    }
    return $result;
  }
  
  /**
  * Load catalog link list with topic link type id
  *
  * @param mixed $catalogIds - array or single category id
  * @param integer $lngId
  * @param integer $max optional, default value = 0
  * @param boolean $unique optional, default value TRUE
  * @access public
  */
  function loadCatalogLinkListWithDetails($catalogIds, $lngId, $max = 0, $unique = TRUE) {
    if (is_array($catalogIds)) {
      foreach ($catalogIds as $catalogId) {
        unset($this->linkList[$catalogId]);
      }
    }
    $filter = $this->databaseGetSQLCondition('l.catalog_id', $catalogIds);
    $maxRecords = ($max > 0) ? (int)$max : NULL;
    $sql = "SELECT l.topic_id, l.cataloglink_id, l.cataloglink_title,
                   l.catalog_id, l.lng_id, t.linktype_id
              FROM %s AS l, %s AS tt, %s AS t
             WHERE $filter
               AND l.lng_id = '%d'
               AND tt.topic_id = l.topic_id
               AND tt.lng_id = l.lng_id
               AND t.topic_id = l.topic_id
             ORDER BY l.cataloglink_sort, l.cataloglink_title";
    $params = array(
      $this->tableCatalogLinks, 
      $this->tableTopicsTrans, 
      $this->tableTopics, 
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params, $maxRecords)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($unique) {
          if (!isset($this->linkList[$row['catalog_id']][$row['topic_id']])) {
            $this->linkList[$row['catalog_id']][$row['topic_id']] = $row;
          }
        } else {
          $this->linkList[$row['catalog_id']][] = $row;
        }
      }
    }
  }

  //-------------------------------- Limited Content ------------------------------------
  /**
  * load a list of links
  *
  * @param mixed $catalogIds - array or single category id
  * @param integer $lngId
  * @param integer $max optional, default value = 0
  * @param boolean $unique optional, default value TRUE
  * @access public
  */
  function loadCatalogLinkList($catalogIds, $lngId, $max = 0, $unique = TRUE) {
    if (is_array($catalogIds)) {
      foreach ($catalogIds as $catalogId) {
        unset($this->linkList[$catalogId]);
      }
    }
    $filter = $this->databaseGetSQLCondition('l.catalog_id', $catalogIds);
    $maxRecords = ($max > 0) ? (int)$max : NULL;
    $sql = "SELECT l.topic_id, l.cataloglink_id, l.cataloglink_title,
                   l.catalog_id, l.lng_id
              FROM %s AS l, %s AS tt
             WHERE $filter
               AND l.lng_id = '%d'
               AND tt.topic_id = l.topic_id
               AND tt.lng_id = l.lng_id
             ORDER BY l.cataloglink_sort, l.cataloglink_title";
    $params = array($this->tableCatalogLinks, $this->tableTopicsTrans, $lngId);
    if ($res = $this->databaseQueryFmt($sql, $params, $maxRecords)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($unique) {
          if (!isset($this->linkList[$row['catalog_id']][$row['topic_id']])) {
            $this->linkList[$row['catalog_id']][$row['topic_id']] = $row;
          }
        } else {
          $this->linkList[$row['catalog_id']][] = $row;
        }
      }
    }
  }

  /**
  * load a list of teasers
  *
  * @param integer $catalogId
  * @access public
  */
  function loadCatalogTeaserList($catalogId) {
    if (isset($catalogId)) {
      if (is_array($catalogId) && count($catalogId) > 0) {
        if (count($catalogId) == 1) {
          $filter = sprintf(" c.catalog_id = %d ", $catalogId[0]);
        } else {
          $filter = sprintf(" c.catalog_id IN (%s) ", implode(',', $catalogId));
        }
      } else {
        $filter = sprintf(" c.catalog_id = %d ", $catalogId);
      }
      $sql = "SELECT tt.topic_id, tt.lng_id
                FROM %s AS c
                LEFT OUTER JOIN %s as cl ON (c.catalog_id = cl.catalog_id)
                LEFT OUTER JOIN %s as tt ON (cl.topic_id = tt.topic_id)
               WHERE %s";
      $params = array(
        $this->tableCatalog,
        $this->tableCatalogLinks,
        $this->tableTopicsTrans,
        $filter
      );
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        $className = get_class($this->module->parentObj);
        $topicObj = new $className();
        while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          $topics[$row['topic_id']][$row['lng_id']] = TRUE;
        }
        if (isset($topics) && is_array($topics) && count($topics) > 0) {
          foreach ($topics as $topicId => $lngs) {
            foreach ($lngs as $lngId => $null) {
              if (!isset($this->topicTeaser[$topicId][$lngId])) {
                if ($topicObj->loadOutput($topicId, $lngId)) {
                  $this->topicTeaser[$topicId][$lngId] = $topicObj->parseContent(FALSE);
                }
              }
            }
          }
        }
      }
    }
  }

  /**
  * Checks the url filename for the given topic and catalog id.
  *
  * @param $currentFileName
  * @param $outputMode
  * @param $lngId
  * @return mixed
  */
  function checkURLFileName($currentFileName, $outputMode, $lngId) {
    if (isset($GLOBALS['PAPAYA_PAGE']->requestData['categ_id']) &&
        $GLOBALS['PAPAYA_PAGE']->requestData['categ_id'] > 0) {
      $catalogId = $GLOBALS['PAPAYA_PAGE']->requestData['categ_id'];
    } elseif (isset($_GET['catalog']) && $_GET['catalog'] > 0) {
      $catalogId = (int)$_GET['catalog'];
    } else {
      $catalogId = (int)$this->module->data['categ'];
    }
    $sql = "SELECT c.catalog_title, l.lng_ident
              FROM %s as c
              JOIN %s as l ON l.lng_id = c.lng_id
             WHERE catalog_id = '%d'
               AND c.lng_id = '%d'";
    $params = array(
      $this->tableCatalogTrans,
      $this->tableLng,
      $catalogId,
      $lngId
    );
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $pageFileName = $this->escapeForFilename(
          $row['catalog_title'],
          'index',
          $row['lng_ident']
        );
        if ($pageFileName != $currentFileName) {
          $url = $this->getWebLink(
            NULL,
            NULL,
            $outputMode,
            NULL,
            NULL,
            $row['catalog_title'],
            $catalogId
          );
          $queryString = (isset($_SERVER['QUERY_STRING'])) ? $_SERVER['QUERY_STRING'] : '';
          return $this->getAbsoluteURL($url).$this->recodeQueryString($queryString);
        } else {
          return FALSE;
        }
      }
    }
    return '';
  }

  /**
  * Get a list of real topic titles using the PagesConnector module
  *
  * @param integer $languageId
  * @param integer $categId optional, default 0
  * @param array $topicIds optional, default NULL
  * @return array
  */
  public function getTopicTitles($languageId, $categId = 0, $topicIds = NULL) {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_pluginloader.php');
    $pagesConnector = base_pluginloader::getPluginInstance(
      '69db080d0bb7ce20b52b04e7192a60bf', $this);
    if ($topicIds === NULL) {
      if (!isset($this->linkList[$categId])) {
        return array();
      }
      $topicIds = array();
      foreach ($this->linkList[$categId] as $linkData) {
        $topicIds[] = $linkData['topic_id'];
      }
    }
    return $pagesConnector->getTitles($topicIds, $languageId);
  }
}
?>