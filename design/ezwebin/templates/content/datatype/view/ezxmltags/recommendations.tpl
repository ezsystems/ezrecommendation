{*
name: {$name}
scenario: {$scenario}
limit: {$limit}
node: {$#node.node_id}
flyout: {$flyout}
*}

{if eq( $flyout, yes )}
{run-once}
    {def $preferred_lib = 'jquery'}
    {ezscript_require( array( concat( 'ezjsc::', $preferred_lib ), concat( 'ezjsc::', $preferred_lib, 'io' ), concat( 'ezbrowserdetection_', $preferred_lib, '.js' ), concat( 'ezflyout_', $preferred_lib, '.js' ) ) )}
{/run-once}
{/if}

<div class="{$scenario|wash()}{if eq( $flyout, 'yes' )} sliding_rec{/if}">
    <a class="destroy_sliding_rec" title="close"></a>
    <div class="attribute-header"><h2>{$name|wash()}</h2></div>

    {include uri='design:content/recommendations.tpl' node=$#node
                                                      scenario=$scenario
                                                      limit=$limit
													  output_itemtypeid=$output_itemtypeid
													  category_based=$category_based
													  create_clickrecommended_event=true()
													  track_rendered_items=true()}
</div>
