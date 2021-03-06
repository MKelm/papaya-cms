<?php
require_once(substr(__FILE__, 0, -65).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

class PapayaCacheIdentifierDefinitionPageTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   * @dataProvider provideParameterData
   */
  public function testGetStatus($expected, $parameters) {
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $definition->papaya(
      $this->getMockApplicationObject(
        array(
          'request' => $this->getMockRequestObject($parameters)
        )
      )
    );
    $this->assertEquals($expected, $definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   */
  public function testGetStatusForPreviewExpectingFalse() {
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $definition->papaya(
      $this->getMockApplicationObject(
        array(
          'request' => $this->getMockRequestObject(array('preview' => TRUE))
        )
      )
    );
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   */
  public function testGetStatusWithDefinedHttpEnvironment() {
    $environment = $_SERVER;
    $_SERVER = array(
      'HTTPS' => 'on',
      'HTTP_HOST' => 'www.sample.tld',
      'SERVER_PORT' => 443
    );
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $definition->papaya($this->getMockApplicationObject());
    $this->assertEquals(
      array(
        'PapayaCacheIdentifierDefinitionPage' => array(
          'scheme' => 'https',
          'host' => 'www.sample.tld',
          'port' => 443,
          'category_id' => 0,
          'page_id' => 0,
          'language' => '',
          'output_mode' => 'html'
        )
      ),
      $definition->getStatus()
     );
    $_SERVER = $environment;
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionPage
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionPage();
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_URL,
      $definition->getSources()
    );
  }

  public static function provideParameterData() {
    return array(
      array(
        array(
          'PapayaCacheIdentifierDefinitionPage' => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'category_id' => 0,
            'page_id' => 0,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array()
      ),
      array(
        array(
          'PapayaCacheIdentifierDefinitionPage' => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'category_id' => 0,
            'page_id' => 42,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array(
          'page_id' => 42
        )
      ),
      array(
        array(
          'PapayaCacheIdentifierDefinitionPage' => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'page_id' => 0,
            'category_id' => 21,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array(
          'category_id' => 21
        )
      ),
      array(
        array(
          'PapayaCacheIdentifierDefinitionPage' => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'page_id' => 42,
            'category_id' => 21,
            'language' => 'de',
            'output_mode' => 'xml'
          )
        ),
        array(
          'category_id' => 21,
          'page_id' => 42,
          'language' => 'de',
          'output_mode' => 'xml'
        )
      ),
      array(
        array(
          'PapayaCacheIdentifierDefinitionPage' => array(
            'scheme' => 'http',
            'host' => '',
            'port' => 80,
            'category_id' => 0,
            'page_id' => 42,
            'language' => '',
            'output_mode' => 'html'
          )
        ),
        array(
          'page_id' => 42,
          'foo' => 'bar'
        )
      ),
    );
  }
}