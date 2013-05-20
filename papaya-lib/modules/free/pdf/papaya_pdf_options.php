<?php
/**
* Options for PDF output
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @version $Id: papaya_pdf_options.php 34957 2010-10-05 15:57:41Z weinert $
*/

/**
* Options for PDF output
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf_options {

  /**
  * local path for binary files (pdf background templates, fonts, ...)
  * @var string
  */
  var $pathDataFiles = '';

  /**
  * owner object (pdf filter)
  * @var filter_pdf $owner
  */
  var $owner = NULL;

  /**
  * font definitions
  * @var array $fonts
  */
  var $fonts = array();

  /**
  * page definitions
  * @var array $pages
  */
  var $pages = array();

  /**
  * tag layout definitions
  * @var array $tags
  */
  var $tags = array();

  /**
  * page templates
  * @var array $templates
  */
  var $templates = array();

  /**
  * header data
  * @var DOMNode|expat_node $headerNode
  */
  var $headerNode = NULL;

  /**
  * footer data
  * @var DOMNode|expat_node $footerNode
  */
  var $footerNode = NULL;

  /**
  * default margins
  * @var array $defaultMargin
  */
  var $defaultMargin = array(
    'top' => 0,
    'right' => 0,
    'bottom' => 0,
    'left' => 0
  );
  /**
  * default font family and size
  * @var array $defaultFont
  */
  var $defaultFont = array(
    'family' => 'Times',
    'size' => 10,
    'bold' => FALSE,
    'italic' => FALSE
  );
  /**
  * default content alignment
  * @var string $defaultAlign
  */
  var $defaultAlign = 'left';

  /**
  * default foreground color for texts
  * @var string
  */
  var $defaultFGColor = '#000';

  /**
  * initialize configuration data
  *
  * @param filter_pdf $owner
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function initialize(&$owner, &$data) {
    $this->owner = &$owner;
    if (is_object($data) && $data->hasChildNodes()) {
      for ($idx = 0; $idx < $data->childNodes->length; $idx++) {
        $childNode = &$data->childNodes->item($idx);
        if (isset($childNode->nodeName) && $childNode->nodeType == XML_ELEMENT_NODE) {
          switch ($childNode->nodeName) {
          case 'templates':
            $this->addTemplates($childNode);
            break;
          case 'fonts':
            $this->addFonts($childNode);
            break;
          case 'pages':
            $this->addPages($childNode);
            break;
          case 'tags':
            $this->addTags($childNode);
            break;
          }
        }
      }
    }
  }

  /**
  * add page background templates
  *
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addTemplates(&$data) {
    if (is_object($data) && $data->hasChildNodes()) {
      for ($idx = 0; $idx < $data->childNodes->length; $idx++) {
        $childNode = &$data->childNodes->item($idx);
        if (isset($childNode->nodeName) &&
            $childNode->nodeType == XML_ELEMENT_NODE &&
            $childNode->nodeName == 'template') {
          $this->addTemplate($childNode);
        }
      }
    }
  }

  /**
  * add a page background template
  *
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addTemplate(&$data) {
    if ($data->hasAttribute('name') && trim($data->getAttribute('name')) != '' &&
        $data->hasAttribute('file') &&
        ($fileName = $data->getAttribute('file')) &&
        file_exists($this->pathDataFiles.$fileName) &&
        is_file($this->pathDataFiles.$fileName) &&
        is_readable($this->pathDataFiles.$fileName)) {
      if ($data->hasAttribute('page-no')) {
        $pageNo = (int)$data->getAttribute('page-no');
      } elseif ($data->hasAttribute('page')) {
        $pageNo = (int)$data->getAttribute('page');
      } else {
        $pageNo = 1;
      }
      $this->templates[$data->getAttribute('name')] = array(
        'file' => $this->pathDataFiles.$fileName,
        'page' => $pageNo,
        'index' => -1
      );
    }
  }

  /**
  * add fonts
  *
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addFonts(&$data) {
    if (is_object($data) && $data->hasChildNodes()) {
      for ($idx = 0; $idx < $data->childNodes->length; $idx++) {
        $childNode = &$data->childNodes->item($idx);
        if ($childNode->nodeType == XML_ELEMENT_NODE &&
            $childNode->nodeName == 'font') {
          $this->addFont($childNode);
        }
      }
    }
  }

  /**
  * add font
  *
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addFont(&$data) {
    $font = array();
    if ($data->hasAttribute('name') &&
        ($fontName = trim($data->getAttribute('name'))) != '' &&
        $this->addFontType($font, $data)) {
      $this->addFontType($font, $data, 'bold');
      $this->addFontType($font, $data, 'italic');
      $this->addFontType($font, $data, 'bolditalic');
      $this->fonts[$fontName] = $font;
    }
  }

  /**
  * add font style (default | bold | italic | bold italic)
  *
  * @param array &$font
  * @param DOMNode|expat_node &$data
  * @param string $name style, default value 'default'
  * @access public
  * @return boolean
  */
  function addFontType(&$font, &$data, $name = 'default') {
    if ($data->hasAttribute($name) &&
        ($fileName = $data->getAttribute($name)) &&
        file_exists($this->pathDataFiles.$fileName) &&
        is_file($this->pathDataFiles.$fileName) &&
        is_readable($this->pathDataFiles.$fileName)) {
      $font[$name] = $this->pathDataFiles.$fileName;
      return TRUE;
    } elseif (isset($font['default']) && $name != 'default') {
      $font[$name] = $font['default'];
    } else {
      $font[$name] = '';
    }
    return FALSE;
  }

  /**
  * add page configurations
  *
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addPages(&$data) {
    if (is_object($data) && $data->hasChildNodes()) {
      $this->defaultFont = $this->parseFont(
        $data->hasAttribute('font') ? $data->getAttribute('font') : '',
        $this->defaultFont
      );
      $this->defaultMargin = $this->parseMargin(
        $data->hasAttribute('margin') ? $data->getAttribute('margin') : ''
      );
      $this->defaultAlign = $this->parseAlign(
        $data->hasAttribute('align') ? $data->getAttribute('align') : ''
      );
      for ($idx = 0; $idx < $data->childNodes->length; $idx++) {
        $childNode = &$data->childNodes->item($idx);
        if ($childNode->nodeType == XML_ELEMENT_NODE &&
            $childNode->nodeName == 'page') {
          $this->addPage($childNode);
        }
      }
    }
  }

  /**
  * add page configuration
  *
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addPage(&$data) {
    if ($data->hasAttribute('name') && trim($data->getAttribute('name')) != '') {
      $pageName = trim($data->getAttribute('name'));
      $page = array(
        'template' => NULL,
        'orientation' => 'vertical',
        'header' => FALSE,
        'footer' => FALSE,
        'mode' => 'columns'
      );
      if ($data->hasAttribute('template') && $data->getAttribute('template') != '') {
        $page['template'] = $data->getAttribute('template');
      }
      if ($data->hasAttribute('orientation') &&
          $data->getAttribute('orientation') == 'horizontal') {
        $page['orientation'] = 'horizontal';
      }
      $page['margin'] = $this->parseMargin(
        $data->hasAttribute('margin') ? $data->getAttribute('margin') : ''
      );
      $page['font'] = $this->parseFont(
        $data->hasAttribute('font') ? $data->getAttribute('font') : '',
        $this->defaultFont
      );
      $page['align'] = $this->parseAlign(
        $data->hasAttribute('align') ? $data->getAttribute('align') : ''
      );
      $page['fgcolor'] = $this->parseFGColor(
        $data->hasAttribute('fgcolor') ? $data->getAttribute('fgcolor') : ''
      );

      $mode = $data->hasAttribute('mode') ? $data->getAttribute('mode') : '';
      switch ($mode) {
      case 'absolute' :
      case 'elements' :
        $page['mode'] = 'elements';
        for ($idx = 0; $idx < $data->childNodes->length; $idx++) {
          $childNode = &$data->childNodes->item($idx);
          if ($childNode->nodeType == XML_ELEMENT_NODE) {
            switch ($childNode->nodeName) {
            case 'element':
              $this->addPageElement($page, $childNode);
              break;
            case 'header':
              $this->addPageHeader($page, $childNode);
              break;
            case 'footer':
              $this->addPageFooter($page, $childNode);
              break;
            }
          }
        }
        break;
      case 'columns' :
      case 'text' :
      default :
        $page['columns'] = array();
        for ($idx = 0; $idx < $data->childNodes->length; $idx++) {
          $childNode = &$data->childNodes->item($idx);
          if ($childNode->nodeType == XML_ELEMENT_NODE) {
            switch ($childNode->nodeName) {
            case 'column':
              $this->addPageColumn($page, $childNode);
              break;
            case 'header':
              $this->addPageHeader($page, $childNode);
              break;
            case 'footer':
              $this->addPageFooter($page, $childNode);
              break;
            }
          }
        }
        if (count($page['columns']) == 0) {
          $column = array(
            'margin' => $page['margin']
          );
          $page['columns'][1] = $column;
        }
        break;
      }
      $this->pages[$pageName] = $page;
    }
  }

  /**
  * add page element configuration
  *
  * @param array &$page
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addPageElement(&$page, &$data) {
    if (isset($page) && $data->hasAttribute('for') &&
        ($elementFor = $data->getAttribute('for')) != '') {
      $element = array(
        'margin' => $this->parseMargin(
          $data->hasAttribute('margin') ? $data->getAttribute('margin') : '',
          $page['margin']
        ),
        'font' => $this->parseFont(
          $data->hasAttribute('font') ? $data->getAttribute('font') : '',
          $page['font']
        ),
        'align' => $this->parseAlign(
          $data->hasAttribute('align') ? $data->getAttribute('align') : '',
          $page['align']
        ),
        'fgcolor' => $this->parseFGColor(
          $data->hasAttribute('fgcolor') ? $data->getAttribute('fgcolor') : '',
          $page['fgcolor']
        )
      );
      $page['elements'][$elementFor] = $element;
    }
  }

  /**
  * add page column configuration
  *
  * @param array &$page
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addPageColumn(&$page, &$data) {
    if (isset($page)) {
      $columnIdx = count($page['columns']) + 1;
      $column = array(
        'margin' => $this->parseMargin(
          $data->hasAttribute('margin') ? $data->getAttribute('margin') : '',
          $page['margin']
        )
      );
      $page['columns'][$columnIdx] = $column;
    }
  }

  /**
  * add page header configuration
  *
  * @param array &$page
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addPageHeader(&$page, &$data) {
    if (isset($page) && $data->hasAttribute('margin')) {
      $page['header'] = array(
        'margin' => $this->parseMargin(
          $data->hasAttribute('margin') ? $data->getAttribute('margin') : '',
          $page['margin']
        ),
        'font' => $this->parseFont(
          $data->hasAttribute('font') ? $data->getAttribute('font') : '',
          $this->defaultFont
        ),
        'align' => $this->parseAlign(
          $data->hasAttribute('align') ? $data->getAttribute('align') : '',
          $this->defaultAlign
        ),
        'fgcolor' => $this->parseFGColor(
          $data->hasAttribute('fgcolor') ? $data->getAttribute('fgcolor') : '',
          $this->defaultFGColor
        )
      );
    }
  }

  /**
  * add page footer configuration
  *
  * @param array &$page
  * @param DOMNode|expat_node &$data
  * @access public
  */
  function addPageFooter(&$page, &$data) {
    if (isset($page) && $data->hasAttribute('margin')) {
      $page['footer'] = array(
        'margin' => $this->parseMargin(
          $data->hasAttribute('margin') ? $data->getAttribute('margin') : '',
          $page['margin']
        ),
        'font' => $this->parseFont(
          $data->hasAttribute('font') ? $data->getAttribute('font') : '',
          $this->defaultFont
        ),
        'align' => $this->parseAlign(
          $data->hasAttribute('align') ? $data->getAttribute('align') : '',
          $this->defaultAlign),
        'fgcolor' => $this->parseFGColor(
          $data->hasAttribute('fgcolor') ? $data->getAttribute('fgcolor') : '',
          $this->defaultFGColor
        )
      );
    }
  }

  /**
  * Add all tag formatting options to configuration object
  * @param DOMNode $data
  * @return void
  */
  function addTags(&$data) {
    if (is_object($data) && $data->hasChildNodes()) {
      for ($idx = 0; $idx < $data->childNodes->length; $idx++) {
        $childNode = &$data->childNodes->item($idx);
        if ($childNode->nodeType == XML_ELEMENT_NODE &&
            $childNode->nodeName == 'tag') {
          $this->addTag($childNode);
        }
      }
    }
  }

  /**
  * Add a single tag formatting options to configuration object
  * @param DOMNode $data
  * @return void
  */
  function addTag(&$data) {
    if (is_object($data) && $data->hasAttribute('name')) {
      $this->tags[$data->getAttribute('name')] = array(
        'font' => $this->parseFont(
          $data->hasAttribute('font') ? $data->getAttribute('font') : ''
        ),
        'fgcolor' => $this->parseFGColor(
          $data->hasAttribute('fgcolor') ? $data->getAttribute('fgcolor') : ''
        )
      );
    }
  }

  /**
  * parse font attribute
  *
  * @param string $str
  * @param array $default defaults, default value NULL
  * @access public
  * @return array
  */
  function parseFont($str, $default = NULL) {
    $fontPatterns = array(
      '~^(\d+)((?:\s+(?:bold|italic|normal))+)?$~i',
      '~^(.+?)(?:\s+(\d+))?((?:\s+(?:bold|italic|normal))+)?$~i'
    );
    if (preg_match($fontPatterns[0], $str, $matches)) {
      $family = NULL;
      $size = ((int)$matches[1] > 0) ? (int)$matches[1] : NULL;
      $stylesString = strtolower($matches[2]);
      if (trim($stylesString) != '') {
        $bold = (FALSE !== strpos($stylesString, 'bold')) ? TRUE : FALSE;
        $italic = (FALSE !== strpos($stylesString, 'italic')) ? TRUE : FALSE;
      }
    } elseif (preg_match($fontPatterns[1], $str, $matches)) {
      $family = (trim($matches[1]) != '') ? trim($matches[1]) : NULL;
      $size = ((int)$matches[2] > 0) ? (int)$matches[2] : NULL;
      if (isset($matches[3])) {
        $stylesString = strtolower($matches[3]);
      } else {
        $stylesString = '';
      }
      if (trim($stylesString) != '') {
        $bold = (FALSE !== strpos($stylesString, 'bold')) ? TRUE : FALSE;
        $italic = (FALSE !== strpos($stylesString, 'italic')) ? TRUE : FALSE;
      }
    }
    if (isset($default)) {
      $result = array(
        'family' => isset($family) ? $family : $default['family'],
        'size' => isset($family) ? $size : $default['size'],
        'bold' => isset($bold) ? $bold : $default['bold'],
        'italic' => isset($italic) ? $italic : $default['italic'],
      );
    } else {
      $result = array(
        'family' => isset($family) ? $family : NULL,
        'size' => isset($family) ? $size : NULL,
        'bold' => isset($bold) ? $bold : NULL,
        'italic' => isset($italic) ? $italic : NULL,
      );
    }
    return $result;
  }

  /**
  * parse margin attribute (works like CSS)
  *
  * @param string $str
  * @param array $default defaults, default value NULL
  * @access public
  * @return array
  */
  function parseMargin($str, $default = NULL) {
    $marginPatterns = array(
      't_r_b_l' => '~^(\d+(\.\d+)?)\s+(\d+(\.\d+)?)\s+(\d+(\.\d+)?)\s+(\d+(\.\d+)?)$~',
      't_rl_b' => '~^(\d+(\.\d+)?)\s+(\d+(\.\d+)?)\s+(\d+(\.\d+)?)$~',
      'tb_rl' => '~^(\d+(\.\d+)?)\s+(\d+(\.\d+)?)$~',
      'trbl' => '~^(\d+(\.\d+)?)$~'
    );
    if (preg_match($marginPatterns['t_r_b_l'], $str, $matches)) {
      $result = array(
        'top' => $matches[1],
        'right' => $matches[3],
        'bottom' => $matches[5],
        'left' => $matches[7],
      );
    } elseif (preg_match($marginPatterns['t_rl_b'], $str, $matches)) {
      $result = array(
        'top' => $matches[1],
        'right' => $matches[3],
        'bottom' => $matches[5],
        'left' => $matches[3],
      );
    } elseif (preg_match($marginPatterns['tb_rl'], $str, $matches)) {
      $result = array(
        'top' => $matches[1],
        'right' => $matches[3],
        'bottom' => $matches[1],
        'left' => $matches[3],
      );
    } elseif (preg_match($marginPatterns['trbl'], $str, $matches)) {
      $result = array(
        'top' => $matches[1],
        'right' => $matches[1],
        'bottom' => $matches[1],
        'left' => $matches[1],
      );
    } elseif (isset($default)) {
      return $default;
    } else {
      return $this->defaultMargin;
    }
    return $result;
  }

  /**
  * check an set the foreground color for a tag
  *
  * @param string $str color string
  * @param NULL | string $default optional, default value NULL
  * @access public
  * @return string color
  */
  function parseFGColor($str, $default = NULL) {
    if (preg_match('(^#?([\dA-Fa-f]{3}|[\dA-Fa-f]{6})$)', $str)) {
      return $str;
    } elseif (isset($default)) {
      return $default;
    } else {
      return $this->defaultFGColor;
    }
  }

  /**
  * parse alignment attribute
  *
  * @param string $str
  * @param string $default align optional, default value NULL
  * @access public
  * @return string align
  */
  function parseAlign($str, $default = NULL) {
    switch ($str) {
    case 'justify':
    case 'right':
    case 'left':
    case 'center':
      return $str;
    default:
      if (isset($default)) {
        return $default;
      } else {
        return $this->defaultAlign;
      }
    }
  }

  /**
  * look for defined styles for a tag
  *
  * @param string $tagName
  * @access public
  * @return mixed
  */
  function getTagLayout($tagName) {
    if (isset($this->tags) && isset($this->tags[$tagName])) {
      return $this->tags[$tagName];
    }
    return FALSE;
  }

  /**
  * initialize pdf document
  *
  * @param papaya_pdf &$pdf pdf document
  * @access public
  */
  function pdfInitialize(&$pdf) {
    $lastFile = NULL;
    foreach ($this->templates as $name => $data) {
      if ((!isset($lastFile)) || $lastFile != $data['file']) {
        $pdf->setSourceFile($data['file']);
        $lastFile = $data['file'];
      }
      $index = $pdf->importPage($data['page']);
      $this->templates[$name]['index'] = $index;
    }
    foreach ($this->fonts as $name => $data) {
      $pdf->addFont($name, '', $data['default']);
      $pdf->addFont($name, 'B', $data['bold']);
      $pdf->addFont($name, 'I', $data['italic']);
      $pdf->addFont($name, 'BI', $data['bolditalic']);
    }
  }

  /**
  * get a page configuration
  *
  * @param string $pageName page configuration name
  * @access public
  * @return array
  */
  function &getPage($pageName) {
    if (isset($pageName) && isset($this->pages[$pageName])) {
      $page = &$this->pages[$pageName];
    } elseif (isset($this->pages['default'])) {
      $page = &$this->pages['default'];
    } else {
      $page = NULL;
    }
    return $page;
  }

  /**
  * add a new page to pdf document
  *
  * @param papaya_pdf &$pdf pdf document
  * @param string $pageName page configuration name
  * @param boolean $resetStyle optional, default value FALSE
  * @access public
  */
  function pdfAddPage(&$pdf, $pageName, $resetStyle = FALSE) {
    $page = &$this->getPage($pageName);
    if (isset($page)) {
      $orientation = ($page['orientation'] == 'horizontal') ? 'L' : 'P';
      $pdf->AddPage($orientation, $pageName, $resetStyle);
      $pdf->currentPageTemplate = $pageName;
    } else {
      $pdf->AddPage();
    }
  }

  /**
  * insert a new page to pdf document
  *
  * @param &$pdf
  * @param $position
  * @param $pageName
  * @param boolean $resetStyle optional, default value FALSE
  * @access public
  */
  function pdfInsertPage(&$pdf, $position, $pageName, $resetStyle = FALSE) {
    $page = &$this->getPage($pageName);
    if (isset($page)) {
      $orientation = ($page['orientation'] == 'horizontal') ? 'L' : 'P';
      $pdf->insertPage($position, $orientation, $pageName, $resetStyle);
    } else {
      $pdf->insertPage($position, 'P', $pageName, $resetStyle);
    }
    $pdf->currentPageTemplate = $pageName;
  }

  /**
  * initialize a new pdf page
  *
  * @param papaya_pdf &$pdf pdf document
  * @param string $pageName page configuration name
  * @param boolean $resetStyle optional, default value FALSE
  * @access public
  */
  function pdfInitPage(&$pdf, $pageName, $resetStyle = FALSE) {
    $page = &$this->getPage($pageName);
    if (isset($page)) {
      if (isset($page['template']) && isset($this->templates[$page['template']]) &&
          isset($this->templates[$page['template']]['index']) &&
          $this->templates[$page['template']]['index'] > 0) {
        $pdf->useTemplate($this->templates[$page['template']]['index']);
      }
      switch($page['mode']) {
      case 'elements' :
        $pdf->setPageStyle($page);
        $pdf->setAutoPageBreak(FALSE);
        $pdf->columnCount = 1;
        $pdf->currentColumn = 1;
        break;
      default :
        if ($this->pdfInitColumn($pdf, $pageName)) {
          if ($resetStyle) {
            $pdf->setPageStyle($page);
          }
        }
      }
    }
  }

  /**
  * initialize a column in the pdf document
  *
  * @param papaya_pdf &$pdf pdf document
  * @param string $pageName page configuration name
  * @param integer $index optional, default value 1
  * @access public
  * @return boolean
  */
  function pdfInitColumn(&$pdf, $pageName, $index = 1) {
    $page = &$this->getPage($pageName);
    if (isset($page)) {
      if (isset($page['columns'][$index])) {
        $column = &$page['columns'][$index];
        $pdf->currentColumn = $index;
        $pdf->CurOrientation = ($page['orientation'] == 'horizontal') ? 'L' : 'P';
        $pdf->setXY($column['margin']['left'], $column['margin']['top']);
        $pdf->setMargins(
          $column['margin']['left'], $column['margin']['top'], $column['margin']['right']
        );
        $pdf->setAutoPageBreak(TRUE, $column['margin']['bottom']);
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * get column count
  *
  * @param papaya_pdf &$pdf pdf document
  * @param string $pageName page configuration name
  * @access public
  * @return integer
  */
  function pdfColumnCount(&$pdf, $pageName) {
    $page = &$this->getPage($pageName);
    if (isset($page)) {
      if (isset($page['columns'])) {
        return count($page['columns']);
      }
    }
    return 1;
  }

  /**
  * initialize a elment in the pdf document
  *
  * @param papaya_pdf &$pdf pdf document
  * @param string $pageName page configuration name
  * @param string $elementName element configuration name
  * @access public
  * @return boolean
  */
  function pdfInitElement(&$pdf, $pageName, $elementName) {
    $page = &$this->getPage($pageName);
    if (isset($page)) {
      if (isset($page['elements'][$elementName])) {
        $element = &$page['elements'][$elementName];
        $pdf->setXY($element['margin']['left'], $element['margin']['top']);
        $pdf->setMargins(
          $element['margin']['left'], $element['margin']['top'], $element['margin']['right']
        );
        $pdf->setPageStyle($element);
        $pdf->currentStyle['align'] = $element['align'];
        $pdf->currentStyle['fgcolor'] = $element['fgcolor'];
        $pdf->setAutoPageBreak(FALSE);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Initialize page measures
  * @param papaya_pdf $pdf
  * @param string  $pageName
  * @return void
  */
  function pdfInitPageMeasures(&$pdf, $pageName) {
    $page = &$this->getPage($pageName);
    if (isset($page)) {
      $orientation = ($page['orientation'] == 'horizontal') ? 'L' : 'P';
      $pdf->initPageMeasures($orientation);
    }
  }

  /**
  * write a element to the pdf document
  *
  * @param papaya_pdf &$pdf pdf document
  * @param string $pageName page configuration name
  * @param string $elementName element configuration name
  * @param simple_xmlnode &$elementData
  * @access public
  */
  function pdfOutputDocumentElement(&$pdf, $pageName, $elementName, &$elementData) {
    if (isset($elementData) && is_object($elementData)) {
      $page = &$this->getPage($pageName);
      if (isset($page) && isset($page[$elementName]) && is_array($page[$elementName])) {
        $element = &$page[$elementName];
        $pdf->setXY($element['margin']['left'], $element['margin']['top']);
        $pdf->setMargins(
          $element['margin']['left'], $element['margin']['top'], $element['margin']['right']
        );
        $pdf->setPageStyle($element);
        $pdf->currentStyle['fgcolor'] = $element['fgcolor'];
        $pdf->currentStyle['align'] = $element['align'];
        $pdf->setAutoPageBreak(FALSE);
        $pdf->openElement($elementData->attributes);
        $this->owner->outputDataChildren($elementData);
        $pdf->closeElement($elementData->attributes);
      }
    }
  }
}
?>