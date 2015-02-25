<style type="text/css">
.form-control{display:block;width:100%;height:34px;padding:6px 12px;font-size:14px;line-height:1.42857143;color:#555;background-color:#fff;background-image:none;border:1px solid #ccc;border-radius:4px;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075);box-shadow:inset 0 1px 1px rgba(0,0,0,.075);-webkit-transition:border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;-o-transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s;transition:border-color ease-in-out .15s,box-shadow ease-in-out .15s}
.form-control:focus{border-color:#66afe9;outline:0;-webkit-box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);box-shadow:inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)}
.form-control::-moz-placeholder{color:#777;opacity:1}
.form-control:-ms-input-placeholder{color:#777}
.form-control::-webkit-input-placeholder{color:#777}
.form-control[disabled],.form-control[readonly],fieldset[disabled] .form-control{cursor:not-allowed;background-color:#eee;opacity:1}
textarea.form-control{height:auto}
</style>
<h1>Add Skeleton Record</h1>
<form action="addonmodules.php?module=linodedns&action=submit_skeleton_add" method="POST">
<label>Server ID</label>
<input name="serverid" class="form-control" type="input" value="" /><br />

<label>Type</label>
<select name="type" class="form-control" />
<option value="2">A</option>
<option value="1">MX</option>
<option value="3">CNAME</option>
<option value="4">TXT</option>
</select>
<br />

<label>Prefix Name</label>
<input name="prefix_name" class="form-control" type="input" value="" /><br />

<label>Address</label>
<input name="address" class="form-control" type="input" value="" /><br />

<label>Priority</label>
<input name="priority" class="form-control" type="input" value="" /><br />

<label>TTL</label>
<input name="ttl" class="form-control" type="input" value="" /><br />

<br />
<input class="btn btn-success" type="submit" value="Submit" />
</form>