<?php
require_once(substr(__FILE__, 0, -44).'/Framework/PapayaTestCase.php');

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Autoloader.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/File/Path.php');

class PapayaAutoloaderTest extends PapayaTestCase {

  /**
  * @covers PapayaAutoloader::load
  */
  public function testLoad() {
    PapayaAutoloader::load('AutoloaderTestClass', dirname(__FILE__).'/TestData/class.php');
    $this->assertTrue(class_exists('AutoloaderTestClass', FALSE));
  }

  /**
  * @covers PapayaAutoloader::getClassFile
  * @covers PapayaAutoloader::prepareFileName
  * @dataProvider getClassFileDataProvider
  */
  public function testGetClassFile($expected, $className) {
    $this->assertStringEndsWith(
      $expected,
      PapayaAutoloader::getClassFile($className)
    );
  }

  /**
  * @covers PapayaAutoloader::registerPath
  */
  public function testRegisterPath() {
    PapayaAutoloader::clearPaths();
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    $this->assertAttributeEquals(
      array('/Papaya/Module/Sample/' => '/foo/bar/'), '_paths', 'PapayaAutoloader'
    );
    PapayaAutoloader::clearPaths();
  }

  /**
  * @covers PapayaAutoloader::compareByCharacterLength
  */
  public function testRegisterPathSortsPaths() {
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    PapayaAutoloader::registerPath('PapayaModule', '/bar/foo/foobar');
    PapayaAutoloader::registerPath('PapayaModuleSampleChild', '/foo/bar/child');
    $this->assertAttributeEquals(
      array(
        '/Papaya/Module/Sample/Child/' => '/foo/bar/child/',
        '/Papaya/Module/Sample/' => '/foo/bar/',
        '/Papaya/Module/' => '/bar/foo/foobar/'
      ),
      '_paths',
      'PapayaAutoloader'
    );
    PapayaAutoloader::clearPaths();
  }

  /**
  * @covers PapayaAutoloader::clearPaths
  */
  public function testClearPaths() {
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    PapayaAutoloader::clearPaths();
    $this->assertAttributeEquals(
      array(), '_paths', 'PapayaAutoloader'
    );
  }

  /**
  * @covers PapayaAutoloader::getClassFile
  * @covers PapayaAutoloader::prepareFileName
  * @dataProvider getModuleClassFileDataProvider
  */
  public function testGetClassFileAfterPathRegistration($expected, $moduleClass,
                                                        $modulePrefix, $modulePath) {
    PapayaAutoloader::registerPath($modulePrefix, $modulePath);
    $this->assertEquals(
      $expected,
      PapayaAutoloader::getClassFile($moduleClass)
    );
    PapayaAutoloader::clearPaths();
  }

  /****************************
  * Data Provider
  ****************************/

  public static function getClassFileDataProvider() {
    return array(
      array('/system/Papaya/Sample.php', 'PapayaSample'),
      array('/system/Papaya/Sample/Abbr.php', 'PapayaSampleABBR'),
      array('/system/Papaya/Sample/Abbr/Class.php', 'PapayaSampleABBRClass'),
      array('/system/base_options.php', 'base_options')
    );
  }

  public static function getModuleClassFileDataProvider() {
    return array(
      array(
        '/some/module/Sample.php', 'PapayaModuleSample', 'PapayaModule', '/some/module'
      ),
      array(
        '/some/module/Group/Sample.php', 'PapayaModuleGroupSample', 'PapayaModule', '/some/module'
      ),
      array(
        '/some/module/external/Sample.php', 'ExternalSample', 'External', '/some/module/external'
      )
    );
  }
}
