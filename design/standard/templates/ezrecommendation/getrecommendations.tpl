{def $current_node_hash = ''
     $nodes_array = array()}
{foreach $recommended_nodes as $rec_item_id => $node}
    {set $current_node_hash = hash( concat( "\"", $rec_item_id, "\"" ), $node.object.contentclass_id )}
    {set $nodes_array = $nodes_array|merge( $current_node_hash )}
    {node_view_gui content_node=$node view='line' scenario=$scenario create_clickrecommended_event=$create_clickrecommended_event}
{/foreach}

{if and( gt( $nodes_array|count(), 0 ), $track_rendered_items )}
    {track_rendered_items( $nodes_array )}
{/if}

{undef $node_array $current_node_hash}
