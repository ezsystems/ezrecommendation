{set-block scope=global variable=cache_ttl}0{/set-block}
{default view='line'}

<div id="ezrecommendation-recommendations">
	<!-- NODE ID: {$node.node_id} -->
	{if eq($scenario, '')}
		{def $scenario = ezini( 'RecommendationSettings', 'DefaultScenario', 'ezrecommendation.ini' )}
	{/if}
	
	{def $recommendations = get_recommendations( $scenario, $node, $limit, $output_itemtypeid, $category_based )}
	{def $itemid_array = array()}
	
	{if ne($recommendations, false())}
	 
		{def $nodes_array = hash()}
		 
		{foreach $recommendations as $rec}
		
			{def $rec_node=fetch( 'content', 'node', hash( 'node_id', $rec.itemId ) )}
			{if $rec_node|is_object()}
			
				{def $current_node_hash = hash(concat("\"", $rec.itemId, "\""), $rec_node.object.contentclass_id)}

				{set $nodes_array = $nodes_array|merge($current_node_hash)}
				
				{node_view_gui content_node=$rec_node view=$view create_clickrecommended_event=$create_clickrecommended_event scenario=$scenario}
				
				{undef $current_node_hash}
			 	
			{/if}

			{undef $rec_node}
		{/foreach}
	{/if}
	
	
	{if and(gt($nodes_array|count(), 0), $track_rendered_items|eq(true()))}
		{track_rendered_items( $nodes_array )}
	{/if}
		

</div>
{/default}
{undef $recommendation $itemid_array}