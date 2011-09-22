{set-block scope=global variable=cache_ttl}0{/set-block}

<div id="ezyoochoose-recommendations">

	{if eq($scenario, '')}
		{def $scenario = ezini( 'RecommendationSettings', 'DefaultScenario', 'ezyoochoose.ini' )}
	{/if}
	
	{def $recommendations = get_recommendations( $scenario, $node, $limit, $category_based)}

	{def $itemid_array = array()}
	
	{if ne($recommendations, false())}
	 
		{def $nodes_array = hash()}
		 
		{foreach $recommendations as $rec}
			{def $rec_node=fetch( 'content', 'node', hash( 'node_id', $rec.itemId ) )}
			{if $rec_node|is_object()}
			
				{def $current_node_hash = hash(concat("\"", $rec.itemId, "\""), $rec_node.object.contentclass_id)}

				{set $nodes_array = $nodes_array|merge($current_node_hash)}
				
				{node_view_gui content_node=$rec_node view='line' create_clickrecommended_event=$create_clickrecommended_event}
			 	
			{/if}

			{undef $rec_node}
		{/foreach}
	{/if}
	
	
	{if and(gt($nodes_array|count(), 0), $track_rendered_items|eq(true()))}
		{track_rendered_items( $nodes_array )}
	{/if}

</div>
{undef $recommendation $itemid_array}