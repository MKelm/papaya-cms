<?php
require_once(substr(__FILE__, 0, -56).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Content/Module/Options.php');

class PapayaContentModuleOptionsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentModuleOptions::_createMapping
  */
  public function testCreateMapping() {
    $content = new PapayaContentModuleOptions();
    $mapping = $content->mapping();
    $this->assertTrue(isset($mapping->callbacks()->onAfterMapping));
  }

  /**
  * @covers PapayaContentModuleOptions::callbackConvertValueByType
  * @dataProvider providePropertiesToFieldsData
  */
  public function testCallbackConvertValueByTypeIntoFields($expected, $properties, $fields) {
    $content = new PapayaContentModuleOptions();
    $this->assertEquals(
      $expected,
      $content->callbackConvertValueByType(
        new stdClass(),
        PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD,
        $properties,
        $fields
      )
    );
  }

  /**
  * @covers PapayaContentModuleOptions::callbackConvertValueByType
  * @dataProvider provideFieldsToPropertiesData
  */
  public function testCallbackConvertValueByTypeIntoProperties($expected, $properties, $fields) {
    $content = new PapayaContentModuleOptions();
    $this->assertEquals(
      $expected,
      $content->callbackConvertValueByType(
        new stdClass(),
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        $properties,
        $fields
      )
    );
  }

  public static function providePropertiesToFieldsData() {
    return array(
      'guid only' => array(
        array(
          'module_guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890'
        )
      ),
      'integer' => array(
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => 42,
          'moduleoption_type' => 'integer'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 42,
          'type' => 'integer'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => 42,
          'moduleoption_type' => 'integer'
        )
      ),
      'array' => array(
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' =>
            '<data version="2">'.
              '<data-element name="0">21</data-element>'.
              '<data-element name="1">42</data-element>'.
            '</data>',
          'moduleoption_type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(21, 42),
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => array(21, 42),
          'moduleoption_type' => 'array'
        )
      )
    );
  }

  public static function provideFieldsToPropertiesData() {
    return array(
      'guid only' => array(
        array(
          'guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890'
        )
      ),
      'integer' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 42,
          'type' => 'integer'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 42,
          'type' => 'integer'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => '42',
          'moduleoption_type' => 'integer'
        )
      ),
      'array - serialized' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(21, 42),
          'type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => 'a:2:{i:0;i:21;i:1;i:42;}',
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => 'a:2:{i:0;i:21;i:1;i:42;}',
          'moduleoption_type' => 'array'
        )
      ),
      'array - empty' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(),
          'type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => '',
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' => '',
          'moduleoption_type' => 'array'
        )
      ),
      'array - xml' => array(
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' => array(21, 42),
          'type' => 'array'
        ),
        array(
          'guid' => 'ab123456789012345678901234567890',
          'name' => 'SAMPLE_NAME',
          'value' =>
            '<data version="2">'.
              '<data-element name="0">21</data-element>'.
              '<data-element name="1">42</data-element>'.
            '</data>',
          'type' => 'array'
        ),
        array(
          'module_guid' => 'ab123456789012345678901234567890',
          'moduleoption_name' => 'SAMPLE_NAME',
          'moduleoption_value' =>
            '<data version="2">'.
              '<data-element name="0">21</data-element>'.
              '<data-element name="1">42</data-element>'.
            '</data>',
          'moduleoption_type' => 'array'
        )
      )
    );
  }
}