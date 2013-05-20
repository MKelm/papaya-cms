<?php
/**
* Link db administration
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
* @subpackage Free-LinkDatabase
* @version $Id: admin_linkdb.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basicclass for database access
*/
require_once(dirname(__FILE__)."/base_linkdb.php");

/**
* Link db administration
*
* @package Papaya-Modules
* @subpackage Free-LinkDatabase
*/
class admin_linkdb extends base_linkdb {

  /**
  * definite a kind of register to open
  * @var integer $openRegister
  */
  var $openRegister = 0;

  /**
  * definite a kind of statistic to show
  * @var integer $showStatistic
  */
  var $showStatistic = 0;

  /**
  * Contain all links
  * @var array $statisticAllYears
  */
  var $statisticAllYears = NULL;

  /**
  * Contain links loaded per year
  * @var array $statisticByYear
  */
  var $statisticByYear = NULL;

  /**
  * Contain links loaded per year and month
  * @var array $statisticByYearAndMonth
  */
  var $statisticByYearAndMonth = NULL;

  /**
  * Contain link loaded per linkid
  * @var array $statisticByLinkId
  */
  var $statisticByLinkId = NULL;

  /**
  * Calender, allocation number to names
  * @var array $months
  */
  var $months = array(
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'December'
  );

  /**
  * Execute - basic function for handling parameters
  *
  * @access public
  */
  function execute() {
    $sessParams = &$this->sessionParams;
    // Manage the open Register of Edit und Statistic
    if (isset($this->params['wiw']) &&
        ($this->params['wiw'] == 0 || $this->params['wiw'] == 1)) {
      $sessParams['openregister'] = $this->params['wiw'];
    }
    if (isset($sessParams['openregister']) &&
        ($sessParams['openregister'] == 0 || $sessParams['openregister'])) {
      $this->openRegister = $sessParams['openregister'];
    }
    if (isset($this->params['stat']) &&
        ($this->params['stat'] >= 0 && $this->params['stat'] <= 3)) {
      $sessParams['stat'] = $this->params['stat'];
    }
    if (isset($sessParams['stat']) &&
        ($sessParams['stat'] >= 0 && $sessParams['stat'] <= 3)) {
      $this->showStatistic = $sessParams['stat'];
    }
    if (!isset($this->params['stat']) || !isset($sessParams['stat'])) {
      $this->params['stat'] = $this->showStatistic;
    }
    $this->params['stat_year'] = (isset($this->params['stat_year'])) ?
      $this->params['stat_year'] : date("Y", time());
    $this->params['stat_month'] = (isset($this->params['stat_month'])) ?
      $this->params['stat_month'] : date("m", time());

    // Manage open Categs
    if (isset($sessParams['categopen']) && is_array($sessParams['categopen'])) {
      $this->categsOpen = $sessParams['categopen'];
    } else {
      $this->categsOpen = array();
    }
    // Manage Categs and Links
    switch (@$this->params['cmd']) {
    case 'open':
      $this->categsOpen[(int)$this->params['categ_id']] = TRUE;
      break;
    case 'close':
      unset($this->categsOpen[(int)$this->params['categ_id']]);
      break;
    case 'repair':
      $this->repairPaths();
      break;
    case 'add_categ':
      if ($newId = $this->addCateg((int)$this->params['categ_id'])) {
        $this->addMsg(MSG_INFO, $this->_gt('Category added.'));
        $this->params['categ_id'] = $newId;
        $this->initializeSessionParam('categ_id', array('cmd', 'link_id'));
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
      }
      break;
    case 'edit_categ':
      $this->loadCateg($this->params['categ_id']);
      $this->initializeCategEditform();
      if ($this->categDialog->modified()) {
        if ($this->categDialog->checkDialogInput()) {
          if ($this->saveCateg()) {
            $this->addMsg(MSG_INFO, $this->_gt('Category modified.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
          }
        }
      }
      break;
    case 'del_categ':
      if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
        if ($this->categExists($this->params['categ_id'])) {
          if ($this->categisEmpty($this->params['categ_id'])) {
            if ($this->deleteCateg($this->params['categ_id'])) {
              $this->addMsg(MSG_INFO, $this->_gt('Category deleted.'));
              if ($this->categExists($this->params['linkcateg_parent_id'])) {
                $this->params['categ_id'] = $this->params['linkcateg_parent_id'];
              } else {
                $this->params['categ_id'] = 0;
              }
              $this->initializeSessionParam('categ_id', array('cmd', 'link_id'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
            }
          } else {
            $this->addMsg(MSG_WARNING, $this->_gt('Category is not empty.'));
            $this->params['cmd'] = '';
          }
        } else {
          $this->addMsg(MSG_WARNING, $this->_gt('Category not found.'));
        }
      }
      break;
    case 'add_link':
      if ($newId = $this->addLink((int)$this->params['categ_id'])) {
        $this->addMsg(MSG_INFO, $this->_gt('Link added.'));
        $this->params['link_id'] = $newId;
        $this->initializeSessionParam('link_id', 'offset');
      } else {
        $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
      }
      break;
    case 'edit_link':
      $this->loadLink($this->params['link_id']);
      $this->initializeLinkEditform();
      if ($this->linkDialog->modified()) {
        if ($this->linkDialog->checkDialogInput()) {
          if ($this->saveLink()) {
            $this->addMsg(MSG_INFO, $this->_gt('Link modified.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error! Changes not saved.'));
          }
        }
      }
      break;
    case 'del_link':
      if (isset($this->params['confirm_delete']) && $this->params['confirm_delete']) {
        if ($this->linkExists($this->params['link_id'])) {
          if ($this->deleteLink((int)$this->params['link_id'])) {
            $this->params['cmd'] = NULL;
            $this->params['link_id'] = 0;
            $this->initializeSessionParam('link_id', array('offset', 'cmd'));
            $this->addMsg(MSG_INFO, $this->_gt('Link deleted.'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
          }
        }
      }
      break;
    case 'cut_link':
      if ($this->linkExists($this->params['link_id'])) {
        if ($this->cutLink((int)$this->params['link_id'])) {
          $this->params['cmd'] = NULL;
          $this->params['link_id'] = 0;
          $this->initializeSessionParam('link_id', array('offset', 'cmd'));
          $this->addMsg(MSG_INFO, $this->_gt('Link cut out.'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
        }
      }
      break;
    case 'cut_categ':
      if ($this->categExists($this->params['categ_id'])) {
        if ($this->cutCateg((int)$this->params['categ_id'])) {
          $this->params['cmd'] = NULL;
          $this->params['categ_id'] = 0;
          $this->initializeSessionParam('categ_id', array('cmd', 'link_id'));
          $this->addMsg(MSG_INFO, $this->_gt('Category cut out.'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
        }
      }
      break;
    case 'paste_categ':
      if (isset($this->params['cut_categ_id']) &&
          $this->categExists($this->params['cut_categ_id']) &&
          isset($this->params['categ_id']) && $this->params['categ_id'] >= 0) {
        if ($this->pasteCateg(
              $this->params['categ_id'], (int)$this->params['cut_categ_id'])
            ) {
          $this->params['cmd'] = NULL;
          $this->params['categ_id'] = $this->params['cut_categ_id'];
          $this->initializeSessionParam('categ_id', array('cmd', 'link_id'));
          $this->addMsg(MSG_INFO, $this->_gt('Category pasted.'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
        }
      }
      break;
    case 'paste_link':
      if (isset($this->params['cut_link_id']) &&
          $this->linkExists($this->params['cut_link_id']) &&
          isset($this->params['categ_id']) &&
          $this->categExists($this->params['categ_id'])) {
        if ($this->pasteLink(
             $this->params['cut_link_id'], $this->params['categ_id'])
           ) {
          $this->params['cmd'] = NULL;
          $this->params['link_id'] = $this->params['cut_link_id'];
          $this->initializeSessionParam('link_id', array('offset', 'cmd'));
          $this->addMsg(MSG_INFO, $this->_gt('Link pasted.'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Database error!'));
        }
      }
      break;
    case 'reset_cbl':
      if ($this->module->hasPerm(3, TRUE)) {
        if (isset($this->params['link_id']) && $this->params['link_id'] > 0 &&
            $this->linkExists($this->params['link_id']) &&
            isset($this->params['confirm_delete']) &&
            $this->params['confirm_delete']) {
          $this->resetLinkCounter($this->params['link_id']);
          unset($this->params['cmd']);
        }
      }
      break;
    case 'reset_cby':
      if ($this->module->hasPerm(3, TRUE)) {
        if (isset($this->params['stat_year']) && isset($this->params['stat']) &&
            $this->params['stat'] == 1 &&
            isset($this->params['confirm_delete']) &&
            $this->params['confirm_delete']) {
          $this->resetLinksByYear($this->params['stat_year']);
          unset($this->params['cmd']);
        }
      }
      break;
    case 'reset_cay':
      if ($this->module->hasPerm(3, TRUE)) {
        if (isset($this->params['confirm_delete']) &&
            $this->params['confirm_delete']) {
          $this->resetLinksAllYears();
          unset($this->params['cmd']);
        }
      }
      break;
    }

    if ($this->statisticExists() && $this->module->hasPerm(2, FALSE)) {
      switch(@$this->params['stat']) {
      case 0:
        $this->loadStatisticAllYears();
        break;
      case 1:
        if (isset($this->params['stat_year'])) {
          $this->loadStatisticByYear($this->params['stat_year']);
        }
        break;
      case 2:
        if (isset($this->params['stat_year']) &&
            isset($this->params['stat_month'])) {
          $this->loadStatisticByYearAndMonth(
            $this->params['stat_year'], $this->params['stat_month']
          );
        }
        break;
      case 3:
        if (isset($this->params['link_id']) && $this->params['link_id'] > 0 &&
            $this->linkExists($this->params['link_id'])) {
          $this->loadStatisticByLinkId(
            $this->params['link_id'], $this->params['stat_year']
          );
        }
        break;
      }
    }
    //put new data into session
    $this->sessionParams['categopen'] = $this->categsOpen;
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
    if ($this->openRegister == 0 ||
        ($this->openRegister == 1 && $this->params['stat'] == 3)) {
      $this->loadCategs();
      if (isset($this->params['categ_id']) && $this->params['categ_id'] > 0) {
        $this->loadLinks($this->params['categ_id']);
        $this->loadCateg($this->params['categ_id']);
        if (isset($this->params['link_id']) && $this->params['link_id'] > 0) {
          $this->loadLink($this->params['link_id']);
        }
      }
      if ($this->cutOutExists()) {
        $this->loadCutedElement();
      }
    }
  }

  /**
  * Get XML - browser output function
  *
  * @access public
  */
  function getXML() {
    if (is_object($this->layout)) {
      $this->getXMLButtons();
      switch (@$this->openRegister) {
      case 0:
        //content output
        $this->getXMLCategTree();
        switch (@$this->params['cmd']) {
        case 'del_categ':
          $this->getXMLDelCategForm();
          break;
        case 'del_link':
          $this->getXMLDelLinkForm();
          break;
        default:
          if (isset($this->link) && is_array($this->link)) {
            $this->getXMLLinkForm();
          } elseif (isset($this->categs) && is_array($this->categs) &&
              isset($this->categs[@(int)$this->params['categ_id']]) &&
              is_array($this->categs[@(int)$this->params['categ_id']])) {
            $this->getXMLCategForm();
          }
          if (isset($this->links) && is_array($this->links) &&
              count($this->links) > 0) {
            $this->getXMLLinkList();
          } else {
            $this->layout->addRight(
              '<sheet width="350" align="center">'.
              '<text><div style="padding: 0px 5px 0px 5px; ">'.
                _gtfile('linkdb_info.txt').'</div></text></sheet>');
          }
          if (isset($this->cuttedLink) && is_array($this->cuttedLink) ||
            isset($this->cuttedCategs) && is_array($this->cuttedCategs) ) {

            $this->getXMLCutedList();
          }
          break;
        }
        break;
      case 1:
        if ($this->statisticExists()) {
          switch(@$this->params['cmd']) {
          case 'reset_cby':
            if ($this->module->hasPerm(3, TRUE)) {
              $this->getXMLResetCounterByYearForm();
            }
            break;
          case 'reset_cay':
            if ($this->module->hasPerm(3, TRUE)) {
              $this->getXMLResetCounterAllYearsForm();
            }
            break;
          case 'reset_cbl':
            if ($this->module->hasPerm(3, TRUE)) {
              $this->getXMLResetCounterByLinkForm();
            }
            break;
          }
          if ($this->module->hasPerm(2, FALSE)) {
            switch ($this->showStatistic) {
            case 0:
              if (isset($this->statisticAllYears) &&
                  is_array($this->statisticAllYears)) {
                $this->getXMLStatisticAllYears();
              }
              break;
            case 1:
              if (isset($this->statisticByYear) &&
                  is_array($this->statisticByYear)) {
                $this->getXMLStatisticByYear();
              }
              break;
            case 2:
              if (isset($this->statisticByYearAndMonth) &&
                  is_array($this->statisticByYearAndMonth)) {
                $this->getXMLStatisticByYearAndMonth();
              }
              break;
            case 3:
              $this->getXMLCategTree();
              if (isset($this->links) && is_array($this->links) &&
                  count($this->links) > 0) {
                $this->getXMLLinkList();
              } else {
                $this->layout->addRight(
                  '<sheet width="350" align="center"><text>'.
                  '<div style="padding: 0px 5px 0px 5px; ">'.
                    _gtfile('linkdb_info_statistic.txt').'</div></text></sheet>');
              }
              if (isset($this->statisticByLinkId) &&
                  is_array($this->statisticByLinkId)) {
                $this->getXMLStatisticByLinkId();
              }
              break;
            }
          }
        } else {
          $this->layout->addRight(
            '<sheet width="350" align="center"><text>'.
            '<div style="padding: 0px 5px 0px 5px; ">'.
              _gtfile('linkdb_statistic.txt').'</div></text></sheet>');
        }
      }
    }
  }

  /**
  * Get XML category tree
  *
  * @access public
  */
  function getXMLCategTree() {
    if (isset($this->categs) && is_array($this->categs)) {
      $result = sprintf(
        '<listview title="%s" width="200">'.LF,
        $this->_gt('Categories')
      );
      $result .= '<items>'.LF;
      $selected = ($this->params['categ_id'] == 0) ? ' selected="selected"' : '';
      $result .= sprintf(
        '<listitem href="%s" title="%s" image="%s" %s/>'.LF,
        $this->buildLink(array('categ_id' => 0, 'categ_id' => 0)).'&ldb[link_id]=0',
        $this->_gt('Base'),
        $this->images['places-desktop'],
        $selected
      );
      $result .= $this->getXMLCategSubTree(0, 0);
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Branch of Category tree
  '
  * @param integer $parent Parent-ID
  * @param integer $indent shifting
  * @return string $result XML
  */
  function getXMLCategSubTree($parent, $indent) {
    $result = '';
    if (isset($this->categTree[$parent]) && is_array($this->categTree[$parent]) &&
        (isset($this->categsOpen[$parent]) || ($parent == 0))) {
      foreach ($this->categTree[$parent] as $id) {
        $result .= $this->getXMLCategEntry($id, $indent);
      }
    }
    return $result;
  }

  /**
  * Element of category tree
  *
  * @param integer $id ID
  * @param integer $indent shifting
  * @return string $result XML
  */
  function getXMLCategEntry($id, $indent) {
    $result = '';
    if (isset($this->categs[$id]) && is_array($this->categs[$id])) {
      $empty = (bool)(!@is_array($this->categTree[$id]));
      $opened = (bool)(isset($this->categsOpen[$id]) && (!$empty));
      if ($empty) {
        $nodeHref = FALSE;
        $node = ' node="empty"';
      } elseif ($opened) {
        $nodeHref = $this->buildLink(
          array('cmd' => 'close', 'categ_id' => (int)$id)
        );
        $node = sprintf(' node="open" nhref="%s"', $nodeHref);
      } else {
        $nodeHref = $this->buildLink(
          array('cmd' => 'open', 'categ_id' => (int)$id)
        );
        $node = sprintf(' node="close" nhref="%s"', $nodeHref);
      }
      $selected = ($this->params['categ_id'] == $id) ?
       ' selected="selected"' : '';
      $result .= sprintf(
        '<listitem href="%s" title="%s" indent="%d" %s %s/>'.LF,
        $this->buildLink(
          array(
            'categ_id' => 0,
            'categ_id' => (int)$id
          )
        ).'&ldb[link_id]=0',
        htmlspecialchars(
          $this->categs[$id]['linkcateg_title']
        ),
        $indent,
        $node,
        $selected
      );
      $result .= $this->getXMLCategSubTree($id, $indent + 1);
    }
    return $result;
  }

  /**
  * Get XML for buttons
  *
  * @access public
  */
  function getXMLButtons() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_btnbuilder.php');
    $toolbar = new base_btnbuilder;
    $toolbar->images = &$this->images;
    $toolbar->addButton(
      'Edit',
      $this->buildLink(array('wiw' => '0')),
      'actions-edit',
      '',
      $this->openRegister == 0
    );
    if ($this->module->hasPerm(2, FALSE)) {
      $toolbar->addButton(
        'Statistic',
        $this->buildLink(array('wiw' => '1')),
        'items-table',
        '',
        $this->openRegister == 1
      );
    }
    $toolbar->addSeperator();

    // Edit area
    if ($this->openRegister == 0) {
      $toolbar->addButton(
        'Repair path',
        $this->buildLink(array('cmd' => 'repair')),
        'items-option',
        '',
        FALSE
      );
      $toolbar->addSeperator();
      $toolbar->addButton(
        'Add category',
        $this->buildLink(
          array('cmd' => 'add_categ', 'categ_id' => (int)$this->params['categ_id'])),
        'actions-folder-add',
        '',
        FALSE
      );
      // category
      if (isset($this->categs[$this->params['categ_id']]) &&
          is_array($this->categs[$this->params['categ_id']]) ) {
        if (!isset($this->params['link_id']) || $this->params['link_id'] == 0 ) {
          $toolbar->addButton(
            'Cut category',
            $this->buildLink(
              array('cmd' => 'cut_categ', 'categ_id' => (int)$this->params['categ_id'])),
            'actions-edit-cut',
            '',
            FALSE
          );
          $toolbar->addButton(
            'Delete category',
            $this->buildLink(
              array('cmd' => 'del_categ', 'categ_id' => (int)$this->params['categ_id'])),
            'actions-folder-delete',
            '',
            FALSE
          );
        }
        $toolbar->addSeperator();
        $this->getPagesNav(
          $toolbar,
          (isset($this->params['offset']) && $this->params['offset'] > 0) ?
            (int)$this->params['offset'] : 0,
          $this->linksPerPage,
          $this->linkCount,
          'offset',
          'Links'
        );
        $toolbar->addSeperator();
        $toolbar->addButton(
          'Add link',
          $this->buildLink(
            array('cmd' => 'add_link', 'categ_id' => (int)$this->params['categ_id'])),
          'actions-alias-add',
          '',
          FALSE
        );
      }
      // links
      if (isset($this->link) && is_array($this->link) &&
          $this->link['link_id'] == $this->params['link_id']) {
        $toolbar->addButton(
          'Cut link',
          $this->buildLink(
            array('cmd' => 'cut_link', 'link_id' => (int)$this->params['link_id'])),
          'actions-edit-cut',
          '',
          FALSE
        );
        $toolbar->addButton(
          'Delete link',
          $this->buildLink(
            array('cmd' => 'del_link', 'link_id' => (int)$this->params['link_id'])),
          'actions-alias-delete',
          '',
          FALSE
        );
      }
    } elseif ($this->openRegister == 1 && $this->statisticExists()) {
      // Statistic area
      $comboData = array(
        0 => 'All Years',
        1 => 'One Year',
        2 => 'Top10',
        3 => 'One Link'
      );
      $toolbar->addCombo(
        'Choice',
        $this->buildLink(array('wiw'=>'1')),
        $this->paramName.'[stat]',
        $this->showStatistic,
        $comboData
      );
      switch ($this->showStatistic) {
      case 0:
        if ($this->module->hasPerm(3, FALSE)) {
          $toolbar->addSeperator();
          $toolbar->addButton(
            'Reset counter',
            $this->buildLink(array('cmd' => 'reset_cay')),
            $this->localImages['counter-delete'],
            '',
            FALSE
          );
        }
        break;
      case 1:
        $years = $this->getAllYearsForStatistic();
        $toolbar->addCombo(
          'Year',
          $this->buildLink(array('wiw'=>1, 'stat'=>1)),
          $this->paramName.'[stat_year]',
          $this->params['stat_year'],
          $years
        );

        if ($this->module->hasPerm(3, FALSE)) {
          $toolbar->addSeperator();
          $toolbar->addButton(
            'Reset counter',
            $this->buildLink(
              array(
                'cmd' => 'reset_cby',
                'stat' => $this->params['stat'],
                'stat_year' => $this->params['stat_year']
              )
            ),
            $this->localImages['counter-delete'],
            '',
            FALSE
          );
        }
        break;
      case 2:
        //all years
        $years = $this->getAllYearsForStatistic();
        $toolbar->addCombo(
          'Year',
          $this->buildLink(array('wiw' => 1, 'stat' => 2)),
          $this->paramName.'[stat_year]',
          $this->params['stat_year'],
          $years
        );

        //all months
        $months = $this->getAllMonthsForStatistic($this->params['stat_year']);
        $toolbar->addCombo(
          'Month',
          $this->buildLink(
          array('wiw' => 1, 'stat' => 2)),
          $this->paramName.'[stat_month]',
          $this->params['stat_month'],
          $months
        );
        break;
      case 3:
        //all years
        $years = $this->getAllYearsForStatistic();
        $toolbar->addCombo(
          'Year',
          $this->buildLink(array('wiw' => 1, 'stat' => 3)),
          $this->paramName.'[stat_year]',
          $this->params['stat_year'],
          $years
        );
        $toolbar->addSeperator();
        $this->getPagesNav(
          $toolbar,
          (isset($this->params['offset']) && $this->params['offset'] > 0) ?
            (int)$this->params['offset'] : 0,
          $this->linksPerPage,
          $this->linkCount,
          'offset',
          'Links'
        );
        if ($this->module->hasPerm(3, FALSE)) {
          $toolbar->addSeperator();
          if (isset($this->params['link_id']) && $this->params['link_id'] > 0 &&
              $this->linkExists($this->params['link_id']) ) {

            $toolbar->addButton(
              'Reset counter',
              $this->buildLink(
                array(
                  'cmd' => 'reset_cbl',
                  'link_id' => (int)$this->params['link_id']
                )
              ),
              $this->localImages['counter-delete'],
              '',
              FALSE
            );
          }
        }
        break;
      }
    }
    if ($str = $toolbar->getXML()) {
      $this->layout->addMenu(
        sprintf('<menu ident="%s">%s</menu>'.LF, 'edit', $str)
      );
    }
  }

  /**
  * Get all exists years from statistic
  *
  * @access public
  * @return array $result
  */
  function getAllYearsForStatistic() {
    $result = array();
    $sql = 'SELECT DISTINCT clicks_year
              FROM %s
             ORDER BY clicks_year DESC';
    if ($res = $this->databaseQueryFmt($sql, array($this->tableLinkdbClicks))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $data = $row['clicks_year'];
        $result[$data] = $data;
      }
    }
    return $result;
  }

  /**
  * Get all exists months from statistic
  *
  * @param integer $year
  * @access public
  * @return array $result
  */
  function getAllMonthsForStatistic($year) {
    $result = array(0=>$this->_gt('all'));
    $sql = 'SELECT DISTINCT clicks_month
              FROM %s
             WHERE clicks_year = %d
             ORDER BY clicks_month DESC';
    if ($res = $this->databaseQueryFmt($sql, array($this->tableLinkdbClicks, $year))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $data = $row['clicks_month'];
        $result[$data] = str_pad($data, 2, '0', STR_PAD_LEFT);
      }
    }
    return $result;
  }

  /**
  * Load statistic of all years
  *
  * @access public
  */
  function loadStatisticAllYears() {
    $sql = 'SELECT clicks_year AS year, SUM( clicks_count ) AS counted
              FROM papaya_linkdb_clicks
          GROUP BY clicks_year
          ORDER BY clicks_year ASC';

    $params = array($this->tableLinkdbClicks);

    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->statisticAllYears[$row['year']] = $row;
      }
    }
  }

  /**
  * Load statistic per year
  *
  * @param integer $year
  * @access public
  */
  function loadStatisticByYear($year) {
    $sql = 'SELECT clicks_month AS month, SUM( clicks_count ) AS counted
              FROM %s
             WHERE clicks_year = %d
             GROUP BY clicks_month
             ORDER BY clicks_month ASC';
    $params = array($this->tableLinkdbClicks, (int)$year);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->statisticByYear[$row['month']] = $row;
      }
    }
  }


  /**
  * Get pages navigation
  *
  * @param object &$toolbar
  * @param integer $offset
  * @param integer $step
  * @param integer $max
  * @param string $paramName optional, default value 'offset'
  * @param string $caption optional, default value 'Pages'
  * @access public
  */
  function getPagesNav(&$toolbar, $offset, $step, $max,
                       $paramName = 'offset', $caption = 'Pages') {
    if (($max > $step) && is_object($toolbar)) {
      if ($offset > 0) {
        $i = (($offset - $step) > 0) ? ($offset - $step) : 0;
        $toolbar->addButton(
          'Prior',
          $this->buildLink(array('cmd' => 'show', $paramName => $i)),
          'actions-go-previous',
          'Prior page'
        );
      }
      for ($i = 0, $x = 1; $i < $max; $i += $step, $x++) {
        $pages[$i] = $x;
      }
      $toolbar->addCombo(
        $caption,
        $this->buildLink(array('cmd' => 'show')),
        $this->paramName.'['.$paramName.']',
        $offset,
        $pages
      );
      if ($offset < ($max - $step)) {
        $i = (($offset + $step) < $max) ? ($offset + $step) : ($max - $step);
        $toolbar->addButton(
          'Next',
          $this->buildLink(array('cmd' => 'show', $paramName => $i)),
          'actions-go-next',
          'Next page'
        );
      }
    }
  }

  /**
  * Get XML for Clipboard
  *
  * @access public
  */
  function getXMLCutedList () {
      $result = sprintf(
        '<listview title="%s" width="200">'.LF,
        $this->_gt('Clipboard')
      );
      $result .= '<items>'.LF;
      $result .= $this->getXMLCutedElements();
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
  }

  /**
  * Get XML of elements for clipboard
  *
  * @access public
  * @return XML $result
  */
  function getXMLCutedElements () {
    $result = '';
    if (isset($this->cuttedCategs) && is_array($this->cuttedCategs)) {
      foreach ($this->cuttedCategs as $cuttedCateg) {
        $result .= sprintf(
          '<listitem title="%s" image="%s">'.LF,
          $cuttedCateg['linkcateg_title'],
          $this->images['items-folder']
        );
        $result .= sprintf('<subitem align="right">'.LF);
        $result .= sprintf(
          '<a href="%s">'.LF,
          $this->buildLink(
            array(
              'cmd'=>'paste_categ',
              'cut_categ_id'=>(int)$cuttedCateg['linkcateg_id'],
              'categ_id'=>$this->params['categ_id']
            )
          )
        );
        $result .= sprintf(
          '<glyph src="%s" hint="%s" />'.LF,
          $this->images['actions-edit-paste'],
          $this->_gt('Paste category'));
        $result .= '</a>'.LF;
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;

      }
    }
    if (isset($this->cuttedLink) && is_array($this->cuttedLink)) {
      foreach ($this->cuttedLink as $cuttedLink) {
        switch ($cuttedLink['link_status']) {
        case 0:
          $image = $this->images['status-sign-warning'];
          break;
        case 1:
          $image = $this->images['status-sign-ok'];
          break;
        case 2:
          $image = $this->images['status-sign-off'];
          break;
        }
        $result .= sprintf(
          '<listitem title="%s" image="%s">'.LF,
          $cuttedLink['link_title'],
          $image
        );
        $result .= sprintf('<subitem align="%s">'.LF, "right");
        $result .= sprintf(
          '<a href="%s">'.LF,
          $this->buildLink(
            array(
              'cmd' => 'paste_link',
              'cut_link_id' => (int)$cuttedLink['link_id']
            )
          )
        );
        $result .= sprintf(
          '<glyph src="%s" hint="%s" />'.LF,
          $this->images['actions-edit-paste'],
          $this->_gt('Paste link')
        );
        $result .= '</a>'.LF;
        $result .= '</subitem>'.LF;
        $result .= '</listitem>'.LF;

      }
    }
    return $result;
  }


  /**
  * Get XML for categoryform
  *
  * @access public
  */
  function getXMLCategForm() {
    if (isset($this->categs[$this->params['categ_id']]) &&
        is_array($this->categs[$this->params['categ_id']])) {
      $this->initializeCategEditForm();
      $this->categDialog->baseLink = $this->baseLink;
      $this->categDialog->dialogTitle = htmlspecialchars($this->_gt('Properties'));
      $this->categDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->categDialog->getDialogXML());
    }
  }

  /**
  * Initialize editform for categ
  *
  * @access public
  */
  function initializeCategEditForm() {
    if (!(isset($this->categDialog) && is_object($this->categDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->categ;
      $hidden = array(
        'cmd' => 'edit_categ',
        'save' => 1,
        'categ_id' => $this->params['categ_id']
      );
      $fields = array(
        'linkcateg_title' => array(
          'Title', 'isNoHTML', TRUE, 'input', 200
        ),
        'linkcateg_description' => array(
          'Description', 'isSomeText', FALSE, 'textarea', 8
        )
      );
      $this->categDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden);
      $this->categDialog->msgs = &$this->msgs;
      $this->categDialog->loadParams();
    }
  }

  /**
  * Get XML for linkform
  *
  * @access public
  */
  function getXMLLinkForm() {
    if (isset($this->links[$this->params['link_id']]) &&
        is_array($this->links[$this->params['link_id']])) {
      $this->initializeLinkEditForm();
      $this->linkDialog->baseLink = $this->baseLink;
      $this->linkDialog->dialogTitle = htmlspecialchars($this->_gt('Properties'));
      $this->linkDialog->dialogDoubleButtons = FALSE;
      $this->layout->add($this->linkDialog->getDialogXML());
    }
  }

  /**
  * Initialize editform for link
  *
  * @access public
  */
  function initializeLinkEditForm() {
    if (!(isset($this->linkDialog) && is_object($this->linkDialog))) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_dialog.php');
      $data = $this->link;
      $data['link_created'] = date('Y-m-d H:i:s', $this->link['link_created']);
      $data['link_modified'] = date('Y-m-d H:i:s', $this->link['link_modified']);
      $hidden = array(
        'cmd' => 'edit_link',
        'save' => 1,
        'link_id' => $data['link_id'],
        'offset' => @(int)$this->params['offset']
      );
      $statusItems = array(
        0 => $this->_gt('created'),
        1 => $this->_gt('published'),
        2 => $this->_gt('hidden')
      );
      $fields = array(
        'link_title' => array('Title', 'isNoHTML', TRUE, 'input', 200),
        'link_description' => array('Description', 'isSomeText', FALSE, 'textarea', 8),
        'link_url' => array('URL', 'isHTTPX', TRUE, 'input', 300),
        'link_created' => array('Created', 'isNum', TRUE, 'disabled_input', 200),
        'link_modified' => array('Modified', 'isNum', TRUE, 'disabled_input', 200),
        'link_status' => array('Status', 'isNum', TRUE, 'combo', $statusItems)
      );
      $this->linkDialog = new base_dialog(
        $this, $this->paramName, $fields, $data, $hidden);
      $this->linkDialog->msgs = &$this->msgs;
      $this->linkDialog->loadParams();
    }
  }

  /**
  * Get XML for linklist
  *
  * @access public
  */
  function getXMLLinkList() {
    if (isset($this->links) && is_array($this->links)) {
      $result = sprintf('<listview width="350" title="%s">', $this->_gt('Links'));
      $result .= '<cols>';
      $result .= sprintf('<col>%s</col>', $this->_gt('Title'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Clicks'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Id'));
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->links as $link) {
        $selected =
          (isset($this->params['link_id']) && $link['link_id'] == $this->params['link_id']) ?
          ' selected="selected"' : '';
        switch ($link['link_status']) {
        case 0:
          $image = $this->images['status-sign-warning'];
          break;
        case 1:
          $image = $this->images['status-sign-ok'];
          break;
        case 2:
          $image = $this->images['status-sign-off'];
          break;
        }
        $result .= sprintf(
          '<listitem href="%s" title="%s" image="%s" %s>',
          $this->buildLink(
            array(
              'link_id' => (int)$link['link_id'],
              'offset' => (int)@$this->params['offset'])
            ),
            htmlspecialchars($link['link_title']),
            $image,
            $selected
          );
        $result .= sprintf('<subitem align="center">%s</subitem>', $link['counted']);
        $result .= sprintf('<subitem align="center">%s</subitem>', $link['link_id']);
        $result .= '</listitem>';

      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addRight($result);
    }
  }

  /**
  * Get XML form to delete link
  *
  * @access public
  */
  function getXMLDelLinkForm() {
    if (isset($this->link) && is_array($this->link) &&
        $this->link['link_id'] == $this->params['link_id']) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_link',
        'link_id' => $this->link['link_id'],
        'confirm_delete' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete link "%s" (%s)?'),
        htmlspecialchars($this->link['link_title']),
        (int)$this->params['link_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get XML counter resetform
  *
  * @access public
  */
  function getXMLResetCounterByYearForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'reset_cby',
      'stat' => 1,
      'stat_year' => $this->params['stat_year'],
      'link_id' => $this->params['stat_year'],
      'confirm_delete' => 1
    );
    $msg = sprintf($this->_gt('Reset counter per year (%s)?'), $this->params['stat_year']);
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question');
    $dialog->baseLink = $this->baseLink;
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Reset';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * Get XML form to reset all links
  *
  * @access public
  */
  function getXMLResetCounterAllYearsForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $hidden = array(
      'cmd' => 'reset_cay',
      'confirm_delete' => 1,
    );
    $msg = sprintf($this->_gt('Reset counter of all years?'));
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question');
    $dialog->baseLink = $this->baseLink;
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Reset';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * Get XML form to reset counter per link
  *
  * @access public
  */
  function getXMLResetCounterByLinkForm() {
    include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
    $this->loadLink($this->params['link_id']);
    $hidden = array(
      'cmd' => 'reset_cbl',
      'stat' => 3,
      'link_id' =>(int)$this->params['link_id'],
      'confirm_delete' => 1,
    );
    $msg = sprintf(
      $this->_gt('Reset counter').' ('.$this->link['link_title'].')?');
    $dialog = new base_msgdialog(
      $this, $this->paramName, $hidden, $msg, 'question');
    $dialog->baseLink = $this->baseLink;
    $dialog->msgs = &$this->msgs;
    $dialog->buttonTitle = 'Reset';
    $this->layout->add($dialog->getMsgDialog());
  }

  /**
  * Delete category form
  *
  * @access public
  */
  function getXMLDelCategForm() {
    if (isset($this->categ) && is_array($this->categ) &&
        $this->categ['linkcateg_id'] == $this->params['categ_id']) {
      include_once(PAPAYA_INCLUDE_PATH.'system/base_msgdialog.php');
      $hidden = array(
        'cmd' => 'del_categ',
        'linkcateg_id' => $this->params['categ_id'],
        'linkcateg_parent_id' => $this->categ['linkcateg_parent_id'],
        'confirm_delete' => 1
      );
      $msg = sprintf(
        $this->_gt('Delete categ "%s" (%s)?'),
        htmlspecialchars($this->categ['linkcateg_title']),
        (int)$this->params['categ_id']
      );
      $dialog = new base_msgdialog(
        $this, $this->paramName, $hidden, $msg, 'question');
      $dialog->baseLink = $this->baseLink;
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Repair paths
  *
  * @access public
  */
  function repairPaths() {
    $this->loadCategs(NULL, TRUE);
    $count = 0;
    if (isset($this->categs) && is_array($this->categs)) {
      foreach ($this->categs as $categId=>$categ) {
        if ($categId > 0) {
          $oldPath = $categ['linkcateg_path'];
          $path = $this->calcPrevPath($categId);
          if ($oldPath != $path) {
            $data = array('linkcateg_path' => $path);
            $this->databaseUpdateRecord(
              $this->tableLinkdbCateg, $data, 'linkcateg_id', $categId
            );
            $count++;
          }
        }
      }
      if ($count > 0) {
        $this->addMsg(MSG_INFO, sprintf($this->_gt('%s paths changed.'), $count));
      }
    }
  }

  /**
  * Add category
  *
  * @param integer $parent parent id
  * @access public
  * @return mixed FALSE or return value of databaseInsertRecord
  */
  function addCateg($parent) {
    $this->loadCateg($this->params['categ_id']);
    if ($this->categExists($parent) || ($parent == 0)) {
      $this->loadCateg($this->params['categ_id']);
      if (isset($this->categ) && is_array($this->categ)) {
        $path = $this->categ['linkcateg_path'].$this->categ['linkcateg_id'].';';
      } else {
        $path = ';0;';
      }
      $data = array(
        'linkcateg_parent_id' => $parent,
        'linkcateg_title' => $this->_gt('New category'),
        'linkcateg_path' => $path
      );
      unset($this->categ);
      return $this->databaseInsertRecord(
        $this->tableLinkdbCateg, 'linkcateg_id', $data);
    }
    return FALSE;
  }

  /**
  * Save category
  *
  * @access public
  * @return mixed FALSE or return value of databaseUpdateRecord
  */
  function saveCateg() {
    $data = array(
      'linkcateg_title' => $this->params['linkcateg_title'],
      'linkcateg_description' => $this->params['linkcateg_description']
    );
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableLinkdbCateg, $data, 'linkcateg_id', (int)$this->params['categ_id']
    );
  }

  /**
  * Delete Category
  *
  * @param integer $id category id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteCateg($id) {
    return $this->databaseDeleteRecord(
      $this->tableLinkdbCateg, 'linkcateg_id', $id);
  }

  /**
  * Delete Link
  *
  * @param integer $id link id
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function deleteLink($id) {
    return $this->databaseDeleteRecord($this->tableLinkdb, 'link_id', $id);
  }

  /**
  * Add new Link
  *
  * @param integer $categ category id
  * @access public
  * @return mixed FALSE or return value of databaseInsertRecord
  */
  function addLink($categ) {
    if ($this->categExists($categ) || $this->params['categ_id'] == 0) {
      $data = array(
        'linkcateg_id' => $categ,
        'link_title' => $this->_gt('New link'),
        'link_description' => '',
        'link_url' => '',
        'link_created' => time(),
        'link_modified' => time(),
        'link_status' => 0
      );
      return $this->databaseInsertRecord($this->tableLinkdb, 'link_id', $data);
    }
    return FALSE;
  }

  /**
  * Save Link
  *
  * @access public
  * @return mixed FALSE or return value of databaseUpdateRecord
  */
  function saveLink() {

    $data = array(
      'link_title' => $this->params['link_title'],
      'link_description' => $this->params['link_description'],
      'link_url' => $this->params['link_url'],
      'link_modified' => time(),
      'link_status' => $this->params['link_status']
    );
    return $this->databaseUpdateRecord(
      $this->tableLinkdb, $data, 'link_id', (int)$this->params['link_id']
    );
    return FALSE;
  }


  /**
  * Load Elements from clipboard
  *
  * @access public
  */
  function loadCutedElement () {
    $sql = 'SELECT linkcateg_id,
                   linkcateg_parent_id,
                   linkcateg_title
              FROM %s
             WHERE linkcateg_parent_id = %d';
    $params = array($this->tableLinkdbCateg, -1);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->cuttedCategs[(int)$row['linkcateg_id']] = $row;
      }
    }
    $sql = 'SELECT link_id,
                   linkcateg_id,
                   link_title,
                   link_status
              FROM %s
             WHERE linkcateg_id = %d';
    $params = array($this->tableLinkdb, -1);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->cuttedLink[(int)$row['link_id']] = $row;
      }
    }
  }


  /**
  * Check if exists cuted Elements
  *
  * @access public
  * @return boolean
  */
  function cutOutExists () {
    $exists = FALSE;
    $sql = "SELECT count(*)
              FROM %s
             WHERE linkcateg_id = '%d'";
    $params = array($this->tableLinkdb, -1);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        if ((bool)$row[0] > 0 ) {
          $exists = TRUE;
        }
      }
    }
    $sql = "SELECT count(*)
              FROM %s
             WHERE linkcateg_parent_id = '%d'";
    $params = array($this->tableLinkdbCateg, -1);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        if ((bool)$row[0] > 0 ) {
          $exists = TRUE;
        }
      }
    }
    return $exists;
  }


  /**
  * Cut Link to the clipboard
  *
  * @param integer $linkId
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function cutLink ($linkId) {
    $data = array('linkcateg_id' => -1);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableLinkdb, $data, 'link_id', (int)$this->params['link_id']
    );
  }

  /**
  * Paste Link from clipboard into Categ
  *
  * @param integer $linkId
  * @param integer $categId
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function pasteLink ( $linkId, $categId) {
    $data = array('linkcateg_id' => $categId);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableLinkdb, $data, 'link_id', (int)$this->params['cut_link_id']
    );
  }

  /**
  * Cut Categ to the clipboard
  *
  * @param integer $categId
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function cutCateg ($categId) {
    $data = array('linkcateg_parent_id' => -1);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableLinkdbCateg, $data, 'linkcateg_id', (int)$this->params['categ_id']
    );
  }

  /**
  * Paste Categ from clipboard into Categstree
  *
  * @param integer $parent
  * @param integer $categ
  * @access public
  * @return mixed FALSE or number of affected_rows or database result object
  */
  function pasteCateg ($parent, $categ) {
    $this->loadCateg($this->params['categ_id']);
    if (isset($this->categ) && is_array($this->categ)) {
      $path = $this->categ['linkcateg_path'].$this->categ['linkcateg_id'].';';
    } else {
      $path = ';0;';
    }
    $data = array(
      'linkcateg_parent_id' => $parent,
      'linkcateg_path' => $path
    );
    unset($this->categ);
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableLinkdbCateg, $data, 'linkcateg_id', (int)$this->params['cut_categ_id']
    );
  }


  /**
  * Load statistic per year and month
  *
  * @param integer $year
  * @param integer $month
  * @access public
  */
  function loadStatisticByYearAndMonth($year, $month) {
    if ($month < 1 ) {
      $sql = 'SELECT links.link_title AS title,
                     SUM(clicks.clicks_count) AS counted
                FROM %s AS clicks, %s AS links
               WHERE clicks.clicks_year = %d
                 AND clicks.link_id = links.link_id
               GROUP BY clicks.link_id
               ORDER BY clicks.clicks_count DESC';
      $params = array($this->tableLinkdbClicks, $this->tableLinkdb, $year);
    } else {
      $sql = 'SELECT links.link_title AS title,
                     clicks.clicks_count AS counted
                FROM %s AS clicks, %s AS links
               WHERE clicks.clicks_year = %d
                 AND clicks.link_id = links.link_id
                 AND clicks.clicks_month = %d
               ORDER BY clicks.clicks_count DESC';

      $params = array($this->tableLinkdbClicks, $this->tableLinkdb, $year, $month);
    }

    if ($res = $this->databaseQueryFmt($sql, $params, 10)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->statisticByYearAndMonth[$row['title']] = $row;
      }
    }
  }

  /**
  * loadf statistic per linkid and year
  *
  * @param integer $linkId
  * @param integer $year
  * @access public
  */
  function loadStatisticByLinkId($linkId, $year) {
    $sql = 'SELECT link_id, clicks_month, clicks_count
              FROM %s
             WHERE link_id = %d
               AND clicks_year = %d
             GROUP BY clicks_month
             ORDER BY clicks_month';
    $params = array($this->tableLinkdbClicks, $linkId, $year);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->statisticByLinkId[$row['clicks_month']] = $row;
      }
    }
  }

  /**
  * Get XML statistic per year and month
  *
  * @access public
  */
  function getXMLStatisticByYearAndMonth() {
    $string = '';
    if (isset($this->statisticByYearAndMonth) &&
        is_array($this->statisticByYearAndMonth)) {
      $result = sprintf(
        '<listview width="350" title="%s">',
        $this->_gt('Statistic per year and month')
      );
      $result .= '<cols>';
      $result .= sprintf('<col>%s</col>', $this->_gt('Link'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Clicks'));
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->statisticByYearAndMonth as $link) {
        $result .= sprintf(
          '<listitem title="%s">',
          papaya_strings::escapeHTMLChars($link['title']));
        $result .= sprintf(
          '<subitem align="center">%d</subitem>',
          (int)$link['counted']
        );
        $result .= '</listitem>';
      }
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML statistic of all years
  *
  * @access public
  */
  function getXMLStatisticAllYears() {
    $string = '';
    $statistic = '';
    if (isset($this->statisticAllYears) && is_array($this->statisticAllYears)) {
      $minMax = array();
      foreach ($this->statisticAllYears as $year) {
        $minMax[] .= $year['counted'];
      }
      $statistic .= sprintf(
        '<diagram mode="%s" orientation="%s" title="%s">',
        'bars',
        'horizontal',
        'Clicks all years'
      );
      $statistic .= sprintf(
        '<scale min="%d" max="%d" title="%s"/>',
        min($minMax),
        max($minMax),
        'text'
      );
      $statistic .= sprintf('<values title="%s">', $this->params['stat_year']);
      $result = sprintf(
        '<listview width="350" title="%s">',
        $this->_gt('Statistic all years')
      );
      $result .= '<cols>';
      $result .= sprintf('<col>%s</col>', $this->_gt('Year'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Clicks'));
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->statisticAllYears as $year) {
        $statistic .= sprintf(
          '<value title="%s" size="%d" percent="%d"/>',
          $year['year'],
          $year['counted'],
          round($year['counted'] * 100 / max($minMax), 2)
        );
        $result .= sprintf('<listitem title="%s">', (string)$year['year']);
        $result .= sprintf(
          '<subitem align="center">%d</subitem>',
          (int)$year['counted']);
        $result .= '</listitem>';
      }
      $statistic .= sprintf('</values>');
      $statistic .= sprintf('</diagram>');
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Get XML statistic per link
  *
  * @access public
  */
  function getXMLStatisticByLinkId() {
    $string = '';
    $statistic = '';
    if (isset($this->statisticByLinkId) && is_array($this->statisticByLinkId)) {
      $minMax = array();
      foreach ($this->statisticByLinkId as $link) {
        $minMax[] .= $link['clicks_count'];
      }
      $statistic .= sprintf(
        '<diagram mode="%s" orientation="%s" title="%s">',
        'bars',
        'horizontal',
        'Clicks per link id'
      );
      $statistic .= sprintf(
        '<scale min="%d" max="%d" title="%s"/>',
        min($minMax),
        max($minMax),
        'text'
      );
      $statistic .= sprintf('<values title="%s">', $this->params['link_id']);
      $result = sprintf(
        '<listview title="%s">',
        $this->_gt('Statistic per link id'));
      $result .= '<cols>';
      $result .= sprintf('<col>%s</col>', $this->_gt('Month'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Clicks'));
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->statisticByLinkId as $link) {
        $statistic .= sprintf(
          '<value title="%s" size="%d" percent="%d"/>',
          $this->months[$link['clicks_month']],
          $link['clicks_count'],
          round($link['clicks_count'] * 100 / max($minMax), 2)
        );
        $result .= sprintf(
          '<listitem title="%s">',
          (string)$this->months[$link['clicks_month']]
        );
        $result .= sprintf(
          '<subitem align="center">%d</subitem>',
          (int)$link['clicks_count']
        );
        $result .= '</listitem>';
      }
      $statistic .= sprintf('</values>');
      $statistic .= sprintf('</diagram>');
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addCenter($result);
    }
  }

  /**
  * Get XML statistic per year
  *
  * @access public
  */
  function getXMLStatisticByYear() {
    $statistic = '';
    $string = '';
    if (isset($this->statisticByYear) && is_array($this->statisticByYear)) {
      $minMax = array();
      foreach ($this->statisticByYear as $month) {
        $minMax[] .= $month['counted'];
      }
      $statistic .= sprintf(
        '<diagram mode="%s" orientation="%s" title="%s">',
        'bars',
        'horizontal',
        'Clicks per year'
      );
      $statistic .= sprintf(
        '<scale min="%d" max="%d" title="%s"/>',
        min($minMax),
        max($minMax),
        'text'
      );
      $statistic .= sprintf('<values title="%s">', $this->params['stat_year']);
      $result = sprintf(
        '<listview width="350" title="%s">',
        $this->_gt('Statistic all months per year')
      );
      $result .= '<cols>';
      $result .= sprintf('<col>%s</col>', $this->_gt('Month'));
      $result .= sprintf('<col align="center">%s</col>', $this->_gt('Clicks'));
      $result .= '</cols>';
      $result .= '<items>';
      foreach ($this->statisticByYear as $month) {
        $statistic .= sprintf(
          '<value title="%s" size="%d" percent="%d" />',
          $this->months[$month['month']],
          $month['counted'],
          round($month['counted'] * 100 / max($minMax), 2)
        );
        $result .= sprintf(
          '<listitem title="%s">',
          (string)$this->months[$month['month']]);
        $result .= sprintf(
          '<subitem align="center">%d</subitem>',
          (int)$month['counted']);
        $result .= '</listitem>';
      }
      $statistic .= sprintf('</values>');
      $statistic .= sprintf('</diagram>');
      $result .= '</items>';
      $result .= '</listview>';
      $this->layout->addLeft($result);
    }
  }

  /**
  * Delete all links per year
  *
  * @param integer $year
  * @access public
  */
  function resetLinksByYear($year) {
    $this->databaseDeleteRecord($this->tableLinkdbClicks, 'clicks_year', $year);
    $this->addMsg(MSG_INFO, sprintf($this->_gt('Counter reset (%s).'), $year));
  }

  /**
  * Delete links per linkid
  *
  * @param integer $linkId
  * @access public
  */
  function resetLinkCounter($linkId) {
    $this->databaseDeleteRecord(
      $this->tableLinkdbClicks, 'link_id', (string)$linkId);
    $this->loadLink($linkId);
    $this->addMsg(
      MSG_INFO, sprintf($this->_gt('Counter reset (%s).'), $this->link['link_title']));
  }

  /**
  * Delete all elements of satistic
  *
  * @access public
  */
  function resetLinksAllYears() {
    $this->databaseEmptyTable($this->tableLinkdbClicks);
    $this->addMsg(MSG_INFO, sprintf($this->_gt('Counter reset (%s).'), 'all years'));
  }

  /**
  * Check for exists elements
  *
  * @access public
  * @return boolean
  */
  function statisticExists () {

    $sql = "SELECT count(link_id) AS counted FROM %s";
    $params = array($this->tableLinkdbClicks);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow()) {
        if ((bool)$row[0] > 0 ) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
?>
