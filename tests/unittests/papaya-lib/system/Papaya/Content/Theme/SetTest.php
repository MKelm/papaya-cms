<?php
require_once(substr(__FILE__, 0, -50).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaContentThemeSetTest extends PapayaTestCase {

  /**
  * @covers PapayaContentThemeSet::_createMapping
  */
  public function testCreateMapping() {
    $themeSet = new PapayaContentThemeSet();
    $this->assertInstanceOf(
      'PapayaDatabaseInterfaceMapping',
      $mapping = $themeSet->mapping()
    );
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromFieldToProperty));
    $this->assertTrue(isset($mapping->callbacks()->onMapValueFromPropertyToField));
  }

  /**
  * @covers PapayaContentThemeSet::mapFieldToProperty
  */
  public function testMapFieldToPropertyPassthru() {
    $themeSet = new PapayaContentThemeSet();
    $this->assertEquals(
      'success',
      $themeSet->mapping()->callbacks()->onMapValueFromFieldToProperty(
        'title', 'themeset_title', 'success'
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::mapFieldToProperty
  */
  public function testMapFieldToPropertyUnserialize() {
    $themeSet = new PapayaContentThemeSet();
    $this->assertEquals(
      array(
        'PAGE' => array(
          'GROUP' => array(
            'FOO' => 'bar'
          )
        )
      ),
      $themeSet->mapping()->callbacks()->onMapValueFromFieldToProperty(
        'values',
        'themeset_values',
        '<data version="2">'.
          '<data-list name="PAGE">'.
            '<data-list name="GROUP">'.
              '<data-element name="FOO">bar</data-element>'.
            '</data-list>'.
          '</data-list>'.
        '</data>'
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::mapPropertyToField
  */
  public function testMapPropertyToFieldPassthru() {
    $themeSet = new PapayaContentThemeSet();
    $this->assertEquals(
      'success',
      $themeSet->mapping()->callbacks()->onMapValueFromPropertyToField(
        'title', 'themeset_title', 'success'
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::mapPropertyToField
  */
  public function testMapPropertyToFieldSerialize() {
    $themeSet = new PapayaContentThemeSet();
    $this->assertEquals(
      '<data version="2">'.
        '<data-list name="PAGE">'.
          '<data-list name="GROUP">'.
            '<data-element name="FOO">bar</data-element>'.
          '</data-list>'.
        '</data-list>'.
      '</data>',
      $themeSet->mapping()->callbacks()->onMapValueFromPropertyToField(
        'values', 'themeset_values', array('PAGE' => array('GROUP' => array('FOO' => 'bar')))
      )
    );
  }

  /**
  * @covers PapayaContentThemeSet::getValuesXml
  */
  public function testGetValuesXml() {
    $definition = $this->getMock('PapayaContentStructure');
    $definition
      ->expects($this->once())
      ->method('getXmlDocument')
      ->with(array())
      ->will($this->returnValue(new PapayaXmlDocument));
    $themeSet = new PapayaContentThemeSet();
    $this->assertInstanceOf('PapayaXmlDocument', $themeSet->getValuesXml($definition));
  }
}