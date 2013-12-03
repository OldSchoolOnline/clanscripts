<?php

/*
 * Bluethrust Clan Scripts v4
 * Copyright 2012
 *
 * Author: Bluethrust Web Development
 * E-mail: support@bluethrust.com
 * Website: http://www.bluethrust.com
 *
 * License: http://www.bluethrust.com/license.php
 *
 */

include_once("../../../../_setup.php");
include_once("../../../../classes/member.php");
include_once("../../../../classes/rank.php");
include_once("../../../../classes/consoleoption.php");
include_once("../../../../classes/medal.php");


$member = new Member($mysqli);
$member->select($_SESSION['btUsername']);
$medalObj = new Medal($mysqli);

$consoleObj = new ConsoleOption($mysqli);

$cID = $consoleObj->findConsoleIDByName("Manage Medals");
$consoleObj->select($cID);
$_GET['cID'] = $cID;


if($member->authorizeLogin($_SESSION['btPassword'])) {


	$memberInfo = $member->get_info_filtered();

	if($member->hasAccess($consoleObj) && $medalObj->select($_POST['mID'])) {
		
		define("MEMBERRANK_ID", $memberInfo['rank_id']);
		
		
		if($_POST['confirm'] == 1) {
			$medalObj->delete();
			include("main.php");
		}
		else {
			$medalName = $medalObj->get_info_filtered("name");
			echo "<p align='center'>Are you sure you want to delete the medal <b>".$medalName."</b>?</p>";
		}
		
	}
	elseif(!$medalObj->select($_POST['mID'])) {
		
		echo "<p align='center'>Unable find the selected medal.  Please try again or contact the website administrator.</p>";
		
	}
	
}



?>