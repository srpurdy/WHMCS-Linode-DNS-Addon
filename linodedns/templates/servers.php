<?php
$sql2 = "SELECT serverid FROM mod_linodedns_servers ORDER BY serverid ASC";
$r2 = mysql_query($sql2);
$sids = array();
$i = 0;
?>
<?php while ($row2=mysql_fetch_assoc($r2)):?>
<?php array_push($sids, $row2['serverid']);?>
<?php endwhile;?>
<h1>Servers</h1>
<p>Select the servers below that you would like to use the linode DNS Manager on</p>
<p>After you have selected your servers <strong>and clicked on Save</strong>. Link your linode domains to your clients <a class="btn btn-danger" style="color:#fff;" href="addonmodules.php?module=linodedns&action=link_accounts">Link Accounts</a></p>
<table class='datatable' width='100%'>
<thead>
<tr>
<th>Opt-In</th>
<th>Server</th>
</tr>
</thead>
<tbody>
<form name="server_list" action="addonmodules.php?module=linodedns&action=servers_submit" method="POST">
<?php while ($row=mysql_fetch_assoc($r)):?>
<tr>
<td><input type="checkbox" id="<?php echo $row['id'];?>" value="<?php echo $row['id'];?>" name="server_id[]" <?php if($sids[$i] == $row['id']):?>checked="checked" <?php endif;?> /></td>
<td><?php echo $row['name'];?></td>
</tr>
<?php if($sids[$i] == $row['id']):?>
<?php $i++;?>
<?php endif;?>
<?php endwhile;?>
</tbody>
</table>
<br />
<input type="submit" value="Save" />
</form>