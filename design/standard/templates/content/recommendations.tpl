{def $scenario_name = cond( $scenario|eq( '' ), ezini( 'RecommendationSettings', 'DefaultScenario', 'ezrecommendation.ini' ), $scenario )
     $div_id_hash = concat( $node.node_id, $scenario, $numrecs, $output_itemtypeid, $category_based, $create_clickrecommended_event, $track_rendered_items )|md5
     $div_id = concat( 'ezreco-reco-', $div_id_hash )}

<script type="text/javascript">
$(document).ready(function () {ldelim}
    var $div = $("#{$div_id}");
    var urlParts = [
        'ezrecommendation',
        'getrecommendations',
        {$node.node_id},
        '{$scenario_name|wash( 'javascript' )}',
        {$numrecs|int},
        {cond( is_set( $itemTypeId ), $itemTypeId, 0 ),
        {cond( is_set( $track_rendered_items ), $track_rendered_items|int, 0 )},
        {cond( is_set( $create_clickrecommended_event ), $create_clickrecommended_event|int, 0 )}
    ];
    var errorMsg = "{'An error occured while loading the recommendations'|i18n( 'ezrecommendation/loading' )|wash( 'javascript' )}";

    {literal}
        $.ez(urlParts.join('::'), false, function (data) {
            $div.removeClass('reco-loading');
            if ( data.error_text )
            {
                $div.addClass('reco-error').html(errorMsg);
            }
            else
            {
                $div.html(data.content);
            }
        });
{/literal}
{rdelim} );
</script>

<div id="{$div_id}" class="reco-loading">
    {'Loading recommended items'|i18n( 'ezrecommendation/loading' )}
</div>
{undef $div_id}
