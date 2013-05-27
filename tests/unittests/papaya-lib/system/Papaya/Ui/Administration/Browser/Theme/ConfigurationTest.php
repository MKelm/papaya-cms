<?php

require_once(substr(__FILE__, 0, -79).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(
  PAPAYA_INCLUDE_PATH.'system/Papaya/Ui/Administration/Browser/Theme/Configuration.php'
);

class PapayaUiAdministrationBrowserThemeConfigurationTest extends PapayaTestCase {

  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::getThemeConfiguration
  */
  public function testGetThemeConfigurationWithAllFields() {
    $expected = array(
      '' => array(
        'name' => 'Theme 1',
        'templates' => 'theme-1',
        'version' => '1.0',
        'date' => '2010-04-07',
        'author' => 'Papaya Software GmbH',
        'description' => 'This is the 1st test theme.',
        'thumbMedium' => 'thumb1_theme1.jpg',
        'thumbLarge' => 'thumb2_theme1.jpg'
      )
    );
    $themePath = dirname(__FILE__).'/../TestData/theme1';
    $themeObject = new PapayaUiAdministrationBrowserThemeConfiguration();
    $this->assertSame($expected, $themeObject->getThemeConfiguration($themePath));
  }

  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::getThemeConfiguration
  */
  public function testGetThemeConfigurationOnlyWithNameAndTemplates() {
    $expected = array(
      '' => array(
        'name' => 'Theme 2',
        'templates' => 'theme-2',
        'version' => '',
        'date' => '',
        'author' => '',
        'description' => '',
        'thumbMedium' => '',
        'thumbLarge' => ''
      )
    );
    $themePath = dirname(__FILE__).'/../TestData/theme2';
    $themeObject = new PapayaUiAdministrationBrowserThemeConfiguration();
    $this->assertSame($expected, $themeObject->getThemeConfiguration($themePath));
  }

  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::getThemeConfiguration
  */
  public function testGetThemeConfigurationWithoutThumbs() {
    $expected = array(
      '' => array(
        'name' => 'Theme 3',
        'templates' => 'theme-3',
        'version' => '6.7',
        'date' => '2009-12-31',
        'author' => 'Papaya Software GmbH',
        'description' => 'This is the 3rd test theme.',
        'thumbMedium' => '',
        'thumbLarge' => ''
      )
    );
    $themePath = dirname(__FILE__).'/../TestData/theme3';
    $themeObject = new PapayaUiAdministrationBrowserThemeConfiguration();
    $this->assertSame($expected, $themeObject->getThemeConfiguration($themePath));
  }

  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::getThemeConfiguration
  */
  public function testGetThemeConfigurationWithSubThemes() {
    $expected = array(
      '' => array(
        'name' => 'Theme 4',
        'templates' => 'theme-4',
        'version' => '6.7',
        'date' => '2009-12-31',
        'author' => 'Papaya Software GmbH',
        'description' => 'This is the 4th test theme.',
        'thumbMedium' => '',
        'thumbLarge' => ''
      ),
      'suba' => array(
        'name' => 'Theme 4 A',
        'templates' => 'theme-4',
        'version' => '6.7',
        'date' => '2009-12-31',
        'author' => 'Papaya Software GmbH',
        'description' => 'This is the 4th test theme with A.',
        'thumbMedium' => '',
        'thumbLarge' => ''
      ),
      'subb' => array(
        'name' => 'Theme 4 B',
        'templates' => 'theme-4',
        'version' => '6.7',
        'date' => '2009-12-31',
        'author' => 'Papaya Software GmbH',
        'description' => 'This is the 4th test theme with B.',
        'thumbMedium' => '',
        'thumbLarge' => ''
      )
    );
    $themePath = dirname(__FILE__).'/../TestData/theme4';
    $themeObject = new PapayaUiAdministrationBrowserThemeConfiguration();
    $this->assertSame($expected, $themeObject->getThemeConfiguration($themePath));
  }

  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::getThemeConfiguration
  */
  public function testGetThemeConfigurationWithInvalidConfigurationFile() {
    $themeObject = new PapayaUiAdministrationBrowserThemeConfiguration();
    $themePath = dirname(__FILE__).'/../TestData/theme999';
    $this->assertSame(array(), $themeObject->getThemeConfiguration($themePath));
  }

  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::loadXml
  */
  public function testLoadXml() {
    $themeObject = new PapayaUiAdministrationBrowserThemeConfiguration();
    $xmlFilePath = dirname(__FILE__).'/../TestData/theme2/theme.xml';
    $this->assertTrue($themeObject->loadXml($xmlFilePath));
  }

  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::loadXml
  */
  public function testLoadXmlWithFileNotExists() {
    $themeObject = new PapayaUiAdministrationBrowserThemeConfiguration();
    $xmlFilePath = dirname(__FILE__).'/../TestData/theme999/theme.xml';
    $this->assertFalse($themeObject->loadXml($xmlFilePath));
  }
  /**
  * @covers PapayaUiAdministrationBrowserThemeConfiguration::getDOMDocumentObject
  */
  public function testGetDOMDocumentObject() {
    $configuration = new PapayaUiAdministrationBrowserThemeConfiguration();
    $document = $configuration->getDOMDocumentObject();
    $this->assertTrue($document instanceof DOMDocument);
  }
}