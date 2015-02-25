<h2>Manage DNS Zones</h2><br />
<div class="btn-group">
<a class="btn btn-default btn-lg btn-large" href="index.php?m=linodedns&action=add_zone">Add Zone</a>
</div>
<div class="clearfix"></div>
<br />
<table class='table table-striped table-framed' width='100%'>
<thead>
<tr>
<th>Domain</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
{foreach from=$domains item=value name=domains}
<tr>
<td>{$value}</td>
<td>
<a class="btn btn-primary" href="index.php?m=linodedns&action=edit_zone&did={$domain_ids[$smarty.foreach.domains.index]}">Edit Zone</a>
</td>
</tr>
{/foreach}
</tbody>
</table>