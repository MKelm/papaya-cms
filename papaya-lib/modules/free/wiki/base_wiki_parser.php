<?php
/**
* papaya Wiki, wiki code to XML parser
*
* @copyright 2002-2008 by papaya Software GmbH - All rights reserved.
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
* @subpackage Beta-Wiki
* @version $Id: base_wiki_parser.php 36972 2012-04-14 12:30:08Z kersken $
*/

/**
* Parses Wiki code to XML
*
* @package Papaya-Modules
* @subpackage Beta-Wiki
*/
class base_wiki_parser {
  /**
  * Syntax-highlighting service object
  * @var object $highlighter
  */
  var $highlighter = NULL;

  /**
  * List type and depth
  * @var string $listType
  */
  var $listType = '';

  /**
  * Flag for definition lists
  * @var boolean $defList
  */
  var $defList = FALSE;

  /**
  * Headline level tracker for TOC generation
  * @var array $headlineLevels
  */
  var $headlineLevels = array();

  /**
  * List of categories
  * @var array $categories
  */
  var $categories = array();

  /**
  * List of wiki links
  * @var array $wikiLinks
  */
  var $wikiLinks = array();

  /**
  * List of article translations
  * @var array $translations
  */
  var $translations = array();

  /**
  * List of files
  * @var array
  */
  var $files = array();

  /**
  * List of references
  * @var array
  */
  var $references = array();

  /**
  * Number of references
  * @var integer
  */
  var $refCount = 0;

  /**
  * List of special wiki keywords
  * @var array
  */
  var $keywords = array(
    'redirect' => 'Redirect',
    'category' => 'Category',
    'file' => 'File',
    'thumb' => 'thumb',
    'references' => 'References'
  );

  /**
  * List of allowed tags
  * @var array
  */
  var $allowedTags = array('ref', 'sub', 'sup');

  /**
  * List of allowed single tags
  * @var array
  */
  var $allowedSingleTags = array('br');

  /**
  * Get the singleton instance of the base_wiki_parser class
  *
  * @access public
  * @return object
  */
  function getInstance() {
    static $instance;
    if (!(isset($instance) && is_object($instance))) {
      $instance = new base_wiki_parser();
    }
    return $instance;
  }

  /**
  * Parse a string of wiki code
  *
  * Check each line for special markup
  * and call the appropriate line handlers
  *
  * @access public
  * @param string $wikiCode
  * @param array $keywords
  * @param boolean $xml optional, default TRUE
  * @return string (XML)
  */
  function parse($wikiCode, $keywords, $xml = TRUE) {
    if (!empty($keywords)) {
      $this->keywords = $keywords;
    }
    $this->headlineLevels = array();
    $this->categories = array();
    $this->wikiLinks = array();
    $this->translations = array();
    $this->files = array();
    $this->references = array();
    $this->refCount = 0;
    if ($xml) {
      $xmlResult = '<wiki>'.LF;
    } else {
      $xmlResult = '';
    }
    $prefix = '';
    // Split the code into single lines
    $lines = preg_split('((\n|\r\n))', $wikiCode);
    // If the first line is a redirection, just return the redirection XML
    // (we allow both 'redirect' and a localized version from the $keywords array)
    if (sizeof($lines) > 0) {
      if (isset($keywords['redirect']) &&
          preg_match('{^#'.$keywords['redirect'].'\s*\[\[([^\]]+)\]\]}i', $lines[0], $matches)) {
        if ($xml) {
          $xmlResult = sprintf('<redirect node="%s"/>'.LF, trim($matches[1]));
        } else {
          $xmlResult = '#REDIRECT';
        }
        return $xmlResult;
      }
      if (preg_match('{^#redirect\s*\[\[([^\]]+)\]\]}i', $lines[0], $matches)) {
        if ($xml) {
          $xmlResult = sprintf('<redirect node="%s"/>'.LF, trim($matches[1]));
        } else {
          $xmlResult = '#REDIRECT';
        }
        return $xmlResult;
      }
    }
    // We're not in table mode right now
    $tableMode = FALSE;
    // Neither in source code mode
    $sourceMode = FALSE;
    // And not in Nowiki mode either
    $noWikiMode = FALSE;
    foreach ($lines as $line) {
      // Remove leading/trailing whitespace while not in source or nowiki modes
      if ($sourceMode == FALSE && $noWikiMode == FALSE) {
        $line = trim($line);
      }
      if (strlen($line) == 0) {
        if ($xml) {
          if (!$tableMode && !$noWikiMode) {
            $xmlResult .= '<br />';
          } elseif ($noWikiMode) {
            $xmlResult .= LF;
          }
        }
        continue;
      }
      if ($xml) {
        // Nowiki mode
        if ($line == '<nowiki>') {
          if (!$noWikiMode) {
            $noWikiMode = TRUE;
            $xmlResult .= '<nowiki>';
          }
          continue;
        } elseif ($line == '</nowiki>') {
          $noWikiMode = FALSE;
          $xmlResult .= '</nowiki>';
          continue;
        } elseif ($noWikiMode == TRUE) {
          $xmlResult .= $line.LF;
          continue;
        }
        // Source code mode
        if (preg_match('(<source\s*( lang="([\w+-]+)")?>)', $line, $matches)) {
          if (!$sourceMode) {
            $sourceMode = TRUE;
            $sourceBuffer = '';
            $langAttribute = '';
            if (count($matches) > 1) {
              $langAttribute = $matches[1];
              $lang = $matches[2];
            }
            $xmlResult .= sprintf('<source%s>', $langAttribute);
            continue;
          }
          continue;
        } elseif ($line == '</source>') {
          $sourceMode = FALSE;
          $xmlResult .= $this->getHighlightedCode($sourceBuffer, $lang);
          $xmlResult .= '</source>';
          continue;
        } elseif ($sourceMode == TRUE) {
          $sourceBuffer .= $line.LF;
          continue;
        }
        // Do we start a table here?
        if (substr($line, 0, 2) == '{|') {
          $tableMode = TRUE;
          $this->rowStarted = FALSE;
          $attrString = (strlen($line) > 2) ? substr($line, 2) : '';
          $xmlResult .= $this->getTableStart($attrString);
        }
      } elseif (substr($line, 0, 2) == '{|' || $line == '|}') {
        continue;
      }
      // Are we in table mode?
      if ($tableMode) {
        // Does the table end in this line?
        if ($line == '|}') {
          // Do we have to close a row?
          if ($this->rowStarted) {
            $rowStarted = FALSE;
            $xmlResult .= '</row>'.LF;
          }
          $tableMode = FALSE;
          $xmlResult .= '</table>'.LF;
        } elseif ($line != '{|') {
          $xmlResult .= $this->handleTableLine($line);
        }
      } else {
        // Special action on first character of the current line required?
        switch($line[0]) {
        case '=':
          if ($xml) {
            $xmlResult .= $this->closeLists();
          }
          $headline = $this->handleHeadline($line, $xml).LF;
          if ($xml && count($this->headlineLevels) == 1) {
            $prefix = $xmlResult;
            $xmlResult = '';
          }
          $xmlResult .= $headline;
          break;
        case '*':
        case '#':
          if ($xml) {
            $xmlResult .= $this->closeLists('def');
          }
          $xmlResult .= $this->handleListItem($line, $xml).LF;
          break;
        case ';':
          if ($xml) {
            $xmlResult .= $this->closeLists('list');
          }
          $xmlResult .= $this->handleDefListItem($line, $xml).LF;
          break;
        case ':':
          if ($xml) {
            $xmlResult .= $this->closeLists();
          }
          $xmlResult .= $this->handleIndentation($line, $xml).LF;
          break;
        default:
          if ($xml) {
            $xmlResult .= $this->closeLists();
          }
          $xmlResult .= $this->handleLine($line, $xml).LF;
          break;
        }
      }
    }
    $xmlResult .= $this->closeLists();
    if ($xml) {
      $xmlResult .= $this->getReferences();
      $xmlResult .= '</wiki>';
    }
    $result = $prefix;
    $refTable = '';
    if ($xml) {
      $result .= $this->getTOC();
    }
    $result .= $xmlResult;
    $result .= $refTable;
    return $result;
  }

  /**
  * Get table of contents from headline list
  *
  * @return string XML
  */
  function getTOC() {
    $result = '';
    if (count($this->headlineLevels) == 0) {
      return $result;
    }
    $result = '<toc>';
    $counter = array(0, 0, 0, 0);
    $count = 0;
    foreach ($this->headlineLevels as $headline) {
      $current = '';
      $count++;
      $counter[$headline['level'] - 2]++;
      for ($i = 0; $i < $headline['level'] - 2; $i++) {
        if ($counter[$i] == 0) {
          $counter[$i] = 1;
        }
      }
      for ($i = $headline['level'] - 1; $i < 4; $i++) {
        if ($counter[$i] > 0) {
          $counter[$i] = 0;
        }
      }
      for ($i = 0; $i < 4; $i++) {
        if ($counter[$i] == 0) {
          if ($i == 1) {
            $current .= '.';
          }
          break;
        }
        if ($i > 0) {
          $current .= '.';
        }
        $current .= $counter[$i];
      }
      $result .= sprintf(
        '<toc-item level="%d" href="#h%d">%s %s</toc-item>',
        $headline['level'],
        $count,
        $current,
        $headline['content']
      );
    }
    $result .= '</toc>';
    return $result;
  }

  /**
  * Get the list of references
  *
  * @return string XML
  */
  function getReferences() {
    $result = '';
    if (count($this->references) > 0) {
      $this->headlineLevels[] = array(
        'level' => 2,
        'content' => $this->keywords['references']
      );
      $result = sprintf(
        '<headline level="2" anchor="h%d">%s</headline>',
        count($this->headlineLevels),
        $this->keywords['references']
      );
      $result .= '<references>'.LF;
      foreach ($this->references as $refId => $refContent) {
        $result .= sprintf(
          '<reftext id="%d">%s</reftext>'.LF,
          $refId,
          $refContent
        );
      }
      $result .= '</references>'.LF;
    }
    return $result;
  }

  /**
  * Handle a default line (or the inner contents of a special line)
  *
  * @access public
  * @param string $line
  * @param boolean $xml optional, default TRUE
  * @return string (XML)
  */
  function handleLine($line, $xml = TRUE) {
    $xmlResult = '';
    // Cheap trick: add a blank at line's end to properly close links and emphasis
    $line .= ' ';
    // Initialize flags
    $tagStarted = FALSE;
    $openedTag = '';
    $tagBuffer = '';
    $betweenTagsBuffer = '';
    $startLinkCount = 0;
    $endLinkCount = 0;
    $emphCount = 0;
    $intLink = FALSE;
    $extLink = FALSE;
    $bold = FALSE;
    $italic = FALSE;
    $linkBuffer = '';
    // Act on each character of the line, individually
    for ($i = 0; $i < strlen($line); $i++) {
      $char = $line[$i];
      switch($char) {
      case '<':
        $tagStarted = TRUE;
        $tagBuffer = '';
        break;
      case '\'':
        if ($intLink || $extLink || $tagStarted || !empty($openedTag)) {
          $linkBuffer .= $char;
        } else {
          $emphCount++;
        }
        break;
      case '[':
        if (empty($openedTag)) {
          $startLinkCount++;
        } else {
          $betweenTagsBuffer .= '[';
        }
        break;
      case ']':
        if (empty($openedTag)) {
          $endLinkCount++;
        } else {
          $betweenTagsBuffer .= ']';
        }
        break;
      default:
        // Tag buffer mode?
        if ($tagStarted == TRUE) {
          if ($char == '>') {
            $tagStarted = FALSE;
            if (substr($tagBuffer, 0, 1) == '/') {
              if (!empty($openedTag)) {
                if ($openedTag == substr($tagBuffer, 1)) {
                  if ($openedTag == 'ref') {
                    $this->refCount++;
                    $this->references[$this->refCount] = $this->handleLine($betweenTagsBuffer);
                    $xmlResult .= sprintf('<ref no="%d" />', $this->refCount);
                  } else {
                    $tagText = sprintf(
                      '<%s>%s</%s>',
                      $openedTag,
                      $this->handleLine($betweenTagsBuffer),
                      $openedTag
                    );
                    $xmlResult .= $tagText;
                  }
                }
                $openedTag = '';
              }
              $betweenTagsBuffer = '';
            } elseif (substr($tagBuffer, -1) == '/') {
              $tag = preg_replace('(^(.*?)\s*/$)', '$1', $tagBuffer);
              if (in_array($tag, $this->allowedSingleTags)) {
                $xmlResult .= sprintf('<%s />', $tag);
              }
            } else {
              if (in_array($tagBuffer, $this->allowedTags)) {
                $openedTag = $tagBuffer;
              }
            }
          } else {
            $tagBuffer .= $char;
          }
        } elseif (!empty($openedTag)) {
          $betweenTagsBuffer .= $char;
        } else {
          // Handle emphasis
          if (!($intLink || $extLink || $tagStarted)) {
            if ($emphCount == 2) {
              if ($italic) {
                $italic = FALSE;
                $emphCount = 0;
                if ($xml) {
                  $xmlResult .= '</italic>';
                }
              } else {
                $italic = TRUE;
                $emphCount = 0;
                if ($xml) {
                  $xmlResult .= '<italic>';
                }
              }
            }
            if ($emphCount == 3 || $emphCount == 4) {
              if ($bold && $italic) {
                $bold = FALSE;
                $emphCount = 0;
                if ($xml) {
                  $xmlResult .= '</italic></bold><italic>';
                }
              } elseif ($bold) {
                $bold = FALSE;
                $emphCount = 0;
                if ($xml) {
                  $xmlResult .= '</bold>';
                }
              } else {
                $bold = TRUE;
                $emphCount = 0;
                if ($xml) {
                  $xmlResult .= '<bold>';
                }
              }
            }
            if ($emphCount == 5) {
              if (!$bold && !$italic) {
                $bold = TRUE;
                $italic = TRUE;
                $emphCount = 0;
                if ($xml) {
                  $xmlResult .= '<bold><italic>';
                }
              } elseif ($bold && $italic) {
                $bold = FALSE;
                $italic = FALSE;
                $emphCount = 0;
                if ($xml) {
                  $xmlResult .= '</italic></bold>';
                }
              }
            }
          }
          if ($emphCount == 1) {
            $xmlResult .= '\'';
          }
          $emphCount = 0;
          // Handle links
          if ($startLinkCount == 2) {
            $intLink = TRUE;
            $startLinkCount = 0;
            $linkBuffer = '';
          }
          if ($startLinkCount == 1) {
            $extLink = TRUE;
            $startLinkCount = 0;
            $linkBuffer = '';
          }
          // Internal links
          if ($endLinkCount == 2) {
            if ($intLink) {
              $linkBuffer = preg_replace('(\'+$)', '', $linkBuffer);
              $intLink = FALSE;
              $endLinkCount = 0;
              $intLinkData = explode('|', $linkBuffer);
              if (isset($intLinkData[1]) && trim($intLinkData[1]) != '') {
                $linkDescription = trim($intLinkData[1]);
              } else {
                $linkDescription = '';
              }
              if (isset($intLinkData[0]) && trim($intLinkData[0]) != '') {
                $linkNode = $intLinkData[0];
                // Assume that we've got a standard link
                $link = TRUE;
                if (isset($this->keywords['category']) && preg_match(
                    '{^((:?)category|'.$this->keywords['category'].'):(.+)$}i',
                    trim($linkNode),
                    $matches)) {
                  // Category entry or link
                  $link = FALSE;
                  if ($matches[2] == ':') {
                    $linkNode = substr($linkNode, 1);
                    $link = TRUE;
                  } elseif ($xml) {
                    $this->categories[] = $matches[3];
                  }
                } elseif (
                    preg_match(
                      '(^(file|'.$this->keywords['file'].'):(.+)$)i',
                      trim($linkNode),
                      $matches
                    )
                  ) {
                  $link = FALSE;
                  if ($xml) {
                    $fileName = $matches[2];
                    $options = array();
                    array_shift($intLinkData);
                    foreach ($intLinkData as $option) {
                      $option = trim($option);
                      if (preg_match('(^(thumb(nail)?|'.$this->keywords['thumb'].')$)i', $option)) {
                        $options['thumb'] = 'true';
                      } elseif ($option == 'left' || $option == 'right' || $option == 'center') {
                        $options['align'] = $option;
                      } elseif ($option == 'border') {
                        $options['border'] = 'true';
                      } elseif (preg_match('(^(\d+)?(x\d+)?px$)', $option, $matches)) {
                        $width = NULL;
                        $height = NULL;
                        if (!empty($matches[1])) {
                          if (strpos($matches[1], 'x') === 0) {
                            $height = substr($matches[1], 1);
                          } else {
                            $width = $matches[1];
                          }
                        }
                        if (!empty($matches[2])) {
                          if (strpos($matches[2], 'x') === 0) {
                            $height = substr($matches[2], 1);
                          }
                        }
                        if ($width !== NULL) {
                          $options['width'] = $width;
                        }
                        if ($height !== NULL) {
                          $options['height'] = $height;
                        }
                      }
                    }
                    $index = count($this->files);
                    $this->files[] = array(
                      'name' => $fileName,
                      'index' => $index,
                      'options' => $options,
                    );
                    $optionString = '';
                    foreach ($options as $name => $value) {
                      $optionString .= sprintf(' %s="%s"', $name, $value);
                    }
                    $xmlResult .= sprintf(
                      '<media index="%d" href="%s"%s />',
                      $index,
                      $fileName,
                      $optionString
                    );
                  }
                } elseif (preg_match('{^([a-z]{2,3}):(.*)$}i', trim($linkNode), $matches)) {
                  // Translation link
                  $link = FALSE;
                  if ($xml) {
                    $this->translations[strtolower($matches[1])] = trim($matches[2]);
                  }
                }
                if ($link) {
                  if ($linkDescription == '') {
                    $linkDescription = trim($linkNode);
                  }
                  if ($xml) {
                    $xmlResult .= sprintf(
                      '<wiki-link node="%s">%s</wiki-link>',
                      trim($linkNode),
                      $this->handleLine($linkDescription, $xml)
                    );
                  } else {
                    $xmlResult .= $this->handleLine($linkDescription, $xml);
                  }
                  if ($xml) {
                    $this->wikiLinks[] = trim($linkNode);
                  }
                }
              }
            }
          }
          // External links
          if ($endLinkCount == 1) {
            if ($extLink) {
              $extLink = FALSE;
              $endLinkCount = 0;
              $extLinkData = preg_split('(\s+)', $linkBuffer);
              if (isset($extLinkData[1]) && trim($extLinkData[1]) != '') {
                $linkDescArray = $extLinkData;
                array_shift($linkDescArray);
                $linkDescription = implode(' ', $linkDescArray);
              } else {
                $linkDescription = '';
              }
              if (isset($extLinkData[0]) && trim($extLinkData[0] != '')) {
                $url = trim($extLinkData[0]);
                // Prepend http:// if necessary
                if (!preg_match('(^[a-z]+:)', $url)) {
                  $url = 'http://'.$url;
                }
                if ($linkDescription == '') {
                  $linkDescription = $url;
                }
                if ($xml) {
                  $xmlResult .= sprintf(
                    '<external-link url="%s">%s</external-link>',
                    $url,
                    $this->handleLine($linkDescription, $xml)
                  );
                } else {
                  $xmlResult .= $this->handleLine($linkDescription, $xml);
                }
              }
            }
          }
          // Add the current character if we're not within a link
          if (!($intLink || $extLink)) {
            $xmlResult .= $char;
          } else {
            $linkBuffer .= $char;
          }
        }
        break;
      }
    }
    // If bold or italic are still open, close them here to prevent errors
    if ($xml) {
      if ($italic) {
        $xmlResult .= '</italic>';
      }
      if ($bold) {
        $xmlResult .= '</bold>';
      }
    }
    return trim($xmlResult);
  }

  /**
  * Handle headlines
  *
  * @param string $line
  * @param boolean $xml optional, default TRUE
  * @return string (XML)
  */
  function handleHeadline($line, $xml = TRUE) {
    // Count leading = signs for headline level
    $levelCount = 0;
    while ($line[$levelCount] == '=') {
      $levelCount++;
    }
    // Strip = signs
    $line = trim($line, '=');
    // Normalize level (2 up to 5 only)
    if ($levelCount < 2) {
      $xmlResult = $this->handleLine($line);
    } else {
      if ($levelCount > 5) {
        $levelCount = 5;
      }
      if ($xml) {
        $this->headlineLevels[] = array(
          'level' => $levelCount,
          'content' => $this->handleLine($line, FALSE)
        );
        $xmlResult = sprintf(
          '<headline level="%d" anchor="h%d">%s</headline>',
          $levelCount,
          count($this->headlineLevels),
          $this->handleLine($line, $xml)
        );
      } else {
        $xmlResult = $this->handleLine($line, $xml);
      }
    }
    return $xmlResult;
  }

  /**
  * Handle list items
  *
  * @param string $line
  * @param boolean $xml optional, default TRUE
  * @return string (XML)
  */
  function handleListItem($line, $xml = TRUE) {
    $xmlResult = '';
    // Get list type and depth from leading * and/or # signs
    $list = '';
    $listCount = 0;
    while (strlen($line) > $listCount && ($line[$listCount] == '*' || $line[$listCount] == '#')) {
      $list .= $line[$listCount];
      $listCount++;
    }
    // Check whether the current line style equals to the previous one,
    // otherwise close and/or open the appropriate lists
    if ($xml) {
      if ($list != $this->listType) {
        if (strlen($list) < strlen($this->listType)) {
          for ($i = strlen($this->listType) - 1; $i >= strlen($list); $i--) {
            $xmlResult .= '</list>'.LF;
          }
        }
        for ($i = strlen($list) - 1; $i >= 0; $i--) {
          if (strlen($this->listType) > $i && $list[$i] != $this->listType[$i]) {
            $xmlResult .= '</list>'.LF;
          }
        }
        for ($i = 0; $i < strlen($list); $i++) {
          if (strlen($this->listType) <= $i || $list[$i] != $this->listType[$i]) {
            switch ($list[$i]) {
            case '*' :
              $xmlResult .= '<list type="bullet">'.LF;
              break;
            case '#' :
              $xmlResult .= '<list type="numeric">'.LF;
              break;
            }
          }
        }
        // Set current list type to the new one
        $this->listType = $list;
      }
    }
    // Strip bullets/numbers
    $line = preg_replace('(^[*#]+\s*)', '', $line);
    if ($xml) {
      $xmlResult .= sprintf('<item>%s</item>', $this->handleLine($line, $xml));
    } else {
      $xmlResult .= $this->handleLine($line, $xml);
    }
    return $xmlResult;
  }

  /**
  * Handle definition list item
  *
  * @param string $line
  * @param boolean $xml optional, default TRUE
  * @return string (XML)
  */
  function handleDefListItem($line, $xml = TRUE) {
    $xmlResult = '';
    // Strip the semicolon; split line on colon
    $line = preg_replace('(^\s*;\s*)', '', $line);
    // Split the string at a whitespace-enclosed colon
    $defParts = preg_split('(\s+:\s+)', $line);
    // If we do not have two parts, this is a normal line
    if (!isset($defParts[1]) || trim($defParts[1]) == '') {
      return $this->handleLine($line, $xml);
    }
    // If we did not open a definition list yet, create one
    if ($xml) {
      if (!$this->defList) {
        $this->defList = TRUE;
        $xmlResult .= '<deflist>'.LF;
      }
      $xmlResult .= sprintf('<deftitle>%s</deftitle>'.LF, $this->handleLine($defParts[0], $xml));
      $xmlResult .= sprintf(
        '<definition>%s</definition>'.LF,
        $this->handleLine($defParts[1], $xml)
      );
    } else {
      $xmlResult .= sprintf(
        '%s: %s',
        $this->handleLine($defParts[0], $xml),
        $this->handleLine($defParts[1], $xml)
      );
    }
    return $xmlResult;
  }

  /**
  * Handle indentation
  *
  * @param string $line
  * @param boolean $xml optional, default TRUE
  * @return string (XML)
  */
  function handleIndentation($line, $xml = TRUE) {
    // For an empty line consisting of nothing but a colon, return an empty string
    if (preg_match('(^:+$)', trim($line))) {
      return '';
    }
    // Count the number of leading colons
    $numColons = 0;
    while ($line[$numColons] == ':') {
      $numColons++;
    }
    // Strip leading colons and whitespace
    $line = preg_replace('{^(:+\s*)}', '', $line);
    if ($xml) {
      $xmlResult = sprintf(
        '<indent steps="%d">%s</indent>',
        $numColons,
        $this->handleLine($line, $xml)
      );
    } else {
      $xmlResult = $this->handleLine($line, $xml);
    }
    return $xmlResult;
  }

  /**
  * Get tag with attributes
  *
  * Requires a tag name, an optional array of valid attributes
  * and an optional string with potential attribute=value pairs
  * Check attributes, and if they are suitable,
  * write them into the start tag.
  * Return the start tag with all valid attributes
  *
  * @param string $tagName
  * @param mixed NULL|array $validAttrs optional, default NULL
  * @param string $attrString optional, default ''
  */
  function getTagWithAttributes($tagName, $validAttrs = NULL, $attrString = '') {
    $xmlResult = sprintf('<%s', $tagName);
    // Strip whitespace
    $attrString = trim($attrString);
    // If it's not empty, check attributes
    if ($attrString != '' && is_array($validAttrs)) {
      $attrs = preg_split('{\s+}', $attrString);
      foreach ($attrs as $attr) {
        // Split current attribute on = sign
        $attrPair = explode('=', $attr);
        // Ignore attributes of an illegal form
        if (sizeof($attrPair) != 2) {
          continue;
        }
        // Check for valid attribute names
        $name = $attrPair[0];
        $value = $attrPair[1];
        if (!in_array($name, $validAttrs)) {
          continue;
        }
        // Strip quotes that might enclose the value
        $value = trim($value, '"');
        // Escape value contents
        $value = htmlspecialchars($value);
        // Add the sanitized attribute
        $xmlResult .= sprintf(' %s="%s"', $name, $value);
      }
    }
    // Add closing bracket
    $xmlResult .= '>';
    return $xmlResult;
  }

  /**
  * Get table start
  *
  * Check attributes for tables,
  * and if they are suitable, write them into
  * the table start tag.
  * Return the start tag with all valid attributes
  *
  * @param string $attrString optional, default ''
  * @return string xml
  */
  function getTableStart($attrString = '') {
    // Declare valid attribute names
    $validAttrs = array(
      'width', 'height', 'border', 'cellpadding',
      'cellspacing', 'align', 'bgcolor'
    );
    $xmlResult = $this->getTagWithAttributes('table', $validAttrs, $attrString);
    $xmlResult .= LF;
    return $xmlResult;
  }

  /**
  * Get table row start
  *
  * Check attributes for table rows,
  * and if they are suitable, write them into
  * the row start tag.
  * Return the start tag with all valid attributes
  *
  * @param string $attrString optional, default ''
  * @return string xml
  */
  function getTableRowStart($attrString = '') {
    // Declare valid attribute names
    $validAttrs = array(
      'height', 'bgcolor'
    );
    $xmlResult = $this->getTagWithAttributes('row', $validAttrs, $attrString);
    $xmlResult .= LF;
    return $xmlResult;
  }

  /**
  * Get table cell start
  *
  * Check attributes for table cells,
  * and if they are suitable, write them into
  * the cell start tag.
  * Return the start tag with all valid attributes
  *
  * @param string $attrString optional, default ''
  * @param string $cellType 'cell'|'headline' optional, default 'cell'
  * @return string xml
  */
  function getTableCellStart($attrString = '', $cellType = 'cell') {
    // Normalize cell type
    if ($cellType != 'headline-cell') {
      $cellType = 'cell';
    }
    // Declare valid attribute names
    $validAttrs = array(
      'height', 'width', 'colspan', 'rowspan', 'align', 'valign', 'bgcolor'
    );
    $xmlResult = $this->getTagWithAttributes($cellType, $validAttrs, $attrString);
    return $xmlResult;
  }

  /**
  * Handle a table line
  *
  * Called for each line in table mode, following after
  * '{|' as the table start in a line of its own,
  * and terminated by '|}'
  *
  * @param string $line
  * @return string xml
  */
  function handleTableLine($line) {
    $xmlResult = '';
    // Handle the line according to its table markup
    $line = trim($line);
    if (substr($line, 0, 2) == '|-') {
      // End of a previous row?
      if ($this->rowStarted) {
        $this->rowStarted = FALSE;
        $xmlResult .= '</row>'.LF;
      }
      // Start a new row with optional attributes
      $attrString = (strlen($line) > 2) ? substr($line, 2) : '';
      $xmlResult .= $this->checkRowStart($attrString);
    } elseif (strlen($line) > 2 && substr($line, 0, 2) == '|+') {
      // Table caption
      $line = substr($line, 2);
      $xmlResult .= sprintf('<caption>%s</caption>'.LF, $line);
    } elseif ($line[0] == '|') {
      // A simple cell
      // Start a row if we do not have one yet
      $xmlResult .= $this->checkRowStart();
      $line = substr($line, 1);
      // Strip multiple cells, if available
      $parts = explode('||', $line);
      // Treat each part as a cell with optional attributes
      foreach ($parts as $cell) {
        $cellParts = explode('|', $cell);
        if (sizeof($cellParts) > 1) {
          $attrString = array_shift($cellParts);
          $content = implode('|', $cellParts);
        } else {
          $attrString = '';
          $content = $cellParts[0];
        }
        $xmlResult .= sprintf(
          '%s%s',
          $this->getTableCellStart($attrString),
          $this->handleLine($content)
        );
        $xmlResult .= '</cell>'.LF;
      }
    } elseif ($line[0] == '!') {
      // A headline cell
      $xmlResult .= $this->checkRowStart();
      $line = substr($line, 1);
      // Strip multiple cells, if available
      $parts = explode('!!', $line);
      // Treat each part as a cell with optional attributes
      foreach ($parts as $cell) {
        $cellParts = explode('|', $cell);
        if (sizeof($cellParts) > 1) {
          $attrString = array_shift($cellParts);
          $content = implode('|', $cellParts);
        } else {
          $attrString = '';
          $content = $cellParts[0];
        }
        $xmlResult .= sprintf(
          '%s%s',
          $this->getTableCellStart($attrString, 'headline-cell'),
          $this->handleLine($content)
        );
        $xmlResult .= '</headline-cell>'.LF;
      }
    }
    return $xmlResult;
  }

  /**
  * Check start of a table row with optional attributes
  *
  * @param string $attrString optional, default ''
  * @return string xml
  */
  function checkRowStart($attrString = '') {
    $xmlResult = '';
    if ($this->rowStarted == FALSE) {
      $this->rowStarted = TRUE;
      $xmlResult .= $this->getTableRowStart($attrString);
    }
    return $xmlResult;
  }

  /**
  * Close lists
  *
  * Called when a line without list items is encountered,
  * as well as at the end of the parsing process,
  * to close any open lists
  * The optional $type parameter determines which kind
  * of lists to close -- by default, all kinds of lists
  * are closed
  *
  * @return string (XML)
  */
  function closeLists($type = 'all') {
    $xmlResult = '';
    if ($type == 'all' || $type == 'def') {
      if ($this->defList) {
        $this->defList = FALSE;
        $xmlResult .= '</deflist>'.LF;
      }
    }
    if ($type == 'all' || $type == 'list') {
      if (strlen($this->listType) > 0) {
        for ($i = 0; $i < strlen($this->listType); $i++) {
          $xmlResult .= '</list>'.LF;
        }
        $xmlResult .= LF;
        $this->listType = '';
      }
    }
    return $xmlResult;
  }

  /**
  * Get syntax-highlighted code
  *
  * @param string $source
  * @param string $lang
  * @return string syntax-highlighted XML
  */
  function getHighlightedCode($source, $lang) {
    if (!is_object($this->highlighter)) {
      $this->highlighter = base_pluginloader::getPluginInstance(
        '7d3a2fa61b1746b10662b849e82011c5', $this
      );
    }
    if (is_object($this->highlighter)) {
      return $this->highlighter->highlight($source, $lang);
    }
    return $source;
  }
}
?>
