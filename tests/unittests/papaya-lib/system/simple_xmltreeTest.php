<?php

require_once(dirname(__FILE__).'/../../Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/sys_simple_xmltree.php');

class simple_xmltreeTest extends PapayaTestCase {

  /**
   * @covers simple_xmltree::unserializeArrayFromXML
   */
  public function testUnserializeArrayFromXML() {
    $xmlStr = '<data><data-element name="PAPAYA_LAYOUT_THEME"><![CDATA[theme]]></data-element><data-element name="PAPAYA_LAYOUT_TEMPLATES"><![CDATA[tpl]]></data-element></data>';
    $expected = array('PAPAYA_LAYOUT_THEME' => 'theme', 'PAPAYA_LAYOUT_TEMPLATES' => 'tpl');
    $actual = null;
    simple_xmltree::unserializeArrayFromXML('', $actual, $xmlStr);
    $this->assertEquals($expected, $actual);
  }

}
