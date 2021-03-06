<?php
require_once(substr(__FILE__, 0, -47).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Iterator/Glob.php');

class PapayaIteratorGlobTest extends PapayaTestCase {

  /**
  * @covers PapayaIteratorGlob::__construct
  */
  public function testConstructor() {
    $glob = new PapayaIteratorGlob(dirname(__FILE__).'/TestDataGlob/*.*');
    $this->assertStringEndsWith(
      '/TestDataGlob/*.*', $this->readAttribute($glob, '_path')
    );
  }

  /**
  * @covers PapayaIteratorGlob::__construct
  * @covers PapayaIteratorGlob::setFlags
  * @covers PapayaIteratorGlob::getFlags
  */
  public function testConstructorWithFlags() {
    $glob = new PapayaIteratorGlob(dirname(__FILE__).'/TestDataGlob/*.*', GLOB_NOSORT);
    $this->assertEquals(
      GLOB_NOSORT, $glob->getFlags()
    );
  }

  /**
  * @covers PapayaIteratorGlob::rewind
  */
  public function testRewind() {
    $glob = new PapayaIteratorGlob(dirname(__FILE__).'/TestDataGlob/*.*');
    $files = iterator_to_array($glob);
    $glob->rewind();
    $this->assertAttributeSame(
      NULL, '_files', $glob
    );
  }

  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::getIterator
  */
  public function testGetIterator() {
    $glob = new PapayaIteratorGlob(dirname(__FILE__).'/TestDataGlob/*.*');
    $files = iterator_to_array($glob);
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleOne.txt', $files[0]
    );
    $this->assertStringEndsWith(
      '/TestDataGlob/sampleTwo.txt', $files[1]
    );
  }


  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::getIterator
  */
  public function testGetIteratorInvalidDirectory() {
    $glob = new PapayaIteratorGlob(dirname(__FILE__).'/TestDataGlob/INVALID_DIRECTORY/*.*');
    $this->assertEquals(
      array(), iterator_to_array($glob)
    );
  }

  /**
  * @covers PapayaIteratorGlob::getFilesLazy
  * @covers PapayaIteratorGlob::count
  */
  public function testCount() {
    $glob = new PapayaIteratorGlob(dirname(__FILE__).'/TestDataGlob/*.*');
    $this->assertEquals(
      2, count($glob)
    );
  }
}