<div class="block-type-recommended block-view-{$block.view} {$block.custom_attributes.scenario}">

<div class="attribute-header"><h2>{$block.name|wash()}</h2></div>

{*$block.custom_attributes|attribute(show,1)*}
{if is_set( $block.custom_attributes.node_id )}
    {def $node = fetch( 'content', 'node', hash( 'node_id', $block.custom_attributes.node_id ) )}
{else}
    {def $node = $#node}
{/if}

{def $scenario = $block.custom_attributes.scenario
     $limit = $block.custom_attributes.limit
     $category_based = false()
     $track_rendered_items = true()
     $create_click_recommended_event = true()}

{*if eq( $block.custom_attributes.category_based, 1 )}
    {set $category_based = true()}
{/if*}

{*if eq( $block.custom_attributes.track_rendered_items, 1 )}
    {set $track_rendered_items = true()}
{/if*}

{*if eq( $block.custom_attributes.create_click_recommended_event, 1 )}
    {set $create_click_recommended_event = true()}
{/if*}

{include uri='design:content/recommendations.tpl' node=$node
                                                  scenario=$scenario
                                                  limit=$limit
                                                  category_based=$category_based
                                                  track_rendered_items=$track_rendered_items
                                                  create_clickrecommended_event=$create_clickrecommended_event}
{undef}
</div>
