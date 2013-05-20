<?php
/**
* Administration interface for task manager
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
* @package Papaya-Modules
* @subpackage Tasks
* @version $Id: Administration.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Task manager (basic functions, shared with other module packages)
*/
require_once(dirname(__FILE__).'/Manager.php');


//require_once(dirname(__FILE__).'/Filter.php');

/**
* Administration interface for task manager
*
* @package Papaya-Modules
* @subpackage Tasks
*/
class PapayaModuleTasksAdministration extends base_object {

  /**
  * Interface module, connection with administation system
  * @var base_plugin
  */
  private $_editModule = NULL;

  /**
  * Selected item if one is selected
  * @var PapayaModuleTasksItem|NULL
  */
  private $_selectedItem = NULL;

  /**
  * List of filtered records
  * @var PapayaModuleTasksList
  */
  private $_items = NULL;

  /**
  * Tasks Manager object
  * @var PapayaModuleTasksManager
  */
  private $_tasksManager = NULL;

  /**
  * The filter object generates the conditions for database queries
  *
  * @var PapayaModuleTasksFilter
  */
  protected $_filterObject = NULL;

  /**
  * Parameter group name
  * @var string
  */
  public $paramName = 'task';
  /**
  * Request parameters group
  * @var PapayaRequestParameters
  */
  private $_parameters = NULL;

  public function __construct($editModule) {
    $this->_editModule = $editModule;
  }

  /**
  * Data needed to display the status of an item
  * @var array
  */
  private $_statusList = array(
    PapayaModuleTasksItem::TASK_CREATED => array(
      'icon' => 'items-task',
      'text' => 'Created'
    ),
    PapayaModuleTasksItem::TASK_CONFIRMED => array(
      'icon' => 'status-sign-ok',
      'text' => 'Confirmed'
    ),
    PapayaModuleTasksItem::TASK_DECLINED => array(
      'icon' => 'status-sign-problem',
      'text' => 'Declined'
    ),
  );

  /**
  * Set/get request parameters group
  *
  * If the property is not yet initialized a get will fetch it from the PapayaRequest instance
  * in the application registry
  *
  * @param PapayaRequestParameters $parameters
  */
  public function requestParameters(PapayaRequestParameters $parameters = NULL) {
    if (isset($parameters)) {
      $this->_parameters = $parameters;
    } elseif (is_null($this->_parameters)) {
      $this->_parameters = $this->papaya()->request->getParameterGroup(
        $this->paramName
      );
    }
    return $this->_parameters;
  }

  /**
  * Get the tasks manager object (implizit create)
  */
  protected function getManager() {
    if (is_null($this->_tasksManager)) {
      $this->_tasksManager = new PapayaModuleTasksManager();
    }
    return $this->_tasksManager;
  }


  /**
  * Get the tasks manager object (implizit create)
  *
  * @param PapayaModuleTasksManager $manager
  */
  protected function setManager(PapayaModuleTasksManager $manager) {
    $this->_tasksManager = $manager;
  }

  /**
  * Execute actions defined by the request parameters, load item if task_id is provided
  */
  public function execute() {
    $taskId = $this->requestParameters()->get('task_id', '');
    $command = $this->requestParameters()->get('cmd', '');
    $offset = $this->requestParameters()->get('offset', '');
    if ($offset != '') {
      $this->setOffset($offset);
    }
    switch ($command) {
    case 'search' :
      if ($this->requestParameters()->has('reset_search')) {
        $this->resetFilter();
        $this->requestParameters()->remove(
          array(
            'id-starts-with', 'status', 'data-contains', 'offset', 'time-from', 'time-to',
            'task-guid')
          );
      } else {
        $filters = array(
          'id-starts-with' => $this->requestParameters()->get('id-starts-with', NULL),
          'status' => $this->requestParameters()->get('status', NULL),
          'data-contains' => $this->requestParameters()->get('data-contains', NULL),
          'time-from' => $this->requestParameters()->get('time-from', NULL),
          'time-to' => $this->requestParameters()->get('time-to', NULL),
          'task-guid' => $this->requestParameters()->get('task-guid', NULL)
        );
        $this->setFilter($filters);
        $this->requestParameters()->remove('offset');
      }
      break;
    case 'sort' :
      $this->setOrderBy(
        $this->requestParameters()->get('sort_created', 'desc')
      );
      break;
    default :
      if (!empty($taskId)) {
        if (in_array($command, array('confirm', 'decline', 'delete')) &&
            $this->checkDialogConfirmation($command)) {
          switch ($command) {
          case 'confirm' :
            if ($this->getManager()->confirm($taskId)) {
              $this->addMsg(MSG_INFO, $this->_gt('Task confirmed.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Confirmation failed.'));
            }
            break;
          case 'decline' :
            if ($this->getManager()->decline($taskId)) {
              $this->addMsg(MSG_INFO, $this->_gt('Task declined.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Decline failed.'));
            }
            break;
          case 'delete' :
            if ($this->getManager()->delete($taskId)) {
              $this->addMsg(MSG_INFO, $this->_gt('Task deleted.'));
            } else {
              $this->addMsg(MSG_ERROR, $this->_gt('Deletion failed.'));
            }
            break;
          }
        }
      }
      $this->_selectedItem = $this->getManager()->get($taskId);
    }
  }

  /**
  * Get filter value (from session)
  *
  * @return array
  */
  public function getFilter() {
    return array(
      'id-starts-with' => $this->papaya()->session->values->get(
        array($this, 'search', 'id-starts-with'), NULL
      ),
      'status' => $this->papaya()->session->values->get(
        array($this, 'search', 'status'), NULL
      ),
      'data-contains' => $this->papaya()->session->values->get(
        array($this, 'search', 'data-contains'), NULL
      ),
      'time-from' => $this->papaya()->session->values->get(
        array($this, 'search', 'time-from'), NULL
      ),
      'time-to' => $this->papaya()->session->values->get(
        array($this, 'search', 'time-to'), NULL
      ),
      'task-guid' => $this->papaya()->session->values->get(
        array($this, 'search', 'task-guid'), NULL
      )
    );
  }

  /**
  * Removes the filter values from session
  */
  public function resetFilter() {
    $filters = $this->getFilter();
    foreach ($filters as $key => $val) {
      unset($this->papaya()->session->values[array($this, 'search', $key)]);
    }
  }

  /**
  * Store filter value in session
  *
  * If an empty value is given for a filter, the filter value will be removed.
  *
  * @param array $filters
  * @param string $filterStatus
  */
  public function setFilter($filters) {
    foreach ($filters as $key => $val) {
      if ($val === NULL || trim($val) == '' || $val === 0) {
        unset ($this->papaya()->session->values[array($this, 'search', $key)]);
      } else {
        $this->papaya()->session->values[array($this, 'search', $key)] = $val;
      }
    }
  }

  /**
  * Get the sort direction (for the created field). The default value will be descending
  * (newest first.)
  *
  * @return string
  */
  public function getOrderBy() {
    return $this->papaya()->session->values->get(
      array($this, 'sort', 'created'), 'desc'
    );
  }

  /**
  * Set the sort direction (for the created field). The default value will be descending
  * (newest first.
  *
  * @param string $direction
  */
  public function setOrderBy($direction = 'desc') {
    if ($direction == 'asc') {
      $this
        ->papaya()->session->values[array($this, 'sort', 'created')] = 'asc';
    } else {
      unset($this->papaya()->session->values[array($this, 'sort', 'created')]);
    }
  }

  /**
  * Retrieves the current paging offset of the tasks list from the session, defaults to 0.
  *
  * @return integer
  */
  public function getOffset() {
    return $this->papaya()->session->values->get(
      array($this, 'offset'), 0
    );
  }

  /**
  * Set the paging offset of the tasks list.
  *
  * @param integer $offset
  */
  public function setOffset($offset) {
    if ($offset >= 0) {
      $this->papaya()->session->values[array($this, 'offset')] = (int)$offset;
    }
  }

  /**
  * Check message dialog confirmation for the given command
  *
  * @param string $command
  */
  private function checkDialogConfirmation($command) {
    if ($this->requestParameters()->get('confirm', '') == $command) {
      $msgDialog = new base_msgdialog(
        $this, $this->paramName, array(), '', 'question'
      );
      return $msgDialog->checkDialogToken($this->requestParameters()->get('token'));
    }
    return FALSE;
  }

  /**
  * Get xml output for administration interface
  */
  public function getXml() {
    $this->_editModule->layout->setParam('COLUMNWIDTH_LEFT', '50%');
    $this->_editModule->layout->setParam('COLUMNWIDTH_RIGHT', '50%');
    $this->getXmlButtons($this->_selectedItem);
    if (isset($this->_selectedItem)) {
      $this->getMessageDialogXml(
        $this->requestParameters()->get('cmd', ''), $this->_selectedItem
      );
      $this->getXmlDetails($this->_selectedItem);
    }
    $this->getSearchDialogXml();
    $this->getXmlListView();
  }

  /**
  * Get XML output for a message dialog
  *
  * @param string $command
  * @param PapayaModuleTasksItem $item
  */
  public function getMessageDialogXml($command, PapayaModuleTasksItem $item) {
    $commands = array(
      'confirm' => array(
         'message' => 'Confirm task "%s" "%s"?',
         'button' => 'Confirm task'
      ),
      'decline' => array(
         'message' => 'Decline task "%s" "%s"?',
         'button' => 'Decline task'
      ),
      'delete' => array(
         'message' => 'Delete task "%s" "%s"?',
         'button' => 'Delete task'
      ),
    );
    if (isset($commands[$command]) &&
        !$this->requestParameters()->has('confirm')) {
      $hidden = array(
        'cmd'=> $command,
        'task_id' => $this->_selectedItem['id'],
        'confirm' => $command
      );
      $dialog = new base_msgdialog(
        $this,
        $this->paramName,
        $hidden,
        sprintf($this->_gt($commands[$command]['message']), $item['id'], $item['title']),
        'question'
      );
      $dialog->msgs = &$this->msgs;
      $dialog->buttonTitle = $commands[$command]['button'];
      $this->_editModule->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get XML output for a button toolbar (menu)
  */
  public function getXmlButtons() {
    if (isset($this->_selectedItem)) {
      $menu = new PapayaUiMenu();
      $menu->identifier = 'edit';
      if ($this->_selectedItem['status'] == PapayaModuleTasksItem::TASK_CREATED) {
        $button = new PapayaUiToolbarButton();
        $button->caption = new PapayaUiStringTranslated('Confirm task');
        $button->image = $this->_editModule->images['status-sign-ok'];
        $button->reference->setParameters(
          array(
            'cmd' => 'confirm',
            'task_id' => $this->_selectedItem['id']
          ),
          $this->paramName
        );
        $menu->elements[] = $button;
        $button = new PapayaUiToolbarButton();
        $button->caption = new PapayaUiStringTranslated('Decline task');
        $button->image = $this->_editModule->images['status-sign-problem'];
        $button->reference->setParameters(
          array(
            'cmd' => 'decline',
            'task_id' => $this->_selectedItem['id']
          ),
          $this->paramName
        );
        $menu->elements[] = $button;
      }
      $button = new PapayaUiToolbarButton();
      $button->caption = new PapayaUiStringTranslated('Delete task');
      $button->image = $this->_editModule->images['actions-task-delete'];
      $button->reference->setParameters(
        array(
          'cmd' => 'delete',
          'task_id' => $this->_selectedItem['id']
        ),
        $this->paramName
      );
      $menu->elements[] = $button;
      $this->_editModule->layout->addMenu($menu->getXml());
    }
  }

  /**
  * Get XML output for a pageable listview of tasks
  */
  public function getXmlListView() {
    $offset = $this->getOffset();
    $orderBy = $this->getOrderBy();
    $items = $this->getManager()->getList(
      30, $offset, $this->getFilter(), $orderBy
    );
    if ($items) {
      $absoluteCount = $items->countAll();
      $result = '';
      if ($absoluteCount > 0) {
        $result .= sprintf(
          '<listview title="%s [%d]">',
           PapayaUtilStringXml::escapeAttribute($this->_gt('Tasks')),
           $absoluteCount
        );
        $this->images = $this->_editModule->images;
        $result .= papaya_paging_buttons::getPagingButtons(
          $this, array(), $offset, 30, $absoluteCount
        );
        $result .= '<cols>';
        $result .= sprintf(
          '<col>%s</col>',
            PapayaUtilStringXml::escapeAttribute($this->_gt('Title'))
        );
        $result .= sprintf(
          '<col align="center" href="%s" sort="%s">%s</col>',
          PapayaUtilStringXml::escapeAttribute(
            $this->getLink(
              array(
                'cmd' => 'sort',
                'sort_created' => ($orderBy == 'asc') ? 'desc' : 'asc'
              )
            )
          ),
          ($orderBy == 'asc') ? 'asc' : 'desc',
          PapayaUtilStringXml::escapeAttribute($this->_gt('Created'))
        );
        $result .= sprintf(
          '<col align="center">%s</col>',
          PapayaUtilStringXml::escapeAttribute($this->_gt('Modifed'))
        );
        $result .= '<col/>';
        $result .= '</cols>';
        $result .= '<items>';
        foreach ($items as $item) {
          if (isset($this->_selectedItem) && $this->_selectedItem['id'] == $item['id']) {
            $selected = ' selected="selected"';
          } else {
            $selected = '';
          }
          $result .= sprintf(
            '<listitem title="%s: %s" image="%s" href="%s"%s>',
            PapayaUtilStringXml::escapeAttribute($item['id']),
            PapayaUtilStringXml::escapeAttribute($item['title']),
            PapayaUtilStringXml::escapeAttribute(
              $this->_editModule->images[$this->_statusList[$item['status']]['icon']]
            ),
            PapayaUtilStringXml::escapeAttribute(
              $this->getLink(
                array(
                  'cmd' => 'show',
                  'task_id' => $item['id']
                )
              )
            ),
            $selected
          );
          $result .= sprintf(
            '<subitem align="center">%s</subitem>',
            PapayaUtilStringXml::escapeAttribute(
              PapayaUtilDate::timestampToString($item['created'], TRUE, FALSE)
            )
          );
          $result .= sprintf(
            '<subitem align="center">%s</subitem>',
            PapayaUtilStringXml::escapeAttribute(
              PapayaUtilDate::timestampToString($item['modified'], TRUE, FALSE)
            )
          );
          $result .= '<subitem align="center">';
          if ($item['status'] == PapayaModuleTasksItem::TASK_CREATED) {
            $result .= sprintf(
              '<a href="%s"><glyph src="%s" hint="%s"/></a>',
              PapayaUtilStringXml::escapeAttribute(
                $this->getLink(
                  array(
                    'cmd' => 'confirm',
                    'task_id' => $item['id']
                  )
                )
              ),
              PapayaUtilStringXml::escapeAttribute($this->_editModule->images['status-sign-ok']),
              PapayaUtilStringXml::escapeAttribute($this->_gt('Confirm task'))
            );
            $result .= sprintf(
              '<a href="%s"><glyph src="%s" hint="%s"/></a>',
              PapayaUtilStringXml::escapeAttribute(
                $this->getLink(
                  array(
                    'cmd' => 'decline',
                    'task_id' => $item['id']
                  )
                )
              ),
              PapayaUtilStringXml::escapeAttribute(
                $this->_editModule->images['status-sign-problem']
              ),
              PapayaUtilStringXml::escapeAttribute($this->_gt('Decline task'))
            );
          } else {
            $result .= sprintf(
              '<a href="%s"><glyph src="%s" hint="%s"/></a>',
              PapayaUtilStringXml::escapeAttribute(
                $this->getLink(
                  array(
                    'cmd' => 'delete',
                    'task_id' => $item['id']
                  )
                )
              ),
              PapayaUtilStringXml::escapeAttribute(
                $this->_editModule->images['status-sign-problem']
              ),
              PapayaUtilStringXml::escapeAttribute($this->_gt('Delete task'))
            );
          }
          $result .= '</subitem>';
          $result .= '</listitem>';
        }
        $result .= '</items>';
        $result .= '</listview>';
        $this->_editModule->layout->addLeft($result);
      } else {
        $this->addMsg(
          MSG_INFO,
          $this->_gt('No tasks available.')
        );
      }
    }
  }

  /**
  * Get XML output displaying details of a task item
  *
  * @param PapayaModuleTask $item
  */
  public function getXmlDetails(PapayaModuleTask $item) {
    $result = '<sheet>';
    $result .= '<header><lines>';
    $result .= sprintf(
      '<line class="headertitle"><glyph src="%s" hint="%s"/>%s</line>',
      PapayaUtilStringXml::escapeAttribute(
        $this->_editModule->images[$this->_statusList[$item['status']]['icon']]
      ),
      PapayaUtilStringXml::escapeAttribute(
        $this->_statusList[$item['status']]['text']
      ),
      PapayaUtilStringXml::escape($item['title'])
    );
    $result .= sprintf(
      '<line class="headersubtitle">%s: %s</line>',
      PapayaUtilStringXml::escape(
        $this->_gt('Id')
      ),
      PapayaUtilStringXml::escape(
        $item['id']
      )
    );
    $result .= sprintf(
      '<line class="headersubtitle">%s: %s</line>',
      PapayaUtilStringXml::escape(
        $this->_gt('Created')
      ),
      PapayaUtilStringXml::escape(
        PapayaUtilDate::timestampToString($item['created'], TRUE, FALSE)
      )
    );
    if ($item['modified'] != $item['created']) {
      if (!empty($item['modified_by'])) {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s (%s)</line>',
          PapayaUtilStringXml::escape(
            $this->_gt('Modified')
          ),
          PapayaUtilStringXml::escape(
            PapayaUtilDate::timestampToString($item['modified'], TRUE, FALSE)
          ),
          PapayaUtilStringXml::escape($this->getUserName($item['modified_by']))
        );
      } else {
        $result .= sprintf(
          '<line class="headersubtitle">%s: %s</line>',
          PapayaUtilStringXml::escape(
            $this->_gt('Modified')
          ),
          PapayaUtilStringXml::escape(
            PapayaUtilDate::timestampToString($item['modified'], TRUE, FALSE)
          )
        );
      }
    }
    $result .= '</lines></header>';
    $result .= sprintf(
      '<text><div style="padding: 10px;"><p>%s</p></div></text>',
      PapayaUtilStringXml::escape($item['description'])
    );
    $result .= '</sheet>';
    $this->_editModule->layout->add($result);
  }

  /**
  * This method creates the search dialog and adds its XML to the layout.
  */
  public function getSearchDialogXml() {
    $statusOptions = array(
      0 => new PapayaUiStringTranslated('All')
    );
    foreach ($this->_statusList as $status => $data) {
      $statusOptions[$status] = new PapayaUiStringTranslated($data['text']);
    }
    $filter = $this->getFilter();

    $taskGuids = $this->_getFilterObject()->getModuleGuids();
    $taskGuidOptions = array(
      0 => new PapayaUiStringTranslated('All')
    );
    foreach ($taskGuids as $guid => $title) {
      $taskGuidOptions[$guid] = new PapayaUiStringTranslated($title);
    }

    $hidden = array(
      'cmd' => 'search'
    );
    $fields = array(
      'id-starts-with' => array(
        'Id', 'isSomeText', FALSE, 'input', 10, '', $filter['id-starts-with']
      ),
      'data-contains' => array(
        'Text', 'isSomeText', FALSE, 'input', 100, '', $filter['data-contains']
      ),
      'status' => array(
        'Status', 'isNum', TRUE, 'combo', $statusOptions, '', $filter['status']
      ),
      'time-from' => array(
        'From', 'isSomeText', FALSE, 'date', 10, '', $filter['time-from']
      ),
      'time-to' => array(
        'To', 'isSomeText', FALSE, 'date', 10, '', $filter['time-to']
      ),
      'task-guid' => array(
        'Type', 'isSomeText', TRUE, 'combo', $taskGuidOptions, '', $filter['task-guid']
      )
    );
    $this->_dialogSearch = new base_dialog(
      $this, $this->paramName, $fields, array(), $hidden
    );
    $this->_dialogSearch->loadParams();
    $this->_dialogSearch->dialogTitle = $this->_gt('Search');
    $this->_dialogSearch->buttonTitle = 'Search';
    $this->_dialogSearch->addButton('reset_search', 'Show all');
    $this->_editModule->layout->addLeft($this->_dialogSearch->getDialogXml());
  }

  /**
  * This method retrieves a user's display name by a surfer id
  *
  * @param string $id the surfer id
  * @return string
  */
  private function getUserName($id) {
    $user = new base_auth();
    if ($user->load($id)) {
      return $user->getDisplayName();
    }
    return '';
  }

  /**
  * Get the filter class object
  *
  * @return PapayaModuleTasksFilter
  */
  protected function _getFilterObject($filterParams = array()) {
    if (NULL === $this->_filterObject) {
      $this->_filterObject = new PapayaModuleTasksFilter($filterParams);
    }
    return $this->_filterObject;
  }

  /**
  * Set the filter class object
  *
  * @param PapayaModuleTasksFilter
  */
  protected function _setFilterObject(PapayaModuleTasksFilter $filterObject) {
    $this->_filterObject = $filterObject;
  }

}