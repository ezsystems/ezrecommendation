{*get zone definition from node if it has a page datatype*}
{def $use_global_layout = true()}
{if is_set( $module_result.node_id )}
    {def $node = fetch( 'content', 'node', hash( 'node_id', $module_result.node_id ) )}
    {if is_set( $node.object.data_map.page )}
        {def $page = $node.object_data_map.page}
        {if is_set( $node.object.data_map.page.content.zones[0] )}
            {if and( is_set( $node.object.data_map.page.content.zones[0].blocks ), $node.object.data_map.page.content.zones[0].blocks|count )}
                {set $use_global_layout = false()}

<!-- NODE ZONE CONTENT: START -->

<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">

{*$node.object.data_map.page.content.zones[0].blocks|attribute(show,1)*}
{foreach $node.object.data_map.page.content.zones[0].blocks as $block}
    {include uri='design:parts/zone_block.tpl' zone=$zones[0]}
{/foreach}

</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>

<!-- NODE ZONE CONTENT: END -->

        {/if}
    {/if}
{/if}

{if $use_global_layout}
{def $global_layout_object = fetch( 'content', 'tree', hash( 'parent_node_id', 1,
                                                             'limit', 1,
                                                             'class_filter_type', include,
                                                             'class_filter_array', array( 'global_layout' ) ) )}

<!-- ZONE CONTENT: START -->

{if $global_layout_object}
<div class="border-box">
<div class="border-tl"><div class="border-tr"><div class="border-tc"></div></div></div>
<div class="border-ml"><div class="border-mr"><div class="border-mc">
<div class="border-content">

{attribute_view_gui attribute=$global_layout_object[0].data_map.page}

{/if}
</div>
</div></div></div>
<div class="border-bl"><div class="border-br"><div class="border-bc"></div></div></div>
</div>

<!-- ZONE CONTENT: END -->
{/if}
