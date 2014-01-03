<?php

/*
 * Bluethrust Clan Scripts v4
 * Copyright 2014
 *
 * Author: Bluethrust Web Development
 * E-mail: support@bluethrust.com
 * Website: http://www.bluethrust.com
 *
 * License: http://www.bluethrust.com/license.php
 *
 */

if(!isset($member) || substr($_SERVER['PHP_SELF'], -11) != "console.php") {


	include_once("../../../../_setup.php");
	include_once("../../../../classes/member.php");
	include_once("../../../../classes/rank.php");
	include_once("../../../../classes/btplugin.php");
	
	$consoleObj = new ConsoleOption($mysqli);
	
	$cID = $consoleObj->findConsoleIDByName("Plugin Manager");
	$consoleObj->select($cID);
	
	$member = new Member($mysqli);
	$member->select($_SESSION['btUsername']);
	
	if(!$member->authorizeLogin($_SESSION['btPassword']) || !$member->hasAccess($consoleObj)) {
		exit();
	}
	
	$pluginObj = new btPlugin($mysqli);
	
}

echo "<table class='formTable' style='margin-top: 0px; border-spacing: 0px'>";

	$result = $mysqli->query("SELECT * FROM ".$dbprefix."plugins ORDER BY name");
	
	if($result->num_rows == 0) {
		
		echo "
			<tr>
				<td colspan='2'>
					<div class='shadedBox' style='width: 50%; margin: 20px auto'>
						<p class='main' align='center'>
							There are no plugins installed.
						</p>
					</div>
				</td>
			</tr>
		";
		
	}

	$x = 0;
	while($row = $result->fetch_assoc()) {
		
		if($x == 0) {
			$x = 1;
			$addCSS = "";	
		}
		else {
			$x = 0;
			$addCSS = " alternateBGColor";	
		}
		
		$arrInstalledPlugins[] = $row['filepath'];
		
		$dispPluginName = filterText($row['name']);
		echo "
			<tr>
				<td class='dottedLine main manageList".$addCSS."'>".$dispPluginName."</td>
				<td align='center' class='dottedLine main manageList".$addCSS."' style='width: 12%'><a href='".$MAIN_ROOT."plugins/".$row['filepath']."/settings.php'><img src='".$MAIN_ROOT."themes/".$THEME."/images/buttons/edit.png' class='manageListActionButton' title='Settings'></a></td>
				<td align='center' class='dottedLine main manageList".$addCSS."' style='width: 12%'><a id='uninstallPlugin' style='cursor: pointer' data-plugin='".$row['filepath']."' data-clicked='0' data-pluginname='".$dispPluginName."'><img src='".$MAIN_ROOT."themes/".$THEME."/images/buttons/delete.png' class='manageListActionButton' title='Uninstall'></a></td>
			</tr>		
		";
		
	}

	echo "</table>
	<div id='uninstallMessage' style='display: none'></div>
	<div id='confirmDelete' style='display: none'></div>
	<script type='text/javascript'>
	
		$(document).ready(function() {
		
			$(\"a[id='uninstallPlugin']\").click(function() {
				
				if($(this).attr('data-clicked') == 0) {
					$(this).attr('data-clicked', 1);
					var thisLink = $(this);	
					$('#confirmDelete').html(\"<p class='main' align='center'>Are you sure you want to delete the plugin: \"+$(this).attr('data-pluginname')+\"?\");
					
					$('#confirmDelete').dialog({
					
						title: 'Plugin Manager',
						zIndex: 9999,
						show: 'scale',
						modal: true,
						width: 450,
						resizable: false,
						buttons: {
							'Yes': function() {
								thisLink.attr('data-clicked', 2);
								thisLink.click();
								$(this).dialog('close');
							},
							'Cancel': function() {
								$(this).dialog('close');
							}
						}
					
					});
				
				}
				else if($(this).attr('data-clicked') == 2) {
					$(this).html(\"<img src='".$MAIN_ROOT."themes/".$THEME."/images/loading-spiral.gif' class='manageListActionButton'>\");
					$(this).attr('data-clicked', 1);
					$(this).css('cursor', 'default');
					
					$.post('".$MAIN_ROOT."plugins/'+$(this).attr('data-plugin')+'/uninstall.php', { pluginDir: $(this).attr('data-plugin') }, function(data) {
					
						postResult = JSON.parse(data);
						
						if(postResult['result'] == 'success') {
							
							$('#uninstallMessage').html(\"<p class='main' align='center'>Successfully uninstalled plugin!</p>\");
							
							
						}
						else {
						
							
							var strErrorHTML = \"<ul>\";
							
							for(var x in postResult['errors']) {
							
								strErrorHTML += \"<li class='main'>\"+postResult['errors'][x]+\"</li>\";
															
							}

							strErrorHTML += \"</ul>\";
							
							$('#uninstallMessage').html(\"<p class='main'>Unable to uninstall plugin because the following errors occurred:<br>\"+strErrorHTML+\"</p>\");
							
						
						}
						
						$('#uninstallMessage').dialog({
						
							title: 'Plugin Manager',
							zIndex: 9999,
							show: 'scale',
							modal: true,
							width: 450,
							resizable: false,
							buttons: {
								'Ok': function() {
									$(this).dialog('close');
								}								
							}
						
						});
					
					
					
						reloadPluginLists();
					
					});
				
				}
			
			});
		
		});
	
	</script>
	";
?>