<h2>DNS Zone For: </h2><br />
<div class="btn-group">
<a class="btn btn-default btn-large btn-lg" href="index.php?m=linodedns&action=add_record&did={$domain_id}">Add Record</a>
</div>
<div class="clearfix"></div>
<br />
<table class='table table-striped table-framed' width='100%'>
<thead>
<tr>
<th>Type</th>
<th>Prefix</th>
<th>Address</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
{foreach from=$api_result item=value}
<tr>
<td>{$value.TYPE}</td>
<td>{$value.NAME}</td>
<td>{$value.TARGET|truncate}</td>
<td>
<a class="btn btn-primary" href="index.php?m=linodedns&action=edit_record&did={$domain_id}&drid={$value.RESOURCEID}">Edit Record</a>
<a class="btn btn-danger" href="index.php?m=linodedns&action=delete_record&did={$domain_id}&drid={$value.RESOURCEID}">Delete Record</a>
</td>
</tr>
{/foreach}
</tbody>
</table>