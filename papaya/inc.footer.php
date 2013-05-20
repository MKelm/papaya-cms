<?php
/**
* Administration interface footer
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
* @package Papaya
* @subpackage Administration
* @version $Id: inc.footer.php 35924 2011-07-15 10:56:27Z weinert $
*/

$PAPAYA_LAYOUT->add($PAPAYA_MSG->get(), 'messages');
$PAPAYA_LAYOUT->setParam(
  'PAPAYA_UI_THEME', $application->options->getOption('PAPAYA_UI_THEME', 'green')
);
print $PAPAYA_LAYOUT->xhtml();

if ($application->options->getOption('PAPAYA_LOG_RUNTIME_REQUEST', FALSE)) {
  $application->database->close();
  PapayaRequestLog::getInstance()->omit();
}

?>