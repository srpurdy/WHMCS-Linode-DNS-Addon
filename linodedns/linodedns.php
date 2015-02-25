<?php
/**
 * Linode DNS
 *
 * This is a DNS Manager Add-On using the Linode API
 * 
 *
 * @package    LinodeDNS
 * @author     PurdyDesigns
 * @copyright  Copyright (c) PurdyDesigns 2008-2015
 * @license    
 * @version    $Id$
 * @link       http://www.purdydesigns.com/
 */

if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

function linodedns_config() {
    $configarray = array(
    "name" => "Linode DNS Manager",
    "description" => "Linode API DNS Manager. For client control of DNS records and Domains, and automation of DNS Entries.",
    "version" => "1.1",
    "author" => "PurdyDesigns",
    "language" => "english",
    "fields" => array(
        "option1" => array ("FriendlyName" => "Linode API Key", "Type" => "text", "Size" => "25", "Description" => "Enter your linode api key. You can get your api key from your account at linode.", "Default" => "API KEY", ),
		"option3" => array ("FriendlyName" => "Enable SSL", "Type" => "yesno", "Size" => "25", "Description" => "To force ssl on client area. If your using forced ssl in whmcs you should set to true. Otherwise set to false", "Default" => "", ),
		"option4" => array ("FriendlyName" => "SOA Email", "Type" => "text", "Size" => "25", "Description" => "Enter your email address for zone creation (required by linode api)", "Default" => "email address", ),
		"option5" => array ("FriendlyName" => "Homepage Display", "Type" => "yesno", "Size" => "25", "Description" => "To Enable Client Area Homepage Access (you will need to create your own access point if you disable)", "Default" => "", ),
    ));
    return $configarray;
}

function linodedns_activate() {

    # Create Custom DB Table
    $query = "CREATE TABLE `mod_linodedns_access` (`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`clientid` INT( 11 ) NOT NULL, `domainid` INT( 11 ) NOT NULL )";
    $result = full_query($query);
	
	$query = "CREATE TABLE `mod_linodedns_servers` (`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,`serverid` INT( 11 ) NOT NULL)";
    $result = full_query($query);
	
	$query = "CREATE TABLE `mod_linodedns_skeleton` (`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY, `serverid` int( 11 ) NOT NULL ,`type` enum('MX','A','CNAME','TXT') NOT NULL, `prefix_name` varchar( 100 ) NOT NULL ,`address` varchar( 100 ) NOT NULL,`priority` int( 11 ) NOT NULL,`ttl` INT( 11 ) NOT NULL)";
    $result = full_query($query);
	
	$sql22 = "SELECT id FROM tblservers";
	$r22 = mysql_query($sql22);
	while ($row22=mysql_fetch_assoc($r22))
		{
		$sid = $row22['id'];
		$query2 = "INSERT INTO `mod_linodedns_skeleton` (`serverid`, `type`, `prefix_name`, `address`, `priority`, `ttl`) VALUES($sid, 'MX', 'mail', '', 10, 3600),($sid, 'A', '', '', 0, 3600),($sid, 'A', 'www', '', 0, 3600),($sid, 'A', '*', '', 0, 3600),($sid, 'A', 'mail', '', 0, 3600)";
		$result = full_query($query2);
		}

    # Return Result
    return array('status'=>'success','description'=>'Module has been activated successfully. Select your servers you wish to use the linode DNS manager. For pre-existing clients you will need to manually provide access to domains.');
    return array('status'=>'error','description'=>'Error installing module.');
    return array('status'=>'info','description'=>'');

}

function linodedns_deactivate() {

    # Remove Custom DB Table
    $query = "DROP TABLE `mod_linodedns_access`";
    $result = full_query($query);
	
	$query = "DROP TABLE `mod_linodedns_servers`";
    $result = full_query($query);
	
	$query = "DROP TABLE `mod_linodedns_skeleton`";
    $result = full_query($query);

    # Return Result
    return array('status'=>'success','description'=>'If successful, you can return a message to show the user here');
    return array('status'=>'error','description'=>'If an error occurs you can return an error message for display here');
    return array('status'=>'info','description'=>'If you want to give an info message to a user you can return it here');

}

function linodedns_upgrade($vars) {

    $version = $vars['version'];
/*
    # Run SQL Updates for V1.0 to V1.1
    if ($version < 1.1) {
        $query = "ALTER `mod_addonexample` ADD `demo2` TEXT NOT NULL ";
        $result = full_query($query);
    }

    # Run SQL Updates for V1.1 to V1.2
    if ($version < 1.2) {
        $query = "ALTER `mod_addonexample` ADD `demo3` TEXT NOT NULL ";
        $result = full_query($query);
    }
*/
}

function linodedns_output($vars) {
    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
	$option3 = $vars['option3'];
	$option4 = $vars['option4'];
    $LANG = $vars['_lang'];
	
	require_once dirname(__FILE__) . "/linode_api/Services/Linode.php";
	
    if (!empty($_REQUEST['action'])){
        $action = $_REQUEST['action'];
    }else{
        $action = 'default';
    }
	if ('servers'==$action){
		$sql = "SELECT id,name FROM tblservers ORDER BY id ASC";
        $r = mysql_query($sql);
	}
	
	if ('link_accounts'==$action){
	$linode = new Services_Linode($option1);
	$linode->batching = true;
	$linode->domain_list();
	$api_r = $linode->batchFlush();
	for($i = 0; $i<=count($api_r[0]['DATA']);$i++)
		{
		$domain = $api_r[0]['DATA'][$i]['DOMAIN'];
		//echo $api_r[0]['DATA'][$i]['DOMAIN'];
		$sql = "SELECT userid,domain FROM tblhosting WHERE domain = '$domain'";
        $r = mysql_query($sql);
		while ($row=mysql_fetch_assoc($r))
			{
			//echo "test";
			$clientid = $row['userid'];
			$domain_id = $api_r[0]['DATA'][$i]['DOMAINID'];
			$sql3 = "SELECT * from mod_linodedns_access WHERE clientid = $clientid AND domainid = $domain_id";
			$r3 = mysql_query($sql3);
			if(mysql_fetch_assoc($r3))
				{
				while ($row3=mysql_fetch_assoc($r3))
					{
					$sql2 = "INSERT INTO mod_linodedns_access (`clientid`,`domainid`) VALUES($clientid,$domain_id)";
					$r2 = mysql_query($sql2);
					}
				}
			else
				{
				$sql2 = "INSERT INTO mod_linodedns_access (`clientid`,`domainid`) VALUES($clientid,$domain_id)";
				$r2 = mysql_query($sql2);
				}
			}
		}
	}
	
	if ('servers_submit'==$action)
		{
		$sql = "TRUNCATE TABLE mod_linodedns_servers";
		$r = mysql_query($sql);
		if (isset($_POST['server_id'])) {
		$server_ids = $_POST['server_id'];
		}
		//print_r($server_ids);
		for($i = 0; $i <= count($server_ids); $i++)
			{
			$sql2 = "INSERT INTO mod_linodedns_servers (`serverid`) VALUES($server_ids[$i])";
			$r2 = mysql_query($sql2);
			}
		}
	
	if ('dnsskeleton'==$action){
		$sql = "SELECT * FROM mod_linodedns_skeleton";
        $r = mysql_query($sql);
	}
	
	if ('skeleton_add'==$action){
	}
	
	if ('submit_skeleton_add'==$action){
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$serverid = $_POST['serverid'];
		$type = $_POST['type'];
		$prefix_name = $_POST['prefix_name'];
		$address = $_POST['address'];
		$priority = $_POST['priority'];
		$ttl = $_POST['ttl'];
		//print_r($_POST);
		$sql2 = "INSERT INTO mod_linodedns_skeleton (`serverid`,`prefix_name`,`address`,`priority`,`ttl`,`type`) VALUES($serverid,'$prefix_name','$address',$priority,$ttl,$type)";
		$r2 = mysql_query($sql2);
	}
	
	if ('skeleton_edit'==$action){
	if($_REQUEST['rid'] == '')
			{
			}
		else
			{
			$rid = $_REQUEST['rid'];
			$sql = "SELECT * FROM mod_linodedns_skeleton WHERE id = $rid";
			$r = mysql_query($sql);
			}
	}
	
	if ('submit_skeleton_edit'==$action){
	if($_REQUEST['rid'] == '')
			{
			}
		else
			{
			$rid = $_REQUEST['rid'];
			$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
			$serverid = $_POST['serverid'];
			$type = $_POST['type'];
			$prefix_name = $_POST['prefix_name'];
			$address = $_POST['address'];
			$priority = $_POST['priority'];
			$ttl = $_POST['ttl'];
			//print_r($_POST);
			$sql2 = "UPDATE mod_linodedns_skeleton SET serverid = $serverid, type = $type, prefix_name = '$prefix_name', address = '$address', priority = $priority, ttl = $ttl WHERE id = $rid";
			$r2 = mysql_query($sql2);
			}
	}
	
	if ('skeleton_delete'==$action){
		if($_REQUEST['rid'] == '')
			{
			}
		else
			{
			$rid = $_REQUEST['rid'];
			$sql = "DELETE FROM mod_linodedns_skeleton WHERE id = $rid";
			$r = mysql_query($sql);
			}
	}
	
	if ('clientaccess'==$action){
		$sql = "SELECT id,firstname,lastname FROM tblclients ORDER BY firstname ASC";
        $r = mysql_query($sql);
		$sql2 = "SELECT id,firstname,lastname FROM tblclients ORDER BY firstname ASC";
        $r2 = mysql_query($sql2);
		try 
			{
			$linode = new Services_Linode($option1);
			$linode->batching = true;
			$linode->domain_list();
			$api_r = $linode->batchFlush();
			}
		catch (Services_Linode_Exception $e)
			{
			echo $e->getMessage();
			}
	}
	
	if ('client_add_submit'==$action){
		$clientid = $_POST['clientid_add'];
		$domainid = $_POST['domainid_add'];
		$exists = false;
		$sql = "SELECT id FROM mod_linodedns_access WHERE clientid = $clientid AND domainid = $domainid";
        $r = mysql_query($sql);
		while ($row=mysql_fetch_assoc($r))
			{
			$exists = true;
			}
		if($exists == false)
			{
			$sql2 = "INSERT INTO mod_linodedns_access (`clientid`,`domainid`) VALUES($clientid,$domainid)";
			$r2 = mysql_query($sql2);
			}
	}
	
	if ('client_remove_submit'==$action){
		$clientid = $_POST['clientid_remove'];
		$domainid = $_POST['domainid_remove'];
		$sql = "DELETE FROM mod_linodedns_access WHERE clientid = $clientid AND domainid = $domainid";
		$r = mysql_query($sql);
	}

    echo '<p>'.$LANG['intro'].'</p>
<p>'.$LANG['description'].'</p>
<p>'.$LANG['documentation'].'</p>';

	$view['global']['mod_action_url'] = $view['global']['mod_url'] . '&action=' . $action;
    $view['global']['action'] = $action;

    include dirname(__FILE__) . '/templates/' . $action . '.php';
    
}

function linodedns_sidebar($vars) {

    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    $option1 = $vars['option1'];
	$option3 = $vars['option3'];
	$option4 = $vars['option4'];
    $LANG = $vars['_lang'];

    $sidebar = '<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16" /> Linode DNS</span>
<ul class="menu">
        <li><a href="#">Version: '.$version.'</a></li>
    </ul>';
    return $sidebar;

}

function linodedns_clientarea($vars)
	{
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
    $option1 = $vars['option1'];
	$option3 = $vars['option3'];
	$option4 = $vars['option4'];
	$option5 = $vars['option5'];
	
	if($option3 == 'on')
		{
		$ssl = true;
		}
	elseif($option3 == '')
		{
		
		$ssl = false;
		}
	
	require_once dirname(__FILE__) . "/linode_api/Services/Linode.php";
	
	if (!empty($_REQUEST['action'])){
        $action = $_REQUEST['action'];
    }else{
        $action = 'default';
    }
	if ('edit_zone'==$action)
		{
		$client_id = $_SESSION['uid'];
		
		if(!empty($_REQUEST['did']))
			{
			$did = $_REQUEST['did'];
			}
		else
			{
			$did = '';
			}
		if($did != '')
			{
			$sql = "SELECT clientid,domainid FROM mod_linodedns_access WHERE domainid = $did";
			$r = mysql_query($sql);
			while ($row=mysql_fetch_assoc($r))
				{
				$cid = $row['clientid'];
				}
			if($cid == $client_id)
				{
				$linode = new Services_Linode($option1);
				$linode->batching = true;
				$linode->domain_resource_list(array('DomainID' => $did));
				$api_r = $linode->batchFlush();
				return array
					(
					'pagetitle' => 'DNS Management',
					'templatefile' => 'client_templates/edit_zone',
					'requirelogin' => true,
					'forcessl' => $ssl,
					'vars' => array
						(
						'api_result' => $api_r[0]['DATA'],
						'domain_id' => $api_r[0]['DATA'][0]['DOMAINID']
						)
					);
				}
			else
				{
				return array
					(
					'pagetitle' => 'DNS Management',
					'templatefile' => 'client_templates/access_error',
					'requirelogin' => true,
					'forcessl' => $ssl,
					);
				}
			}
		}
	
	if ('add_record'==$action)
		{
		$client_id = $_SESSION['uid'];
		
		if(!empty($_REQUEST['did']))
			{
			$did = $_REQUEST['did'];
			}
		else
			{
			$did = '';
			}
		if($did != '')
			{
			$sql = "SELECT clientid,domainid FROM mod_linodedns_access WHERE domainid = $did";
			$r = mysql_query($sql);
			while ($row=mysql_fetch_assoc($r))
				{
				$cid = $row['clientid'];
				}
			if($cid == $client_id)
				{
				return array
					(
					'pagetitle' => 'DNS Management',
					'templatefile' => 'client_templates/add_record',
					'requirelogin' => true,
					'forcessl' => $ssl,
					'vars' => array
						(
						'did' => $did,
						)
					);
				}
			else
				{
				return array
					(
					'pagetitle' => 'DNS Management',
					'templatefile' => 'client_templates/access_error',
					'requirelogin' => true,
					'forcessl' => $ssl,
					);
				}
			}
		}
	if ('submit_add_record'==$action)
		{
		$client_id = $_SESSION['uid'];
		
		if(!empty($_REQUEST['did']))
			{
			$did = $_REQUEST['did'];
			}
		else
			{
			$did = '';
			}
		if($did != '')
			{
			$sql = "SELECT clientid,domainid FROM mod_linodedns_access WHERE domainid = $did";
			$r = mysql_query($sql);
			while ($row=mysql_fetch_assoc($r))
				{
				$cid = $row['clientid'];
				}
			if($cid == $client_id)
				{
				$linode = new Services_Linode($option1);
				$linode->batching = true;
				if($_POST['type'] == 'MX')
						{
						$linode->domain_resource_create(array('DomainID' => $did, 'Type' => $_POST['type'], 'Target' => $_POST['prefix_name'], 'Priority' => $_POST['priority'], 'TTL_sec' => $_POST['ttl']));
						}
					elseif($_POST['type'] == 'A')
						{
						$linode->domain_resource_create(array('DomainID' => $did, 'Type' => $_POST['type'], 'Name' => $_POST['prefix_name'], 'Target' => $_POST['address'], 'TTL_sec' => $_POST['ttl']));
						}
					elseif($_POST['type'] == 'TXT')
						{
						$linode->domain_resource_create(array('DomainID' => $did, 'Type' => $_POST['type'], 'Name' => $_POST['prefix_name'], 'Target' => $_POST['address'], 'TTL_sec' => $_POST['ttl']));
						}
					elseif($_POST['type'] == 'CNAME')
						{
						$linode->domain_resource_create(array('DomainID' => $did, 'Type' => $_POST['type'], 'Name' => $_POST['prefix_name'], 'Target' => $_POST['address'], 'TTL_sec' => $_POST['ttl']));
						}
				$api_r = $linode->batchFlush();
				$url='index.php?m=linodedns&action=edit_zone&did='.$did;
				header("Location: ".$url); 
				}
			}
		}
	
	if ('edit_record'==$action)
		{
		$client_id = $_SESSION['uid'];
		
		if(!empty($_REQUEST['did']))
			{
			$did = $_REQUEST['did'];
			}
		else
			{
			$did = '';
			}
		if($did != '')
			{
			$sql = "SELECT clientid,domainid FROM mod_linodedns_access WHERE domainid = $did";
			$r = mysql_query($sql);
			while ($row=mysql_fetch_assoc($r))
				{
				$cid = $row['clientid'];
				}
			if($cid == $client_id)
				{
				if(!empty($_REQUEST['drid']))
					{
					$drid = $_REQUEST['drid'];
					$linode = new Services_Linode($option1);
					$linode->batching = true;
					$linode->domain_resource_list(array('DomainID' => $did, 'ResourceID' => $drid));
					$api_r = $linode->batchFlush();
					return array
						(
						'pagetitle' => 'DNS Management',
						'templatefile' => 'client_templates/edit_record',
						'requirelogin' => true,
						'forcessl' => $ssl,
						'vars' => array
							(
							'did' => $did,
							'drid' => $drid,
							'api_result' => $api_r[0]['DATA']
							)
						);
				}
			else
				{
				return array
					(
					'pagetitle' => 'DNS Management',
					'templatefile' => 'client_templates/access_error',
					'requirelogin' => true,
					'forcessl' => $ssl,
					);
				}
			}
		}
	}
	
	if ('submit_edit_record'==$action)
		{
		$client_id = $_SESSION['uid'];
		
		if(!empty($_REQUEST['did']))
			{
			$did = $_REQUEST['did'];
			}
		else
			{
			$did = '';
			}
		if($did != '')
			{
			$sql = "SELECT clientid,domainid FROM mod_linodedns_access WHERE domainid = $did";
			$r = mysql_query($sql);
			while ($row=mysql_fetch_assoc($r))
				{
				$cid = $row['clientid'];
				}
			if($cid == $client_id)
				{
				if(!empty($_REQUEST['drid']))
					{
					$drid = $_REQUEST['drid'];
					$linode = new Services_Linode($option1);
					$linode->batching = true;
					if($_POST['type'] == 'MX')
							{
							$linode->domain_resource_update(array('DomainID' => $did, 'ResourceID' => $drid, 'Type' => $_POST['type'], 'Target' => $_POST['prefix_name'], 'Priority' => $_POST['priority'], 'TTL_sec' => $_POST['ttl']));
							}
						elseif($_POST['type'] == 'A')
							{
							$linode->domain_resource_update(array('DomainID' => $did, 'ResourceID' => $drid, 'Type' => $_POST['type'], 'Name' => $_POST['prefix_name'], 'Target' => $_POST['address'], 'TTL_sec' => $_POST['ttl']));
							}
						elseif($_POST['type'] == 'TXT')
							{
							$linode->domain_resource_update(array('DomainID' => $did, 'ResourceID' => $drid, 'Type' => $_POST['type'], 'Name' => $_POST['prefix_name'], 'Target' => $_POST['address'], 'TTL_sec' => $_POST['ttl']));
							}
						elseif($_POST['type'] == 'CNAME')
							{
							$linode->domain_resource_update(array('DomainID' => $did, 'ResourceID' => $drid, 'Type' => $_POST['type'], 'Name' => $_POST['prefix_name'], 'Target' => $_POST['address'], 'TTL_sec' => $_POST['ttl']));
							}
					$api_r = $linode->batchFlush();
					$url='index.php?m=linodedns&action=edit_zone&did='.$did;
					header("Location: ".$url); 
					}
				}
			}
		}
	
	if ('delete_record'==$action)
		{
		$client_id = $_SESSION['uid'];
		
		if(!empty($_REQUEST['did']))
			{
			$did = $_REQUEST['did'];
			}
		else
			{
			$did = '';
			}
		if($did != '')
			{
			$sql = "SELECT clientid,domainid FROM mod_linodedns_access WHERE domainid = $did";
			$r = mysql_query($sql);
			while ($row=mysql_fetch_assoc($r))
				{
				$cid = $row['clientid'];
				}
			if($cid == $client_id)
				{
				if(!empty($_REQUEST['drid']))
					{
					$drid = $_REQUEST['drid'];
					$linode = new Services_Linode($option1);
					$linode->batching = true;
					$linode->domain_resource_delete(array('DomainID' => $did, 'ResourceID' => $drid));
					$api_r = $linode->batchFlush();
					$url='index.php?m=linodedns&action=edit_zone&did='.$did;
					header("Location: ".$url); 
					}
					
				}
			else
				{
				return array
					(
					'pagetitle' => 'DNS Management',
					'templatefile' => 'client_templates/access_error',
					'requirelogin' => true,
					'forcessl' => $ssl,
					);
				}
			}
		}
	
	if ('add_zone'==$action)
		{
		$server_status = false;
		$client_id = $_SESSION['uid'];
		$ip_array = array();
		$active = 'Active';
		$sql = "SELECT dedicatedip,server,domainstatus FROM tblhosting WHERE userid = $client_id";
		$r = mysql_query($sql);
		while ($row=mysql_fetch_assoc($r))
			{
			if($row['domainstatus'] == 'Active' AND $row['server'] != '0')
				{
				if($row['dedicatedip'] == '')
					{
					}
				else
					{
					array_push($ip_array, $row['dedicatedip']);
					}
				$server_id = $row['server'];
				$sql2 = "SELECT ipaddress FROM tblservers WHERE id = $server_id";
				$r2 = mysql_query($sql2);
				while ($row2=mysql_fetch_assoc($r2))
					{
					if(in_array($row2['ipaddress'], $ip_array))
						{
						}
					else
						{
						$server_id = $row['server'];
						$sql4 = "SELECT serverid FROM mod_linodedns_servers WHERE serverid = $server_id";
						$r4 = mysql_query($sql4);
						while ($row4=mysql_fetch_assoc($r4))
							{
							array_push($ip_array, $row2['ipaddress']);
							}
						}
					}
				
				$server_id = $row['server'];
				$sql3 = "SELECT serverid FROM mod_linodedns_servers WHERE serverid = $server_id";
				$r3 = mysql_query($sql3);
				while ($row3=mysql_fetch_assoc($r3))
					{
					$server_status = true;
					break;
					}
				}
			else
				{
				}
			}
			if($server_status == true)
				{
				return array
					(
					'pagetitle' => 'DNS Management',
					'templatefile' => 'client_templates/add_zone',
					'requirelogin' => true,
					'forcessl' => $ssl,
					'vars' => array
						(
						'ips' => $ip_array,
						)
					);
					}
		}
	
	if ('submit_add_zone'==$action)
		{
		if($_POST['domain'] == '')
			{
			}
		else
			{
			if($_POST['ip_address'] == '')
				{
				}
			else
				{
				$client_id = $_SESSION['uid'];
				$cur_ip = $_POST['ip_address'];
				
				//check for dedicated ip address
				$sql4 = "SELECT server FROM tblhosting WHERE userid = $client_id AND dedicatedip = '$cur_ip'";
				$r4 = mysql_query($sql4);
				while ($row4=mysql_fetch_assoc($r4))
					{
					$sid = $row4['server'];
					}
					
				//check for dedicated ip address
				$sql2 = "SELECT id FROM tblservers WHERE ipaddress = '$cur_ip'";
				$r2 = mysql_query($sql2);
				while ($row2=mysql_fetch_assoc($r2))
					{
					$sid = $row2['id'];
					}
				//echo $sid;
				$sql = "SELECT * FROM mod_linodedns_skeleton WHERE serverid = '$sid'";
				$r = mysql_query($sql);
				$linode = new Services_Linode($option1);
				$linode->batching = true;
				$linode->domain_create(array('Domain' => $_POST['domain'], 'Type' => 'Master', 'SOA_Email' => $option4));
				$api_r = $linode->batchFlush();
				$domain_id = $api_r[0]['DATA']['DomainID'];
				while ($row=mysql_fetch_assoc($r))
					{
					if($row['type'] == 'MX')
						{
						$linode->domain_resource_create(array('DomainID' => $domain_id, 'Type' => $row['type'], 'Target' => $row['prefix_name'].'.'.$_POST['domain'], 'Priority' => $row['priority']));
						}
					elseif($row['type'] == 'A')
						{
						$linode->domain_resource_create(array('DomainID' => $domain_id, 'Type' => $row['type'], 'Name' => $row['prefix_name'], 'Target' => $_POST['ip_address']));
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
				$sql5 = "INSERT INTO mod_linodedns_access (`clientid`,`domainid`) VALUES($client_id,$domain_id)";
				$r5 = mysql_query($sql5);
				//print_r($api_r);
				}
			}
		}
		
	//We need to determine which domains this user has access too
	$client_id = $_SESSION['uid'];
	$sql = "SELECT clientid,domainid FROM mod_linodedns_access WHERE clientid = $client_id";
	$r = mysql_query($sql);
	$domain_ids = array();
	$domains = array();
	while ($row=mysql_fetch_assoc($r))
		{
		array_push($domain_ids, $row['domainid']);
		}

	for($i = 0; $i <= count($domain_ids) -1; $i++)
		{
		$linode = new Services_Linode($option1);
		$linode->batching = true;
		$linode->domain_list(array('DomainID' => $domain_ids[$i]));
		$api_r = $linode->batchFlush();
		//print_r($api_r);
		array_push($domains, $api_r[0]['DATA'][0]['DOMAIN']);
		}
	//print_r($domain_ids);
	$server_status = false;
	$sql = "SELECT dedicatedip,server,domainstatus FROM tblhosting WHERE userid = $client_id";
	$r = mysql_query($sql);
	while ($row=mysql_fetch_assoc($r))
		{
		if($row['domainstatus'] == 'Active' AND $row['server'] != '0')
			{
			$server_id = $row['server'];
			$sql2 = "SELECT ipaddress FROM tblservers WHERE id = $server_id";
			$r2 = mysql_query($sql2);
			while ($row2=mysql_fetch_assoc($r2))
				{
				$server_id = $row['server'];
				$sql3 = "SELECT serverid FROM mod_linodedns_servers WHERE serverid = $server_id";
				$r3 = mysql_query($sql3);
				while ($row3=mysql_fetch_assoc($r3))
					{
					$server_status = true;
					}
				}
			}
		}
	if($server_status == true)
		{
		return array(
			
			'pagetitle' => 'DNS Management',
			'templatefile' => 'client_templates/linode_dns_main',
			'requirelogin' => true,
			'forcessl' => $ssl,
			'vars' => array(
				'domain_ids' => $domain_ids,
				'domains' => $domains
			)
			
		);
		}
	else
		{
		return array(
			
			'pagetitle' => 'DNS Management',
			'templatefile' => 'client_templates/access_error',
			'requirelogin' => true,
			'forcessl' => $ssl,
			
		);
		}
	}