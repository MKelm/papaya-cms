<?php
/**
* Page module - Contact list
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
* @package Papaya-Modules
* @subpackage _Base-Community
* @version $Id: content_contact_list.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class page module
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_content.php');

/**
* Surfer class
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_surfer.php');

/**
* Surfer admin class
*/
require_once(dirname(__FILE__).'/base_surfers.php');

/**
* Contact manager class
*/
require_once(dirname(__FILE__).'/base_contacts.php');

/**
 * Page module - Contact list
 *
* @package Papaya-Modules
* @subpackage _Base-Community
 */
class content_contact_list extends base_content {

  /**
  * Preview allowed?
  * @var boolean
  */
  var $preview = FALSE;

  /**
  * Surfer object
  * @var base_surfer
  */
  var $surferObj = NULL;

  /**
  * Surfer admin object
  * @var surfer_admin
  */
  var $surferAdmin = NULL;

  /**
  * Surfer contact manager object
  * @var contact_manager
  */
  var $contactManager = NULL;

  /**
  * Edit fields
  * @var array
  */
  var $editFields = array(
    'Settings',
    'contacts_per_page' => array(
      'Contacts per page', 'isNum', TRUE, 'input', 30, '0 for all contacts', 10
    ),
    'detail_page' => array(
      'Detail page', 'isNum', TRUE, 'pageid', 30, '', 0
    ),
    'param_field' => array(
      'Parameter field name', 'isNoHTML', TRUE, 'input', 50,
      'Name of http request parameter for detail page', 'surfer_handle'
    ),
    'Captions',
    'caption_show_all' => array(
      'Show all', 'isNoHTML', TRUE, 'input', 50, '', 'Show all contacts'
    ),
    'caption_show_partial' => array(
      'Show partial', 'isNoHTML', TRUE, 'input', 50, '', 'Show partial list'
    ),
    'caption_backward' => array(
      'Previous', 'isNoHTML', TRUE, 'input', 50, '', 'previous'
    ),
    'caption_forward' => array(
      'Next', 'isNoHTML', TRUE, 'input', 50, '', 'next'
    )
  );

  /**
  * Database table surfer
  * @var string
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Database table surfer contacts
  * @var string
  */
  var $tableContacts = PAPAYA_DB_TBL_SURFERCONTACTS;

  /**
   * Constructor
   * @param object $owner Owner object
   * @param string $paramName Parameter group name
   */
  function __construct(&$owner, $paramName = 'cnt') {
    parent::__construct($owner, $paramName);
    // Retrieve the singleton instance surfer object
    $this->surferObj = &base_surfer::getInstance();
    // Create a surfer admin object
    $this->surferAdmin = new surfer_admin($this->msgs);
  }

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    $result = '<contacts>'.LF;
    $surferId = '';
    if (isset($this->params['surfer_handle']) &&
        trim($this->params['surfer_handle']) != '') {
      // If a surfer handle is provided as a parameter, use it
      $surferId = $this->surferAdmin->getIdByHandle($this->params['surfer_handle']);
    } elseif ($this->surferObj->isValid) {
      // Otherwise try to use the currently logged-in surfer
      $surferId = $this->surferObj->surfer['surfer_id'];
    }
    // Check whether there is a surfer
    if ($surferId) {
      // Check showall parameter
      if (isset($this->params['showall']) && $this->params['showall'] == 1) {
        $showAll = TRUE;
      } else {
        $showAll = FALSE;
      }
      // Create contact manager instance
      $contactManager = new contact_manager($surferId);
      // Get the surfer's contact array including some or all of the contacts
      if (!$showAll
          && isset($this->data['contacts_per_page'])
          && $this->data['contacts_per_page'] > 0) {
        $offset = @(int)$this->params['offset'];
        $contactNum = $contactManager->getContactNumber();
        $contacts =
          $contactManager->getContacts($offset, $this->data['contacts_per_page']);
      } else {
        $contacts = $contactManager->getContacts();
        $contactNum = 0;
      }
      foreach ($contacts as $contactId) {
        $contactHandle = $this->surferAdmin->getHandleById($contactId);
        if (isset($this->data['detail_page']) && $this->data['detail_page'] > 0) {
          $link = sprintf(
            'href="%s?%s[%s]=%s"',
            $this->getWebLink($this->data['detail_page']),
            $this->paramName,
            $this->data['param_field'],
            $contactHandle
          );
        } else {
          $link = '';
        }
        $result .= sprintf('<contact %s name="%s"/>'.LF, $link, $contactHandle);
      }
      // Show navigation if necessary
      if ($contactNum > 0) {
        $addr = $this->getLink();
        if (!$showAll) {
          $result .= '<navigation>'.LF;
          // Do we need a link back?
          if ($offset > 0) {
            $prev = $offset - $this->data['contacts_per_page'];
            if ($prev < 0) {
              $prev = 0;
            }
            $result .= sprintf(
              '<backward href="%s?%s[offset]=%s" caption="%s"/>'.LF,
              $addr,
              $this->paramName,
              $prev,
              $this->data['caption_backward']
            );
          }
          // Do we need a forward link?
          if ($offset + $this->data['contacts_per_page'] <= $contactNum) {
            $next = $offset + $this->data['contacts_per_page'];
            $result .= sprintf(
              '<forward href="%s?%s[offset]=%s" caption="%s"/>'.LF,
              $addr,
              $this->paramName,
              $next,
              $this->data['caption_forward']
            );
          }
          // Link to show all
          $result .= sprintf(
            '<showall href="%s?%s[showall]=1" caption="%s"/>'.LF,
            $addr,
            $this->paramName,
            $this->data['caption_show_all']
          );
          $result .= '</navigation>'.LF;
        }
      }
      // Link to show partial list only
      if ($showAll) {
        $addr = $this->getBaseLink();
        $result .= sprintf(
          '<showpartial href="%s" caption="%s"/>'.LF,
          $addr,
          $this->data['caption_show_partial']
        );
      }
    }
    $result .= '</contacts>'.LF;
    return $result;
  }

}

?>
