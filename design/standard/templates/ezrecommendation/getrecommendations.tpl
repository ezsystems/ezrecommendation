{def $current_node_hash = ''
     $node_array = array()}
{foreach $recommended_nodes as $node}
    {set $current_node_hash = hash( concat( "\"", $rec.itemId, "\"" ), $node.object.contentclass_id )}
    {set $nodes_array = $nodes_array|merge( $current_node_hash )}
    {node_view_gui content_node=$node view='line' create_clickrecommended_event=$create_clickrecommended_event}
{/foreach}


{if and( gt( $nodes_array|count(), 0 ), $track_rendered_items|eq( true() ) )}
    {track_rendered_items( $nodes_array )}
{/if}

{undef $node_array $current_node_hash}
