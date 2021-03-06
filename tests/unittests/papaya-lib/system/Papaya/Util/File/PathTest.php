<?php
require_once(substr(__FILE__, 0, -48).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Util/File/Path.php');

class PapayaUtilFilePathTest extends PapayaTestCase {

  /**
  * @covers PapayaUtilFilePath::cleanup
  * @dataProvider provideCleanupData
  */
  public function testCleanup($expected, $string, $trailingSlash = TRUE) {
    $this->assertEquals(
      $expected,
      PapayaUtilFilePath::cleanup($string, $trailingSlash)
    );
  }

  /**
  * @covers PapayaUtilFilePath::ensureIsAbsolute
  * @dataProvider provideEnsureIsAbsoluteData
  */
  public function testEnsureIsAbsolute($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilFilePath::ensureIsAbsolute($string)
    );
  }

  /**
  * @covers PapayaUtilFilePath::ensureTrailingSlash
  * @dataProvider provideEnsureTrailingSlashData
  */
  public function testEnsureTrailingSlash($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilFilePath::ensureTrailingSlash($string)
    );
  }

  /**
  * @covers PapayaUtilFilePath::ensureNoTrailingSlash
  * @dataProvider provideEnsureNoTrailingSlashData
  */
  public function testEnsureNoTrailingSlash($expected, $string) {
    $this->assertEquals(
      $expected,
      PapayaUtilFilePath::ensureNoTrailingSlash($string)
    );
  }

  /**
  * @covers PapayaUtilFilePath::getBasePath
  * @backupGlobals
  */
  public function testGetBasePathIncludingDocumentRoot() {
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/file';
    $this->assertEquals(
      '/path/to/',
      PapayaUtilFilePath::getBasePath(TRUE)
    );
  }

  /**
  * @covers PapayaUtilFilePath::getBasePath
  * @backupGlobals
  */
  public function testGetBasePathExcludingDocumentRoot() {
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/file';
    $_SERVER['DOCUMENT_ROOT'] = '/path';
    $this->assertEquals(
      '/to/',
      PapayaUtilFilePath::getBasePath(FALSE)
    );
  }

  /**
  * @covers PapayaUtilFilePath::getBasePath
  * @backupGlobals
  */
  public function testGetBasePathExcludingDocumentRootWithDeviceLetter() {
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/file';
    $_SERVER['DOCUMENT_ROOT'] = 'c:\\path\\';
    $this->assertEquals(
      '/to/',
      PapayaUtilFilePath::getBasePath(FALSE)
    );
  }

  /**
  * @covers PapayaUtilFilePath::getDocumentRoot
  * @backupGlobals
  */
  public function testGetDocumentRoot() {
    $_SERVER['DOCUMENT_ROOT'] = '/path';
    $this->assertEquals(
      '/path/',
      PapayaUtilFilePath::getDocumentRoot()
    );
  }

  /**
  * @covers PapayaUtilFilePath::getDocumentRoot
  * @backupGlobals
  */
  public function testGetDocumentRootFromScriptFilename() {
    $_SERVER['DOCUMENT_ROOT'] = NULL;
    $_SERVER['SCRIPT_FILENAME'] = '/path/to/papaya/papaya/file';
    $options = $this->getMockConfigurationObject(
      array(
        'PAPAYA_ADMIN_PAGE' => TRUE,
        'PAPAYA_PATH_WEB' => '/papaya/',
      )
    );
    $this->assertEquals(
      '/path/to/',
      PapayaUtilFilePath::getDocumentRoot($options)
    );
  }

  /**
  * @covers PapayaUtilFilePath::getDocumentRoot
  * @backupGlobals
  */
  public function testGetDocumentRootDefaultReturn() {
    $_SERVER['DOCUMENT_ROOT'] = NULL;
    $_SERVER['SCRIPT_FILENAME'] = NULL;
    $this->assertEquals(
      '/',
      PapayaUtilFilePath::getDocumentRoot()
    );
  }

  public function testClear() {
    $this->createTemporaryDirectory();
    $oldMask = umask(0);
    mkdir($this->_temporaryDirectory.'/GROUP/ELEMENT/', 0777, TRUE);
    umask($oldMask);
    file_put_contents(
      $this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS',
      'DATA'
    );
    $this->assertFileExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
    PapayaUtilFilePath::clear($this->_temporaryDirectory);
    $this->assertFileNotExists($this->_temporaryDirectory.'/GROUP/ELEMENT/PARAMETERS');
    rmdir($this->_temporaryDirectory);
  }

  /*********************************
  * Data Provider
  *********************************/

  public static function provideCleanupData() {
    return array(
      array('/', '/'),
      array('/sample/', '/sample/'),
      array('/sample/', 'sample/'),
      array('/sample/', 'sample//'),
      array('/sample/', '////sample//'),
      array('/foo/bar/', '/foo/bar/'),
      array('/bar/', '/foo/../bar/'),
      array('/baz/', '/foo/bar/../../baz/'),
      array('/foo/baz/', '/foo/bar/.././baz/'),
      array('c:/foo/baz/', 'c:\foo\bar/.././baz/'),
      array('/', '/', FALSE),
      array('/sample', '/sample/', FALSE),
    );
  }

  public static function provideEnsureIsAbsoluteData() {
    return array(
      array('/', '/'),
      array('/sample', '/sample'),
      array('c:/sample', 'c:/sample'),
      array('/sample', 'sample'),
    );
  }

  public static function provideEnsureTrailingSlashData() {
    return array(
      array('/', '/'),
      array('sample/', 'sample/'),
      array('sample/', 'sample'),
    );
  }

  public static function provideEnsureNoTrailingSlashData() {
    return array(
      array('', '/'),
      array('sample', 'sample/'),
      array('sample', 'sample'),
    );
  }
}
