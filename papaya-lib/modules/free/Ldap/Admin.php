<?php
/**
* LDAP Administration Class
*
* This File contains the class <var>Admin.php</var>
*
* @copyright by papaya Software GmbH, Cologne, Germany - All rights reserved.
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Modules
* @subpackage Free-Ldap
* @version $Id: Admin.php 38410 2013-04-17 16:56:45Z kersken $

/**
* LDAP Administration Class
*
* This class contains a simple LDAP browser as a proof of concept for the LDAP connectivity
*
* @package Papaya-Modules
* @subpackage Free-Ldap
*/
class PapayaModuleLdapAdmin extends base_object {
  /**
  * The module's own GUID to read module options
  * @var string
  */
  private $guid = 'aebd1459c750727731222c46b9d4ac75';

  /**
  * The search dialog
  * @var base_dialog
  */
  private $dialog = NULL;

  /**
  * Execute the module's commands
  *
  * For now, there aren't any
  */
  public function execute() {
    $this->initializeParams();
  }

  /**
  * Get the module's XML output
  *
  * @param papaya_xsl $layout
  */
  public function getXml($layout) {
    $searchResult = '';
    if (isset($this->params['search']) && $this->params['search'] == 1) {
      $this->initializeDialog();
      if (is_object($this->dialog) && $this->dialog->checkDialogInput()) {
        $searchResult = $this->getSearchResultXml();
      }
    }
    $layout->add($this->getDialog());
    if ($searchResult != '') {
      $layout->add($searchResult);
    }
  }

  /**
  * Get XML output of the search dialog
  *
  * @return string XML
  */
  public function getDialog() {
    $this->initializeDialog();
    if (is_object($this->dialog)) {
      return $this->dialog->getDialogXML();
    }
  }

  /**
  * Initialized the search dialog
  *
  */
  public function initializeDialog() {
    if (!is_object($this->dialog)) {
      $fields = array(
        'LDAP Bind User',
        'ldap_user_dn' => array(
          'User DN',
          'isNoHTML',
          TRUE,
          'input',
          200
        ),
        'ldap_password' => array(
          'Password',
          'isNoHTML',
          TRUE,
          'password',
          200
        ),
        'Search Specification',
        'ldap_base_dn' => array(
          'Base DN',
          'isNoHTML',
          TRUE,
          'input',
          200
        ),
        'ldap_filter' => array(
          'Filter',
          'isNoHTML',
          TRUE,
          'input',
          200,
          '',
          '(objectClass=*)'
        ),
        'ldap_attributes' => array(
          'Attributes to find',
          'isNoHTML',
          TRUE,
          'input',
          200,
          'Separate several attributes by comma; * (default) = all attributes',
          '*'
        ),
        'ldap_attr_only' => array(
          'Attributes only',
          'isNum',
          TRUE,
          'yesno',
          NULL,
          'If you choose yes, only the attributes without values will be retrieved.',
          0
        )
      );
      $data = array();
      $hidden = array('search' => 1);
      $this->dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      if (is_object($this->dialog)) {
        $this->dialog->dialogTitle = $this->_gt('Perform LDAP Search');
        $this->dialog->buttonTitle = $this->_gt('Search');
        $this->dialog->loadParams();
      }
    }
  }

  /**
  * Get search result output
  *
  * For now, a simple HTML representation of a print_r() result is used.
  * In the long run, it should be replaced by something more reader-friendly.
  *
  * @return string XML
  */
  public function getSearchResultXml() {
    $result = '';
    $host = papaya_module_options::readOption($this->guid, 'LDAP_HOST', '');
    $port = papaya_module_options::readOption($this->guid, 'LDAP_PORT', 389);
    if ($host == '') {
      $this->addMsg(MSG_ERROR, $this->_gt('No LDAP server specified.'));
      return $result;
    }
    $wrapper = new PapayaModuleLdapWrapper($host, $port);
    if ($wrapper->bind($this->params['ldap_user_dn'], $this->params['ldap_password'])) {
      $data = $wrapper->search(
        $this->params['ldap_base_dn'],
        $this->params['ldap_filter'],
        preg_split('(,\s*)', $this->params['ldap_attributes']),
        $this->params['ldap_attr_only'] == 1 ? TRUE : FALSE
      );
      $wrapper->close();
      if (!empty($data)) {
        $result = sprintf('<panel title="%s">'.LF, $this->_gt('Search Result'));
        $result .= str_replace(
          array(' ', "\n"),
          array('&#160;', '<br />'),
          print_r($data, TRUE)
        );
        $result .= '</panel>'.LF;
      } else {
        $error = $wrapper->getLastError();
        $this->addMsg(
          MSG_ERROR,
          sprintf($this->_gt('LDAP error %d: %s'), $error[0], $error[1])
        );
      }
    } else {
      $error = $wrapper->getLastError();
      $this->addMsg(
        MSG_ERROR,
        sprintf($this->_gt('LDAP error %d: %s'), $error[0], $error[1])
      );
    }
    return $result;
  }
}