<h1>DNS Skeleton</h1>
<p>Here you can control the default DNS Records that are created when a customer orders hosting services. You can have a custom skeleton for each server within your WHMCS Installation.<br /><br />
Note: You do NOT need to enter ip address or domain information as this will be automatically entered when a hosting account is created. This is so you can add addtional custom records.</p>
<div class="btn-group">
<a href="addonmodules.php?module=linodedns&action=skeleton_add">Add Record</a>
</div>
<table class='datatable' width='100%'>
<thead>
<tr>
<th>Server ID</th>
<th>Type</th>
<th>Prefix_name</th>
<th>Address</th>
<th>Priority</th>
<th>TTL</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while ($row=mysql_fetch_assoc($r)):?>
<tr>
<td><?php echo $row['serverid'];?></td>
<td><?php echo $row['type'];?></td>
<td><?php echo $row['prefix_name'];?></td>
<td><?php echo $row['address'];?></td>
<td><?php echo $row['priority'];?></td>
<td><?php echo $row['ttl'];?></td>
<td>
<a href="addonmodules.php?module=linodedns&action=skeleton_edit&rid=<?php echo $row['id'];?>">Edit</a>
<a href="addonmodules.php?module=linodedns&action=skeleton_delete&rid=<?php echo $row['id'];?>">Delete</a>
</td>
</tr>
<?php endwhile;?>
</tbody>
</table>