<?php

class PapayaUiContentPage extends PapayaObject {

  private $_page = NULL;
  private $_translation = NULL;

  private $_pageId = 0;
  private $_language = '';
  private $_isPublic = TRUE;

  public function __construct($pageId, $language, $isPublic = TRUE) {
    $this->_pageId = (int)$pageId;
    $this->_language = $language;
    $this->_isPublic = (boolean)$language;
  }

  public function assign($data) {
    PapayaUtilConstraints::assertArrayOrTraversable($data);
    $this->page()->assign($data);
    $this->translation()->assign($data);
  }

  public function page(PapayaContentPage $page = NULL) {
    if (isset($page)) {
      $this->_page = $page;
    } elseif (NULL == $this->_page) {
      if ($this->isPublic()) {
        $this->_page = new PapayaContentPagePublication();
      } else {
        $this->_page = new PapayaContentPage();
      }
      $this->_page->activateLazyLoad($this->_pageId);
    }
    return $this->_page;
  }

  public function translation(PapayaContentPageTranslation $translation = NULL) {
    if (isset($translation)) {
      $this->_translation = $translation;
    } elseif (NULL == $this->_translation) {
      if ($this->isPublic()) {
        $this->_translation = new PapayaContentPagePublicationTranslation();
      } else {
        $this->_translation = new PapayaContentPageTranslation();
      }
      if ($language = $this->getPageLanguage()) {
        $this->_translation->activateLazyLoad(
          array('page_id' => $this->_pageId, 'language_id' => $language['id'])
        );
      }
    }
    return $this->_page;
  }

  public function getPageId() {
    return $this->_pageId;
  }

  public function getPageViewId() {
    if ($translation = $this->translation()) {
      return $translation['view_id'];
    }
    return 0;
  }

  public function getPageLanguage() {
    if ($this->_language instanceOf PapayaContentLanguage) {
      return $this->_language;
    } elseif (isset($this->_language) && isset($this->papaya()->languages)) {
      return $this->_language = $this->papaya()->languages->getLanguage($this->_language);
    }
    return NULL;
  }

  public function isPublic() {
    return $this->_isPublic;
  }

}