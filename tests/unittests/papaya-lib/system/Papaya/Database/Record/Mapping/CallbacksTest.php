<?php
require_once(substr(__FILE__, 0, -66).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Database/Record/Mapping/Callbacks.php');

class PapayaDatabaseRecordMappingCallbacksTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordMappingCallbacks::__construct
  */
  public function testConstructor() {
    $callbacks = new PapayaDatabaseRecordMappingCallbacks();
    $this->assertEquals(array(), $callbacks->onBeforeMapping->defaultReturn);
    $this->assertEquals(array(), $callbacks->onBeforeMappingFieldsToProperties->defaultReturn);
    $this->assertEquals(array(), $callbacks->onBeforeMappingPropertiesToFields->defaultReturn);
    $this->assertEquals(array(), $callbacks->onAfterMapping->defaultReturn);
    $this->assertEquals(array(), $callbacks->onAfterMappingFieldsToProperties->defaultReturn);
    $this->assertEquals(array(), $callbacks->onAfterMappingPropertiesToFields->defaultReturn);
    $this->assertNull($callbacks->onMapValue->defaultReturn);
    $this->assertNull($callbacks->onMapValueFromFieldToProperty->defaultReturn);
    $this->assertNull($callbacks->onMapValueFromPropertyToField->defaultReturn);
    $this->assertNull($callbacks->onGetPropertyForField->defaultReturn);
    $this->assertNull($callbacks->onGetFieldForProperty->defaultReturn);
  }
}