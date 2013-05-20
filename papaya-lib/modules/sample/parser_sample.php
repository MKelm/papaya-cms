<?php

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_parsermodule.php');

class parser_sample extends base_parsermodule {

  private $_childOptions = array(
    1 => array(
      1 => 'Child Option 1.1',
      2 => 'Child Option 1.2',
      3 => 'Child Option 1.3'
    ),
    2 => array(
      4 => 'Child Option 2.1',
      5 => 'Child Option 2.2',
      6 => 'Child Option 2.3'
    ),
    3 => array(
      7 => 'Child Option 3.1',
      8 => 'Child Option 3.2',
      9 => 'Child Option 3.3'
    )
  );

  public function getAttributeFields() {
    $mainOptions = array(
      1 => 'Main Option One',
      2 => 'Main Option Two',
      3 => 'Main Option Three'
    );
    $attributeFields = array(
      'text' => array('Caption', 'isNoHTML', TRUE, 'input', 100, '', ''),
      'select_main' => array('Main Options', 'isNum', TRUE, 'combo', $mainOptions, ''),
      'select_child' => array('Child Options', 'isNum', TRUE, 'combo', array(), '')
    );
    return $attributeFields;
  }

  /**
  * Return a popurl that can be used to show a larger, individual popup from the
  * addon popup in the richtext editor.
  *
  * array(
  *   'url' => 'sample.html', // popup url
  *   'width' => '200', // width in px or percent
  *   'height' => '50%', // height in px or percent
  *   'button' => 'Select' // button caption
  * );
  *
  * @return string
  */
  public function getAttributePopup() {
    return array(
      'url' => '../../../../'.$this->getLink(
        array(
          'module' => $this->guid,
          'src' => 'script/sample.html'
        ),
        '',
        'modglyph.php'
      ),
      'width' => '70%',
      'height' => '50%',
      'button' => 'Select'
    );
  }

  /**
  * Get tag data to show in addon dialog
  *
  * @param array $params
  * @return array $data
  */
  public function getPapayaTagData($params) {
    $data = array();
    if (isset($params['select_main']) &&
        isset($this->_childOptions[$params['select_main']])) {
      $data['select_child']['options'] = $this->_childOptions[$params['select_main']];
    } else {
      $data['select_child']['options'] = array();
    }
    if (isset($params['select_child'])) {
      $data['select_child']['value'] = $params['select_child'];
    }
    return $data;
  }

  public function createTag($papayaTag) {
    return '';
  }
}