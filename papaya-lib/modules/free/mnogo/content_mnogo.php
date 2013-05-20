<?php
/**
* Base object for all content modules
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Mnogo
* @version $Id: content_mnogo.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Base content class for mnogo
**/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Base object for all content modules
* Check for derived objects later (at import).
*
* @package Papaya-Modules
* @subpackage Free-Mnogo
*/
class content_mnogo extends base_content {
  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'nl2br' => array('Automatic linebreak', 'isNum', FALSE, 'translatedcombo',
      array(0 => 'Yes', 1 => 'No'),
      'Apply linebreaks from input to the HTML output.', 0),
    'title' => array('Title', 'isSomeText', TRUE, 'input', 400, '', ''),
    'text' => array('Text', 'isSomeText', FALSE, 'richtext', 30, '', ''),
    'Options',
    'match' => array('Modus', 'isAlpha', FALSE, 'combo',
      array('all' => 'And', 'any' => 'Or', 'bool' => 'Boolean', 'phrase' => 'Phrase'),
      '', 'or'
    ),
    'pos' => array('Fields', 'isAlphaNum', FALSE, 'combo', array(
      '2221' => 'All', '0020' => 'Title', '2000' => 'Description',
      '0200' => 'Keywords', '0001' => 'Document'), '', '2221',
    ),
    'words' => array('Word position', 'isAlpha', FALSE, 'combo', array(
      'word' => 'Complete', 'begin' => 'Begin', 'end' => 'End', 'substr' => 'Part'),
      'substr'
    ),
    'count' => array('Matches per page', 'isNum', FALSE, 'combo', array(
      10 => 10, 20 => 20, 30 => 30, 50 => 50, 75 => 75, 100 => 100),
      '', 20
    ),
    'title_prefix' => array('Page title prefix', 'isNoHTML', FALSE, 'input', 400, '', ''),
    'result_mode' => array('Result Mode', 'isNum', TRUE, 'combo',
      array(0 => 'Meta description', 1 => 'Text snippet'), '', 0),
    'Error messages',
    'ERROR_SEARCHSTRING' => array('Invalid search pattern', 'isNoHTML', FALSE,
      'input', 300, '', 'Invalid search string'),
    'ERROR_EMPTYRESULT' => array('No results', 'isNoHTML', FALSE,
      'input', 300, '', 'No results'),
    'ERROR_MNOGOSEARCH' => array('MnogoSearch error', 'isNoHTML', FALSE,
      'input', 300, '', 'Mnogosearch Error'),
    'Configuration',
    'minsearchlength'=> array('Search length minimum', 'isNum', TRUE,
      'input', 2, '', 4),
    'mnogo_mode' => array('Mode', 'isNum', TRUE, 'combo',
      array(0 => 'single', 1 => 'multi table'), '', 0),
    'mnogo_dburi' => array ('Database URI', 'isSomeText', FALSE, 'input', 200, '', ''),
    'mnogo_ignore_lang' => array('Ignore Language', 'isNum', TRUE, 'combo',
      array(0 => 'no', 1 => 'yes'), '', 0),
  );

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    $this->setDefaultData();
    $result = sprintf(
      '<title encoded="%s">%s</title>'.LF,
      rawurlencode($this->data['title']),
      papaya_strings::escapeHTMLChars($this->data['title'])
    );
    $result .= sprintf(
      '<text>%s</text>',
      $this->getXHTMLString($this->data['text'], !((bool)$this->data['nl2br']))
    );
    $result .= $this->getSearchForm();
    if (isset($this->data['minsearchlength']) && $this->data['minsearchlength'] >= 2) {
      $minWordLength = (int)$this->data['minsearchlength'];
    } else {
      $minWordLength = 2;
    }
    if (isset($this->params['searchfor'])) {
      $this->logStatistic($this->parentObj->getContentLanguageId(), $this->params['searchfor']);
      if (!extension_loaded('mnogosearch')) {
        $result .= '<message>No ext/mnogosearch installed</message>';
      } elseif (papaya_strings::strlen($this->params['searchfor']) >= $minWordLength) {
        $result .= $this->search();
      } else {
        //fehler suchstring zu kurz
        $result .= sprintf(
          '<message>%s</message>',
          papaya_strings::escapeHTMLChars($this->data['ERROR_SEARCHSTRING'])
        );
      }
    }
    return $result;
  }

  /**
  * Get search form
  *
  * @access public
  * @return string $result XML
  */
  function getSearchForm() {
    $result = sprintf(
      '<searchdialog action="%s">'.LF,
      papaya_strings::escapeHTMLChars($this->baseLink)
    );
    $result .= sprintf(
      '<input type="text" name="%s[searchfor]" value="%s" class="text" />'.LF,
      papaya_strings::escapeHTMLChars($this->paramName),
      empty($this->params['searchfor'])
        ? '' : papaya_strings::escapeHTMLChars($this->params['searchfor'])
    );
    $result .= '</searchdialog>'.LF;
    return $result;
  }

  /**
  * Get search combo
  *
  * @param string $name
  * @access public
  * @return string $result XML
  */
  function getSearchCombo($name) {
    $items = empty($this->editFields[$name][4]) ? array() : $this->editFields[$name][4];
    $result = sprintf('<select name="%s[%s]">'.LF, $this->paramName, $name);
    if (isset($items) && is_array($items)) {
      foreach ($items as $key=>$val) {
        if (isset($this->params[$name]) && $this->params[$name] == $key) {
          $selected = ' selected="selected"';
        } elseif (isset($this->data[$name]) && $this->data[$name] == $key) {
          $selected = ' selected="selected"';
        } else {
          $selected = '';
        }
        $result .= sprintf(
          '<option value="%s"%s>%s</option>',
          papaya_strings::escapeHTMLChars($key),
          $selected,
          papaya_strings::escapeHTMLChars($val)
        );
      }
    }
    $result .= '</select>'.LF;
    return $result;
  }

  /**
  * Get search oupput
  *
  * @access public
  * @return string $result XML
  */
  function search() {
    $result = '';
    $searchFor = $this->prepareSearchString();
    switch ($this->data['mnogo_mode']) {
    case 1 :
      $mode = 'multi';
      break;
    default :
      $mode = 'single';
      break;
    }
    if (isset($this->data['mnogo_dburi']) && trim($this->data['mnogo_dburi']) != '') {
      $databaseURI = utf8_decode($this->data['mnogo_dburi']);
      if (substr($databaseURI, -1) != '/') {
        $databaseURI .= '/';
      }
    } else {
      $databaseURI = PAPAYA_DB_URI.'/';
    }
    if (Udm_Api_Version() >= 30204) {
      $this->udmAgent = Udm_Alloc_Agent($databaseURI.'?dbmode='.$mode);
    } else {
      $this->udmAgent = Udm_Alloc_Agent($databaseURI, $mode);
    }
    Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_CHARSET, 'UTF-8');
    if (Udm_Api_Version() >= 30204) {
      Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_BROWSER_CHARSET, 'UTF-8');
    }
    Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_CACHE_MODE, UDM_DISABLED);
    $minWordLength = ($this->data['minsearchlength'] >= 2)
      ? (int)$this->data['minsearchlength'] : 2;
    Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_MIN_WORD_LEN, $minWordLength);
    Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_MAX_WORD_LEN, 100);
    Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_WEIGHT_FACTOR, 'CFA61');
    if (function_exists('Udm_Set_Agent_Param_Ex')) {
   	  Udm_Set_Agent_Param_Ex($this->udmAgent, 'DateFormat', '%s');
    }
    $searchMode = $this->initSearchParam(
      'match',
      UDM_PARAM_SEARCH_MODE,
      array(
        'any' => UDM_MODE_ANY,
        'all' => UDM_MODE_ALL,
        'bool' => UDM_MODE_BOOL,
        'phrase' => UDM_ENABLED
      ),
      UDM_MODE_ANY
    );
    $wordMatch = $this->initSearchParam(
      'words',
      UDM_PARAM_WORD_MATCH,
      array(
        'begin' => UDM_MATCH_BEGIN,
        'end' => UDM_MATCH_END,
        'substr' => UDM_MATCH_SUBSTR,
        'word' => UDM_MATCH_WORD
      ),
      UDM_MATCH_SUBSTR
    );
    $searchPos = $this->initSearchParam(
      'pos',
      UDM_PARAM_WEIGHT_FACTOR,
      array(
        '2221' => '2221',
        '0020' => '0020',
        '2000' => '2000',
        '0200' => '0200',
        '0001' => '0001'
      ),
      '2221'
    );
    $pageSize = $this->initSearchParam(
      'count',
      UDM_PARAM_PAGE_SIZE,
      array(10=>10, 20=>20, 30=>30, 50=>50, 75=>75, 100=>100),
      10
    );
    if (isset($this->params['page']) && $this->params['page'] > 1) {
      $pageNum = $this->params['page'];
      Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_PAGE_NUM, $pageNum - 1);
    } else {
      $pageNum = 1;
      Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_PAGE_NUM, 0);
    }
    if (!isset($this->parentObj->currentLanguage)) {
      $this->parentObj->loadCurrentLanguage($this->parentObj->getContentLanguageId);
    }
    if (Udm_Api_Version() >= 30204 &&
        !(isset($this->data['mnogo_ignore_lang']) && $this->data['mnogo_ignore_lang'])) {
      $lngIdent = strtr(
        strtolower($this->parentObj->currentLanguage['lng_short']),
        '_',
        '-'
      );
      Udm_Add_Search_Limit($this->udmAgent, UDM_LIMIT_LANG, $lngIdent);
    }
    if (Udm_Api_Version() >= 30204) {
      Udm_Set_Agent_Param($this->udmAgent, UDM_PARAM_QUERY, $searchFor);
    }
    $this->logStatistic($this->parentObj->getContentLanguageId(), $searchFor);
    $res = Udm_Find($this->udmAgent, $searchFor);
    if (($errorNumber = Udm_Errno($this->udmAgent)) > 0) {
      $result .= '<message>'.$this->data['ERROR_MNOGOSEARCH'].
        ': '.Udm_Error($this->udmAgent).'</message>';
    } else {
      $this->prepareCleanStringTable();
      $resultArray['matches'] = udm_get_res_param($res, UDM_PARAM_FOUND);
      $resultArray['first_doc'] = udm_get_res_param($res, UDM_PARAM_FIRST_DOC);
      $resultArray['last_doc'] = udm_get_res_param($res, UDM_PARAM_LAST_DOC);
      //$resultArray['num_rows'] = udm_get_res_param ($res, UDM_PARAM_NUM_ROWS);
      $resultArray['num_rows'] =
        (int)$resultArray['last_doc'] - (int)$resultArray['first_doc'];
      if ($resultArray['num_rows'] >= 0 and $resultArray['first_doc'] > 0) {
        $resultArray['num_rows']++;
      } else {
        $resultArray['num_rows'] = 0;
      }
      $resultArray['wordinfo'] = udm_get_res_param($res, UDM_PARAM_WORDINFO);
      if (Udm_Api_Version() >= 30204) {
        $resultArray['wordinfo_all'] = udm_get_res_param($res, UDM_PARAM_WORDINFO_ALL);
      }
      $resultArray['searchtime'] = udm_get_res_param($res, UDM_PARAM_SEARCHTIME);
      if ($resultArray['num_rows'] < 1) {
        $result .= sprintf(
          '<message lng="%s">%s</message>',
          papaya_strings::escapeHTMLChars($lngIdent),
          papaya_strings::escapeHTMLChars($this->data['ERROR_EMPTYRESULT'])
        );
      } else {
        $result .= sprintf(
          '<matches time="%s" count="%d" wordinfo="%s" searchfor="%s" first="%d" last="%d">'.LF,
          papaya_strings::escapeHTMLChars($resultArray['searchtime']),
          (int)$resultArray['matches'],
          papaya_strings::escapeHTMLChars($resultArray['wordinfo']),
          empty($this->params['searchfor'])
            ? '' : papaya_strings::escapeHTMLChars($this->params['searchfor']),
          (int)$resultArray['first_doc'],
          (int)$resultArray['last_doc']
        );
        $this->initSearchWordHighlight($wordMatch, $resultArray['wordinfo']);
        for ($i = 0; $i < $resultArray['num_rows']; $i++) {
          if ($match = $this->fetchSearchRow($res, $i)) {
            if (preg_match('~(.*)/([^/?]+)~', $match['url'], $regs)) {
              $shortHREF = '.../'.$regs[2];
              if (strlen($regs[2]) > 60) {
                if (preg_match('~(\.\w+)$~', $regs[2], $ext)) {
                  $extLen = strlen($ext[1]);
                }
                $shortHREF = substr($shortHREF, 0, 60 - $extLen).'...'.$ext[1];
              }
            } else {
              $shortHREF = '';
            }
            $result .= sprintf(
              '<match title="%s" href="%s" short_href="%s" modified="%s" rating="%d"'.
              ' mimetype="%s" pos="%d" lang="%s">',
              papaya_strings::escapeHTMLChars($match['title']),
              papaya_strings::escapeHTMLChars($match['url']),
              papaya_strings::escapeHTMLChars($shortHREF),
              date('Y-m-d H:i:s', $match['lastmod']),
              (int)$match['rating'],
              papaya_strings::escapeHTMLChars($match['contype']),
              $resultArray['first_doc'] + $i,
              papaya_strings::escapeHTMLChars($match['lang'])
            );
            $result .= $this->getXHTMLString(
              $this->highlightSearchWords(
                papaya_strings::escapeHTMLChars($match['text'])
              ),
              FALSE
            );
            $result .= '</match>'.LF;
          }
        }
        $result .= '</matches>'.LF;
        $pageMax = ceil($resultArray['matches'] / $pageSize);
        $result .= '<pages>'.LF;
        for ($i = 1; $i <= $pageMax; $i++) {
          $selected = ($pageNum == $i) ? ' selected="selected"' : '';
          $linkParams = array(
            'page' => $i,
            'searchfor' => $searchFor,
            'match' => empty($this->params['match'])
              ? $this->data['match'] : $this->params['match'],
            'pos' => empty($this->params['pos'])
              ? $this->data['pos'] : $this->params['pos'],
            'words' => empty($this->params['words'])
              ? $this->data['words'] : $this->params['words'],
            'count' => empty($this->params['count'])
              ? $this->data['count'] : $this->params['count']
          );
          $result .= sprintf(
            '<page no="%d" href="%s"%s/>',
            $i,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(NULL, NULL, NULL, $linkParams, $this->paramName)
            ),
            $selected
          );
        }
        $result .= '</pages>'.LF;
      }
    }
    return $result;
  }

  /**
  * Prepare search string
  *
  * @access public
  * @return string
  */
  function prepareSearchString() {
    $bools = array('&', '|', '~', '+', '-'); // Alternative boolean operators
    $isBool = FALSE;
    if (!empty($this->params['searchfor'])) {
      $searchStrings = preg_split("/ +/", trim($this->params['searchfor']));
      foreach ($searchStrings as $string) {
        $boolTest = substr($string, 0, 1);
        if (in_array($boolTest, $bools)) {
          $isBool = TRUE;
          break;
        }
      }
      if ($isBool) {
        $this->params['match'] = 'bool';
        include_once(PAPAYA_INCLUDE_PATH.'system/base_searchstringparser.php');
        $sParser = new searchStringParser();
        $result = $sParser->getMnogoSearch($this->params['searchfor']);
        return $result;
      } else {
        return implode(' ', $searchStrings);
      }
    }
    return '';
  }

  /**
  * Initialize search parameters
  *
  * @param string $name
  * @param string $option
  * @param array $values
  * @param string $default
  * @access public
  * @return string $result
  */
  function initSearchParam($name, $option, $values, $default) {
    if (isset($this->params[$name]) && isset($values[$this->params[$name]])) {
      Udm_Set_Agent_Param($this->udmAgent, $option, $values[$this->params[$name]]);
      $result = $values[$this->params[$name]];
    } elseif (isset($this->data[$name]) && isset($values[$this->data[$name]])) {
      Udm_Set_Agent_Param($this->udmAgent, $option, $values[$this->data[$name]]);
      $result = $values[$this->data[$name]];
    } else {
      Udm_Set_Agent_Param($this->udmAgent, $option, $default);
      $result = $default;
    }
    return $result;
  }

  /**
  * Fetch search row
  *
  * @param integer $res
  * @param integer $idx
  * @access public
  * @return mixed NULL or array $field
  */
  function fetchSearchRow($res, $idx) {
    $field['url'] = $this->cleanString(trim(Udm_Get_Res_Field($res, $idx, UDM_FIELD_URL)));
    if ($field['url'] != '') {
      $field['ndoc'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_ORDER));
      $field['rating'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_RATING));
      $field['contype'] = '';
      //$field['contype'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_CONTENT));
      $field['docsize'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_SIZE));
      $field['lastmod'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_MODIFIED));
      $field['title'] = $this->cleanString(
        trim(Udm_Get_Res_Field($res, $idx, UDM_FIELD_TITLE)), TRUE
      );
      if (!empty($this->data['title_prefix']) &&
          strpos($field['title'], $this->data['title_prefix']) === 0) {
        $field['title'] = substr($field['title'], strlen($this->data['title_prefix']));
      }
      if ($this->data['result_mode'] == 0) {
        $field['text'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_DESC));
      } else {
        $field['text'] = '';
      }
      if (trim($field['text']) == '') {
        $field['text'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_TEXT));
      }
      $field['keyw'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_KEYWORDS));

      if (Udm_Api_Version() >= 30204) {
        $field['lang'] = $this->cleanString(Udm_Get_Res_Field($res, $idx, UDM_FIELD_LANG));
      }
      return $field;
    } else {
      return NULL;
    }
  }

  /**
  * Clean string
  *
  * @param string $str
  * @param boolean $debug optional, default value FALSE
  * @access public
  * @return string $result
  */
  function cleanString($str, $debug = FALSE) {
    $result = strtr(papaya_strings::ensureUTF8($str), $this->cleanStringTable);
    return $result;
  }

  /**
  * Prepare clean string table
  *
  * @access public
  */
  function prepareCleanStringTable() {
    $this->cleanStringTable = array_flip(get_html_translation_table(HTML_ENTITIES));
    foreach ($this->cleanStringTable as $repl => $char) {
      if (ord($char) > 8) {
        $utf8Char = chr(0xC0 | ord($char) >> 6).chr(0x80 | ord($char) & 0x3F);
      } else {
        $utf8Char = ' ';
      }
      $this->cleanStringTable[$repl] = $utf8Char;

    }
    $this->cleanStringTable['&#34;'] = '"';
    $this->cleanStringTable['&quot;'] = '"';
    $this->cleanStringTable['&#36;'] = '&amp;';
    $this->cleanStringTable['<<'] = '\xAB';
    $this->cleanStringTable['>>'] = '\xBB';
    for ($i = 0; $i <= 8; $i++) {
      $this->cleanStringTable[chr($i)] = ' ';
    }
  }

  /**
  * Initialize search word hightlight
  *
  * @param string $wordMatch
  * @param string $wordInfo
  * @access public
  */
  function initSearchWordHighlight($wordMatch, $wordInfo) {
    $this->searchWordPattern = FALSE;
    if (preg_match_all('/\s*(.*?)\s*:\s*\d+,?/', $wordInfo, $regs)) {
      foreach ($regs[1] as $word) {
        $words[] = preg_quote(strtolower($word));
      }
      $searchWords = implode('|', $words);
    } else {
      $searchWords = '';
    }
    if ($searchWords != '') {
      switch ($wordMatch) {
      case UDM_MATCH_BEGIN:
        $replacePattern = '(\b('.$searchWords.'))iu';
        break;
      case UDM_MATCH_END:
        $replacePattern = '(('.$searchWords.')\b)iu';
        break;
      case UDM_MATCH_SUBSTR:
        $replacePattern = '(('.$searchWords.'))iu';
        break;
      default:
        $replacePattern = '(\b('.$searchWords.')\b)iu';
      }
      $this->searchWordPattern = $replacePattern;
    }
  }

  /**
  * Highlight search words
  *
  * @param string $str
  * @param string $highlightBegin optional, default value bold-open-tagg
  * @param string $highlightEnd optional, default value bold-close-tagg
  * @access public
  * @return string $str
  */
  function highlightSearchWords($str, $highlightBegin = '<b>', $highlightEnd = '</b>') {
    if ($this->searchWordPattern) {
      return preg_replace(
        $this->searchWordPattern, $highlightBegin.'$1'.$highlightEnd, $str);
    } else {
      return $str;
    }
  }

  /**
  * logs search words
  * @param integer $lngId the current content language id
  * @param string $term the search term
  */
  function logStatistic($lngId, $term) {
    $data = array(
      'lng_id' => $lngId,
      'searchterm' => $term,
    );
    include_once(PAPAYA_INCLUDE_PATH.'system/base_statistic_entries_tracking.php');
    return base_statistic_entries_tracking::logEntry($this->guid, 'mnogo_search', $data);
  }
}

?>