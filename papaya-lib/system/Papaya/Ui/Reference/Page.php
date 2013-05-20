<?php
/**
* Papaya Interface Page Reference (Hyperlink Reference)
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Page.php 37187 2012-07-04 10:38:10Z weinert $
*/

/**
* Papaya Interface Page Reference (Hyperlink Reference)
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiReferencePage extends PapayaUiReference {

  /**
  * Page identification data
  * @var array
  */
  protected $_pageData = array(
    'title' => 'index',
    'category_id' => 0,
    'id' => 0,
    'language' => '',
    'mode' => 'html',
    'preview' => FALSE,
    'preview_time' => 0
  );
  
  private $_pageReferences;

  /**
  * Static create function to allow fluent calls.
  *
  * @param PapayaUrl $url
  * @return PapayaUiReferencePage
  */
  public static function create(PapayaUrl $url = NULL) {
    return new self($url);
  }

  /**
  * @see papaya-lib/system/Papaya/Interface/PapayaUiReference#get()
  */
  public function get() {
    $result = $this->url()->getHostUrl().$this->_basePath;
    $result .= $this->_pageData['title'];
    if ($this->_pageData['category_id'] > 0) {
      $result .= '.'.$this->_pageData['category_id'];
    }
    if ($this->_pageData['id'] > 0 ||
        $this->_pageData['category_id'] > 0) {
      $result .= '.'.$this->_pageData['id'];
    }
    if (!empty($this->_pageData['language'])) {
      $result .= '.'.$this->_pageData['language'];
    }
    $result .= '.'.$this->_pageData['mode'];
    if ($this->_pageData['preview']) {
      $result .= '.preview';
      if ($this->_pageData['preview_time'] > 0) {
        $result .= '.'.$this->_pageData['preview_time'];
      }
    }
    $result .= $this->getQueryString();
    if (!empty($this->_pageData['fragment'])) {
      $result .= '#'.urlencode($this->_pageData['fragment']);
    }
    return $result;
  }

  /**
  * @see papaya-lib/system/Papaya/Interface/PapayaUiReference#load($request)
  */
  public function load($request) {
    parent::load($request);
    $this->setPageTitle(
      $request->getParameter('page_title', 'index', NULL, PapayaRequest::SOURCE_PATH)
    );
    $this->setPageId(
      $request->getParameter('page_id', 0, NULL, PapayaRequest::SOURCE_PATH),
      FALSE
    );
    $this->setPageLanguage(
      $request->getParameter('language', '', NULL, PapayaRequest::SOURCE_PATH),
      FALSE
    );
    $this->setOutputMode(
      $request->getParameter('output_mode', 'html', NULL, PapayaRequest::SOURCE_PATH)
    );
    $this->setPreview(
      $request->getParameter('preview', FALSE, NULL, PapayaRequest::SOURCE_PATH),
      $request->getParameter('preview_time', 0, NULL, PapayaRequest::SOURCE_PATH)
    );
  }

  /**
  * Set page id
  *
  * @param integer $pageId
  * @param boolean $autoConfigure
  * @return PapayaUiReferencePage
  */
  public function setPageId($pageId, $autoConfigure = TRUE) {
    $this->prepare();
    if ($pageId > 0) {
      $this->_pageData['id'] = (int)$pageId;
      if ($autoConfigure && $pageId > 0 && ($factory = $this->pageReferences())) {
        $factory->configure($this);
      }
    }
    return $this;
  }

  /**
  * Get page id
  *
  * @return integer
  */
  public function getPageId() {
    return $this->_pageData['id'];
  }

  /**
  * Set page title (normalized string)
  *
  * @param $pageTitle
  * @return PapayaUiReferencePage
  */
  public function setPageTitle($pageTitle) {
    $this->prepare();
    if (preg_match('(^[a-zA-Z\d_-]+$)D', $pageTitle)) {
      $this->_pageData['title'] = (string)$pageTitle;
    }
    return $this;
  }

  /**
  * Get page title
  * @return string
  */
  public function getPageTitle() {
    return $this->_pageData['title'];
  }

  /**
  * Set page language identifier
  *
  * @param string $languageIdentifier
  * @param boolean $autoConfigure
  * @return PapayaUiReferencePage
  */
  public function setPageLanguage($languageIdentifier, $autoConfigure = TRUE) {
    $this->prepare();
    if (preg_match('(^[a-z]{2,6}$)D', $languageIdentifier)) {
      $this->_pageData['language'] = (string)$languageIdentifier;
      if ($this->_pageData['id'] > 0) {
        if ($autoConfigure && isset($this->papaya()->pageReferences)) {
          $this->papaya()->pageReferences->configure($this);
        }
      }
    }
    return $this;
  }

  public function getPageLanguage() {
    return $this->_pageData['language'];
  }

  /**
  * Set category id
  *
  * @param integer $categoryId
  * @return PapayaUiReferencePage
  */
  public function setCategoryId($categoryId) {
    $this->prepare();
    if ($categoryId >= 0) {
      $this->_pageData['category_id'] = (int)$categoryId;
    }
    return $this;
  }

  /**
  * Set output mode identifier
  *
  * @param string $outputMode
  * @return PapayaUiReferencePage
  */
  public function setOutputMode($outputMode) {
    $this->prepare();
    if (preg_match('(^[a-z]{1,20}$)D', $outputMode)) {
      $this->_pageData['mode'] = (string)$outputMode;
    }
    return $this;
  }

  /**
  * Set preview mode and time
  *
  * @param boolean $isPreview
  * @param integer $previewTime optional, default value 0
  * @return PapayaUiReferencePage
  */
  public function setPreview($isPreview, $previewTime = NULL) {
    $this->prepare();
    $this->_pageData['preview'] = (bool)$isPreview;
    if ($isPreview && isset($previewTime)) {
      $this->_pageData['preview_time'] = (int)$previewTime;
    } elseif (!$isPreview) {
      $this->_pageData['preview_time'] = 0;
    }
    return $this;
  }

  /**
  * Set fragement
  *
  * @param string $fragment
  * @return PapayaUiReferencePage
  */
  public function setFragment($fragment) {
    $this->prepare();
    $this->_pageData['fragment'] = (string)$fragment;
    return $this;
  }
  
  /**
   * Getter/Setter for the page reference factory - an object that load page and domain
   * data for links.
   * 
   * @param PapayaUiReferencePageFactory $factory
   * @return PapayaUiReferencePageFactory
   */
  public function pageReferences(PapayaUiReferencePageFactory $factory = NULL) {
    if (isset($factory)) {
      $this->_pageReferences = $factory;
    } elseif (NULL === $this->_pageReferences && isset($this->papaya()->pageReferences)) {
      $this->_pageReferences = $this->papaya()->pageReferences;
    }
    return $this->_pageReferences;
  }
}