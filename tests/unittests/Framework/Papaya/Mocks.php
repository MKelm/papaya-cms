<?php

class PapayaMocks {

  private $_testCase = NULL;

  public function __construct(PHPUnit_Framework_TestCase $testCase) {
    $this->_testCase = $testCase;
  }


  /*********************
   * $papaya
   ********************/

  public function application(array $objects = array()) {
    $testCase = $this->_testCase;
    $values = array();
    foreach ($objects as $identifier => $object) {
      $name = strToLower($identifier);
      $values[$name] = $object;
    }
    if (empty($values['options'])) {
      $values['options'] = $this->options();
    }
    if (empty($values['request'])) {
      $values['request'] = $this->request();
    }
    $testCase->{'context_application_objects'.spl_object_hash($this)} = $values;

    include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Application.php');
    $application = $testCase->getMock('PapayaApplication');
    $application
      ->expects($testCase->any())
      ->method('__isset')
      ->will($testCase->returnCallback(array($this, 'callbackApplicationHasObject')));
    $application
      ->expects($testCase->any())
      ->method('__get')
      ->will($testCase->returnCallback(array($this, 'callbackApplicationGetObject')));
    $application
      ->expects($testCase->any())
      ->method('hasObject')
      ->will($testCase->returnCallback(array($this, 'callbackApplicationHasObject')));
    $application
      ->expects($testCase->any())
      ->method('getObject')
      ->will($testCase->returnCallback(array($this, 'callbackApplicationGetObject')));
    return $application;
  }

  public function callbackApplicationHasObject($name, $className = '') {
    $testCase = $this->_testCase;
    $values = $testCase->{'context_application_objects'.spl_object_hash($this)};
    $name = strToLower($name);
    if (isset($values[$name])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public function callbackApplicationGetObject($name, $className = '') {
    $testCase = $this->_testCase;
    $values = $testCase->{'context_application_objects'.spl_object_hash($this)};
    $name = strToLower($name);
    if (isset($values[$name])) {
      return $values[$name];
    } elseif (!empty($className)) {
      PapayaApplication::includeClass($className);
      return new $className;
    }
  }

  /*********************
   * $papaya->options
   ********************/

  public function options(array $values = array(), array $tables = array()) {
    $testCase = $this->_testCase;
    $testCase->{'context_options_'.spl_object_hash($this)} = $values;
    $testCase->{'context_tables_'.spl_object_hash($this)} = $tables;

    include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Configuration.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Configuration/Cms.php');
    $options = $testCase
      ->getMockBuilder('PapayaConfigurationCms')
      ->disableOriginalConstructor()
      ->getMock();
    $options
      ->expects($testCase->any())
      ->method('get')
      ->will($testCase->returnCallback(array($this, 'callbackOptionsGet')));
    $options
      ->expects($testCase->any())
      ->method('getOption')
      ->will($testCase->returnCallback(array($this, 'callbackOptionsGet')));
    $options
      ->expects($testCase->any())
      ->method('getTableName')
      ->will($testCase->returnCallback(array($this, 'callbackOptionsGetTableName')));
    return $options;
  }

  public function callbackOptionsGet($name, $default = NULL) {
    $property = 'context_options_'.spl_object_hash($this);
    if (isset($this->_testCase->$property) &&
        is_array($this->_testCase->$property)) {
      $values = $this->_testCase->$property;
      if (isset($values[$name])) {
        return $values[$name];
      }
    }
    return $default;
  }

  public function callbackOptionsGetTableName($name, $usePrefix = TRUE) {
    $property = 'context_options_tables_'.spl_object_hash($this);
    $values = $this->_testCase->$property;
    if ($usePrefix && isset($values['papaya_'.$name])) {
      return $values['papaya_'.$name];
    } elseif (!$usePrefix && isset($values[$name])) {
      return $values[$name];
    } elseif ($usePrefix) {
      return 'papaya_'.$name;
    } else {
      return $name;
    }
  }


  /*********************
   * $papaya->request
   ********************/

  public function request(
    array $parameters = array(), $url = 'http://www.test.tld/test.html', $separator = '[]'
  ) {
    $testCase = $this->_testCase;
    $property = 'context_request_parameters_'.spl_object_hash($this);

    include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Url.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request.php');
    include_once(PAPAYA_INCLUDE_PATH.'system/Papaya/Request/Parameters.php');

    $testCase->$property = new PapayaRequestParameters();
    $testCase->$property->merge($parameters);

    $request = $testCase->getMock('PapayaRequest');
    $request
      ->expects($testCase->any())
      ->method('getUrl')
      ->will($testCase->returnValue(new PapayaUrl($url)));
    $request
      ->expects($testCase->any())
      ->method('getParameterGroupSeparator')
      ->will($testCase->returnValue($separator));
    $request
      ->expects($testCase->any())
      ->method('getBasePath')
      ->will($testCase->returnValue('/'));
    $request
      ->expects($testCase->any())
      ->method('setParameterGroupSeparator')
      ->will($testCase->returnValue($request));
    $request
      ->expects($testCase->any())
      ->method('getParameter')
      ->will($testCase->returnCallback(array($this, 'callbackRequestGetParameter')));
    $request
      ->expects($testCase->any())
      ->method('getParameters')
      ->will($testCase->returnCallback(array($this, 'callbackRequestGetParameters')));
    $request
      ->expects($testCase->any())
      ->method('getParameterGroup')
      ->will($testCase->returnCallback(array($this, 'callbackRequestGetParameterGroup')));
    $request
      ->expects($testCase->any())
      ->method('getMethod')
      ->will($testCase->returnValue('get'));
    return $request;
  }

  public function callbackRequestGetParameter($name, $default = '') {
    $property = 'context_request_parameters_'.spl_object_hash($this);
    return $this->_testCase->$property->get($name, $default);
  }

  public function callbackRequestGetParameters() {
    $property = 'context_request_parameters_'.spl_object_hash($this);
    return $this->_testCase->$property;
  }

  public function callbackRequestGetParameterGroup($name) {
    $property = 'context_request_parameters_'.spl_object_hash($this);
    return $this->_testCase->$property->getGroup($name);
  }

}