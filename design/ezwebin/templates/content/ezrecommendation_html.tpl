{if $track}
	{def $obj = fetch( 'content', 'object', hash( 'object_id', $content.content_info.object_id ))}
	
	{foreach $obj.data_map as $attribute}
		{if eq($attribute.data_type_string, 'ezrecommendation')}

			{def $data_array=fetch( 'ezrecommendation', 'recommendation_enable', hash('xmlDataText', $attribute.data_text) )}
			{*default attribute_base=ContentObjectAttribute*}
			{if eq( $data_array, 1 )}

				{def $parameters = generate_html($content, 'click')
				     $request_url = ''}
				
				{if eq($parameters, false())}
					{set $request_url = 'ezreco.gif'|ezimage()}
				{else}
					{*set $request_url = $parameters|ezurl('double', 'full')*}
					{set $request_url = '/ezrecommendation/request'|ezurl('no', 'full')}
					{set $request_url = concat( '"', $request_url, $parameters, '"')}
				{/if}
				{break}
			{/if}
			
		{/if}
	{/foreach}

{else}	

	{def $request_url = 'ezreco.gif'|ezimage()}

{/if}

{if eq($current_user.is_logged_in, true())}
	{def $user_id = $current_user.contentobject_id}
{else} 	
	{def $user_id = 10}
{/if}

<script type="text/javascript">
	ezreco.img({$request_url}, {$user_id});
</script>
