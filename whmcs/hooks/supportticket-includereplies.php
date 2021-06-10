<?php

use WHMCS\Database\Capsule;

add_hook('EmailPreSend', 1, function($vars) {
    if (in_array($vars['messagename'], array('Support Ticket Reply'))) {
	$ticketid = $vars['relid'];
	$replies = Capsule::select(Capsule::raw('SELECT * FROM tblticketreplies WHERE tid = "' . $vars['relid'] . '" ORDER BY id DESC'));
	$companyname = Capsule::select(Capsule::raw('SELECT * FROM tblconfiguration WHERE setting = "CompanyName"'));
	$companyname = $companyname[0]->value;
	$r = 0;
        foreach ($replies as $v) {
		if ($r <> 0) {
			if (empty($v->admin)) {
				if (empty($v->email)) {
					$registereduser = Capsule::select(Capsule::raw('SELECT * FROM tblclients WHERE id = "' . $v->userid . '"'));
					$sender = $registereduser[0]->email;
				}
				else {
					$sender = $v->email;
				}
			}
			else {
				$sender = $v->admin.' ['.$companyname.']';
			}
			$merge_fields['support_ticket_replies'][] = 'On '.$v->date.', '.$sender.' wrote:<br/>'.nl2br($v->message);
		}
		$r = $r + 1;
        }
        return $merge_fields;
    }
});
