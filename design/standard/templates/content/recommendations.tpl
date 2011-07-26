{set-block scope=global variable=cache_ttl}0{/set-block}

<div id="ezyoochoose-recommendations">

	{def $recommendations = get_recommendations($solution, $scenario, $node, $limit, $category_based)}
	
	{def $itemid_array = array()}
	
	{if ne($recommendations, false())}
	 
		{def $classid_changed = false()}
		{def $classid = 0}
		 
		{foreach $recommendations as $rec}
			{def $rec_node=fetch( 'content', 'node', hash( 'node_id', $rec.itemId ) )}
			{if $rec_node|is_object()}
				
			 	{if and(ne($rec_node.object.contentclass_id, $classid), eq($classid, 0))}
			 		{set $classid = $rec_node.object.contentclass_id}
			 	{elseif ne($rec_node.object.contentclass_id, $classid)}
			 		{set $classid_changed = true()}
			 	{/if} 
			 	
				{set $itemid_array = $itemid_array|insert(0, $rec.itemId)} 
				{include uri='design:node/view/recommendation_line.tpl'  solution=$solution node=$rec_node track_click=$create_clickrecommended_event}
			{/if}
			{undef $rec_node}		
		{/foreach}
	{/if}
	
	{if and(gt($itemid_array|array_sum(), 0), $track_rendered_items|eq(true()), eq($classid_changed, false()))}
		{track_rendered_items($solution, $classid, $itemid_array|implode(','))}
	{/if}

</div>
{undef $recommendation $itemid_array}