<?php
/**
* Actionbox - Contact list
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
* @version $Id: actbox_contact_list.php 36224 2011-09-20 08:00:57Z weinert $
*/

/**
* Basic class Action box
*/
require_once(PAPAYA_INCLUDE_PATH.'system/base_actionbox.php');

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
* Actionbox - Contact list
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class actionbox_contact_list extends base_actionbox {
  /**
  * Edit fields
  * @var array $editFields
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
  * Parameter group name
  * @var string $paramName
  */
  var $paramName = 'cnt';

  /**
  * Database table surfer
  * @var string $tableSurfer
  */
  var $tableSurfer = PAPAYA_DB_TBL_SURFER;

  /**
  * Database table surfer contacts
  * @var string $tableContact
  */
  var $tableContacts = PAPAYA_DB_TBL_SURFERCONTACTS;

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    // Retrieve the singleton instance surfer object
    $surferObj = &base_surfer::getInstance();
    // Create a surfer admin object
    $surferAdmin = new surfer_admin($this->msgs);
    $result = '<contacts>'.LF;
    $surferId = '';
    if (isset($this->params['surfer_handle']) &&
        trim($this->params['surfer_handle']) != '') {
      // If a surfer handle is provided as a parameter, use it
      $surferId = $surferAdmin->getIdByHandle($this->params['surfer_handle']);
    } elseif ($surferObj->isValid) {
      // Otherwise try to use the currently logged-in surfer
      $surferId = $surferObj->surfer['surfer_id'];
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
        $offset = empty($this->params['offset']) ? 0 : (int)$this->params['offset'];
        $contactNum = $contactManager->getContactNumber();
        $contacts = $contactManager->getContacts(
          $offset,
          $this->data['contacts_per_page']
        );
      } else {
        $contacts = $contactManager->getContacts();
        $contactNum = 0;
      }
      foreach ($contacts as $contactId) {
        $contactHandle = $surferAdmin->getHandleById($contactId);
        if (isset($this->data['detail_page']) && $this->data['detail_page'] > 0) {
          $link = sprintf(
            ' href="%s"',
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(
                $this->data['detail_page'],
                NULL,
                NULL,
                array($this->data['param_field'] => $contactHandle),
                $this->paramName
              )
            )
          );
        } else {
          $link = '';
        }
        $result .= sprintf(
          '<contact%s name="%s"/>'.LF,
          $link,
          papaya_strings::escapeHTMLChars($contactHandle)
        );
      }
      // Show navigation if necessary
      if ($contactNum > 0) {
        if (!$showAll) {
          $result .= '<navigation>'.LF;
          // Do we need a link back?
          if ($offset > 0) {
            $prev = $offset - $this->data['contacts_per_page'];
            if ($prev < 0) {
              $prev = 0;
            }
            $result .= sprintf(
              '<backward href="%s" caption="%s"/>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(NULL, NULL, NULL, array('offset' => $prev), $this->paramName)
              ),
              papaya_strings::escapeHTMLChars($this->data['caption_backward'])
            );
          }
          // Do we need a forward link?
          if ($offset + $this->data['contacts_per_page'] <= $contactNum) {
            $next = $offset + $this->data['contacts_per_page'];
            $result .= sprintf(
              '<forward href="%s" caption="%s"/>'.LF,
              papaya_strings::escapeHTMLChars(
                $this->getWebLink(NULL, NULL, NULL, array('offset' => $next), $this->paramName)
              ),
              papaya_strings::escapeHTMLChars($this->data['caption_forward'])
            );
          }
          // Link to show all
          $result .= sprintf(
            '<showall href="%s" caption="%s"/>'.LF,
            papaya_strings::escapeHTMLChars(
              $this->getWebLink(NULL, NULL, NULL, array('showall' => 1), $this->paramName)
            ),
            papaya_strings::escapeHTMLChars($this->data['caption_show_all'])
          );
          $result .= '</navigation>'.LF;
        }
      }
      // Link to show partial list only
      if ($showAll) {
        $result .= sprintf(
          '<showpartial href="%s" caption="%s"/>'.LF,
          papaya_strings::escapeHTMLChars($this->getWebLink()),
          papaya_strings::escapeHTMLChars($this->data['caption_show_partial'])
        );
      }
    }
    $result .= '</contacts>'.LF;
    return $result;
  }

}

?>
