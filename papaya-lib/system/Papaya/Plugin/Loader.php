<?php
/**
* The PluginLoader allows to to get module/plugin objects by guid.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Plugins
* @version $Id: Loader.php 38482 2013-05-08 16:48:53Z weinert $
*/

/**
* The PluginLoader allows to to get module/plugin objects by guid.
*
* It can be used as an registry for single instance plugins, too (external singleton). The main
* method of this class is get(), but preload() can be used to preload the nessesary data for
* multiple plugin guids at once.
*
* @package Papaya-Library
* @subpackage Plugins
*
* @property PapayaPluginList $plugins
* @property PapayaPluginOptionGroups $options
*/
class PapayaPluginLoader extends PapayaObject {

  /**
  * Database access to plugin data
  *
  * @var PapayaPluginList
  */
  private $_plugins = NULL;

  /**
  * Access to plugin options data, grouped by plugin
  *
  * @var PapayaPluginOptionGroups
  */
  private $_optionGroups = NULL;

  /**
  * Internal list of single instance plugins (external singletons)
  *
  * @var array
  */
  private $_instances = array();

  /**
  * define plugins and options as readable properties
  *
  * @param string $name
  * @return mixed
  */
  public function __get($name) {
    switch ($name) {
    case 'plugins' :
      return $this->plugins();
    case 'options' :
      return $this->options();
    }
    throw new LogicException(
      sprintf('Can not read unkown property %s::$%s', get_class($this), $name)
    );
  }

  /**
  * define plugins and options as readable properties
  *
  * @param string $name
  * @return mixed
  */
  public function __set($name, $value) {
    switch ($name) {
    case 'plugins' :
      $this->plugins($value);
      return;
    case 'options' :
      $this->options($value);
      return;
    }
    throw new LogicException(
      sprintf('Can not write unkown property %s::$%s', get_class($this), $name)
    );
  }

  /**
  * Getter/Setter für plugin data list
  */
  public function plugins(PapayaPluginList $plugins = NULL) {
    if (isset($plugins)) {
      $this->_plugins = $plugins;
    }
    if (is_null($this->_plugins)) {
      $this->_plugins = new PapayaPluginList();
    }
    return $this->_plugins;
  }

  /**
  * Getter/Setter für plugin option groups (grouped by module guid)
  */
  public function options(PapayaPluginOptionGroups $groups = NULL) {
    if (isset($groups)) {
      $this->_optionGroups = $groups;
    }
    if (is_null($this->_optionGroups)) {
      $this->_optionGroups = new PapayaPluginOptionGroups();
    }
    return $this->_optionGroups;
  }

  /**
  * Preload plugin data by guid. This functions allows to minimize database queries. Less database
  * queries means better performance.
  *
  * @param array $guids
  */
  public function preload(array $guids = array()) {
    $this->plugins()->load($guids);
  }

  /**
  * Check if the data for a given plugin guid is available.
  *
  * @param string $guid
  * @return boolean
  */
  public function has($guid) {
    $plugins = $this->plugins();
    $plugins->load(array($guid));
    if ($pluginData = $plugins->item($guid)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
  * Create and get a plugin instance. If the plugin package defines an autoload prefix it will
  * be registered in the PapayaAutoloader
  *
  * @param string $guid
  * @param Object|NULL $parent
  * @param array $data
  * @param boolean $singleInstance Plugin object should be created once,
  *   additional call will return the first instance.
  * @return Object|NULL
  */
  public function get($guid, $parent = NULL, $data = NULL, $singleInstance = FALSE) {
    $plugins = $this->plugins();
    $plugins->load(array($guid));
    if ($pluginData = $plugins->item($guid)) {
      $this->prepareAutoloader($pluginData);
      if ($this->preparePluginFile($pluginData)) {
        $plugin = $this->createObject($pluginData, $parent, $singleInstance);
        if ($plugin instanceOf PapayaPluginEditable) {
          if (is_array($data) || $data instanceOf Traversable) {
            $plugin->content()->assign($data);
          } elseif (is_string($data)) {
            $plugin->content()->setXml($data);
          }
        } elseif (!empty($data) && method_exists($plugin, 'setData')) {
          if (is_array($data) || $data instanceOf Traversable) {
            $plugin->setData(
              PapayaUtilStringXml::serializeArray(
                PapayaUtilArray::ensure($data)
              )
            );
          } else {
            $plugin->setData($data);
          }
        }
        return $plugin;
      }
    }
    return NULL;
  }

  /**
  * Alias for {@see PapayaPluginLoader::get()}. For backwards compatibility only.
  *
  * @param string $guid
  * @param Object|NULL $parent
  * @param array $data
  * @param string $class
  * @param string $file
  * @param boolean $singleInstance Plugin object should be created once,
  *   additional call will return the first instance.
  * @return Object|NULL
  */
  public function getPluginInstance($guid, $parent = NULL, $data = NULL,
                             $class = NULL, $file = NULL, $singleton = FALSE) {
    return $this->get($guid, $parent, $data, $singleton);
  }

  /**
  * Loads the plugin data for the guid and returns the filename. The autoloader of the
  * plugin group will be activated, too.
  *
  * @param string $guid
  * @return string
  */
  public function getFileName($guid) {
    $plugins = $this->plugins();
    $plugins->load(array($guid));
    if ($pluginData = $plugins->item($guid)) {
      $this->prepareAutoloader($pluginData);
      return $this->getModulePath().$pluginData['path'].$pluginData['file'];
    }
    return '';
  }

  /**
  * If the plugin class does not already exists, the autoloader for the plugin package is
  * registered.
  *
  * @param array $pluginData
  */
  private function prepareAutoloader(array $pluginData) {
    if (!class_exists($pluginData['class'], FALSE)) {
      if (!empty($pluginData['prefix'])) {
        PapayaAutoloader::registerPath(
          $pluginData['prefix'], $this->getModulePath().$pluginData['path']
        );
      }
    }
  }

  /**
  * Prepares and includes a plugin file.
  *
  * @param array $pluginData
  * @return boolean
  */
  private function preparePluginFile(array $pluginData) {
    if (!class_exists($pluginData['class'], TRUE)) {
      $fileName = $this->getModulePath().$pluginData['path'].$pluginData['file'];
      if (!(
            file_exists($fileName) &&
            is_readable($fileName) &&
            include_once($fileName)
          )) {
        $logMessage = new PapayaMessageLog(
          PapayaMessageLogable::GROUP_MODULES,
          PapayaMessage::TYPE_ERROR,
          sprintf('Can not include module file "%s"', $fileName)
        );
        $logMessage->context()->append(new PapayaMessageContextBacktrace());
        $this->papaya()->messages->dispatch($logMessage);
        return FALSE;
      }
      if (!class_exists($pluginData['class'], FALSE)) {
        $logMessage = new PapayaMessageLog(
          PapayaMessageLogable::GROUP_MODULES,
          PapayaMessage::TYPE_ERROR,
          sprintf('Can not find module class "%s"', $pluginData['class'])
        );
        $logMessage->context()->append(new PapayaMessageContextBacktrace());
        $this->papaya()->messages->dispatch($logMessage);
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * Creates and returns the plugin object. If a single instance is requested, the plugin is stored
  * in an internal list. A second call will return the stored object.
  *
  * @param array $pluginData
  * @param Object|NULL $parent
  * @param boolean $singleInstance
  * @return Object|NULL
  */
  private function createObject(array $pluginData, $parent, $singleInstance = FALSE) {
    if ($singleInstance &&
        isset($this->_instances[$pluginData['guid']])) {
      return $this->_instances[$pluginData['guid']];
    }
    $result = new $pluginData['class']($parent);
    if ($result instanceOf PapayaObjectInterface) {
      $result->papaya($this->papaya());
    }
    $result->guid = $pluginData['guid'];
    if ($singleInstance) {
      $this->_instances[$pluginData['guid']] = $result;
    }
    return $result;
  }

  /**
   * Return the base path all modules are in (in subdirectories)
   *
   * @return string
   */
  private function getModulePath() {
    return PapayaUtilFilePath::cleanup(
      $this->papaya()->options->getOption('PAPAYA_MODULES_PATH')
    );
  }
}
