{def $scenario_name = cond( $scenario|eq( '' ), ezini( 'RecommendationSettings', 'DefaultScenario', 'ezrecommendation.ini' ), $scenario )
     $div_id = concat( 'ezrecommendation-recommendations', $node.node_id )}
<script type="text/javascript">
$(document).ready(function () {ldelim}

    var $div = $("#{$div_id}"),
        urlParts = [
            'ezrecommendation',
            'getrecommendations',
            {$node.node_id},
            '{$scenario_name|wash( 'javascript' )}',
            {cond( is_set( $limit ), $limit|int, $numrecs|int )},
            {cond( is_set( $track_rendered_items ), $track_rendered_items|int, 0 )},
            {cond( is_set( $create_clickrecommended_event ), $create_clickrecommended_event|int, 0 )}
        ],
        errorMsg = "{'An error occured while loading the recommendations'|i18n( 'ezrecommendation/loading' )|wash( 'javascript' )}";

    {literal}
    $.ez(urlParts.join('::'), false, function (data) {
        $div.removeClass('reco-loading');
        if (data.error_text) {
            $div.addClass('reco-error').html(errorMsg);
        } else {
            $div.html(data.content);
        }
    });

    {/literal}

{rdelim});
</script>

<div id="{$div_id}" class="reco-loading">
    {'Loading recommended items'|i18n( 'ezrecommendation/loading' )}
</div>
{undef $div_id}
