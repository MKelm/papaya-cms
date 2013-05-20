<?php
/**
* PDF output filter
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link      http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-PDF
* @version $Id: filter_pdf.php 37965 2013-01-16 11:14:29Z weinert $
*/

/**
* Basic class output filters
*/
require_once (PAPAYA_INCLUDE_PATH.'system/base_outputfilter.php');

/**
* PDF output filter
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class filter_pdf extends base_outputfilter {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'xslfile' => array('XSLT Template', 'isFile', TRUE, 'filecombo',
      array('callback:getTemplatePath', '/^\w+\.xsl$/i'), '', ''),
    'timelimit' => array('Time limit (seconds)', 'isNum', TRUE, 'input', 3, '', 30),
    'memorylimit' => array('Memory Limit', '(^\d+\s*\w{0,2}$)', FALSE, 'input', 10, '', ''),
    'fullpage' => array('Full page', 'isNum', TRUE, 'yesno', '', '', 1),
    'link_outputmode' => array(
      'Link output mode', 'isAlphaNum', TRUE, 'function', 'callbackOutputModes'
    )
  );

  /**
  * List of module options
  * @var array
  */
  var $pluginOptionFields = array(
    'TMP_PATH' => array(
      'Temporary path', 'isPath', TRUE, 'input', '400', '', '/tmp/'
    ),
  );

  /**
  * default template subdirectory
  * @var string
  */
  var $templatePath = 'pdf';

  /**
  * Error message
  * @var string $errorMessage
  */
  var $errorMessage = '';

  /**
  * papaya_pdf object
  * @var papaya_pdf $pdf
  */
  var $pdf = NULL;

  /**
  * Output charset
  * @var string $outputCharset
  */
  var $outputCharset = 'UTF-8';

  /**
  * Bad strings
  * @var array $badStrings
  */
  var $badStrings = array('&amp;nbsp;', '&amp;ndash;', '&amp;bdquo;',
    '&amp;ldquo;', '&amp;hellip;', '&amp;lsquo;', '&amp;rsquo;');
  /**
  * Bad string replacements
  * @var array $badStringRepl
  */
  var $badStringRepl = array(' ', '-', ',,', '"', '...', ",", "'");

  /**
  * temporary files (for image conversion)
  * @var array $tmpFiles;
  */
  var $tmpFiles = array();

  private $_processLimits = array();

  /**
  * Parse page
  *
  * @see FPDF::Output()
  *
  * @param base_topic &$topic
  * @param sys_xsl &$layout
  * @access public
  * @return string
  */
  function parsePage(&$topic, &$layout) {
    $this->setUpLimits();

    $xslFile = $this->getTemplatePath().$this->data['xslfile'];

    $layout->setXSL($xslFile);
    $output = papaya_strings::ensureUTF8(
      str_replace($this->badStrings, $this->badStringRepl, $layout->xhtml())
    );

    //dump the transformed xml
    if (isset($_GET['dump'])) {
      header('Content-type: text/xml');
      switch ($_GET['dump']) {
      case 'pdf' :
        echo $output;
        break;
      case 'xml':
        echo $layout->xml();
        break;
      }
      $this->tearDownLimits();
      exit();
    }

    if (class_exists('DOMDocument', FALSE)) {
      $xmlTree = new DOMDocument('1.0', 'UTF-8');
    } else {
      $xmlTree = &simple_xmltree::create();
    }
    $xmlTree->loadXML($output);

    include_once(dirname(__FILE__).'/papaya_pdf_document.php');

    $pdfDoc = new papaya_pdf_document();
    $pdfDoc->pathDataFiles = $this->getTemplatePath();
    $pdfDoc->callbackImage = array(&$this, 'getImageForPDF');
    $pdfDoc->callbackLink = array(&$this, 'getLinkForPDF');

    $docTitle = $this->getWebLink(
      $topic->topicId,
      $topic->getContentLanguageId(),
      NULL,
      NULL,
      NULL,
      $topic->topic['TRANSLATION']['topic_title']
    );

    $result = $pdfDoc->get($xmlTree, $docTitle);
    foreach ($this->tmpFiles as $fileName) {
      if (file_exists($fileName)) {
        @unlink($fileName);
      }
    }
    $this->tmpFiles = array();
    $this->tearDownLimits();
    return $result;
  }

  /**
  * Parse xml
  *
  * @see FPDF::Output()
  *
  * @param sys_xsl &$layout
  * @access public
  * @return string
  */
  function parseXml($layout) {
    $this->setUpLimits();
    $xslFile = $this->getTemplatePath().$this->data['xslfile'];
    $layout->setXSL($xslFile);
    $output = papaya_strings::ensureUTF8(
      str_replace($this->badStrings, $this->badStringRepl, $layout->xhtml())
    );
    $xmlTree = new DOMDocument('1.0', 'UTF-8');
    $xmlTree->loadXML($output);

    include_once(dirname(__FILE__).'/papaya_pdf_document.php');
    $pdfDoc = new papaya_pdf_document();
    $pdfDoc->pathDataFiles = $this->getTemplatePath();
    $pdfDoc->callbackImage = array($this, 'getImageForPDF');
    $pdfDoc->callbackLink = array($this, 'getLinkForPDF');
    $result = $pdfDoc->get($xmlTree);
    foreach ($this->tmpFiles as $fileName) {
      if (file_exists($fileName)) {
        @unlink($fileName);
      }
    }
    $this->tmpFiles = array();
    $this->tearDownLimits();
    return $result;
  }

  /**
  * Parse box
  *
  * @param base_topic $topic
  * @param array $box box database record
  * @param string $xmlString box output xml
  * @access public
  * @return string|FALSE
  */
  function parseBox($topic, $box, $xmlString) {
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_xsl.php');
    $xsl = new papaya_xsl($this->getTemplatePath().$this->data['xslfile']);
    $xsl->setParam('PAGE_TODAY', date('Y-m-d H:i:s'));
    if (defined('PAPAYA_WEBSITE_REVISION') && trim(PAPAYA_WEBSITE_REVISION) != '') {
      $xsl->setParam('PAPAYA_WEBSITE_REVISION', PAPAYA_WEBSITE_REVISION);
    }
    $themeHandler = new PapayaThemeHandler();
    $options = $this->papaya()->options;
    $xsl->setParam('PAGE_THEME', $themeHandler->getTheme());
    $xsl->setParam('PAGE_THEME_SET', $themeHandler->getThemeSet());
    $xsl->setParam('PAGE_THEME_PATH', $themeHandler->getUrl());
    $xsl->setParam('PAGE_THEME_PATH_LOCAL', $themeHandler->getLocalThemePath());
    $xsl->setParam('PAGE_WEB_PATH', $options->get('PAPAYA_PATH_WEB', '/'));
    if (isset($topic) && isset($topic->currentLanguage['lng_short'])) {
      $xsl->setParam('PAGE_LANGUAGE', $topic->currentLanguage['lng_short']);
    }
    $xsl->setParam('PAGE_URL_LEVEL_SEPARATOR', $options->get('PAPAYA_URL_LEVEL_SEPARATOR', ''));
    $xsl->setParam('PAPAYA_DBG_DEVMODE', $options->get('PAPAYA_DBG_DEVMODE', FALSE));
    $xsl->setParam(
      'PAPAYA_DEBUG_LANGUAGE_PHRASES', $options->get('PAPAYA_DEBUG_LANGUAGE_PHRASES', FALSE)
    );

    if (FALSE === strpos($xmlString, '<?xml')) {
      if (defined('PAPAYA_LATIN1_COMPATIBILITY') && PAPAYA_LATIN1_COMPATIBILITY) {
        include_once(PAPAYA_INCLUDE_PATH.'system/papaya_strings.php');
        $xmlString = papaya_strings::ensureUTF8($xmlString);
      }
      $xsl->setXml(
        '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.papaya_strings::entityToXML($xmlString)
      );
    } else {
      $xsl->setXml(papaya_strings::entityToXML($xmlString));
    }
    if (!($result = $xsl->parse(FALSE, TRUE))) {
      $this->errorMessage = $xsl->getLastErrorHTML();
      return FALSE;
    } else {
      return preg_replace('(<\\?xml[^>]+\\?>)', '', $result);
    }
  }

  /**
  * Check configuration
  *
  * @param boolean $page optional, default value TRUE
  * @access public
  * @return boolean
  */
  function checkConfiguration($page = TRUE) {
    if (empty($this->data['xslfile'])) {
      $this->errorMessage = 'XSLT Template not set.';
      return FALSE;
    } else {
      $xslFile = $this->getTemplatePath().$this->data['xslfile'];
      if (!(is_file($xslFile) && is_readable($xslFile))) {
        $this->errorMessage = 'XSLT Template not readable.';
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
  * Get image for PDF
  *
  * @param array $imgAttr image attributes
  * @access public
  * @return string|NULL image string
  */
  function getImageForPDF($imgAttr) {
    $imgURL = empty($imgAttr['src']) ? '' : $imgAttr['src'];
    $pSid = '[a-zA-Z\d,-]{20,40}';
    $pImage = '~/?(sid([a-z]*?('.$pSid.')))?
      (([a-z\d_-]+/)*)
      ([a-z\d_-]+)\.(image)(\.(gif|jpg|png))?(\?[^#]*)?~ix';
    $pMedia = '~^/?(sid([a-z]*?('.$pSid.')))?
      (([a-z\d_-]+/)*)
      ([a-z\d_-]+\.(media|thumb)(\.(preview))?
      (\.(([a-z\d_]+)(\.([a-z\d]+))?)))~ix';
    if (empty($imgURL)) {
      $localFileName = '';
    } elseif (preg_match($pImage, $imgURL, $regs)) {
      $localFileName = $this->getDynamicImage($imgAttr, $regs);
    } elseif (preg_match($pMedia, $imgURL, $regs)) {
      $localFileName = $this->getMediaImage($imgAttr, $regs);
    } else {
      $localFileName = $this->getStaticImage($imgAttr);
    }
    if ((!empty($localFileName)) &&
        file_exists($localFileName) &&
        is_readable($localFileName)) {
      $imgData = getimagesize($localFileName);
      switch ($imgData[2]) {
      case IMAGETYPE_GIF :
      case IMAGETYPE_PNG :
        // gif/png - convert to jpeg
        $formatSrc = ($imgData[2] == IMG_GIF) ? 'gif' : 'png';
        $localFileName = $this->getConvertedFile($localFileName, $formatSrc);
        break;
      case IMAGETYPE_JPEG :
        //jpeg - no change
        break;
      default :
        //unknown image type - ignore
        return NULL;
      }
      return $localFileName;
    }
    return NULL;
  }

  /**
  * Convert a local file to a temporary jpeg file
  *
  * @param string $localFileName
  * @param string $formatSrc png or gif
  * @access public
  * @return string temporary jpeg file
  */
  function getConvertedFile($localFileName, $formatSrc) {
    static $tmpPath;

    if (!isset($tmpPath)) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_module_options.php');
      $tmpPath = base_module_options::readOption(
        $this->guid,
        'TMP_PATH',
        '/tmp/'
      );
      if (is_dir($tmpPath) && is_writeable($tmpPath)) {
        $tmpPath = substr($tmpPath, 0, -1);
      } else {
        $tmpPath = '/tmp';
      }
    }

    //gif or png - convert it to a jpeg
    include_once(PAPAYA_INCLUDE_PATH.'system/papaya_imageconvert.php');
    if ($converter = papaya_imageconvert::getConverter($localFileName)) {
      $converter->initialize();
      //converter found
      if ($converter->canConvert($formatSrc, 'jpg')) {
        //can convert this file formats

        if ($tmpName = tempnam('/tmp', 'papayapdfimage')) {
          //got a tmp name
          //maximum quality for jpeg
          $this->jpegQuality = 100;
          if ($converter->convert($localFileName, $tmpName, 'jpg', 100)) {
            $this->tmpFiles[] = $tmpName;
            return $tmpName;
          }
        }
      }
    }
    return NULL;
  }

  /**
  * Get dynamic image (papaya image generator)
  *
  * @param array $imgAttr
  * @param array $matchData
  * @access public
  * @return string local filename
  */
  function getDynamicImage($imgAttr, $matchData) {
    if (!(isset($this->imgGenerator) && is_object($this->imgGenerator))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_imagegenerator.php');
      $this->imgGenerator = new base_imagegenerator();
    }
    $queryString = isset($matchData[10]) ? $matchData[10] : '';
    $queryString = ltrim($queryString, '?');
    $imageIndent = isset($matchData[6]) ? $matchData[6] : '';
    $query = new PapayaRequestParametersQuery('');
    $params = $query->setString($queryString)->values()->toArray();
    $imgParams = empty($params['img']) ? array() : $params['img'];
    if ($this->imgGenerator->loadByIdent($imageIndent)) {
      if ($imageData = $this->imgGenerator->generateImage(FALSE, $imgParams, IMAGETYPE_JPEG)) {
        if ($tmpName = tempnam('/tmp', 'papayapdfimage')) {
          if ($fh = fopen($tmpName, 'w')) {
            fwrite($fh, $imageData);
            $this->tmpFiles[] = $tmpName;
            return $tmpName;
          }
        }
      }
    }
    return NULL;
  }

  /**hmic
  * get image or thumbnail from media database
  *
  * @param array $imgAttr
  * @param array $matchData
  * @access public
  * @return string local filename
  */
  function getMediaImage($imgAttr, $matchData) {
    static $mediaDB;
    $fileName = $matchData[11];
    $subPath = '';
    for ($i = 0; $i < PAPAYA_MEDIADB_SUBDIRECTORIES; $i++) {
      $subPath .= $fileName[$i].'/';
    }
    $localFile = $subPath.$fileName;
    switch ($matchData[7]) {
    case 'thumb':
      if (file_exists(PAPAYA_PATH_THUMBFILES.$localFile)) {
        return PAPAYA_PATH_THUMBFILES.$localFile;
      }
    default :
      $path = PAPAYA_PATH_MEDIAFILES;
      $fileId = substr($matchData[12], 0, 32);
      $fileVersion = (int)substr($matchData[12], 33);
      if (empty($mediaDB)) {
        include_once(PAPAYA_INCLUDE_PATH.'system/base_mediadb.php');
        $mediaDB = &base_mediadb::getInstance();
      }
      if ($fileData = $mediaDB->getFile($fileId, $fileVersion)) {
        $localFile = $fileData['FILENAME'];
        return $localFile;
      }
    }
    return NULL;
  }

  /**
  * get static image
  *
  * @param array $imgAttr
  * @access public
  * @return string local filename
  */
  function getStaticImage($imgAttr) {
    if (empty($imgAttr['src'])) {
      return '';
    }
    //direct image urls
    $fileName = NULL;
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
      $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
      if (substr($documentRoot, -1) == '/') {
        $documentRoot = substr($documentRoot, 0, -1);
      }
    } else {
      $documentRoot = '';
    }

    if (substr($imgAttr['src'], 0, 1) == '/') {
      //in document root
      $fileName = $documentRoot.$imgAttr['src'];
    } elseif (preg_match('(^https?://)i', $imgAttr['src'])) {
      //cdn?
      if (strpos($imgAttr['src'], '?') !== FALSE) {
        $fileName = substr($imgAttr['src'], 0, strpos($imgAttr['src'], '?'));
      } else {
        $fileName = $imgAttr['src'];
      }
      if (defined('PAPAYA_CDN_THEMES') && PAPAYA_CDN_THEMES != '' &&
          0 === strpos($imgAttr['src'], PAPAYA_CDN_THEMES)) {
        $fileName = str_replace(
          '//',
          '/',
          PAPAYA_PATH_THEMES_LOCAL.substr($fileName, strlen(PAPAYA_CDN_THEMES))
        );
      } else {
        $protocol = PapayaUtilServerProtocol::get();
        $url = $protocol.'://'.$_SERVER['HTTP_HOST'];
        if (0 === strpos($fileName, $url)) {
          $fileName = $documentRoot.substr($fileName, strlen($url));
        }
      }
    } else {
      $fileName = $this->getBasePath(TRUE).$imgAttr['src'];
    }
    return $fileName;
  }

  /**
  * get an external image
  *
  * not implemented yet
  *
  * @access public
  * @return string local filename
  */
  function getExternalImage() {

  }

  /**
  * check link href and return the target url
  *
  * only absolute links are support so far
  *
  * @param array $linkAttr
  * @access public
  * @return string|NULL
  */
  function getLinkForPdf($linkAttr) {
    if (!empty($linkAttr['href'])) {
      if (substr($linkAttr['href'], 0, 1) != '#') {
        if (checkit::isHTTPX($linkAttr['href'], TRUE)) {
          return $linkAttr['href'];
        } elseif (substr(strtolower($linkAttr['href']), 0, 7) == 'mailto:') {
          return $linkAttr['href'];
        } elseif (!preg_match('(^\w+:)', $linkAttr['href'])) {
          return $this->getAbsoluteURL($linkAttr['href'], NULL, FALSE);
        }
      }
    }
    return NULL;
  }

  /**
  * Get XHTML for special edit field showing all currunt output modes in a selectbox
  *
  * @param string $name
  * @param array $field
  * @param string $data
  */
  function callbackOutputModes($name, $field, $data) {
    $result = sprintf(
      '<select name="%s[%s]" class="dialogSelect dialogScale">',
      papaya_strings::escapeHTMLChars($this->paramName),
      papaya_strings::escapeHTMLChars($name)
    );
    if (empty($data)) {
      $data = $this->parentObj->viewLink['viewmode_ext'];
    }
    include_once(PAPAYA_INCLUDE_PATH.'system/base_viewlist.php');
    $viewList = new base_viewlist();
    $viewList->loadViewModesList();
    if (isset($viewList->viewModes) && is_array($viewList->viewModes)) {
      foreach ($viewList->viewModes as $viewMode) {
        $selected = ($viewMode['viewmode_ext'] == $data) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<option value="%s"%s>%s</option>',
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext']),
          $selected,
          papaya_strings::escapeHTMLChars($viewMode['viewmode_ext'])
        );
      }
    }
    $result .= '</select>';
    return $result;
  }

  public function setUpLimits() {
    if (isset($this->data['timelimit'])) {
      set_time_limit($this->data['timelimit']);
    }
    if (isset($this->data['memorylimit'])) {
      $neededMemory = PapayaUtilBytes::fromString($this->data['memorylimit']);
      if ($neededMemory > 0) {
        if ($memoryLimit = @ini_get('memory_limit')) {
          $this->_processLimits['memory'] = PapayaUtilBytes::fromString($memoryLimit);
        }
        if (extension_loaded('suhosin')) {
          $suhosinMemoryLimit = PapayaUtilBytes::fromString(@ini_get('suhosin.memory_limit'));
          if ($suhosinMemoryLimit > 0 && $suhosinMemoryLimit < $neededMemory) {
            @ini_set('memory_limit', $suhosinMemoryLimit);
          } elseif ($suhosinMemoryLimit > 0) {
            @ini_set('memory_limit', $neededMemory);
          }
        } else {
          @ini_set('memory_limit', $neededMemory);
        }
      }
    }
  }

  public function tearDownLimits() {
    if (isset($this->_processLimits['memory'])) {
      @ini_set('memory_limit', $this->_processLimits['memory']);
    }
  }
}