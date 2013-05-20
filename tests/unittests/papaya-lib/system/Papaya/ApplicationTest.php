<?php
require_once(substr(__FILE__, 0, -45).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application.php');

class PapayaApplicationTest extends PapayaTestCase {

  /**
  * @covers PapayaApplication::getInstance
  */
  public function testGetInstanceOneInstance() {
    $app1 = PapayaApplication::getInstance();
    $app2 = PapayaApplication::getInstance();
    $this->assertInstanceOf(
      'PapayaApplication',
      $app1
    );
    $this->assertSame(
      $app1, $app2
    );
  }
  /**
  * @covers PapayaApplication::getInstance
  */
  public function testGetInstanceTwoInstances() {
    $app1 = PapayaApplication::getInstance();
    $app2 = PapayaApplication::getInstance(TRUE);
    $this->assertInstanceOf(
      'PapayaApplication',
      $app1
    );
    $this->assertNotSame(
      $app1, $app2
    );
  }

  /**
  * @covers PapayaApplication::registerProfiles
  */
  public function testRegisterProfiles() {
    $profile = $this->getProfileObjectMockFixture();
    $profiles = $this->getMock('PapayaApplicationProfiles');
    $profiles
      ->expects($this->once())
      ->method('getProfiles')
      ->will($this->returnValue(array($profile)));
    $app = new PapayaApplication();
    $app->registerProfiles($profiles);
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfile() {
    $profile = $this->getProfileObjectMockFixture();
    $app = new PapayaApplication();
    $app->registerProfile($profile);
    $this->assertSame(
      array('sampleclass' => $profile),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileDuplicateIgnore() {
    $profileOne = $this->getProfileObjectMockFixture();
    $profileTwo = $this->getProfileObjectMockFixture();
    $app = new PapayaApplication();
    $app->registerProfile($profileOne);
    $app->registerProfile($profileTwo, PapayaApplication::DUPLICATE_IGNORE);
    $this->assertSame(
      array('sampleclass' => $profileOne),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileDuplicateOverwrite() {
    $profileOne = $this->getProfileObjectMockFixture();
    $profileTwo = $this->getProfileObjectMockFixture();
    $app = new PapayaApplication();
    $app->registerProfile($profileOne);
    $app->registerProfile($profileTwo, PapayaApplication::DUPLICATE_OVERWRITE);
    $this->assertSame(
      array('sampleclass' => $profileTwo),
      $this->readAttribute($app, '_profiles')
    );
  }

  /**
  * @covers PapayaApplication::registerProfile
  */
  public function testRegisterProfileDuplicateError() {
    $profileOne = $this->getProfileObjectMockFixture();
    $profileTwo = $this->getProfileObjectMockFixture();
    $app = new PapayaApplication();
    $app->registerProfile($profileOne);
    $this->setExpectedException(
      'InvalidArgumentException',
      'Duplicate application object profile:'
    );
    $app->registerProfile($profileTwo, PapayaApplication::DUPLICATE_ERROR);
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectAfterSet() {
    $object = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('SAMPLE', $object);
    $this->assertSame(
      $object,
      $app->getObject('SAMPLE')
    );
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectWithoutSetExpectingError() {
    $object = new stdClass();
    $app = new PapayaApplication();
    $this->setExpectedException(
      'InvalidArgumentException',
      'Unknown profile identifier:'
    );
    $app->getObject('SAMPLE');
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectWithImplicitCreate() {
    $app = new PapayaApplication();
    $this->assertInstanceOf(
      'stdClass',
      $app->getObject('SAMPLE', 'stdClass')
    );
  }

  /**
  * @covers PapayaApplication::getObject
  */
  public function testGetObjectWithProfile() {
    $object = new stdClass();
    $profile = $this->getProfileObjectMockFixture();
    $profile
      ->expects($this->once())
      ->method('createObject')
      ->will($this->returnValue($object));
    $app = new PapayaApplication();
    $app->registerProfile($profile);
    $this->assertSame(
      $object,
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObject() {
    $object = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('stdClass', $object);
    $this->assertSame(
      array('stdclass' => $object),
      $this->readAttribute($app, '_objects')
    );
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObjectDuplicateError() {
    $app = new PapayaApplication();
    $app->setObject('SampleClass', new stdClass());
    $this->setExpectedException(
      'LogicException',
      'Application object does already exists:'
    );
    $app->setObject('SampleClass', new stdClass());
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObjectDuplicateIgnore() {
    $objectOne = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('SampleClass', $objectOne);
    $app->setObject('SampleClass', new stdClass(), PapayaApplication::DUPLICATE_IGNORE);
    $this->assertSame(
      $objectOne,
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::setObject
  */
  public function testSetObjectDuplicateOverwrite() {
    $objectTwo = new stdClass();
    $app = new PapayaApplication();
    $app->setObject('SampleClass', new stdClass());
    $app->setObject('SampleClass', $objectTwo, PapayaApplication::DUPLICATE_OVERWRITE);
    $this->assertSame(
      $objectTwo,
      $app->getObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectExpectingFalse() {
    $app = new PapayaApplication();
    $this->assertFalse(
      $app->hasObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectExpectingTrue() {
    $app = new PapayaApplication();
    $app->setObject('SampleClass', new stdClass());
    $this->assertTrue(
      $app->hasObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectWithProfileExpectingTrue() {
    $profile = $this->getProfileObjectMockFixture();
    $app = new PapayaApplication();
    $app->registerProfile($profile);
    $this->assertTrue(
      $app->hasObject('SampleClass')
    );
  }

  /**
  * @covers PapayaApplication::hasObject
  */
  public function testHasObjectWithProfileExpectingFalse() {
    $profile = $this->getProfileObjectMockFixture();
    $app = new PapayaApplication();
    $app->registerProfile($profile);
    $this->assertFalse(
      $app->hasObject('SampleClass', FALSE)
    );
  }

  /**
  * @covers PapayaApplication::__get
  */
  public function testMagicMethodGetWithProfile() {
    $object = new stdClass();
    $profile = $this->getProfileObjectMockFixture();
    $profile
      ->expects($this->once())
      ->method('createObject')
      ->will($this->returnValue($object));
    $app = new PapayaApplication();
    $app->registerProfile($profile);
    $this->assertSame(
      $object,
      $app->SampleClass
    );
  }

  /**
  * @covers PapayaApplication::__set
  */
  public function testMagicMethodSet() {
    $object = new stdClass();
    $app = new PapayaApplication();
    $app->stdClass = $object;
    $this->assertSame(
      array('stdclass' => $object),
      $this->readAttribute($app, '_objects')
    );
  }

  /**
  * @covers PapayaApplication::__set
  */
  public function testMagicMethodSetWithInvalidValueExpectingException() {
    $app = new PapayaApplication();
    $this->setExpectedException('InvalidArgumentException');
    $app->propertyName = 'INVALID_VALUE';
  }

  /**
  * @covers PapayaApplication::__isset
  */
  public function testMagicMethodIssetWithProfileExpectingTrue() {
    $profile = $this->getProfileObjectMockFixture();
    $app = new PapayaApplication();
    $app->registerProfile($profile);
    $this->assertTrue(
      isset($app->SampleClass)
    );
  }

  /**
  * @covers PapayaApplication::__call
  */
  public function testMagicMethodCall() {
    $app = new PapayaApplication();
    $app->sampleClass($sample = new stdClass());
    $this->assertSame($sample, $app->sampleClass());
  }

  /****************************
  * Data Provider
  ****************************/

  private function getProfileObjectMockFixture() {
    $profile = $this->getMock(
      'PapayaApplicationProfile', array('getIdentifier', 'createObject')
    );
    $profile
      ->expects($this->any())
      ->method('getIdentifier')
      ->will($this->returnValue('SampleClass'));
    return $profile;
  }
}
?>