{if $api_error eq ''}
<div class="alert alert-succes">Your Zone was added successfully</div>
{else}
<div class="alert alert-danger">{$api_error}</div>
{/if}