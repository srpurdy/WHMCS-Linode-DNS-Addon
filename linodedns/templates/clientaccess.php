<style type="text/css">
.linodedns_field{font-size:16px;padding:10px;width:100%;background:#eee;border:1px solid #ccc;}
</style>
<h1>Client Domain Access</h1>
<p>Below you can select which clients have access to specific domain zone files. Simply select the client, and check off what domains they have access too.</p>

<h2>Add Access</h2>
<form name="client_list" action="addonmodules.php?module=linodedns&action=client_add_submit" method="POST">
<select class="linodedns_field" name="clientid_add">
<?php while ($row=mysql_fetch_assoc($r)):?>
<option value="<?php echo $row['id'];?>"><?php echo $row['firstname'];?> <?php echo $row['lastname'];?></option>
<?php endwhile;?>
</select>
<br /><br />
<select class="linodedns_field" name="domainid_add">
<?php for($i = 0; $i<=count($api_r[0]['DATA']) -1;$i++):?>
<option value="<?php echo $api_r[0]['DATA'][$i]['DOMAINID'];?>"><?php echo $api_r[0]['DATA'][$i]['DOMAIN'];?></option>
<?php endfor;?>
</select>
<br /><br />
<input type="submit" value="Add" />
</form>

<h2>Remove Access</h2>
<form name="client_list_remove" action="addonmodules.php?module=linodedns&action=client_remove_submit" method="POST">
<select class="linodedns_field" name="clientid_remove">
<?php while ($row2=mysql_fetch_assoc($r2)):?>
<option value="<?php echo $row2['id'];?>"><?php echo $row2['firstname'];?> <?php echo $row2['lastname'];?></option>
<?php endwhile;?>
</select>
<br /><br />
<select class="linodedns_field" name="domainid_remove">
<?php for($i = 0; $i<=count($api_r[0]['DATA']) -1;$i++):?>
<option value="<?php echo $api_r[0]['DATA'][$i]['DOMAINID'];?>"><?php echo $api_r[0]['DATA'][$i]['DOMAIN'];?></option>
<?php endfor;?>
</select>
<br /><br />
<input type="submit" value="Remove" />
</form>