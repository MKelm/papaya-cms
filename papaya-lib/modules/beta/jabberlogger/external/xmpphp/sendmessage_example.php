<?php

// activate full error reporting
//error_reporting(E_ALL & E_STRICT);

include 'XMPPHP/XMPP.php';

#Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
#If this doesn't work, are you running 64-bit PHP with < 5.2.6?
$conn = new XMPPHP_XMPP('jabber.papaya-cms.com', 5223, 'papaya5dev', 'pass1234', 'Papaya/', 'jabber.papaya-cms.com', $printlog=TRUE, $loglevel=XMPPHP_Log::LEVEL_INFO);

try {
    $conn->useEncryption(TRUE);
    $conn->useSSL(TRUE);
    $conn->connect();
    $conn->processUntil('session_start');
    $conn->presence();
    $conn->message('elbrecht@jabber.papaya-cms.com', 'This is a test message!');
    $conn->disconnect();
} catch(XMPPHP_Exception $e) {
    die($e->getMessage());
}
