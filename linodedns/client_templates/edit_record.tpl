<h2>Edit Record</h2>
<form action="index.php?m=linodedns&action=submit_edit_record&did={$did}&drid={$drid}" method="POST">
{foreach from=$api_result item=value}
<label>Type</label>
<select name="type" class="form-control" />
<option value="A" {if $value.TYPE eq 'A'}selected{/if}>A</option>
<option value="MX" {if $value.TYPE eq 'MX'}selected{/if}>MX</option>
<option value="CNAME" {if $value.TYPE eq 'CNAME'}selected{/if}>CNAME</option>
<option value="TXT" {if $value.TYPE eq 'TXT'}selected{/if}>TXT</option>
</select>
<br />

<label>Prefix Name / example www , mail</label>
<input name="prefix_name" class="form-control" type="input" value="{$value.NAME}" /><br />

<label>Address / ip address or domain</label>
<input name="address" class="form-control" type="input" value="{$value.TARGET}" /><br />

<label>Priority / a number like 10, 20, 30 etc</label>
<input name="priority" class="form-control" type="input" value="{$value.PRIORITY}" /><br />

<label>TTL / Time to Live</label>
<input name="ttl" class="form-control" type="input" value="{$value.TTL_SEC}" /><br />

<br />
<input class="btn btn-success" type="submit" value="Submit" />
{/foreach}
</form>