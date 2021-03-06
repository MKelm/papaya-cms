<?php
/**
* Xsl template engine, uses ext/xsl or ext/xslcache
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
* @subpackage Template
* @version $Id: Xsl.php 37657 2012-11-09 14:11:15Z weinert $
*/

/**
* Xsl template engine, uses ext/xsl or ext/xslcache
*
* @property PapayaObjectOptionsList $parameters
* @property PapayaObjectList $loaders
* @property DOMDocument $values
*
* @package Papaya-Library
* @subpackage Template
*/
class PapayaTemplateEngineXsl extends PapayaTemplateEngine {

  /**
  * Transformation result buffer
  * @var string
  */
  private $_result = '';

  /**
  * Transformation xslt template file
  * @var string
  */
  private $_templateFile = '';

  /**
  * Allow to use ext/xslcache e.g. cached xslt bytecode
  * @var boolean
  */
  private $_useCache = TRUE;

  /**
  * Xslt processor
  * @var XsltCache|XsltProcessor
  */
  private $_processor = NULL;

  /**
  * Error handling wrapper for lixml/libxslt errors
  *
  * @var PapayaXmlErrors
  */
  private $_errorHandler = NULL;


  public function setTemplateString($string) {
    $this->_template = $string;
    $this->_templateFile = FALSE;
    $this->useCache(FALSE);
  }

  /**
  * Set the xsl file for the transformation, throw an exception it it is not readable
  *
  * @throws InvalidArgumentException
  * @param string $fileName
  */
  public function setTemplateFile($fileName) {
    if (file_exists($fileName) &&
        is_file($fileName) &&
        is_readable($fileName)) {
      $this->_templateFile = $fileName;
    } else {
      throw new InvalidArgumentException(
        sprintf(
          'File "%s" not found or not readable.', $fileName
        )
      );
    }
  }

  /**
  * Use the xslcache extension if possible or
  * enable/disable the caching if the processor is already created.
  *
  * The function will return TRUE if the cache will be used.
  *
  * @param boolean|NULL $use
  * @return boolean
  */
  public function useCache($use = NULL) {
    if (!is_null($use)) {
      if ($use && class_exists('XsltCache', FALSE)) {
        $this->_useCache = TRUE;
      } else {
        $this->_useCache = FALSE;
      }
    }
    if (($this->_useCache && $this->_processor instanceof XsltProcessor) ||
        (!$this->_useCache && $this->_processor instanceof XsltCache)) {
      $this->_processor = NULL;
    }
    return $this->_useCache;
  }

  /**
  * Set the xslt processor object
  *
  * @throws InvalidArgumentException
  * @param XsltCache|XsltProcessor $processor
  */
  public function setProcessor($processor) {
    if ($processor instanceof XsltProcessor ||
        $processor instanceof XsltCache) {
      $this->_processor = $processor;
    } else {
      throw new InvalidArgumentException(
        sprintf(
          'Expecting instance of XsltProcessor or XsltCache: "%s" given.',
          is_object($processor) ? get_class($processor) : gettype($processor)
        )
      );
    }
  }

  /**
  * Get the xslt processor object
  *
  * @param string $xslFileName
  * @return XsltCache|XsltProcessor
  */
  public function getProcessor() {
    if (is_null($this->_processor)) {
      if ($this->_useCache &&
          class_exists('XsltCache', FALSE)) {
        $this->_processor = new XsltCache();
      } else {
        $this->_processor = new XsltProcessor();
      }
    }
    return $this->_processor;
  }

  /**
  * Set libxml errors handler
  *
  * @param PapayaXmlErrors $errorHandler
  */
  public function setErrorHandler(PapayaXmlErrors $errorHandler) {
    $this->_errorHandler = $errorHandler;
  }

  /**
  * Set libxml errors handler
  *
  * @param PapayaXmlErrors $errorHandler
  */
  public function getErrorHandler() {
    if (is_null($this->_errorHandler)) {
      $this->_errorHandler = new PapayaXmlErrors();
    }
    return $this->_errorHandler;
  }

  /**
  * Load xsl file into processor
  *
  * @throws PapayaXmlException
  * @return TRUE
  */
  public function prepare() {
    $errors = $this->getErrorHandler();
    $errors->activate();
    if (!$this->_templateFile) {
      $this->useCache(FALSE);
    }
    $processor = $this->getProcessor();
    if ($processor instanceof XsltCache) {
      $processor->importStylesheet($this->_templateFile, $this->_useCache);
    } elseif ($this->_templateFile) {
      $xslDom = new DOMDocument('1.0', 'UTF-8');
      $xslDom->load($this->_templateFile);
      $processor->importStylesheet($xslDom);
      unset($xslDom);
    } else {
      $xslDom = new DOMDocument('1.0', 'UTF-8');
      $xslDom->loadXml($this->_template);
      $processor->importStylesheet($xslDom);
      unset($xslDom);
    }
    $errors->omit();
    $errors->deactivate();
    return TRUE;
  }

  /**
  * Run template processing and set result.
  *
  * @return boolean
  */
  public function run() {
    $this->_result = '';
    $errors = $this->getErrorHandler();
    $errors->activate();
    foreach ($this->parameters as $name => $value) {
      $this->_processor->setParameter('', $name, $value);
    }
    try {
      $result = $this->_processor->transformToXML(
        ($context = $this->getContext()) ? $context : $this->values
      );
      $errors->omit();
      $this->_result = $result;
      $errors->deactivate();
      return TRUE;
    } catch (Exception $e) {
      $errors->omit();
      $errors->deactivate();
      return FALSE;
    }
  }

  /**
  * Get processing result
  *
  * @return string
  */
  public function getResult() {
    return $this->_result;
  }
}