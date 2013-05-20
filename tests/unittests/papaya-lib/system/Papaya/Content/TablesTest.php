<?php
require_once(substr(__FILE__, 0, -48).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Content/Tables.php');
require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Content/Options.php');

class PapayaContentTablesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithoutOptions() {
    $tables = new PapayaContentTables();
    $this->assertEquals(
      'topic', $tables->get(PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithOptionsButDefaultValue() {
    $tables = new PapayaContentTables();
    $tables->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      'papaya_topic', $tables->get(PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithOptions() {
    $tables = new PapayaContentTables();
    $tables->papaya(
      $this->getMockApplicationObject(
        array(
          'Options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_DB_TABLEPREFIX' => 'foo'
            )
          )
        )
      )
    );
    $this->assertEquals(
      'foo_topic', $tables->get(PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::get
  */
  public function testGetWithOptionsIsEmptyString() {
    $tables = new PapayaContentTables();
    $tables->papaya(
      $this->getMockApplicationObject(
        array(
          'Options' => $this->getMockConfigurationObject(
            array(
              'PAPAYA_DB_TABLEPREFIX' => ''
            )
          )
        )
      )
    );
    $this->assertEquals(
      'topic', $tables->get(PapayaContentTables::PAGES)
    );
  }

  /**
  * @covers PapayaContentTables::getTables
  */
  public function testGetTables() {
    $this->assertInternalType('array', PapayaContentTables::getTables());
  }
}