<h2>Add Record</h2>
<form action="index.php?m=linodedns&action=submit_add_record&did={$did}" method="POST">
<label>Type</label>
<select name="type" class="form-control" />
<option value="A">A</option>
<option value="MX">MX</option>
<option value="CNAME">CNAME</option>
<option value="TXT">TXT</option>
</select>
<br />

<label>Prefix Name / example www , mail</label>
<input name="prefix_name" class="form-control" type="input" value="" /><br />

<label>Address / ip address or domain</label>
<input name="address" class="form-control" type="input" value="" /><br />

<label>Priority / a number like 10, 20, 30 etc</label>
<input name="priority" class="form-control" type="input" value="" /><br />

<label>TTL / Time to Live</label>
<input name="ttl" class="form-control" type="input" value="3600" /><br />

<br />
<input class="btn btn-success" type="submit" value="Submit" />
</form>