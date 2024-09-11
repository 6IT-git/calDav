<?php

namespace App\Plateform\Entity;


class CalendarCalDAV {
	private $url;
	private $displayname;
	private $ctag;
	private $calID;
	private $rgba_color;
	private $rbg_color;
	private $order;
	
	function __construct ( $url, $displayname = null, $ctag = null, $calID = null, $rbg_color = null, $order = null ) {
		$this->url = $url;
		$this->displayname = $displayname;
		$this->ctag = $ctag;
		$this->calID = $calID;
		$this->rbg_color = $rbg_color;
		$this->order = $order;
	}
	
	function __toString () {
		return( '(URL: '.$this->url.'   Ctag: '.$this->ctag.'   Displayname: '.$this->displayname .')'. "\n" );
	}
	
	// Getters
	
	function getURL () {
		return $this->url;
	}
	
	function getDisplayName () {
		return $this->displayname;
	}
	
	function getCTag () {
		return $this->ctag;
	}
	
	function getCalendarID () {
		return $this->calID;
	}
	
	function getRBGcolor () {
		return $this->rbg_color;
	}
	
	function getOrder () {
		return $this->order;
	}
	
	
	// Setters
	
	function setURL ( $url ) {
		$this->url = $url;
	}
	
	function setDisplayName ( $displayname ) {
		$this->displayname = $displayname;
	}
	
	function setCtag ( $ctag ) {
		$this->ctag = $ctag;
	}
	
	function setCalendarID ( $calID ) {
		$this->calID = $calID;
	}
	
	function setRBGcolor ( $rbg_color ) {
		$this->rbg_color = $rbg_color;
	}
	
	function setOrder ( $order ) {
		$this->order = $order;
	}
}