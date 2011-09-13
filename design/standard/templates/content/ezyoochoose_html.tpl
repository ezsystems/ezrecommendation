{if $track}
	
	{def $request_url = generate_html($content, 'click')}
	{if eq($request_url, false())}
		{set $request_url = 'yc.gif'|ezimage()}
	{else}
		{set $request_url = $request_url|ezurl('double', 'full')}
	{/if}

{else}	

	{def $request_url = 'yc.gif'|ezimage()}

{/if}

{if eq($current_user.is_logged_in, true())}
	{def $user_id = $current_user.contentobject_id}
{else} 	
	{def $user_id = 10}
{/if}

<script type="text/javascript">
	ezyc.img({$request_url}, {$user_id});
</script>
