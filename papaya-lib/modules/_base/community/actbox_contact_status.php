<?php
/**
* Actionbox - Contact status
*
* Displaying contact status and tracing
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
* @subpackage _Base-Community
* @version $Id: actbox_contact_status.php 36224 2011-09-20 08:00:57Z weinert $
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
* Actionbox - Contact status
*
* Displaying contact status and tracing
*
* @package Papaya-Modules
* @subpackage _Base-Community
*/
class actionbox_contact_status extends base_actionbox {
  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array(
    'Settings',
    'contacts_per_page' => array(
      'Contact paths per page', 'isNum', TRUE, 'input', 30, '0 for all contact paths', 10
    ),
    'detail_page' => array(
      'Detail page', 'isNum', TRUE, 'pageid', 30, '', 0
    ),
    'param_field' => array(
      'Parameter field name', 'isNoHTML', TRUE, 'input', 50,
      'Name of http request parameter for detail page', 'surfer_handle'
    ),
    'Captions',
    'caption_direct_contact' => array(
      'Direct contact', 'isNoHTML', TRUE, 'input', 50, '', 'Direct contact'
    ),
    'caption_pending_contact' => array(
      'Requested contact', 'isNoHTML', TRUE, 'input', 50, '', 'Requested contact'
    ),
    'caption_indirect_contact' => array(
      'Indirect contact', 'isNoHTML', TRUE, 'input', 50, '', 'Indirect contact(s)'
    ),
    'caption_no_contact' => array(
      'No contact', 'isNoHTML', TRUE, 'input', 50, '', 'No contact'
    ),
    'caption_show_paths' => array(
      'Show contact paths', 'isNoHTML', TRUE, 'input', 50, '', 'Show contact paths'
    ),
    'caption_show_basic' => array(
      'Show basic', 'isNoHTML', TRUE, 'input', 50, '', 'Show basic info only'
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
  * @var string $tableContacts
  */
  var $tableContacts = PAPAYA_DB_TBL_SURFERCONTACTS;

  /**
  * Get parsed data
  *
  * @access public
  * @return string $result XML
  */
  function getParsedData() {
    $result = '';
    $this->setDefaultData();
    // Retrieve the singleton instance surfer object
    $surferObject = &base_surfer::getInstance();
    // Currently logged in surfer
    $surferId = '';
    if ($surferObject->isValid) {
      $surferId = $surferObject->surfer['surfer_id'];
    }
    // Contact surfer
    $contactSurferId = '';
    if (isset($this->params['contact_handle']) &&
        trim($this->params['contact_handle'] != '')) {
      /**
      * @todo use the contact manger or a dedicated class (connector) for this
      */
      $surferAdmin = new surfer_admin($this->msgs);
      $contactSurferId = $surferAdmin->getIdByHandle(
        $this->params['contact_handle']
      );
    }
    // Only do anything if there are surfers
    if ($surferId && $contactSurferId) {
      // Create contact manager instance
      $contactManager = new contact_manager($surferId);
      $result = '<contact-status>'.LF;
      // Check showpaths parameter
      if (isset($this->params['showpaths']) && $this->params['showpaths'] == 1) {
        $showPaths = TRUE;
      } else {
        $showPaths = FALSE;
      }
      // Show simple contact info only
      $contact = $contactManager->findContact($contactSurferId);
      $indrectContact = FALSE;
      // Add text according to the "closest" available contact type
      if ($contact == SURFERCONTACT_NONE) {
        $statusCaption = $this->data['caption_no_contact'];
      } elseif ($contact & SURFERCONTACT_DIRECT) {
        $statusCaption = $this->data['caption_direct_contact'];
      } elseif ($contact & SURFERCONTACT_PENDING) {
        $statusCaption = $this->data['caption_pending_contact'];
      } elseif ($contact & SURFERCONTACT_INDIRECT) {
        $statusCaption = $this->data['caption_indirect_contact'];
        // Save this to determine whether a sample path should be shown
        $indirectContact = TRUE;
      }
      $result .= sprintf(
        '<status-info>%s</status-info>',
        papaya_strings::escapeHTMLChars($statusCaption)
      );
      // Get and display contact paths if selected
      if ($showPaths) {
        // Force to get the path list if the contact is direct
        if ($contact & SURFERCONTACT_DIRECT) {
          $contact = $contactManager->findContact($contactSurferId, TRUE);
        }
        // Display contact paths if there are any
        if ($contact > SURFERCONTACT_NONE) {
          $result .= '<contact-paths>';
          foreach ($contactManager->pathList as $contactPath) {
            $contactItemHandle = $surferAdmin->getHandleById($contactItem);
            $result .= '<contact-path>';
            foreach ($contactPath as $contactItem) {
              if (isset($this->data['detail_page']) && $this->data['detail_page'] > 0) {
                $link = sprintf(
                  'href="%s"',
                  papaya_strings::escapeHTMLChars(
                    $this->getWebLink(
                      $this->data['detail_page'],
                      NULL,
                      NULL,
                      array($this->data['param_field'] => $contactItemHandle),
                      $this->paramName
                    )
                  )
                );
              } else {
                $link = '';
              }
              $result .= sprintf(
                '<surfer %s handle="%s"/>',
                $link,
                papaya_strings::escapeHTMLChars($contactItemHandle)
              );
            }
            $result .= '</contact-path>';
          }
          $result .= '</contact-paths>';
        }
      } else {
        // Show a sample contact path in case of indirect contact
        if ($indirectContact) {
          $result .= '<contact-paths><contact-path>';
          $contactPath = $contactManager->pathList[0];
          foreach ($contactPath as $contactItem) {
            $contactItemHandle = $surferAdmin->getHandleById($contactItem);
            if (isset($this->data['detail_page']) && $this->data['detail_page'] > 0) {
              $link = sprintf(
                'href="%s"',
                papaya_strings::escapeHTMLChars(
                  $this->getWebLink(
                    $this->data['detail_page'],
                    NULL,
                    NULL,
                    array($this->data['param_field'] => $contactItemHandle),
                    $this->paramName
                  )
                )
              );
            } else {
              $link = '';
            }
            $result .= sprintf(
              '<surfer %s handle="%s"/>',
              $link,
              papaya_strings::escapeHTMLChars($contactItemHandle)
            );
          }
          $result .= '</contact-path></contact-paths>';
        }
      }
      // Navigational link
      $addr = $this->getBaseLink();
      if ($showPaths) {
        $result .= sprintf(
          '<view-link href="%s?%s[contact_handle]=%s" caption="%s"/>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              NULL,
              NILL,
              NULL,
              array('contact_handle' => $this->params['contact_handle']),
              $this->paramName
            )
          ),
          papaya_strings::escapeHTMLChars($this->data['caption_show_basic'])
        );
      } else {
        $result .= sprintf(
          '<view-link href="%s?%s[contact_handle]=%s" caption="%s"/>'.LF,
          papaya_strings::escapeHTMLChars(
            $this->getWebLink(
              NULL,
              NILL,
              NULL,
              array(
                'contact_handle' => $this->params['contact_handle'],
                'showpaths' => '1'
              ),
              $this->paramName
            )
          ),
          papaya_strings::escapeHTMLChars($this->data['caption_show_paths'])
        );
      }
      $result .= '</contact-status>'.LF;
      return $result;
    }
  }

}

?>
