<?php
require_once(substr(__FILE__, 0, -46).'/Framework/PapayaTestCase.php');
PapayaTestCase::registerPapayaAutoloader();

require_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Plugin/Loader.php');

class PapayaPluginLoaderTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginLoader::plugins
  */
  public function testPluginsGetAfterSet() {
    $plugins = $this->getMock('PapayaPluginList');
    $loader = new PapayaPluginLoader();
    $this->assertSame(
      $plugins, $loader->plugins($plugins)
    );
  }

  /**
  * @covers PapayaPluginLoader::plugins
  */
  public function testPluginsGetWithImplicitCreate() {
    $loader = new PapayaPluginLoader();
    $this->assertInstanceOf(
      'PapayaPluginList', $loader->plugins()
    );
  }

  /**
  * @covers PapayaPluginLoader::options
  */
  public function testOptionsGetAfterSet() {
    $options = $this->getMock('PapayaPluginOptionGroups');
    $loader = new PapayaPluginLoader();
    $this->assertSame(
      $options, $loader->options($options)
    );
  }

  /**
  * @covers PapayaPluginLoader::options
  */
  public function testOptionsGetWithImplicitCreate() {
    $loader = new PapayaPluginLoader();
    $this->assertInstanceOf(
      'PapayaPluginOptionGroups', $loader->options()
    );
  }

  /**
  * @covers PapayaPluginLoader::__get
  * @covers PapayaPluginLoader::__set
  */
  public function testMagicPropertyPlguinsGetAfterSet() {
    $plugins = $this->getMock('PapayaPluginList');
    $loader = new PapayaPluginLoader();
    $loader->plugins = $plugins;
    $this->assertSame(
      $plugins, $loader->plugins
    );
  }

  /**
  * @covers PapayaPluginLoader::__get
  * @covers PapayaPluginLoader::__set
  */
  public function testMagicPropertyOptionsGetAfterSet() {
    $options = $this->getMock('PapayaPluginOptionGroups');
    $loader = new PapayaPluginLoader();
    $loader->options = $options;
    $this->assertSame(
      $options, $loader->options
    );
  }

  /**
  * @covers PapayaPluginLoader::__get
  */
  public function testMagicMethodGetWithInvalidPropertyExpectingException() {
    $loader = new PapayaPluginLoader();
    $this->setExpectedException(
      'LogicException', 'Can not read unkown property PapayaPluginLoader::$unkownProperty'
    );
    $dummy = $loader->unkownProperty;
  }

  /**
  * @covers PapayaPluginLoader::__set
  */
  public function testMagicMethodSetWithInvalidPropertyExpectingException() {
    $loader = new PapayaPluginLoader();
    $this->setExpectedException(
      'LogicException', 'Can not write unkown property PapayaPluginLoader::$unkownProperty'
    );
    $loader->unkownProperty = 'dummy';
  }

  /**
  * @covers PapayaPluginLoader::preload
  */
  public function testPreload() {
    $plugins = $this->getMock('PapayaPluginList');
    $plugins
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(array('123')));
    $loader = new PapayaPluginLoader();
    $loader->plugins($plugins);
    $loader->preload(array('123'));
  }

  /**
  * @covers PapayaPluginLoader::has
  */
  public function testHasExpectingTrue() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $this->assertTrue($loader->has('123'));
  }

  /**
  * @covers PapayaPluginLoader::has
  */
  public function testHasExpectingFalse() {
    $plugins = $this->getMock('PapayaPluginList');
    $plugins
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo(array('123')));
    $loader = new PapayaPluginLoader();
    $loader->plugins($plugins);
    $this->assertFalse($loader->has('123'));
  }

  /**
  * @covers PapayaPluginLoader::get
  * @covers PapayaPluginLoader::getModulePath
  * @covers PapayaPluginLoader::prepareAutoloader
  * @covers PapayaPluginLoader::preparePluginFile
  * @covers PapayaPluginLoader::createObject
  */
  public function testGet() {
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => dirname(__FILE__).'/TestData/')
          )
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $this->assertInstanceOf(
      'PluginLoader_SampleClass', $loader->get('123')
    );
  }

  /**
  * @covers PapayaPluginLoader::getPluginInstance
  */
  public function testGetPluginInstance() {
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => dirname(__FILE__).'/TestData/')
          )
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $this->assertInstanceOf(
      'PluginLoader_SampleClass', $loader->getPluginInstance('123')
    );
  }

  /**
  * @covers PapayaPluginLoader::get
  * @covers PapayaPluginLoader::getModulePath
  * @covers PapayaPluginLoader::prepareAutoloader
  * @covers PapayaPluginLoader::preparePluginFile
  * @covers PapayaPluginLoader::createObject
  */
  public function testGetWithPluginData() {
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => dirname(__FILE__).'/TestData/')
          )
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $samplePlugin = $loader->get('123', NULL, array('foo' => 'bar'));
    $this->assertEquals(
      '<data version="2"><data-element name="foo">bar</data-element></data>',
      $samplePlugin->data
    );
  }

  /**
  * @covers PapayaPluginLoader::get
  * @covers PapayaPluginLoader::getModulePath
  * @covers PapayaPluginLoader::prepareAutoloader
  * @covers PapayaPluginLoader::preparePluginFile
  * @covers PapayaPluginLoader::createObject
  */
  public function testGetWithSingleInstance() {
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => dirname(__FILE__).'/TestData/')
          )
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_SampleClass'
        )
      )
    );
    $plugin = $loader->get('123', NULL, array(), TRUE);
    $this->assertSame(
      $plugin, $loader->get('123', NULL, array(), TRUE)
    );
  }

  /**
  * @covers PapayaPluginLoader::get
  */
  public function testGetWithNonExistingPlugin() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        FALSE
      )
    );
    $this->assertNull($loader->get('123'));
  }

  /**
  * @covers PapayaPluginLoader::get
  * @covers PapayaPluginLoader::prepareAutoloader
  * @covers PapayaPluginLoader::preparePluginFile
  */
  public function testGetWithInvalidPluginFileExpectingMessage() {
    $messages = $this->getMock('PapayaMessageManager', array('dispatch'));
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLog'));
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => dirname(__FILE__).'/TestData/')
          ),
          'messages' => $messages
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'InvalidFile.php',
          'class' => 'PluginLoader_InvalidSampleClass'
        )
      )
    );
    $this->assertNull($loader->get('123'));
  }

  /**
  * @covers PapayaPluginLoader::get
  * @covers PapayaPluginLoader::prepareAutoloader
  * @covers PapayaPluginLoader::preparePluginFile
  */
  public function testGetWithAutloaderPrefix() {
    PapayaAutoloader::clearPaths();
    $messages = $this->getMock('PapayaMessageManager', array('dispatch'));
    $messages
      ->expects($this->any())
      ->method('dispatch')
      ->withAnyParameters();
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => dirname(__FILE__))
          ),
          'messages' => $messages
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => 'TestData/',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_InvalidSampleClass',
          'prefix' => 'PluginLoaderAutoloadPrefix'
        )
      )
    );
    $this->assertNull($loader->get('123'));
    $this->assertAttributeEquals(
      array(
        '/Plugin/Loader/Autoload/Prefix/' => str_replace('\\', '/', dirname(__FILE__)).'/TestData/'
      ),
      '_paths',
      'PapayaAutoloader'
    );
    PapayaAutoloader::clearPaths();
  }

  /**
  * @covers PapayaPluginLoader::get
  * @covers PapayaPluginLoader::prepareAutoloader
  * @covers PapayaPluginLoader::preparePluginFile
  */
  public function testGetWithInvalidPluginClassExpectingMessage() {
    $messages = $this->getMock('PapayaMessageManager', array('dispatch'));
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf('PapayaMessageLog'));
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => dirname(__FILE__).'/TestData/')
          ),
          'messages' => $messages
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => '',
          'file' => 'SampleClass.php',
          'class' => 'PluginLoader_InvalidSampleClass'
        )
      )
    );
    $this->assertNull($loader->get('123'));
  }

  /**
  * @covers PapayaPluginLoader::getFileName
  */
  public function testGetFileName() {
    PapayaAutoloader::clearPaths();
    $loader = new PapayaPluginLoader();
    $loader->papaya(
      $this->getMockApplicationObject(
        array(
          'options' => $this->getMockConfigurationObject(
            array('PAPAYA_MODULES_PATH' => '/base/path/')
          )
        )
      )
    );
    $loader->plugins(
      $this->getPluginListFixture(
        array(
          'guid' => '123',
          'path' => 'sample/path/',
          'file' => 'sample.php',
          'class' => 'SampleClass',
          'prefix' => 'PluginLoaderAutoloadPrefix',
        )
      )
    );
    $this->assertEquals(
      '/base/path/sample/path/sample.php', $loader->getFileName('123')
    );
    $this->assertAttributeEquals(
      array(
        '/Plugin/Loader/Autoload/Prefix/' => '/base/path/sample/path/'
      ),
      '_paths',
      'PapayaAutoloader'
    );
    PapayaAutoloader::clearPaths();
  }

  /**
  * @covers PapayaPluginLoader::getFileName
  */
  public function testGetFileNameOfNonExistingPlugin() {
    $loader = new PapayaPluginLoader();
    $loader->plugins(
      $this->getPluginListFixture(
        FALSE
      )
    );
    $this->assertEquals('', $loader->getFileName('123'));
  }

  /*************************
  * Fistures
  *************************/

  private function getPluginListFixture($record) {
    $plugins = $this->getMock('PapayaPluginList');
    $plugins
      ->expects($this->atLeastOnce())
      ->method('load')
      ->with($this->equalTo(array('123')));
    $plugins
      ->expects($this->atLeastOnce())
      ->method('item')
      ->with($this->equalTo('123'))
      ->will($this->returnValue($record));
    return $plugins;
  }
}
