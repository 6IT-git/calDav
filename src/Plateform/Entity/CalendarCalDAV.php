<?php

namespace App\Plateform\Entity;


class CalendarCalDAV extends CalDAVCalend{
	function __construct ( $url, $displayname = null, $ctag = null, $calID = null, $rbg_color = null, $order = null ) {
		parent::__construct( 
			$url, 
			$displayname, 
			$ctag, 
			$calID, 
			$rbg_color, 
			$order
		);
	}

}