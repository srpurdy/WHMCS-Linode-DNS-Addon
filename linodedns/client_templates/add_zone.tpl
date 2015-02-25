<h2>Add Zone</h2>
<form action="index.php?m=linodedns&action=submit_add_zone" method="POST">
<label>Domain Name</label>
<input name="domain" type="input" value="" />

<label>Server IP Address</label>
<select name="ip_address">
{foreach from=$ips item=value name=ips}
<option value="{$value}">{$value}</option>
{/foreach}
</select>
<br />
<input class="btn btn-success" type="submit" value="Submit" />
</form>