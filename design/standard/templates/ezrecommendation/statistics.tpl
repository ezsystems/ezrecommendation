<div class="context-block">
    <div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">
        <h1 class="context-title">{"eZ Recommendation Service Statistics"|i18n( 'extension/ezrecommendation/statistics' )}</h1>
        <div class="header-mainline"></div>
    </div></div></div></div></div></div>

    {if $stats_received|eq(true())}
        <table class="list" cellspacing="0">
            <tr>
                <th>{"Date"|i18n( 'extension/ezrecommendation/statistics' )}</th>

                <th>{"Click-Events"|i18n( 'extension/ezrecommendation/statistics' )}</th>
                {if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('publisher')}
                    <th>{"Consume-Events"|i18n( 'extension/ezrecommendation/statistics' )}</th>
                {/if}
                <th>{"Delivered-Recommendations"|i18n( 'extension/ezrecommendation/statistics' )}</th>
                <th>{"Clicked-Recommended"|i18n( 'extension/ezrecommendation/statistics' )}</th>

                {if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('shop')}
                    <th>{"Purchase-Events"|i18n( 'extension/ezrecommendation/statistics' )}</th>
                    <th>{"Purchased-Recommended"|i18n( 'extension/ezrecommendation/statistics' )}</th>
                    <th>{"Revenue"|i18n( 'extension/ezrecommendation/statistics' )}</th>
                {/if}
            </tr>
            {foreach $stats as $stat sequence array( 'bglight', 'bgdark' ) as $bgClass}
                <tr class="{$bgClass}">
                    <td id="ezrecommendation_stat_date">{$stat.timespanBegin|wash()}</td>
                    <td>{$stat.clickEvents|wash()}</td>
                    {if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('publisher')}
                        <td>{$stat.consumeEvents|wash()}</td>
                    {/if}
                    <td>{$stat.deliveredRecommendations|wash()}</td>
                    <td>{$stat.clickedRecommended|wash()}</td>
                    {if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('shop')}
                        <td>{$stat.purchaseEvents|wash()}</td>
                        <td>{$stat.purchasedRecommended|wash()}</td>
                        <td>
                            <table class="stat_revenue" cellspacing="0">
                            {foreach $stat.revenue as $key => $value}
                                <tr><td>{$value}</td><td class="stat_curr">{$key}</td></tr>
                            {/foreach}
                            </table>
                        </td>
                    {/if}
                </tr>
            {/foreach}
        </table>
    {else}
        <div class="message-error">
            <h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span>No statistics received.</h2>
        </div>
    {/if}
</div>
{foreach $class_attributes as $attribute}
    {if not(or( $attribute.identifier|eq( '' ), $attribute.data_type_string|eq( 'ezrecommendation' ) ) )}
        <option value="{$attribute.identifier|wash}" {if $attribute.identifier|eq($class_attribute.content.price)}selected="selected"{/if}>{$attribute.name|wash}</option>
    {/if}
{/foreach}

