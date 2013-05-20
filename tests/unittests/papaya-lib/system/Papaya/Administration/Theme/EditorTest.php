<?php
require_once(substr(__FILE__, 0, -61).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Administration/Theme/Editor.php');

class PapayaAdministrationThemeEditorTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationThemeEditor::createContent
   */
  public function testCreateContent() {
    $page = new PapayaAdministrationThemeEditor_TestProxy($this->getMock('papaya_xsl'));
    $this->assertInstanceOf(
      'PapayaAdministrationPagePart', $page->createContent()
    );
  }

  /**
   * @covers PapayaAdministrationThemeEditor::createNavigation
   */
  public function testCreateNavigation() {
    $page = new PapayaAdministrationThemeEditor_TestProxy($this->getMock('papaya_xsl'));
    $this->assertInstanceOf(
      'PapayaAdministrationPagePart', $page->createNavigation()
    );
  }
}

class PapayaAdministrationThemeEditor_TestProxy extends PapayaAdministrationThemeEditor {

  public function createContent() {
    return parent::createContent();
  }

  public function createNavigation() {
    return parent::createNavigation();
  }
}