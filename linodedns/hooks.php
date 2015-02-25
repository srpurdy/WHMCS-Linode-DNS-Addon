<?php
/**
 * Linode DNS Manager Hook File
 *
 *
 * @package    Linode DNS Manager
 * @author     PurdyDesigns
 * @copyright  Copyright (c) PurdyDesigns 2008-2015
 * @license    
 * @version    $Id$
 * @link       http://www.purdydesigns.com/
 */

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function linodedns_clientgui($vars) 
	{
	$sql3 = "SELECT * FROM tbladdonmodules WHERE module = 'linodedns'";
	$r3 = mysql_query($sql3);
	while ($row3=mysql_fetch_assoc($r3))
		{
		if($row3['setting'] == 'option5')
			{
			$option5 = $row3['value'];
			}
		}
	if($option5 == 'on')
		{
		$client_id = $_SESSION['uid'];
		$sql = "SELECT dedicatedip,server,domainstatus FROM tblhosting WHERE userid = $client_id";
		$r = mysql_query($sql);
		while ($row=mysql_fetch_assoc($r))
			{
			if($row['domainstatus'] == 'Active' AND $row['server'] != '0')
				{
				$server_id = $row['server'];
				$sql2 = "SELECT serverid FROM mod_linodedns_servers WHERE serverid = $server_id";
				$r2 = mysql_query($sql2);
				while ($row2=mysql_fetch_assoc($r2))
					{
					$footer_return = "<div class='well textcenter'><h3>Manage DNS Zones</h3>";
					$footer_return .= "<div class='internalpadding'><a class='btn btn-default btn-lg btn-large' href='index.php?m=linodedns'>Manage DNS Zones</a></a></div></div>";
					$footer_return .= "<table class='table table-striped table-framed' width='100%'>";
					$footer_return .= "<thead></thead>";
					$footer_return .= "</table>";
					break;
					}
				}
			else
				{			
				$footer_return = '';
				}
			}
		return $footer_return;
		}
	}

function linodedns_dns_records($vars)
	{
		$sql3 = "SELECT * FROM tbladdonmodules WHERE module = 'linodedns'";
		$r3 = mysql_query($sql3);
		while ($row3=mysql_fetch_assoc($r3))
			{
			if($row3['setting'] == 'option1')
				{
				$option1 = $row3['value'];
				}
			if($row3['setting'] == 'option4')
				{
				$option4 = $row3['value'];
				}
			}
	if($vars['params']['type'] == 'hostingaccount' OR $vars['params']['type'] == 'reselleraccount')
		{
	
		require_once dirname(__FILE__) . "/linode_api/Services/Linode.php";
		$server_status = false;
		$domain = $vars['params']['domain'];
		$serverip  = $vars['params']['serverip'];
		$serverid  = $vars['params']['serverid'];
		$userid = $vars['params']['userid'];
		//$email = $vars['email'];
		$sql4 = "SELECT serverid FROM mod_linodedns_servers WHERE serverid = $serverid";
		$r4 = mysql_query($sql4);
		while ($row4=mysql_fetch_assoc($r4))
			{
			$server_status = true;
			break;
			}
		if($server_status == true)
			{
			$sql = "SELECT * FROM mod_linodedns_skeleton WHERE serverid = $serverid";
			$r = mysql_query($sql);
			// We will connect to the linode api in order to send information.
			try 
				{
				$linode = new Services_Linode($option1);
				$linode->batching = true;
				$linode->domain_create(array('Domain' => $domain, 'Type' => 'Master', 'SOA_Email' => $option4));
				$result = $linode->batchFlush();
				$domain_id = $result[0]['DATA']['DomainID'];
				while ($row=mysql_fetch_assoc($r))
					{
					if($row['type'] == 'MX')
						{
						$linode->domain_resource_create(array('DomainID' => $domain_id, 'Type' => $row['type'], 'Target' => $row['prefix_name'].'.'.$domain, 'Priority' => $row['priority']));
						}
					elseif($row['type'] == 'A')
						{
						$linode->domain_resource_create(array('DomainID' => $domain_id, 'Type' => $row['type'], 'Name' => $row['prefix_name'], 'Target' => $serverip));
						}
					elseif($row['type'] == 'TXT')
						{
						$linode->domain_resource_create(array('DomainID' => $domain_id, 'Type' => $row['type'], 'Name' => $row['prefix_name'], 'Target' => $row['address']));
						}
					elseif($row['type'] == 'CNAME')
						{
						$linode->domain_resource_create(array('DomainID' => $domain_id, 'Type' => $row['type'], 'Name' => $row['prefix_name'], 'Target' => $row['address']));
						}
					}
				$result2 = $linode->batchFlush();
				$sql5 = "INSERT INTO mod_linodedns_access (`clientid`,`domainid`) VALUES($userid,$domain_id)";
				$r5 = mysql_query($sql5);
				}

			catch (Services_Linode_Exception $e)
				{
				echo $e->getMessage();
				}
			}
		}
	}
// Define Client Login Hook Call
add_hook("ClientAreaHomepage",1,"linodedns_clientgui");
add_hook("AfterModuleCreate",1,"linodedns_dns_records");
?>