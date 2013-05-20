<?php
/**
* Implement FPDF/FPDI for papaya CMS
*
* @copyright 2002-2006 by papaya Software GmbH - All rights reserved.
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
* @version $Id: papaya_pdf.php 38492 2013-05-16 13:44:15Z weinert $
*/

/**
* Define font path for fpdf default fonts
*/
define('FPDF_FONTPATH', '');

/**
* updf super class
*/
require_once(dirname(__FILE__).'/external/fpdf/ufpdf.php');
/**
* pdf string token class
*/
require_once(dirname(__FILE__).'/papaya_pdf_string.php');
/**
* pdf wordbreak token class
*/
require_once(dirname(__FILE__).'/papaya_pdf_wordbreak.php');

/**
* Implement FPDF/FPDI for papaya CMS
*
* @package Papaya-Modules
* @subpackage Free-PDF
*/
class papaya_pdf extends UFPDF {

  /**
  * document object
  * @var papaya_pdf_document $docObj
  */
  var $docObj = NULL;

  /**
  * configuration object
  * @var papaya_pdf_options $confObj
  */
  var $confObj = NULL;

  /**
  * current page template name
  * @var string $currentPageTemplate
  */
  var $currentPageTemplate = NULL;
  /**
  * current column number
  * @var integer $currentColumn
  */
  var $currentColumn = 1;

  /**
  * buffer for current link data
  * @var string
  */
  var $currentLink = '';

  /**
  * buffer for current link target data (triggers a local link target for the next output)
  * @var string
  */
  var $currentLinkTarget = '';

  /**
  * array with local link ids that uses the link target names for keys
  * @var array
  */
  var $localLinks = array(
    //name => id
  );

  /**
  * break before next section
  * @var boolean $breakBeforeNextSection
  */
  var $breakBeforeNextSection = FALSE;

  /**
  * default styles
  * @var array $defaultStyle
  */
  var $defaultStyle = array(
    'bold' => 0, 'italic' => 0, 'underline' => 0, 'list' => 0,
    'align' => 'left', 'fgcolor' => '#000', 'bgcolor' => '',
    'bullet-chars' => '-',
    'font-family' => NULL, 'font-size' => NULL,
    'link' => ''
  );
  /**
  * current style
  * @var array $currentStyle
  */
  var $currentStyle = NULL;
  /**
  * current font
  * @var array $currentFont
  */
  var $currentFontData = array(
    'bold' => FALSE,
    'italic' => FALSE,
    'underline' => FALSE,
    'font-family' => NULL,
    'font-size' => NULL
  );



  /**
  * element buffer
  * @var array $elementBuffer
  */
  var $elementBuffer = array();
  /**
  * element mode (text|table|outputtable)
  * @var string $elementMode
  */
  var $elementMode = 'text';
  /**
  * table buffer
  * @var papaya_pdf_table $tableBuffer
  */
  var $tableBuffer = NULL;
  /**
  * table cell buffer
  * @var papaya_pdf_table_cell $currentTableCell
  */
  var $currentTableCell = NULL;

  /**
  * buffer probierty - last line height (for linebreak)
  * @var float
  */
  var $lastLineHeight = 0;

  /**
  * indent step size
  * @var float $indentStep
  */
  var $indentStep = 5;

  /**
  * bullet font
  * @var string $defaultListBulletFont
  */
  var $defaultListBulletFont = "symbol";

  /**
  * status "in paragraph"
  * @var boolean $inParagraph
  */
  var $inParagraph = TRUE;
  /**
  * status "in column"
  * @var boolean $inColumn
  */
  var $inColumn = TRUE;
  /**
  * status "in image"
  * @var boolean $inImage
  */
  var $inImage = TRUE;
  /**
  * status "in text"
  * @var boolean $inText
  */
  var $inText = FALSE;

  /**
  * status "where in ordered list"
  * @var integer $inOrderedList
  */
  var $inOrderedList = FALSE;

  /**
  * status "where in ordered list"
  * @var int $inOrderedListAt
  */
  var $inOrderedListAt = 0;

  var $outlines = array();
  var $OutlineRoot;

  /**
  * additional left padding inside columns
  * @var integer
  */
  var $lPadding = 0;

  /**
  * additional right padding inside columns
  * @var integer
  */
  var $rPadding = 0;

  /**
  * points to mm
  * @var float $dtpPoint
  */
  var $dtpPoint = 0.352778;
  /**
  * image dpi
  * @var float $mediaDpi
  */
  var $mediaDpi = 120;
  /**
  * image dpmm
  * @var float $mediaK
  */
  var $mediaK = 0;

  /**
  * line space
  * @var float $lineSpacePt
  */
  var $lineSpacePt = 3.5;

  var $freeCoreFonts = array(
    'helvetica' => 'DejaVuSans',
      'helveticaB' => 'DejaVuSans-Bold',
      'helveticaI'=>'DejaVuSans-Oblique',
      'helveticaBI'=>'DejaVuSans-BoldOblique',
    'times' => 'DejaVuSerif',
      'timesB' => 'DejaVuSerif-Bold',
      'timesI' => 'DejaVuSerif-Oblique',
      'timesBI'=> 'DejaVuSerif-BoldOblique',
    'mono' => 'DejaVuSansMono',
      'monoB' => 'DejaVuSansMono-Bold',
      'monoI' => 'DejaVuSansMono-Oblique',
      'monoBI' => 'DejaVuSansMono-BoldOblique',
    'symbol' => 'OpenSymbol',
      'symbolB' => 'OpenSymbol',
      'symbolI' => 'OpenSymbol',
      'symbolBI' => 'OpenSymbol');

  /**
  * remember data like page template
  * @var array
  */
  var $pageInfos = array();

  /**
   * The page the current font was set for, this is to avoid setting the same font twice for the
   * same page, but recognize if page changes.
   *
   * @var integer
   */
  var $FontSetForPage = 0;

  /**
  * constructor
  *
  * @param papaya_pdf_options &$conf
  * @access public
  */
  function __construct(&$doc, &$conf) {
    parent::fpdi();
    $this->docObj = &$doc;
    $this->confObj = &$conf;
    $this->currentStyle = $this->defaultStyle;
    $this->mediaK = $this->mediaDpi / 25.4;

    $this->CoreFonts = &$this->freeCoreFonts;
  }

  /**
  * PHP 4 constructor redirect
  *
  * @param papaya_pdf_options &$conf
  * @access public
  */
  function papaya_pdf(&$doc, &$conf) {
    $this->__construct($doc, $conf);
  }

  /**
  * work with absolute font paths
  *
  * @access public
  * @return string empty font path ''
  */
  function _getfontpath() {
    return '';
  }

  /**
  * css hex color to rgb array
  *
  * @param string $colorStr
  * @access public
  * @return array r-g-b values
  */
  function getRGB($colorStr) {
    $colorStr = ltrim($colorStr, "#");
    if (strlen($colorStr) <= 3) {
      while (strlen($colorStr) < 3) {
        $colorStr .= '0';
      }
      $r = hexdec(str_repeat(substr($colorStr, 0, 1), 2));
      $g = hexdec(str_repeat(substr($colorStr, 1, 1), 2));
      $b = hexdec(str_repeat(substr($colorStr, 2, 1), 2));
    } else {
      while (strlen($colorStr) < 6) {
        $colorStr .= '0';
      }
      $r = hexdec(substr($colorStr, 0, 2));
      $g = hexdec(substr($colorStr, 2, 2));
      $b = hexdec(substr($colorStr, 4, 2));
    }
    return array($r, $g, $b);
  }

  /**
  * set foreground color
  *
  * @param mixed $color
  * @access public
  */
  function setFGColor($color) {
    if (empty($color)) {
      $this->currentFGColor = array(0, 0, 0);
    } elseif (!is_array($color)) {
      $this->currentFGColor = $this->getRGB($color);
    } else {
      $this->currentFGColor = $color;
    }
    $this->SetTextColor(
      $this->currentFGColor[0], $this->currentFGColor[1], $this->currentFGColor[2]
    );
  }

  /**
  * set background color
  *
  * @param mixed $color
  * @access public
  */
  function setBGColor($color) {
    if (empty($color)) {
      $this->currentBGColor = array(255, 255, 255);
    } elseif (!is_array($color)) {
      $this->currentBGColor = $this->getRGB($color);
    } else {
      $this->currentBGColor = $color;
    }
    $this->SetFillColor(
      $this->currentBGColor[0], $this->currentBGColor[1], $this->currentBGColor[2]
    );
  }

  /**
  * embed font style file
  *
  * @param string $family font family
  * @param string $style font style, default value ''
  * @param string $file font filename, default value ''
  * @access public
  */
  function addFont($family, $style ='', $fileName = '') {
    $family = strtolower($family);
    $fileName = $this->_getAbsoluteFontFile($fileName);

    if (file_exists($fileName) && is_file($fileName) && is_readable($fileName)) {
      $style = strtoupper($style);
      if ($style == 'IB') {
        $style = 'BI';
      }
      $fontKey = $family.$style;
      if (isset($this->fonts[$fontKey])) {
        $this->Error('Font already added: '.$family.' '.$style);
      }

      $path = dirname($fileName);
      include($fileName);

      if (!isset($name)) {
        $this->Error('Could not include font definition file');
      }
      $i = count($this->fonts) + 1;
      $this->fonts[$fontKey] = array(
        'i' => $i,
        'type' => $type,
        'name' => $name,
        'desc' => $desc,
        'up' => $up,
        'ut' => $ut,
        'cw' => $cw,
        'file' => $path.'/'.$file,
        'ctg' => $path.'/'.$ctg
      );
      if ($diff) {
        //Search existing encodings
        $d = 0;
        $nb = count($this->diffs);
        for ($i = 1; $i <= $nb; $i++) {
          if ($this->diffs[$i] == $diff) {
            $d = $i;
            break;
          }
        }
        if ($d == 0) {
          $d = $nb + 1;
          $this->diffs[$d] = $diff;
        }
        $this->fonts[$fontKey]['diff'] = $d;
      }
      if ($type == 'TrueTypeUnicode') {
        $this->FontFiles[$path.'/'.$file] = array('length1' => $originalsize);
      } else {
        $this->FontFiles[$path.'/'.$file] = array('length1' => $size1, 'length2' => $size2);
      }
    } else {
      $this->Error('Font file not found: '.$file);
    }
  }

  /**
   * Sets the active font family, style and size.
   *
   * @access public
   * @param  string $family
   * @param  string $style=''
   * @param  integer $size=0
   */
  function setFont($family, $style='', $size=0) {
      //Select a font; size given in points
    global $fpdf_charwidths;

    $family = strtolower($family);
    if ($family == '') {
      $family = $this->FontFamily;
    }
    if ($family == 'arial') {
      $family = 'helvetica';
    } elseif ($family == 'symbol' or $family == 'zapfdingbats') {
      $style = '';
    }
    $style = strtoupper($style);
    if (FALSE !== strpos($style, 'U')) {
      $this->underline = TRUE;
      $style = str_replace('U', '', $style);
    } else {
      $this->underline = FALSE;
    }
    if ($style == 'IB') {
      $style = 'BI';
    }
    if ($size == 0) {
      $size = $this->FontSizePt;
    }
    //Test if font is already selected
    if ($this->FontFamily == $family &&
        $this->FontStyle == $style &&
        $this->FontSizePt == $size &&
        $this->FontSetForPage == $this->page &&
        !$this->intpl) {
      return;
    }

    //Test if used for the first time
    $fontkey = $family.$style;

    if (!isset($this->fonts[$fontkey])) {
      //Check if one of the standard fonts
      if (isset($this->CoreFonts[$fontkey])) {
        $this->addFont($family, $style, $this->CoreFonts[$fontkey].'.php');
      } else {
        $this->Error('Undefined font: '.$family.' '.$style);
      }
    }

    //Select it
    $this->FontFamily = $family;
    $this->FontStyle = $style;
    $this->FontSizePt = $size;
    $this->FontSize = $size / $this->k;
    $this->CurrentFont = &$this->fonts[$fontkey];
    if ($this->page > 0) {
      $this->FontSetForPage = $this->page;
      $this->_out(
        sprintf(
          'BT '.$this->fontprefix.'%d %.2f Tf ET',
          $this->CurrentFont['i'],
          $this->FontSizePt
        )
      );
    }

    if ($this->intpl) {
        $this->res['tpl'][$this->tpl]['fonts'][$fontkey] =& $this->fonts[$fontkey];
    } else {
        $this->res['page'][$this->page]['fonts'][$fontkey] =& $this->fonts[$fontkey];
    }
  }

  /**
  * Add a bookmark to the internal toc
  * @param string $txt
  * @param integer $level
  * @param float $y
  * @param integer $pageNo
  * @return void
  */
  function addBookmark($txt, $level = 0, $y = -1, $pageNo = 0) {
    if ($y == -1) {
      $y = $this->GetY();
    }
    $this->outlines[] = array(
      't' => $txt,
      'l' => $level,
      'y' => $y,
      'p' => empty($pageNo) ? $this->PageNo() : $pageNo
    );
  }

  /**
  * pre process bookmarks, optimize level structure
  * @return void
  */
  function prepareBookmarks() {
    $nb = count($this->outlines);
    if ($nb == 0) {
      return;
    }
    $lru = array();
    $level = 0;
    //header('Content-type: text/plain');
    foreach ($this->outlines as $i => $o) {
      //fix indent if here is a level missing
      if ($o['l'] > 0 && $o['l'] - 1 >= $level) {
        $o['l'] = $level + 1;
        $this->outlines[$i] = $o;
      }
      if ($o['l'] > 0) {
        $parent = $lru[$o['l'] - 1];
        //Set parent and last pointers
        $this->outlines[$i]['parent'] = $parent;
        $this->outlines[$parent]['last'] = $i;
        if ($o['l'] > $level) {
          //Level increasing: set first pointer
          $this->outlines[$parent]['first'] = $i;
        }
      } else {
        $this->outlines[$i]['parent'] = $nb;
      }
      if ($o['l'] <= $level && $i > 0) {
        //Set prev and next pointers
        $prev = $lru[$o['l']];
        $this->outlines[$prev]['next'] = $i;
        $this->outlines[$i]['prev'] = $prev;
      }
      $lru[$o['l']] = $i;
      $level = $o['l'];
    }
  }

  /**
  * output bookmarks to pdf document stream
  * @return void
  */
  function _putbookmarks() {
    $nb = count($this->outlines);
    if ($nb == 0) {
      return;
    }
    $this->prepareBookmarks();
    //Outline items
    $n = $this->n + 1;
    $last = 0;
    foreach ($this->outlines as $i => $o) {
      $this->_newobj();
      $this->_out('<</Title '.$this->_textstring($o['t']));
      $this->_out('/Parent '.($n + $o['parent']).' 0 R');
      if (isset($o['prev'])) {
        $this->_out('/Prev '.($n + $o['prev']).' 0 R');
      }
      if (isset($o['next'])) {
        $this->_out('/Next '.($n + $o['next']).' 0 R');
      }
      if (isset($o['first'])) {
        $this->_out('/First '.($n + $o['first']).' 0 R');
      }
      if (isset($o['last'])) {
        $this->_out('/Last '.($n + $o['last']).' 0 R');
      }
      $this->_out(
        sprintf(
          '/Dest [%d 0 R /XYZ 0 %.2f null]',
          1 + 2 * $o['p'],
          ($this->h - $o['y']) * $this->k
        )
      );
      $this->_out('/Count 0>>');
      $this->_out('endobj');
      if ($o['l'] == 0) {
        $last = $i;
      }
    }
    //Outline root
    $this->_newobj();
    $this->OutlineRoot = $this->n;
    $this->_out('<</Type /Outlines /First '.$n.' 0 R');
    $this->_out('/Last '.($n + $last).' 0 R>>');
    $this->_out('endobj');
  }

  /**
  * output resources to pdf stream
  * @return void
  */
  function _putResources() {
    parent::_putResources();
    $this->_putBookmarks();
  }

  /**
  * put catalog into pdf stream
  * @return void
  */
  function _putCatalog() {
    parent::_putcatalog();
    if (count($this->outlines) > 0) {
      $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
      $this->_out('/PageMode /UseOutlines');
    }
  }

  /**
  * write index/toc to pdf document data
  * @param array $attr
  * @return void
  */
  function writeIndex($attr) {
    $this->elementMode = 'toc';
    if (isset($this->outlines) && is_array($this->outlines) && count($this->outlines) > 0) {
      $this->prepareBookmarks();
      $attr = $this->simplify($attr);
      $this->rPadding = $this->GetStringWidth('9999');
      $this->lPadding = 0;
      $startPage = $this->page;
      foreach ($this->outlines as $i => $o) {
        if ($i > 0) {
          $this->Ln();
          if ($o['l'] == 0) {
            $this->Ln();
          }
        }
        if ($this->checkPagebreakTrigger($this->getLineHeight())) {
          $this->acceptPageBreak();
        }
        $this->currentStyle['list'] = $o['l'];
        $this->currentStyle['bullet-chars'] = '';
        if (trim($o['t']) == '') {
          $this->writeText('.');
        } else {
          $this->writeText($o['t']);
        }
        $pos = $this->writeBuffer();
        if ($pos) {
          $this->outlines[$i]['pos'] = $pos;
          $this->outlines[$i]['pos']['p'] = $this->page;
          $this->outlines[$i]['pos']['c'] = $this->currentColumn;
        }
      }
      $this->rPadding = 0;
      if (!empty($attr['line'])) {
        $lineColor = $this->getRGB($attr['line']);
      }
      foreach ($this->outlines as $i => $o) {
        if (isset($o['pos'])) {
          $text = (string)$o['p'];
          $this->goToPageColumn($o['pos']['p'], $o['pos']['c']);
          $y = $o['pos']['y'];
          $x = $o['pos']['x'] + $this->getLineWidth() -
            $this->GetStringWidth($text) - ($this->indentStep * $o['l']);
          $this->Text($x, $y, $text);
          $lineX = $o['pos']['x'] + $o['pos']['w'] + 1;
          if (!empty($attr['line'])) {
            $this->SetDrawColor($lineColor[0], $lineColor[1], $lineColor[2]);
            $this->Line($lineX, $y, $x - 1, $y);
          }
          $linkId = $this->addLink();
          $this->SetLink($linkId, $o['y'], $o['p']);
          $this->Link(
            $lineX,
            $y - $this->getLineHeight(),
            $x - $lineX + $this->GetStringWidth($text),
            $this->getLineHeight(),
            $linkId
          );
        }
      }
      if (!empty($attr['title'])) {
        array_unshift(
          $this->outlines,
          array(
            't' => $attr['title'],
            'l' => 0,
            'y' => -1,
            'p' => $startPage
          )
        );
      }
    }
    $this->elementMode = 'text';
  }

  /**
  * begin a new page or select an existing one
  *
  * @param string $orientation
  * @access public
  */
  function _beginPage($orientation) {
    $this->page++;
    if (!isset($this->pages[$this->page])) {
      $this->pages[$this->page] = '';
    }
    $this->state = 2;
    $this->initPageMeasures($orientation);
  }

  /**
  * initialize page meduars (width and height)
  * @param string $orientation
  * @return void
  */
  function initPageMeasures($orientation) {
    $this->x = $this->lMargin;
    $this->y = $this->tMargin;
    $this->FontFamily = '';
    //Page orientation
    if (!$orientation) {
      $orientation = $this->DefOrientation;
    } else {
      $orientation = strtoupper($orientation{0});
      if ($orientation != $this->DefOrientation) {
        $this->OrientationChanges[$this->page] = TRUE;
      }
    }
    //Change orientation
    if ($orientation == 'P') {
      $this->wPt = $this->fwPt;
      $this->hPt = $this->fhPt;
      $this->w = $this->fw;
      $this->h = $this->fh;
    } else {
      $this->wPt = $this->fhPt;
      $this->hPt = $this->fwPt;
      $this->w = $this->fh;
      $this->h = $this->fw;
    }
    $this->PageBreakTrigger = $this->h - $this->bMargin;
    $this->CurOrientation = $orientation;
  }

  /**
  * end pdf document output
  * @return void
  */
  function _endDoc() {
    $this->_setLocalLinks();
    parent::_endDoc();
  }

  /**
  * add a page
  *
  * @param string $orientation optional, default value 'P'
  * @param string $pageName page configuration name, default value NULL
  * @param boolean $resetStyle optional, default value FALSE
  * @access public
  */
  function addPage($orientation = 'P', $pageName = NULL, $resetStyle = FALSE) {
    parent::addPage($orientation);
    if (!isset($pageName)) {
      $pageName = $this->currentPageTemplate;
    }
    if (!empty($pageName)) {
      $this->confObj->pdfInitPage($this, $pageName, $resetStyle);
    }
    if (isset($this->pageInfos[$this->page - 1])) {
      $this->pageInfos[$this->page] = $this->pageInfos[$this->page - 1];
    } else {
      $this->pageInfos[$this->page] = array('title' => '', 'subtitle' => '');
    }
    $this->pageInfos[$this->page]['title-changed'] = FALSE;
    $this->pageInfos[$this->page]['subtitle-changed'] = FALSE;
    $this->pageInfos[$this->page]['template'] = $pageName;
    $this->inColumn = FALSE;
    $this->inParagraph = FALSE;
    $this->inText = FALSE;
  }

  /**
  * Insert a new page (and move the exiting ones)
  * @param integer $position
  * @param string $orientation
  * @param string|NULL $pageName
  * @param boolean $resetStyle
  * @return void
  */
  function insertPage($position, $orientation = 'P', $pageName = NULL, $resetStyle = FALSE) {
    if ($position > 1 && !isset($this->pages[$position])) {
      $this->addPage($orientation, $pageName, $resetStyle);
      return;
    } elseif ($position < 1) {
      $position = 1;
    }
    //move the existing pages to make room for the new one
    for ($i = count($this->pages); $i >= $position; $i--) {
      if (isset($this->pages[$i])) {
        $this->pages[$i + 1] = $this->pages[$i];
      }
      if (isset($this->pageInfos[$i])) {
        $this->pageInfos[$i + 1] = $this->pageInfos[$i];
      }
      if (isset($this->PageLinks[$i])) {
        $this->PageLinks[$i + 1] = $this->PageLinks[$i];
      }
      if (isset($this->OrientationChanges[$i])) {
        $this->OrientationChanges[$i + 1] = $this->OrientationChanges[$i];
      } elseif (isset($this->OrientationChanges[$i + 1])) {
        unset($this->OrientationChanges[$i + 1]);
      }
    }
    if (isset($this->outlines) && is_array($this->outlines)) {
      foreach ($this->outlines as $i => $o) {
        if ($o['p'] >= $position) {
          $this->outlines[$i]['p']++;
        }
      }
    }
    if (isset($this->localLinks) && is_array($this->localLinks)) {
      foreach ($this->localLinks as $i => $l) {
        if (isset($l['p']) && $l['p'] >= $position) {
          $this->localLinks[$i]['p']++;
        }
      }
    }
    //move to page pointer to before the new page position
    $this->page = $position - 1;
    $this->pages[$position] = NULL;
    $this->PageLinks[$position] = NULL;
    $this->pageInfos[$position] = NULL;
    $this->OrientationChanges[$position] = NULL;
    $this->AddPage($orientation, $pageName, $resetStyle);
  }

  /**
  * set page style
  *
  * @param array $pageStyle
  * @access public
  */
  function setPageStyle($pageStyle) {
    $this->resetTextStatus();
    $style = '';
    if ($pageStyle['font']['bold']) {
      $style .= 'B';
    }
    if ($pageStyle['font']['italic']) {
      $style .= 'I';
    }
    $this->setCurrentFont($pageStyle['font']['family'], $style, $pageStyle['font']['size']);
    $pageStyle['font-family'] = $pageStyle['font']['family'];
    $pageStyle['font-size'] = $pageStyle['font']['size'];
    $this->setAttributeStyles($pageStyle, TRUE);
  }


  /**
  * set page title and subtitle status - can be used in header/footer
  *
  * @param string|NULL $pageTitle
  * @param string|NULL $pageSubTitle
  * @access public
  * @return void
  */
  function setPageTitle($pageTitle, $pageSubTitle) {
    if (isset($this->pageInfos[$this->page])) {
      if (isset($pageTitle) &&
          !$this->pageInfos[$this->page]['title-changed']) {
        //change title and subtitle
        $this->pageInfos[$this->page]['title'] = $pageTitle;
        $this->pageInfos[$this->page]['title-changed'] = TRUE;
        //no subtitle means empty and unchanged subtitle
        if (isset($pageSubTitle)) {
          $this->pageInfos[$this->page]['subtitle'] = $pageSubTitle;
          $this->pageInfos[$this->page]['subtitle-changed'] = TRUE;
        } else {
          //no subtitle provided - reset it and wait for next change
          $this->pageInfos[$this->page]['subtitle'] = $pageSubTitle;
          $this->pageInfos[$this->page]['subtitle-changed'] = FALSE;
        }
      } elseif (isset($pageSubTitle) &&
                !$this->pageInfos[$this->page]['subtitle-changed']) {
        //change only the subtitle
        $this->pageInfos[$this->page]['subtitle'] = $pageSubTitle;
        $this->pageInfos[$this->page]['subtitle-changed'] = TRUE;
      }
    }
  }

  /**
  * get current status - buffer, styles, ...
  *
  * @access public
  * @return array
  */
  function getTextStatus() {
    return array(
      'base-font-family' => $this->currentFontData['font-family'],
      'base-font-size-pt' => $this->currentFontData['font-size'],
      'font-family' => $this->FontFamily,
      'font-style' => $this->FontStyle,
      'font-size-pt' => $this->FontSizePt,
      'style' => $this->currentStyle,
      'elementBuffer' => $this->elementBuffer,
      'elementMode' => $this->elementMode,
    );
  }

  /**
  * set current status - buffer, styles, ...
  *
  * @param array $status
  * @access public
  */
  function setTextStatus($status) {
    $this->setCurrentFont($status['base-font-family'], '', $status['base-font-size-pt']);
    $this->setFont(
      $status['font-family'], $status['font-style'], $status['font-size-pt']
    );
    $this->elementBuffer = $status['elementBuffer'];
    $this->currentStyle = $status['style'];
    $this->elementMode = $status['elementMode'];
  }

  /**
  * reset current status to defaults - buffer, styles, ...
  *
  * font family and size depends on more properties, that why it is not here
  *
  * @access public
  */
  function resetTextStatus() {
    $this->elementBuffer = array();
    $this->currentStyle = $this->defaultStyle;
    $this->elementMode = 'text';
  }

  /**
  * page or column break
  *
  * @access public
  * @return boolean new page
  */
  function acceptPageBreak() {
    $initColumn = $this->confObj->pdfInitColumn(
      $this, $this->currentPageTemplate, $this->currentColumn + 1
    );
    if ($initColumn) {
      $this->inColumn = FALSE;
      $this->inParagraph = FALSE;
      $this->inText = FALSE;
      return FALSE;
    } elseif ($this->AutoPageBreak) {
      //Automatic page break
      //get current "indent"
      $offset = $this->x - $this->lMargin;

      //close page
      $ws = $this->ws;
      $k = $this->k;
      if ($ws > 0) {
        $this->ws = 0;
        $this->_out('0 Tw');
      }

      $position = $this->page + 1;
      if (isset($this->pages[$position]) && $this->elementMode == 'toc') {
        $this->insertPage($position, $this->CurOrientation, 'toc', FALSE);
      } else {
        $this->AddPage($this->CurOrientation, $this->currentPageTemplate, FALSE);
      }
      //set current "indent"
      $this->x += $offset;

      //open page
      if ($ws > 0) {
        $this->ws = $ws;
        $this->_out(sprintf('%.3f Tw', $ws * $k));
      }
      $this->inColumn = FALSE;
      $this->inParagraph = FALSE;
      $this->inText = FALSE;

      if ($this->elementMode == 'tableoutput' &&
          !empty($this->tableBuffer) &&
          $this->tableBuffer->border) {
        $this->setY($this->getY() + $this->tableBuffer->border + $this->tableBuffer->padding);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * check for page break trigger
  *
  * @param $nextElementHeight
  * @access public
  * @return boolean
  */
  function checkPageBreakTrigger($nextElementHeight) {
    $y = $this->GetY() + $nextElementHeight;
    if ($this->elementMode == 'tableoutput') {
      $y += $this->tableBuffer->padding;
    }
    return ((!$this->InFooter) && $y >= $this->PageBreakTrigger);
  }

  /**
  * select page an column
  *
  * @param integer $page
  * @param integer $column
  * @access public
  */
  function gotoPageColumn($page, $column) {
    if ($page != $this->page || $column != $this->currentColumn) {
      $this->page = $page;
      $this->currentColumn = $column;
      $this->confObj->pdfInitColumn(
        $this, $this->currentPageTemplate, $this->currentColumn);
    }
  }

  /**
  * calculate line height for a given count of lines using current font height
  *
  * @param integer $lines line count
  * @access public
  * @return float line height in mm
  */
  function getLineHeight($lines = 1) {
    return (($this->FontSizePt + $this->lineSpacePt) * $lines * $this->dtpPoint);
  }

  /**
  * calculate text height
  *
  * @access public
  * @return float line height in mm
  */
  function getTextHeight() {
    return ($this->FontSizePt * $this->dtpPoint);
  }

  /**
  * get line width
  *
  * @access public
  * @return float line width
  */
  function getLineWidth() {
    if (isset($this->currentTableCell) && $this->elementMode == 'tableoutput') {
      return ($this->currentTableCell->getWidth() - ($this->tableBuffer->padding * 2));
    } else {
      return $this->getColumnWidth();
    }
  }

  /**
  * get column width
  *
  * @access public
  * @return float
  */
  function getColumnWidth() {
    return ($this->w - $this->lMargin - $this->rMargin - $this->lPadding - $this->rPadding);
  }

  /**
  * return line left start
  *
  * @access public
  * @return float
  */
  function getLineLeft() {
    if (isset($this->currentTableCell) && $this->elementMode == 'tableoutput') {
      return (
        $this->getColumnLeft() + $this->currentTableCell->getLeft() + $this->tableBuffer->padding
      );
    } else {
      return $this->getColumnLeft();
    }
  }

  /**
  * get column left
  *
  * @access public
  * @return float
  */
  function getColumnLeft() {
    return $this->lMargin;
  }

  /**
  * calculate image height for an image and add image infos
  *
  * @param string $file image file name
  * @param float $width image width
  * @access public
  * @return array width, height
  */
  function calcImageSize($file, $maxWidth, $mediaK) {
    //add an image to the list and return height
    if (!isset($this->images[$file])) {
      $mqr = get_magic_quotes_runtime();
      set_magic_quotes_runtime(0);

      //First use of image, get info
      list(, , $imgType) = @getimagesize($file);
      if (!($imgType == 2 || $imgType == 3)) {
        $this->Error('Image is not JPEG or PNG: '.$file);
      } elseif ($imgType == 2) {
        $type = 'jpg';
        $info = $this->_parsejpg($file);
      } elseif ($imgType == 3) {
        $type = 'png';
        $info = $this->_parsepng($file);
      }
      set_magic_quotes_runtime($mqr);
      $info['i'] = count($this->images) + 1;
      $this->images[$file] = $info;
    } else {
      $info = $this->images[$file];
    }
    $imgWidth = $info['w'] / $mediaK;
    $imgHeight = $info['h'] / $mediaK;
    if ($maxWidth < $imgWidth) {
      return array(
        'width' => $maxWidth,
        'height' => $imgHeight / ($imgWidth / $maxWidth)
      );
    } else {
      return array(
        'width' => $imgWidth,
        'height' => $imgHeight
      );
    }
  }

  /**
  * Add some text to output buffer
  *
  * @param string $str
  * @access public
  */
  function writeText($str) {
    if ($this->elementMode == 'table') {
      $this->addTableContentText($str);
    } else {
      $this->beginParagraph();
      //add text to buffer
      $words = preg_split('([ \r\n\t]+)', $str);
      $addTextBreaks = FALSE;
      $style = $this->getCurrentElementStyle();
      $this->activateCurrentStyle($style);
      for ($i = 0; $i < count($words); $i++) {
        $word = trim($words[$i]);
        if ($word === '') {
          $this->writeTextBreak();
        } else {
          if ($addTextBreaks) {
            $this->elementBuffer[] = new papaya_pdf_wordbreak($this);
          } else {
            $addTextBreaks = TRUE;
          }
          $this->elementBuffer[] = new papaya_pdf_string($this, $word, $style);
        }
      }
    }
  }

  /**
  * Add text break to output buffer
  * @return void
  */
  function writeTextBreak() {
    if ($this->elementMode != 'table') {
      if (isset($this->elementBuffer) &&
          is_array($this->elementBuffer) &&
          count($this->elementBuffer) > 0 &&
          !is_a(end($this->elementBuffer), 'papaya_pdf_wordbreak')) {
        $this->elementBuffer[] = new papaya_pdf_wordbreak($this);
      }
    }
  }

  /**
  * write element buffer
  *
  * @access public
  * @return FALSE|array last output position or FALSE
  */
  function writeBuffer() {
    $result = FALSE;
    if (count($this->elementBuffer) <= 0) {
      //nothing to do
      return $result;
    }
    $paraStyle = $this->elementBuffer[0]->style;
    $this->activateCurrentStyle($paraStyle);
    $para = $this->getParagraphSize($paraStyle);

    //check that here is enough space
    if ($this->checkPageBreakTrigger($para['lineheight']) * 2) {
      //column or page break
      $this->acceptPageBreak();
      $para = $this->getParagraphSize($paraStyle);
    }

    $this->writeListBullet($paraStyle, $para);

    $lineBuffer = array();
    $lineBufferWidth = 0;
    $buffer = array();
    $bufferWidth = 0;
    $spaceCount = 0;
    foreach (array_keys($this->elementBuffer) as $elementIdx) {
      if (is_a($this->elementBuffer[$elementIdx], 'papaya_pdf_string')) {
        $buffer[] = $this->elementBuffer[$elementIdx];
        $bufferWidth += $this->elementBuffer[$elementIdx]->width;
      } else {
        $neededWidth = $bufferWidth + $lineBufferWidth + $this->elementBuffer[$elementIdx]->width;
        if ($neededWidth >= $para['textwidth']) {
          $startedAt = $this->writeBufferLine(
            $lineBuffer,
            $para,
            $lineBufferWidth,
            $paraStyle['align'],
            $spaceCount
          );
          if (!$result) {
            $result = $startedAt;
          }
          $lineBuffer = $buffer;
          $lineBuffer[] = $this->elementBuffer[$elementIdx];
          $lineBufferWidth = $bufferWidth + $this->elementBuffer[$elementIdx]->width;
          $buffer = array();
          $bufferWidth = 0;
          $spaceCount = 1;
          //enough space for another line on this page?
          if ($this->checkPageBreakTrigger($para['lineheight'] * 2)) {
            //column or page break
            $this->acceptPageBreak();
            $this->beginParagraph();
            $para = $this->getParagraphSize($paraStyle);
          } else {
            //enough space - go to next line
            $this->Ln();
          }
        } else {
          $lineBuffer = array_merge($lineBuffer, $buffer);
          $lineBuffer[] = $this->elementBuffer[$elementIdx];
          $lineBufferWidth += $bufferWidth + $this->elementBuffer[$elementIdx]->width;
          $buffer = array();
          $bufferWidth = 0;
          $spaceCount++;
        }
      }
    }
    //check line buffer - this is the last line of this paragraph
    if (count($lineBuffer) > 0 || count($buffer) > 0) {
      if ($bufferWidth + $lineBufferWidth > $para['textwidth'] && count($lineBuffer) > 0) {
        $startedAt = $this->writeBufferLine(
          $lineBuffer,
          $para,
          $lineBufferWidth,
          $paraStyle['align'],
          $spaceCount,
          FALSE
        );
        //enough space - go to next line
        $this->Ln();
        $this->writeBufferLine(
          $buffer,
          $para,
          $bufferWidth,
          $paraStyle['align'],
          0,
          TRUE
        );
      } else {
        $startedAt = $this->writeBufferLine(
          array_merge($lineBuffer, $buffer),
          $para,
          $bufferWidth + $lineBufferWidth,
          $paraStyle['align'],
          $spaceCount,
          TRUE
        );
      }
      if (!$result) {
        $result = $startedAt;
      }
      //enough space for another line on this page?
      if ($this->checkPageBreakTrigger($para['lineheight'])) {
        //column or page break
        $this->acceptPageBreak();
      }
    }
    //clear element buffer
    $this->elementBuffer = array();
    return $result;
  }

  /**
  * write the current line buffer to pdf
  *
  * @param array &$lineBuffer
  * @param array $para paragraph measures
  * @param float $lineLength
  * @param array &$lineStyles
  * @param integer $spaceCount
  * @param string $align
  * @param boolean $lastLine optional, default value FALSE
  * @access public
  * @return FALSE|array output position or FALSE
  */
  function writeBufferLine(&$lineBuffer, $para, $lineLength, $align,
                           $spaceCount, $lastLine = FALSE) {
    if (count($lineBuffer) > 0) {
      $lastElement = end($lineBuffer);
      if (is_a($lastElement, 'papaya_pdf_wordbreak')) {
        $spaceCount--;
        $lineLength -= $lastElement->width;
        array_pop($lineBuffer);
        if (count($lineBuffer) < 1) {
          return FALSE;
        }
      }
      $this->inText = TRUE;
      $this->inColumn = TRUE;
      $this->inParagraph = TRUE;
      $this->inImage = FALSE;

      if ($lastLine && $align == 'justify') {
        $align = 'left';
      }
      // paint background
      $this->activateCurrentStyle($lineBuffer[0]->style);
      if (!empty($para['bgcolor'])) {
        $this->Rect(
          $para['left'],
          $this->GetY(),
          $para['width'],
          $para['lineheight'],
          'F'
        );
      }

      //if the current link target is set - the output triggers a marker
      if (!empty($this->currentLinkTarget)) {
        $this->_addLocalLinkTarget(
          $this->currentLinkTarget,
          $this->page,
          $this->GetY()
        );
      }

      $spaceIncWidth = 0;
      $y = $this->GetY();
      if (!empty($this->CurrentFont['desc']['CapHeight'])) {
        $textY = $this->GetY() + ($this->CurrentFont['desc']['CapHeight'] * $this->FontSize / 1000);
      } else {
        $textY = $this->GetY() + $para['textheight'];
      }
      $this->lastLineHeight = $para['lineheight'];
      switch ($align) {
      case 'justify':
        $x = $para['left'] + $para['indent'];
        //calc space size
        if (count($lineBuffer) > 1) {
          $spaceIncWidth = ($para['textwidth'] - $lineLength) / $spaceCount;
          if ($spaceIncWidth < 0) {
            $spaceIncWidth = 0;
          }
        }
        $width = $lineLength;
        break;
      case 'center':
        //get left start
        $x = $para['left'] +
          (($para['width'] - $lineLength - $para['indent']) / 2) + $para['indent'];
        $width = $lineLength;
        break;
      case 'right':
        //get left start
        $x = $para['left'] + $para['textwidth'] - $lineLength;
        $width = $para['textwidth'];
        break;
      case 'left':
      default:
        //get left start
        $x = $para['left'] + $para['indent'];
        $width = $para['textwidth'];
        break;
      }
      $result = array(
        'x' => $x,
        'y' => $textY,
        'w' => $width,
        'h' => $para['lineheight']
      );
      $lastStyle = $lineBuffer[0]->style;
      $lastSpaceLength = 0;
      foreach ($lineBuffer as $wordIdx => $word) {
        if (is_a($word, 'papaya_pdf_string')) {
          //style changes
          $this->activateCurrentStyle($word->style);
          $this->Text($x, $textY, $word->content);
          if ($lastSpaceLength > 0 &&
              $lastStyle['underline'] > 0 &&
              $lastStyle['underline'] == $word->style['underline']) {
            $this->_doUnderlineSpacing($x - $lastSpaceLength, $textY, $lastSpaceLength);
          }
          if (!empty($word->style['link'])) {
            $lineHeight = $para['lineheight'];
            if (0 === strpos($word->style['link'], '#')) {
              $linkTarget = $this->_addLocalLink(substr($word->style['link'], 1));
            } else {
              $linkTarget = $word->style['link'];
            }
            if ($lastStyle['link'] != $word->style['link']) {
              $lastSpaceLength = 0;
            }
            $this->Link(
              $x - $lastSpaceLength,
              $y,
              $this->GetStringWidth($word->content) + $lastSpaceLength,
              $lineHeight,
              $linkTarget
            );
          } else {
            $lastLinkTarget = '';
          }
          $lastStyle['underline'] == $word->style['underline'];
          $x += $word->width;
        } else {
          $lastSpaceLength = $word->width + $spaceIncWidth;
          $x += $word->width + $spaceIncWidth;
        }
      }
      $endPos = $x;
      $result['w'] = $endPos - $para['left'] - $para['indent'];
    } else {
      $result = FALSE;
    }
    return $result;
  }

  /**
  * paint an underline for a spacing between to texts
  * @param float $x
  * @param float $y
  * @param float $width
  * @return void
  */
  function _doUnderlineSpacing($x, $y, $width) {
    $up = $this->CurrentFont['up'];
    $ut = $this->CurrentFont['ut'];
    $w = $width;
    $this->_out(
      sprintf(
        '%.3f %.3f %.3f rg',
        $this->currentFGColor[0] / 255,
        $this->currentFGColor[1] / 255,
        $this->currentFGColor[2] / 255
      )
    );
    $this->_out(
      sprintf(
        '%.2f %.2f %.2f %.2f re f',
        $x * $this->k,
        ($this->h - ($y - $up / 1000 * $this->FontSize)) * $this->k,
        $w * $this->k,
        -$ut / 1000 * $this->FontSizePt
      )
    );
    $this->_out(
      sprintf(
        '%.3f %.3f %.3f rg',
        $this->currentBGColor[0] / 255,
        $this->currentBGColor[1] / 255,
        $this->currentBGColor[2] / 255
      )
    );
  }

  /**
  * write list bullet to pdf document
  *
  * @param array &$paraStyle
  * @param array $para
  * @access public
  */
  function writeListBullet(&$paraStyle, $para) {
    if ($this->getListIndent($paraStyle) > 0) {
      $font = array(
        'family' => $this->FontFamily,
        'style' => $this->FontStyle,
        'sizePt' => $this->FontSizePt
      );

      $fontToUse = (isset($this->fonts['user-symbol']))
        ? 'user-symbol' : $this->defaultListBulletFont;
      $bulletToUse = $paraStyle['bullet-chars'];

      $y = $this->GetY();
      if (!empty($this->CurrentFont['desc']['CapHeight'])) {
        $textY = $this->GetY() + ($this->CurrentFont['desc']['CapHeight'] * $this->FontSize / 1000);
      } else {
        $textY = $this->GetY() + $para['textheight'];
      }

      if (!$this->inOrderedList) {
        $this->setCurrentFont($fontToUse, '', $this->FontSizePt);
      } else {
        $bulletToUse = (string)$this->inOrderedListAt;
      }

      if ($paraStyle['align'] == 'right') {
        $x = $para['left'] + $para['width'] - $para['indent'];
        $bulletIndent = ($para['indent'] - $this->GetStringWidth($bulletToUse)) / 2;
        $this->Text(
          $x + $bulletIndent, $textY, $bulletToUse);
      } else {
        $x = $para['left'];
        $bulletIndent = ($para['indent'] - $this->GetStringWidth($bulletToUse)) / 2;
        $this->Text(
          $x + $bulletIndent, $textY, $bulletToUse);
      }

      if (!$this->inOrderedList) {
        $this->setCurrentFont($font['family'], $font['style'], $font['sizePt']);
      }
    }
  }

  /**
  * get paragraph size
  *
  * @param array &$paraStyle
  * @access public
  * @return array
  */
  function getParagraphSize(&$paraStyle) {
    $result = array(
      'width' => $this->getLineWidth(),
      'lineheight' => $this->getLineHeight(),
      'textheight' => $this->getTextHeight(),
      'indent' => $this->getListIndent($paraStyle),
      'left' => $this->getLineLeft() + $this->lPadding,
      'bgcolor' => empty($paraStyle['bgcolor']) ? '' : $paraStyle['bgcolor']
    );
    $result['textwidth'] = $result['width'] - $result['indent'];
    return $result;
  }

  /**
  * begin a new paragraph
  *
  * @access public
  */
  function beginParagraph($feedHeight = 0) {
    if (!$this->inParagraph) {
      if ($this->inColumn && (!$this->inImage)) {
        if (empty($this->paragraphMargin)) {
          $this->Ln();
        } else {
          $this->Ln($this->paragraphMargin);
        }
      }
      $this->inParagraph = TRUE;
      $this->paragraphMargin = $feedHeight;
      $this->inColumn = TRUE;
      $this->inImage = FALSE;
      $this->inText = FALSE;
    }
  }

  /**
  * end current paragraph
  *
  * @access public
  */
  function endParagraph($feedHeight = 0) {
    if (isset($this->inParagraph) && $this->inParagraph) {
      if (!$this->inImage) {
        if (empty($this->paragraphMargin)) {
          $this->Ln();
        } else {
          $this->setDrawColor(0, 0, 255);
          $this->Ln($this->paragraphMargin);
          $this->setDrawColor(255, 0, 255);
        }
      }
      $this->inParagraph = FALSE;
      $this->paragraphMargin = $feedHeight;
      $this->inImage = FALSE;
      $this->inText = FALSE;
    }
  }

  /**
  * Output an image
  *
  * @param string $fileName
  * @access public
  */
  function writeImage($fileName, $attr) {
    $attr = $this->simplify($attr);
    if (isset($attr['dpi']) && $attr['dpi'] >= 75 && $attr['dpi'] <= 300) {
      $mediaK = $attr['dpi'] / 25.4;
    } else {#
      $mediaK = $this->mediaK;
    }
    if ($this->elementMode == 'table') {
      $imageSize = $this->calcImageSize($fileName, $this->getLineWidth(), $mediaK);
      $this->addTableContentImage(
        $fileName, $imageSize['width'], $imageSize['height']
      );
    } else {
      $this->writeBuffer();
      $imageSize = $this->calcImageSize($fileName, $this->getLineWidth(), $mediaK);
      if ($this->inParagraph && $this->inColumn) {
        $this->endParagraph();
      }
      if ($this->checkPageBreakTrigger($imageSize['height'])) {
        $this->AcceptPageBreak();
      }
      $this->activateCurrentStyle($this->getCurrentElementStyle());

      //the width could change at a column or page break
      $lineWidth = $this->getLineWidth();
      $imageSize = $this->calcImageSize($fileName, $lineWidth, $mediaK);
      $lineHeight = $this->getLineHeight();
      $imageBottom = $this->GetY() + (ceil($imageSize['height'] / $lineHeight) * $lineHeight);

      $x = $this->getLineLeft();
      $y = $this->GetY();
      $this->Rect($x, $y, $this->getLineWidth(), $imageBottom - $y, 'F');
      $this->Image($fileName, $x, $y, $imageSize['width'], $imageSize['height']);
      if (!empty($this->currentLink)) {
        $this->Link($x, $y, $imageSize['width'], $imageSize['height'], $this->currentLink);
      }
      $this->SetY($imageBottom);

      if ($this->checkPageBreakTrigger($this->getLineHeight() * 2)) {
        $this->AcceptPageBreak();
      }
      $this->Rect(
        $this->getLineLeft(), $this->GetY(), $this->getLineWidth(), $lineHeight, 'F'
      );

      $this->inImage = TRUE;
      $this->inColumn = TRUE;
      $this->inParagraph = TRUE;
    }
  }

  /**
  * write a table
  *
  * @access public
  */
  function writeTable() {
    if (isset($this->tableBuffer)) {
      $this->beginParagraph();
      $rowCount = $this->tableBuffer->rowCount();
      if ($rowCount > 0) {
        $row = &$this->tableBuffer->getRowByIndex(0);
        $row->setLeftTop(
          $this->page, $this->currentColumn, $this->GetX(), $this->GetY()
        );
        for ($i = 0; $i < $rowCount; $i++) {
          $this->writeTableRow($i);
        }
        $this->setY($this->GetY() + 1);
      }
    }
  }

  /**
  * write a table row
  *
  * @param integer $rowIdx
  * @access public
  */
  function writeTableRow($rowIdx) {
    if (isset($this->tableBuffer)) {
      if ($row = &$this->tableBuffer->getRowByIndex($rowIdx)) {
        $leftTop = $row->getLeftTop();
        if ($leftTop['y'] + ceil($row->minHeight) > $this->PageBreakTrigger &&
            !$this->InFooter) {
          $this->AcceptPageBreak();
          $row->setLeftTop(
            $this->page, $this->currentColumn, $this->GetX(), $this->GetY()
          );
          $leftTop = $row->getLeftTop();
        }

        $this->gotoPageColumn($leftTop['page'], $leftTop['col']);
        $this->writeTableBorderH(
          $leftTop['x'],
          $leftTop['y'],
          $this->tableBuffer->width,
          $this->tableBuffer->borderColor
        );

        $cellCount = $row->cellCount();
        for ($i = 0; $i < $cellCount; $i++) {
          $this->writeTableCell($row->getCellByIndex($i), $leftTop);
          $row->setLeftBottom($this->page, $this->currentColumn, $this->GetX(), $this->GetY());
        }

        $leftBottom = $row->getLeftBottom();
        if ($leftTop['page'] == $leftBottom['page'] &&
            $leftTop['col'] == $leftBottom['col']) {
          $y = $leftTop['y'];
          $rowHeight = $leftBottom['y'] - $leftTop['y'];
          for ($i = 0; $i < $cellCount; $i++) {
            $cell = &$row->getCellByIndex($i);
            $left = $cell->getLeft();
            if ($i == 0) {
              $this->writeTableBorderV(
                $this->lMargin + $left, $y, $rowHeight, $this->tableBuffer->borderColor
              );
            }
            $this->writeTableBorderV(
              $this->lMargin + $left + $cell->getWidth(),
              $y,
              $rowHeight,
              $this->tableBuffer->borderColor
            );
          }
          $this->writeTableBorderH(
            $leftBottom['x'],
            $leftBottom['y'],
            $this->tableBuffer->width,
            $this->tableBuffer->borderColor
          );
        } else {
          $pageColCount = $this->confObj->pdfColumnCount($this, $this->currentPageTemplate);
          for ($page = $leftBottom['page']; $page >= $leftTop['page']; $page--) {
            for ($pageCol = $pageColCount; $pageCol >= 1; $pageCol--) {
              if (($page == $leftBottom['page'] && $pageCol > $leftBottom['col']) ||
                  ($page == $leftTop['page'] && $pageCol < $leftTop['col'])) {
                continue;
              }
              $this->gotoPageColumn($page, $pageCol);
              $bottom = $this->h - $this->bMargin;
              if ($page == $leftTop['page'] && $pageCol == $leftTop['col']) {
                //table start column
                $this->writeTableBorderH(
                  $this->lMargin,
                  $bottom,
                  $this->tableBuffer->width,
                  $this->tableBuffer->borderColor
                );
                $y = $leftTop['y'];
                $rowHeight = $bottom - $leftTop['y'];
              } elseif ($page == $leftBottom['page'] && $pageCol == $leftBottom['col']) {
                //table end column
                $this->writeTableBorderH(
                  $this->lMargin,
                  $this->tMargin,
                  $this->tableBuffer->width,
                  $this->tableBuffer->borderColor
                );
                $y = $this->tMargin;
                $rowHeight = $leftBottom['y'] - $this->tMargin;
                $this->writeTableBorderH(
                  $this->lMargin,
                  $leftBottom['y'],
                  $this->tableBuffer->width,
                  $this->tableBuffer->borderColor
                );
              } else {
                //all columns between
                $this->writeTableBorderH(
                  $this->lMargin,
                  $this->tMargin,
                  $this->tableBuffer->width,
                  $this->tableBuffer->borderColor
                );
                $this->writeTableBorderH(
                  $this->lMargin, $bottom, $this->tableBuffer->width);
                $y = $this->tMargin;
                $rowHeight = $bottom - $this->tMargin;
              }
              for ($i = 0; $i < $cellCount; $i++) {
                $cell = &$row->getCellByIndex($i);
                $left = $cell->getLeft();
                if ($i == 0) {
                  $this->writeTableBorderV(
                    $this->lMargin + $left,
                    $y,
                    $rowHeight,
                    $this->tableBuffer->borderColor
                  );
                }
                $this->writeTableBorderV(
                  $this->lMargin + $left + $cell->getWidth(),
                  $y,
                  $rowHeight,
                  $this->tableBuffer->borderColor
                );
              }
            }
          }
        }
        $this->gotoPageColumn($leftBottom['page'], $leftBottom['col']);
        $this->setY($leftBottom['y']);
      }
    }
  }

  /**
  * write a table cell
  *
  * @param integer &$cell
  * @param array $rowLeftTop
  * @access public
  */
  function writeTableCell(&$cell, $rowLeftTop) {
    if (isset($cell)) {
      if ($this->page != $rowLeftTop['page'] ||
        $this->currentColumn != $rowLeftTop['col']) {
        $this->gotoPageColumn($rowLeftTop['page'], $rowLeftTop['col']);
      };
      $x = $rowLeftTop['x'] + $cell->getLeft();
      if ($this->tableBuffer->border > 0) {
        $y = $rowLeftTop['y'] + $this->tableBuffer->padding;
      } else {
        $y = $rowLeftTop['y'];
      }
      $this->setXY($x, $y);
      $this->currentTableCell = &$cell;
      $this->elementMode = 'tableoutput';
      $this->inColumn = FALSE;
      $this->inParagraph = FALSE;
      $this->inText = FALSE;
      $this->setAttributeStyles($cell->getAttributes(), TRUE);
      $cell->outputData($this);
      $this->setAttributeStyles($cell->getAttributes(), FALSE);
      $this->writeBuffer();
      $this->endParagraph();
      $this->elementMode = 'text';
      $null = NULL;
      $this->currentTableCell = &$null;
    }
  }

  /**
  * paint a horizontal table border
  *
  * @param float $x
  * @param float $y
  * @param float $width
  * @param array $color - default black
  * @access public
  */
  function writeTableBorderH($x, $y, $width, $color = NULL) {
    if ($this->tableBuffer->border > 0) {
      if (isset($color)) {
        $this->setDrawColor($color[0], $color[1], $color[2]);
      } else {
        $this->setDrawColor(0, 0, 0);
      }
      $this->Line($x, $y, $x + $width, $y);
    }
  }

  /**
  * paint a vertical table border
  *
  * @param float $x
  * @param float $y
  * @param float $width
  * @param array $color - default black
  * @access public
  */
  function writeTableBorderV($x, $y, $height, $color = NULL) {
    if ($this->tableBuffer->border > 0) {
      if (isset($color)) {
        $this->setDrawColor($color[0], $color[1], $color[2]);
      } else {
        $this->setDrawColor(0, 0, 0);
      }
      $this->Line($x, $y, $x, $y + $height);
    }
  }

  /**
  * line break
  *
  * @param $height
  * @access public
  */
  function Ln($height = NULL) {
    parent::Ln(isset($height) ? $height : $this->lastLineHeight);
  }

  /**
  * open an element
  *
  * @param array $attr
  * @access public
  */
  function openElement($attr) {
    $attr = $this->simplify($attr);
    if (isset($attr['align']) &&
        in_array($attr['align'], array('right', 'justify', 'left'))) {
      $this->currentAlign = $attr['align'];
    }
    $this->inColumn = FALSE;
    $this->inParagraph = FALSE;
    $this->inText = FALSE;
  }

  /**
  * close/end the current element
  *
  * @param array $attr
  * @access public
  */
  function closeElement($attr) {
    $attr = $this->simplify($attr);
    $this->writeBuffer();
    $this->inParagraph = FALSE;
  }

  /**
  * open a section
  *
  * @param array $attr
  * @access public
  */
  function openSection($attr) {
    $attr = $this->simplify($attr);
    if ((isset($attr['break-before']) && $attr['break-before'] == 'yes') ||
        $this->breakBeforeNextSection) {
      if (isset($attr['page']) && $attr['page'] != '') {
        $this->confObj->pdfAddPage($this, $attr['page'], TRUE);
      } else {
        $this->confObj->pdfAddPage($this, $this->currentPageTemplate, TRUE);
      }
      $this->breakBeforeNextSection = FALSE;
    }
    $this->setPageTitle(
      isset($attr['title']) ? $attr['title'] : NULL,
      isset($attr['subtitle']) ? $attr['subtitle'] : NULL
    );
    $this->setAttributeStyles($attr, TRUE);
    $this->beginParagraph();
  }

  /**
  * close current section
  *
  * @param array $attr
  * @access public
  */
  function closeSection($attr) {
    $attr = $this->simplify($attr);
    $this->writeBuffer();
    $this->endParagraph();

    $this->setAttributeStyles($attr, FALSE);

    if (isset($attr['break-after']) && $attr['break-after'] == 'yes') {
      $this->breakBeforeNextSection = TRUE;
    }
  }

  /**
  * Activate layout for a tag (add to status stacks)
  * @param string $tag
  * @return void
  */
  function enableTagLayout($tag) {
    if ($tagLayout = $this->confObj->getTagLayout($tag)) {
      if (isset($tagLayout['font'])) {
        if (isset($tagLayout['font']['bold']) && $tagLayout['font']['bold']) {
          $this->setStyle('b', TRUE);
        }
        if (isset($tagLayout['font']['italic']) && $tagLayout['font']['italic']) {
          $this->setStyle('i', TRUE);
        }
        if (isset($tagLayout['font']['family']) && $tagLayout['font']['family']) {
          $this->addAttributeStyle('font-family', $tagLayout['font']['family']);
        }
        if (isset($tagLayout['font']['size']) && $tagLayout['font']['size']) {
          $this->addAttributeStyle('font-size', $tagLayout['font']['size']);
        }
      }
      if (isset($tagLayout['fgcolor']) && $tagLayout['fgcolor']) {
        $this->addAttributeStyle('fgcolor', $tagLayout['fgcolor']);
      }
    } elseif ($tag == 'b') {
      $this->setStyle('b', TRUE);
    } elseif ($tag == 'i') {
      $this->setStyle('i', TRUE);
    } elseif ($tag == 'u') {
      $this->setStyle('u', TRUE);
    }
  }

  /**
  * Deactivate layout for a tag (remove from status stacks)
  * @param string $tag
  * @return void
  */
  function disableTagLayout($tag) {
    if ($tagLayout = $this->confObj->getTagLayout($tag)) {
      if (isset($tagLayout['font'])) {
        if (isset($tagLayout['font']['bold']) && $tagLayout['font']['bold']) {
          $this->setStyle('b', FALSE);
        }
        if (isset($tagLayout['font']['italic']) && $tagLayout['font']['italic']) {
          $this->setStyle('i', FALSE);
        }
        if (isset($tagLayout['font']['family']) && $tagLayout['font']['family']) {
          $this->removeAttributeStyle('font-family', $tagLayout['font']['family']);
        }
        if (isset($tagLayout['font']['size']) && $tagLayout['font']['size']) {
          $this->removeAttributeStyle('font-size', $tagLayout['font']['size']);
        }
      }
      if (isset($tagLayout['fgcolor']) && $tagLayout['fgcolor']) {
        $this->removeAttributeStyle('fgcolor', $tagLayout['fgcolor']);
      }
    } elseif ($tag == 'b') {
      $this->setStyle('b', FALSE);
    } elseif ($tag == 'i') {
      $this->setStyle('i', FALSE);
    } elseif ($tag == 'u') {
      $this->setStyle('u', FALSE);
    }
  }

  /**
  * open a tag
  *
  * @param string $tag
  * @param array $attr
  * @access public
  */
  function openTag($tag, $attr) {
    $attr = $this->simplify($attr);
    $this->enableTagLayout($tag);
    if (!empty($attr['id'])) {
      $this->currentLinkTarget = $attr['id'];
    }
    switch ($tag) {
    case 'table' :
      $this->writeBuffer();
      if ($this->inText) {
        $this->endParagraph();
      }
      $this->openTable($attr);
      return;
    case 'tr' :
      $this->openTableRow($attr);
      return;
    case 'th' :
      $this->openTableCell($attr, TRUE);
      return;
    case 'td' :
      $this->openTableCell($attr);
      return;
    }
    if ($this->elementMode == 'table') {
      $this->openTableContentTag($tag, $attr);
    } else {
      if (empty($attr['margin-bottom'])) {
        $marginBottom = 0;
      } else {
        $marginBottom = (float)$attr['margin-bottom'];
      }
      switch ($tag) {
      case 'pdf-page' :
        $this->writeText($this->page);
        break;
      case 'pdf-pagecount' :
        $this->writeText(count($this->pages));
        break;
      case 'pdf-position' :
        $separator = empty($attr['separator']) ? '/' : $attr['separator'];
        $prefix = empty($attr['prefix']) ? '' : $attr['prefix'];
        $suffix = empty($attr['suffix']) ? '' : $attr['suffix'];
        $this->writeText($prefix.$this->page.$separator.count($this->pages).$suffix);
        break;
      case 'pdf-title' :
        if (!empty($this->pageInfos[$this->page]['title'])) {
          $prefix = empty($attr['prefix']) ? '' : $attr['prefix'];
          $suffix = empty($attr['suffix']) ? '' : $attr['suffix'];
          $this->writeText($prefix.$this->pageInfos[$this->page]['title'].$suffix);
        }
        break;
      case 'pdf-subtitle' :
        if (!empty($this->pageInfos[$this->page]['subtitle'])) {
          $prefix = empty($attr['prefix']) ? '' : $attr['prefix'];
          $suffix = empty($attr['suffix']) ? '' : $attr['suffix'];
          $this->writeText($prefix.$this->pageInfos[$this->page]['subtitle'].$suffix);
        }
        break;
      case 'bookmark' :
        if (!empty($attr['title'])) {
          if (!empty($attr['level']) && $attr['level'] > 0 && $attr['level'] < 10) {
            $level = (int)$attr['level'];
          } else {
            $level = 0;
          }
          if (!empty($attr['page-start']) && $attr['page-start'] == 'yes') {
            $position = 0;
          } else {
            $position = -1;
          }
          $this->addBookmark($attr['title'], $level, $position);
        }
        break;
      case 'h1' :
      case 'h2' :
      case 'h3' :
      case 'h4' :
      case 'h5' :
        $this->writeBuffer();
        $this->setAttributeStyles($attr, TRUE);
        if ($this->inText) {
          $this->endParagraph();
        }
        $this->beginParagraph($marginBottom);
        if (!empty($attr['title'])) {
          $level = substr($tag, -1) - 1;
          $this->addBookmark($attr['title'], $level);
        }
        break;
      case 'p' :
        $this->writeBuffer();
        $this->setAttributeStyles($attr, TRUE);
        if ($this->inText) {
          $this->endParagraph();
        }
        $this->beginParagraph($marginBottom);
        break;
      case 'ul' :
        $this->writeBuffer();
        if ($this->inText) {
          $this->endParagraph();
        }
        $this->setStyle('list', TRUE);
        $this->setAttributeStyles($attr, TRUE);
        $this->beginParagraph($marginBottom);
        break;
      case 'ol' :
        $this->writeBuffer();
        if ($this->inText) {
          $this->endParagraph();
        }
        $this->inOrderedList = TRUE;
        $this->inOrderedListAt = 0;
        $this->setStyle('list', TRUE);
        $this->setAttributeStyles($attr, TRUE);
        $this->beginParagraph($marginBottom);
        break;
      case 'li' :
        if ($this->inOrderedList) {
          $this->inOrderedListAt++;
        }
        break;
      case 'a' :
        if (!empty($attr['name'])) {
          $this->currentLinkTarget = $attr['name'];
        } elseif (!empty($attr['href']) &&
                  substr(trim($attr['href']), 0, 1) == '#') {
          $this->currentLink = trim($attr['href']);
          $this->setStyle('u', TRUE);
        } else {
          $href = $this->docObj->getLinkForPDF($attr);
          if (!empty($href)) {
            $this->currentLink = $href;
            $this->setStyle('u', TRUE);
          }
        }
        break;
      case 'image' :
        $this->writeBuffer();
        if ($this->elementMode == 'tableoutput' && isset($attr['src']) &&
            file_exists($attr['src']) && is_file($attr['src']) &&
            is_readable($attr['src'])) {
          $this->writeImage($attr['src'], $attr);
        }
        break;
      case 'br':
        $this->writeBuffer();
        if ($this->inColumn) {
          $this->Ln();
          if ($this->checkPagebreakTrigger($this->getLineHeight())) {
            $this->acceptPageBreak();
          }
          if (!(isset($attr['transparent']) && $attr['transparent'] == 'yes')) {
            $this->Rect(
              $this->getLineLeft(),
              $this->GetY(),
              $this->getLineWidth(),
              $this->getLineHeight(),
              'F'
            );
          }
        }
        break;
      }
    }
  }

  /**
  * close current tag
  *
  * @param string $tag
  * @param array $attr
  * @access public
  */
  function closeTag($tag, $attr) {
    $attr = $this->simplify($attr);
    $this->disableTagLayout($tag);
    switch ($tag) {
    case 'table' :
      $this->closeTable();
      return;
    case 'tr' :
      $this->closeTableRow();
      return;
    case 'th' :
      $this->closeTableCell(TRUE);
      return;
    case 'td' :
      $this->closeTableCell();
      return;
    }
    if ($this->elementMode == 'table') {
      $this->closeTableContentTag($tag, $attr);
    } else {
      switch ($tag) {
      case 'h1' :
      case 'h2' :
      case 'h3' :
      case 'h4' :
      case 'h5' :
        $this->writeBuffer();
        $this->endParagraph();
        $this->setAttributeStyles($attr, FALSE);
        break;
      case 'p' :
        $this->writeBuffer();
        $this->endParagraph();
        $this->setAttributeStyles($attr, FALSE);
        break;
      case 'ul' :
        $this->writeBuffer();
        $this->setStyle('list', FALSE);
        $this->setAttributeStyles($attr, FALSE);
        break;
      case 'ol' :
        $this->writeBuffer();
        $this->setStyle('ordered-list', FALSE);
        $this->setAttributeStyles($attr, FALSE);
        $this->inOrderedList = FALSE;
        $this->inOrderedListAt = 0;
        $this->setStyle('list', FALSE);
        break;
      case 'li' :
        $this->writeBuffer();
        $this->Ln();
        break;
      case 'a' :
        if (!empty($this->currentLink)) {
          $this->setStyle('u', FALSE);
          $this->currentLink = NULL;
        }
        break;
      }
    }
  }

  /**
  * open a table (create table buffer)
  *
  * @param array $attr
  * @access public
  */
  function openTable($attr) {
    $this->writeBuffer();
    include_once(dirname(__FILE__).'/papaya_pdf_table.php');
    if (!isset($this->tableBuffer)) {
      $this->tableBuffer = new papaya_pdf_table($this, $attr);
    }
  }

  /**
  * open a table row (write to table buffer)
  *
  * @param array $attr
  * @access public
  */
  function openTableRow($attr) {
    $this->writeBuffer();
    if (isset($this->tableBuffer)) {
      $this->tableBuffer->addRow($attr);
    }
  }

  /**
  * open a table cell (write to table buffer)
  *
  * @param array $attr
  * @access public
  */
  function openTableCell($attr) {
    $this->writeBuffer();
    if (isset($this->tableBuffer)) {
      $this->tableBuffer->addCell($attr);
      $this->elementMode = 'table';
    }
  }

  /**
  * add table cell text content  (write to table buffer)
  *
  * @param string $str text
  * @access public
  */
  function addTableContentText($str) {
    if (isset($this->tableBuffer)) {
      $this->tableBuffer->addCellContent($str);
    }
  }

  /**
  * add table cell image content (write to table buffer)
  *
  * @param string $fileName
  * @param float $width
  * @param float $height
  * @access public
  */
  function addTableContentImage($fileName, $width, $height) {
    if (isset($this->tableBuffer)) {
      $this->tableBuffer->addCellContentImage($fileName, $width, $height);
    }
  }

  /**
  * open a tag inside a table cell (write to table buffer)
  *
  * @param string $tag
  * @param array $attr
  * @access public
  */
  function openTableContentTag($tag, $attr) {
    if (isset($this->tableBuffer)) {
      $this->tableBuffer->addCellContentTag($tag, $attr);
    }
  }

  /**
  * close a tag inside a table cell (write to table buffer)
  *
  * @param $tag
  * @param $attr
  * @access public
  */
  function closeTableContentTag($tag, $attr) {
    if (isset($this->tableBuffer)) {
      $this->tableBuffer->endCellContentTag($tag, $attr);
    }
  }

  /**
  * close current table cell (write to table buffer)
  *
  * @access public
  */
  function closeTableCell() {
    if (isset($this->tableBuffer)) {
      $this->elementBuffer = array();
      $this->tableBuffer->endCell();
      $this->elementMode = 'text';
    }
  }

  /**
  * close current table row (write to table buffer)
  *
  * @access public
  */
  function closeTableRow() {
    $this->writeBuffer();
    if (isset($this->tableBuffer)) {
      //close cell if exists
      $this->closeTableCell();
      //close row
      $this->tableBuffer->endRow();
    }
  }

  /**
  * close table, calculate widths and trigger output, reset table buffer
  *
  * @access public
  */
  function closeTable() {
    $this->writeBuffer();
    if (isset($this->tableBuffer)) {
      //close row if exists
      $this->closeTableRow();
      $this->tableBuffer->calcColumnWidths($this->getColumnWidth());
      $this->writeTable();
      unset($this->tableBuffer);
    }
  }

  /**
  * set integer text style
  *
  * @param string $tag
  * @param boolean $enable
  * @access public
  */
  function setStyle($tag, $enable) {
    switch ($tag) {
    case 'b':
      $this->currentStyle['bold'] += ($enable ? 1 : -1);
      break;
    case 'i':
      $this->currentStyle['italic'] += ($enable ? 1 : -1);
      break;
    case 'u':
      $this->currentStyle['underline'] += ($enable ? 1 : -1);
      break;
    case 'list':
      $this->currentStyle['list'] += ($enable ? 1 : -1);
      break;
    case 'bullet-chars':
      $this->currentStyle['bullet-chars'] += ($enable ? 1: -1);
      break;
    }
  }

  /**
  * set string text style
  *
  * @param $attr
  * @param $enable
  * @access public
  */
  function setAttributeStyles($attr, $enable) {
    $attr = $this->simplify($attr);
    $attrNames = array('align', 'bgcolor', 'fgcolor', 'bullet-chars', 'font-family', 'font-size');
    foreach ($attrNames as $attrName) {
      if (isset($attr[$attrName])) {
        if ($enable) {
          $this->addAttributeStyle($attrName, $attr[$attrName]);
        } else {
          $this->removeAttributeStyle($attrName, $attr[$attrName]);
        }
      }
    }
  }

  /**
  * add string text style
  *
  * @param string $attr
  * @param string $value
  * @access public
  */
  function addAttributeStyle($attr, $value) {
    if (isset($value) && $value != '') {
      switch ($attr) {
      case 'bullet-chars':
        if (is_array($this->currentStyle['bullet-chars'])) {
          array_unshift($this->currentStyle['bullet-chars'], $value);
        } else {
          $this->currentStyle['bullet-chars'] = $value;
        }
        break;
      case 'align':
        if (in_array($value, array('left', 'right', 'center', 'justify'))) {
          if (is_array($this->currentStyle['align'])) {
            array_unshift($this->currentStyle['align'], (string)$value);
          } else {
            $this->currentStyle['align'] = array((string)$value);
          }
        }
        break;
      case 'bgcolor':
        if (is_array($this->currentStyle['bgcolor'])) {
          array_unshift($this->currentStyle['bgcolor'], (string)$value);
        } else {
          $this->currentStyle['bgcolor'] = array((string)$value);
        }
        break;
      case 'fgcolor':
        if (is_array($this->currentStyle['fgcolor'])) {
          array_unshift($this->currentStyle['fgcolor'], (string)$value);
        } else {
          $this->currentStyle['fgcolor'] = array((string)$value);
        }
        break;
      case 'font-family':
        if (is_array($this->currentStyle['font-family'])) {
          array_unshift($this->currentStyle['font-family'], (string)$value);
        } else {
          $this->currentStyle['font-family'] = array((string)$value);
        }
        break;
      case 'font-size':
        if (is_array($this->currentStyle['font-size'])) {
          array_unshift($this->currentStyle['font-size'], (int)$value);
        } else {
          $this->currentStyle['font-size'] = array((int)$value);
        }
        break;
      }
    }
  }

  /**
  * remove string text style
  *
  * @param string $attr
  * @param string $value
  * @access public
  */
  function removeAttributeStyle($attr, $value) {
    if (isset($value) && $value != '') {
      switch ($attr) {
      case 'bullet-chars':
        @array_shift($this->currentStyle['bullet-chars']);
        if (count($this->currentStyle['bullet-chars']) <= 0) {
          $this->currentStyle['bullet-chars'] = $this->defaultStyle['bullet-chars'];
        }
        break;
      case 'align':
        if (in_array($value, array('left', 'right', 'center', 'justify'))) {
          @array_shift($this->currentStyle['align']);
          if (count($this->currentStyle['align']) <= 0) {
            $this->currentStyle['align'] = $this->defaultStyle['align'];
          }
        }
        break;
      case 'bgcolor':
        @array_shift($this->currentStyle['bgcolor']);
        if (count($this->currentStyle['bgcolor']) <= 0) {
          $this->currentStyle['bgcolor'] = $this->defaultStyle['bgcolor'];
        }
        break;
      case 'fgcolor':
        @array_shift($this->currentStyle['fgcolor']);
        if (count($this->currentStyle['bgcolor']) <= 0) {
          $this->currentStyle['bgcolor'] = $this->defaultStyle['bgcolor'];
        }
        break;
      case 'font-family':
        if (isset($this->currentStyle['font-family']) &&
            is_array($this->currentStyle['font-family'])) {
          @array_shift($this->currentStyle['font-family']);
          if (count($this->currentStyle['font-family']) <= 0) {
            $this->currentStyle['font-family'] = NULL;
          }
        }
        break;
      case 'font-size':
        if (isset($this->currentStyle['font-size']) &&
            is_array($this->currentStyle['font-size'])) {
          @array_shift($this->currentStyle['font-size']);
          if (count($this->currentStyle['font-size']) <= 0) {
            $this->currentStyle['font-size'] = NULL;
          }
        }
        break;
      }
    }
  }

  /**
  * get current text style
  *
  * @access public
  * @return array
  */
  function getCurrentElementStyle() {
    $result = $this->defaultStyle;
    if (isset($this->currentStyle)) {
      foreach ($this->defaultStyle as $styleItem => $styleDefault) {
        $result[$styleItem] = $styleDefault;
        if (isset($this->currentStyle[$styleItem])) {
          if (is_array($this->currentStyle[$styleItem]) &&
              ($this->currentStyle[$styleItem]) > 0) {
            $result[$styleItem] = reset($this->currentStyle[$styleItem]);
          } elseif (is_string($this->currentStyle[$styleItem]) ||
                    is_int($this->currentStyle[$styleItem])) {
            $result[$styleItem] = $this->currentStyle[$styleItem];
          }
        }
      }
    }
    if (!empty($this->currentLink)) {
      $result['link'] = $this->currentLink;
    }
    return $result;
  }

  /**
  * set an remember current font (elements can have different fonts)
  *
  * @param $family
  * @param $size
  * @access private
  */
  function setCurrentFont($family, $style, $size) {
    $this->currentFontData['font-family'] = $family;
    $this->currentFontData['font-size'] = (int)$size;
    $this->currentFontData['bold'] = FALSE;
    $this->currentFontData['italic'] = FALSE;
    $this->currentFontData['underline'] = FALSE;
    if (FALSE !== strpos($style, 'B')) {
      $this->currentFontData['bold'] = TRUE;
    }
    if (FALSE !== strpos($style, 'I')) {
      $this->currentFontData['italic'] = TRUE;
    }
    if (FALSE !== strpos($style, 'U')) {
      $this->currentFontData['underline'] = TRUE;
    }
    $this->SetFont($family, $style, (int)$size);
  }

  /**
  * activate current style
  *
  * @param array|NULL curStyle
  * @access public
  */
  function activateCurrentStyle($curStyle = NULL) {
    $style = '';
    if (
        (isset($curStyle['bold']) && $curStyle['bold'] > 0) ||
        $this->currentFontData['bold']
       ) {
      $style .= 'B';
    }
    if (
        (isset($curStyle['italic']) && $curStyle['italic'] > 0) ||
        $this->currentFontData['italic']
       ) {
      $style .= 'I';
    }
    if (
        (isset($curStyle['underline']) && $curStyle['underline'] > 0) ||
        $this->currentFontData['underline']
       ) {
      $style .= 'U';
    }
    if (isset($curStyle['font-family'])) {
      $fontFamily = $curStyle['font-family'];
    } elseif (isset($this->currentFontData['font-family'])) {
      $fontFamily = $this->currentFontData['font-family'];
    } else {
      $fontFamily = NULL;
    }
    if (isset($curStyle['font-size'])) {
      $fontSize = $curStyle['font-size'];
    } elseif (isset($this->currentFontData['font-size'])) {
      $fontSize = $this->currentFontData['font-size'];
    } else {
      $fontSize = NULL;
    }
    if ($fontFamily && $fontSize) {
      $this->SetFont($fontFamily, $style, $fontSize);
    } elseif ($curStyle['font-family']) {
      $this->SetFont($fontFamily, $style);
    } elseif ($fontSize) {
      $this->SetFont('', $style, $fontSize);
    } else {
      $this->SetFont('', $style);
    }
    if (isset($curStyle)) {
      $this->setFGColor($curStyle['fgcolor']);
      $this->setBGColor($curStyle['bgcolor']);
    }
  }

  /**
  * get current list indent
  *
  * @param array $curStyle
  * @access public
  */
  function getListIndent($curStyle) {
    if (isset($curStyle) && ($curStyle['list'] >= 0)) {
      return $this->indentStep * $curStyle['list'];
    }
    return 0;
  }

  /**
  * Simplify node attributes list to an array
  * @param DOMNodelist|array $var
  * @return array|NULL
  */
  function simplify(&$var) {
    if (is_array($var)) {
      return $var;
    } elseif (is_object($var)) {
      $result = array();
      for ($i = 0; $i < $var->length; $i++) {
        $node = &$var->item($i);
        $result[$node->name] = $node->value;
      }
      return $result;
    } else {
      return NULL;
    }
  }

  /**
  * Import static pages from another pdf
  * @param DOMNode $node
  * @param string $fileName
  * @return void
  */
  function importPages(&$node, $fileName) {
    if ($node->hasAttribute('page-no')) {
      $pageAttr = $node->getAttribute('page-no');
      $pageNumbers = explode(',', $pageAttr);
    }
    if (!(isset($pageNumbers) && is_array($pageNumbers))) {
      $pageNumbers = array(1);
    }
    if ($node->hasAttribute('page')) {
      $pageTemplate = $node->getAttribute('page');
    } else {
      $pageTemplate = '';
    }
    if ($node->hasAttribute('title')) {
      $pageTitle = $node->getAttribute('title');
    } else {
      $pageTitle = NULL;
    }
    if ($node->hasAttribute('subtitle')) {
      $pageSubTitle = $node->getAttribute('subtitle');
    } else {
      $pageSubTitle = NULL;
    }
    if (isset($pageNumbers) && is_array($pageNumbers) && count($pageNumbers) > 0) {
      $this->setSourceFile($fileName);
      $indexList = array();
      foreach ($pageNumbers as $pageNo) {
        if ((!empty($pageNo)) && $pageNo > 0) {
          $index = $this->importPage($pageNo);
          $this->confObj->pdfAddPage($this, $pageTemplate, TRUE);
          $this->setPageTitle($pageTitle, $pageSubTitle);
          $this->useTemplate($index);
          $indexList[$pageNo] = $this->pageNo();
        }
      }
      $this->addImportBookmarks($node, $indexList);
      $this->breakBeforeNextSection = TRUE;
    }
  }

  /**
  * Add bookmarks for static page imports
  * @param DOMNode $node
  * @param array $indexList
  * @return unknown_type
  */
  function addImportBookmarks(&$node, &$indexList) {
    if ($node->hasChildNodes()) {
      for ($idx = 0; $idx < $node->childNodes->length; $idx++) {
        $childNode = &$node->childNodes->item($idx);
        if ($childNode->nodeType == XML_ELEMENT_NODE) {
          switch ($childNode->nodeName) {
          case 'bookmark' :
            if ($childNode->hasAttribute('title')) {
              $title = trim($childNode->getAttribute('title'));
              if (!empty($title)) {
                $pageNo = 0;
                if ($childNode->hasAttribute('page-no')) {
                  $pageAttr = (int)$childNode->getAttribute('page-no');
                  if (isset($indexList[$pageAttr])) {
                    $pageNo = $indexList[$pageAttr];
                  }
                }
                if (empty($pageNo) || $pageNo < 1) {
                  $pageNo = reset($indexList);
                }
                if ($childNode->hasAttribute('position')) {
                  $y = (int)$childNode->getAttribute('position');
                }
                if (empty($y) || $y < 0) {
                  $y = 0;
                }
                if ($childNode->hasAttribute('level')) {
                  $level = (int)$childNode->getAttribute('level');
                }
                if (empty($level) || $level < 0) {
                  $level = 0;
                }
                $this->addBookmark($title, $level, $y, $pageNo);
              }
            }
            break;
          }
        }
      }
    }
  }

  /**
  * Add target for a local link inside the document
  * @param string $linkTarget
  * @param integer $page
  * @param float $y
  * @return string
  */
  function _addLocalLinkTarget($linkTarget, $page, $y) {
    if (!isset($this->localLinks[$linkTarget])) {
      $this->localLinks[$linkTarget] = array(
        'id' => $this->AddLink(),
        'p' => $page,
        'y' => $y,
        'used' => FALSE
      );
    } else {
      $this->localLinks[$linkTarget] = array(
        'id' => $this->localLinks[$linkTarget]['id'],
        'p' => $page,
        'y' => $y,
        'used' => $this->localLinks[$linkTarget]['used']
      );
    }
    $this->currentLinkTarget = '';
  }

  /**
  * Add local link
  * @param string $linkTarget
  * @return string
  */
  function _addLocalLink($linkTarget) {
    if (!isset($this->localLinks[$linkTarget])) {
      $this->localLinks[$linkTarget] = array(
        'id' => $this->AddLink(),
        'used' => TRUE
      );
    } else {
      $this->localLinks[$linkTarget]['used'] = TRUE;
    }
    return $this->localLinks[$linkTarget]['id'];
  }

  /**
  * Apply local links to document stream
  * @return void
  */
  function _setLocalLinks() {
    if (isset($this->localLinks) && is_array($this->localLinks)) {
      foreach ($this->localLinks as $link) {
        if (isset($link['p']) && isset($link['used']) && $link['used']) {
          $this->setLink($link['id'], $link['y'], $link['p']);
        }
      }
    }
  }
}
?>