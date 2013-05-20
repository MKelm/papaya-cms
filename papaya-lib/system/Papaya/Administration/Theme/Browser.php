<?php

class PapayaAdministrationBrowser extends PapayaUiControl {

  private $_listview = NULL;

  public function listview(PapayaUiListview $listview = NULL) {
    if (isset($listview)) {
      $this->_listview = $listview;
    } else {
      $themes = new PapayaThemeList();
      $themes->papaya($this->papaya());
      $this->_listview = new PapayaUiListview();
      $this->_listview->caption = new PapayaUiStringTranslated('Themes');
      $this->_listview->builder($builder = new PapayaUiListviewItemsBuilder($themes));
      $this->_listview->builder()->callbacks()->onCreateItem = array($this, 'callbackCreateItem');
      $this->_listview->builder()->callbacks()->onCreateItem->context = $builder;
      $this->_listview->parameterGroup($this->parameterGroup());
      $this->_listview->parameters($this->parameters());
    }
    $this->_listview = NULL;
  }

  public function callbackCreateItem($builder, $items, $element, $index) {
    $item = new PapayaUiListviewItem('items-theme', (string)$element);
    $item->papaya($this->papaya());
    $item->reference->setParameters(
      array(
        'cmd' => 'theme_show',
        'theme' => $element
      ),
      $this->parameterGroup()
    );
    $item->selected = (
      !$this->parameters()->get('set_id', 0) &&
      $this->parameters()->get('theme', '') == $element
    );
    return $item;
  }

}
