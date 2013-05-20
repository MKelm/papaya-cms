<?php
/**
* Implementation of XSL-processing
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage XSLT
* @version $Id: sys_xsl.php 37103 2012-06-06 09:09:45Z weinert $
*/

/**
* Used library
*/
require_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');

/**
* XML/XSL transformation controller
*
* @package Papaya-Library
* @subpackage XSLT
*/
class sys_xsl extends PapayaObject {
  /**
  * XML data
  *
  * @var string $xmlData
  */
  var $xmlData = "";
  /**
  * XSL-stylesheet-file
  *
  * @var string $xslFileName
  */
  var $xslFileName = "";

  /**
  * Template Parameter
  *
  * @var array $param
  */
  var $params = array();

  /**
  * Template values
  */
  var $values = NULL;

  /**
  * Output string
  *
  * @var string $result
  */
  var $result = "";

  /**
  * Parsing start timestamp (for simple performance debugs)
  *
  * @var integer $timeStart
  */
  var $timeStart = 0;

  /**
  * Available XSLT-extensions
  *
  * @var array $extensions
  */
  var $extensions = array();

  /**
  * Preferred XSLT-Extension
  *
  * @var array $preferred
  */
  var $preferred = 'xslcache';

  /**
  * Constructor
  *
  * @param string $xslFileName XSL-file optional, default ""
  * @access public
  */
  function __construct($xslFileName = "") {
    $this->xslFileName = $xslFileName;
  }

  /**
  * Combined getter/setter for the template values object
  *
  * @param unknown_type $values
  */
  public function values($values = NULL) {
    if (isset($values)) {
      $this->_values = $values;
    } elseif (is_null($this->_values)) {
      $this->_values = new PapayaTemplateValues();
    }
    return $this->_values;
  }

  /**
  * Set template values from xml string
  *
  * @param $xml
  */
  public function setXml($xml) {
    $errors = new PapayaXmlErrors();
    $errors->activate();
    try {
      $loaded = $this->values()->document()->loadXml($xml);
      $errors->omit();
      $errors->deactivate();
    } catch (PapayaXmlException $e) {
      $message = new PapayaMessageLog(
        PapayaMessageLogable::GROUP_SYSTEM,
        PapayaMessage::TYPE_ERROR,
        $e->getMessage()
      );
      $message->context()->append(
        new PapayaMessageContextText($xml)
      );
      $this->papaya()->messages->dispatch($message);
    }
    return $loaded;
  }

  public function setParam($name, $value) {
    $this->params[strtoupper($name)] = $value;
  }

  /**
  * Check extension
  *
  * @access public
  * @return boolean
  */
  function checkExtensions() {
    unset($this->extensions);
    if (extension_loaded('xslcache')) {
      $this->extensions['xslcache'] = TRUE;
    }
    if (extension_loaded('xsl')) {
      $this->extensions['xsl'] = TRUE;
    }
  }

  /**
  * Parse data
  *
  * @param boolean $output = Direkt ausgeben
  * @access public
  * @return string parsed $result or message
  */
  function parse($output = FALSE) {
    $this->preferred = $this
      ->papaya()->options->getOption('PAPAYA_XSLT_EXTENSION', $this->preferred);
    unset($this->lastError);
    if ($this->papaya()->options->getOption('PAPAYA_LOG_RUNTIME_TEMPLATE', FALSE)) {
      $this->timeStart = microtime();
    }
    $this->checkExtensions();
    $engine = new PapayaTemplateEngineXsl();
    $engine->setTemplateFile($this->xslFileName);
    $engine->useCache($this->preferred != 'xsl');
    $engine->parameters['SYSTEM_TIME'] = date('Y-m-d H:i:s');
    $engine->parameters['SYSTEM_TIME_OFFSET'] = date('O');
    if (defined('PAPAYA_VERSION_STRING')) {
      $engine->parameters['PAPAYA_VERSION'] = PAPAYA_VERSION_STRING;
    }
    foreach ($this->params as $name => $value) {
      $engine->parameters[$name] = $value;
    }
    $engine->values($this->values()->document());
    try {
      $engine->prepare();
      $this->outputRuntimeDebug('Prepared XSLT file "%s"');
      $engine->run();
      $this->outputRuntimeDebug('Processed XSLT file "%s"');
      return $this->result = $engine->getResult();
    } catch (PapayaXmlException $e) {
      $message = new PapayaMessageLog(
        PapayaMessageLogable::GROUP_SYSTEM,
        PapayaMessage::TYPE_ERROR,
        $e->getMessage()
      );
      if ($e->getContextFile()) {
        $message->context()->append(
          new PapayaMessageContextFile(
            $e->getContextFile(), $e->getContextLine(), $e->getContextColumn()
          )
        );
      }
      $this->papaya()->messages->dispatch($message);
    }
    return '';
  }

  /**
  * Output runtime debug message
  *
  * @param string $msg
  * @param boolean $reset
  * @return void
  */
  function outputRuntimeDebug($msg) {
    if ($this->papaya()->options->getOption('PAPAYA_LOG_RUNTIME_TEMPLATE', FALSE)) {
      $timeEnd = microtime();
      $root = PapayaUtilFilePath::getDocumentRoot();
      if (!empty($root) &&
          0 === strpos($this->xslFileName, $root)) {
        $xslFile = '~'.substr($this->xslFileName, strlen($root));
      } else {
        $xslFile = $this->xslFileName;
      }
      $runtime = new PapayaMessageLog(
        PapayaMessageLogable::GROUP_DEBUG,
        PapayaMessage::TYPE_DEBUG,
        sprintf(
          $msg,
          $xslFile
        )
      );
      $runtime
        ->context()
        ->append(new PapayaMessageContextRuntime($this->timeStart, $timeEnd));
      $this->papaya()->messages->dispatch($runtime);
      $this->timeStart = microtime();
    }
  }
}
?>