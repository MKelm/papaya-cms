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
* @version $Id: actbox_catalog_related_categories.php 32288 2009-09-30 17:40:22Z weinert $
*/

/**
* Basic class aktion box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

/**
* Function class catalog
*/
require_once(dirname(__FILE__).'/base_catalog.php');

/**
* Catalog page navigation - outputs a list of catalog catagories and
* links to a catalog content page
*
* @package Papaya-Modules
* @subpackage Free-Catalog
*/
class actionbox_catalog_related_categories extends base_actionbox {

  /**
  * Base catalog object
  * @var object base_catalog $baseCatalog
  */
  var $baseCatalog = NULL;

  /**
  * Catalog List
  * @var boolean $listCatalog
  */
  var $listCatalog = NULL;

  /**
  * Content edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'page_id' => array ('Page Id', 'isNum', TRUE, 'input', 5, ''),
    'categ_base_id' => array ('Catalog Id', 'isNum', TRUE, 'input', 5, '')
  );

  /**
  * handler to ignore catalog type
  * @var integer $ignoreCatalogType
  */
  var $ignoreCatalogType = 3;

  /**
  * Get Parsed Data
  *
  * @access public
  * @return string XML
  */
  function getParsedData() {
    $pageId = @(int)$this->data['page_id'];

    if (isset($GLOBALS['PAPAYA_PAGE']) &&
        isset($GLOBALS['PAPAYA_PAGE']->requestData['categ_id'])) {
      $currentCategId = (int)$GLOBALS['PAPAYA_PAGE']->requestData['categ_id'];
    } else {
      $currentCategId = 0;
    }

    $this->baseCatalog = new base_catalog();
    $this->baseCatalog->loadRelatedCatalogList(
      $this->parentObj->topicId,
      $this->parentObj->getContentLanguageId(),
      empty($this->data['categ_base_id']) ? 0 : (int)$this->data['categ_base_id']
    );
    if (isset($this->baseCatalog->catalogList) &&
        is_array($this->baseCatalog->catalogList) &&
        count($this->baseCatalog->catalogList) > 0) {
      $this->decodeList($this->baseCatalog->catalogList);
      $this->baseCatalog->loadCatalogDetailPerIds(
        $this->listCatalog, $this->parentObj->getContentLanguageId()
      );
      $str = sprintf('<related>'.LF);
      if (is_array($this->baseCatalog->catalogList) &&
          count($this->baseCatalog->catalogList) > 0) {
        foreach ($this->baseCatalog->catalogList as $category) {
          //Liste aufbauen
          $focus = ($category['catalog_id'] == $currentCategId) ? ' focus="focus"' : '';
          $strTemp = strstr(
            $category['catalog_parent_path'],
            ';'.@(int)$this->data['categ_base_id'].';'
          );
          $strTemp = substr($strTemp, strlen($this->data['categ_base_id']) + 2, -1);
          if ($strTemp) {
            $pathItems = explode(';', $strTemp);
          } else {
            $pathItems = array();
          }
          if (count($pathItems) > 0 ||
              $category['catalog_id'] != $this->data['categ_base_id']) {
            $str .= sprintf('<path>'.LF);
            foreach ($pathItems as $categId) {
              if ($categId > 0) {
                $focus = ($categId == $currentCategId) ? ' focus="focus"' : '';
                $str .= sprintf(
                  '<mapitem id="%d" href="%s" title="%s"  %s/>'.LF,
                  (int)$categId,
                  papaya_strings::escapeHTMLChars(
                    $this->getWebLink(
                      $pageId,
                      NULL,
                      NULL,
                      NULL,
                      NULL,
                      $this->listCatalog[$categId]['catalog_title'],
                      $categId
                    )
                  ),
                  papaya_strings::escapeHTMLChars(
                    $this->listCatalog[$categId]['catalog_title']
                  ),
                  $focus
                );
              }
            }
            if ($category['catalog_id'] != $this->data['categ_base_id'] &&
                $this->listCatalog[$category['catalog_id']]['catalogtype_id'] !=
                $this->ignoreCatalogType) {
              $str .= sprintf(
                '<mapitem id="%d" href="%s" title="%s"  %s/>'.LF,
                (int)$category['catalog_id'],
                papaya_strings::escapeHTMLChars(
                  $this->getWebLink(
                    $pageId,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    $category['catalog_title'],
                    $category['catalog_id']
                  )
                ),
                papaya_strings::escapeHTMLChars(
                  $category['catalog_title']
                ),
                $focus
              );
            }
            $str .= sprintf('</path>'.LF);
          }
        }
        $str .= '</related>'.LF;
        return $str;
      }
    }
    return '<related/>'.LF;
  }

  /**
  * Decode parent path list to elements
  *
  * @param array $list
  * @access public
  */
  function decodeList($list) {
    if (isset($list) && is_array($list)) {
      foreach ($list as $category) {
        $strTemp = strstr(
          $category['catalog_parent_path'],
          ';'.@(int)$this->data['categ_base_id'].';'
        );
        $strTemp = substr($strTemp, strlen($this->data['categ_base_id']) + 2, -1);
        if ($strTemp) {
          $pathItems = explode(';', $strTemp);
          $this->listCatalog[(int)$category['catalog_id']] = TRUE;
        } else {
          $pathItems = array();
          if ($category['catalog_parent'] == @(int)$this->data['categ_base_id']) {
            $this->listCatalog[(int)$category['catalog_id']] = TRUE;
          } else {
            unset($list[$category['catalog_id']]);
            unset($this->baseCatalog->catalogList[$category['catalog_id']]);
          }
        }
        if (is_array($pathItems) && count($pathItems) > 0) {
          foreach ($pathItems as $id) {
            if ($id > 0 && $id != $this->data['categ_base_id']) {
              $this->listCatalog[$id] = FALSE;
            }
          }
        }
      }
    }
  }
}
?>