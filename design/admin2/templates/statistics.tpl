<div class="ezrecommendation-statistics">

    <div class="context-block">

        <div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

            <h1 class="context-title">eZ Recommendation Service Statistics</h1>

        <div class="header-mainline"></div>



        </div></div></div></div></div></div>
    
            {if $stats_received|eq(true())}
                    <table class="list" cellspacing="0">
                            <tr>
                                <th>Date</th>
                                
                                
                                {*<th>timespanBegin</th>
                                <th>timespanDuration</th>*}
                                
                                <th>Click-Events</th>
                                {if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('publisher')}
                                    <th>Consume-Events</th>
                                {/if}
                                <th>Delivered-Recommendations</th>
                                <th>Clicked-Recommended</th>

                                {if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('shop')}
                                    <th>Purchase-Events</th>
                                    <th>Purchased-Recommended</th>
                                    <th>Revenue</th>
                                {/if}        
                            </tr>
                {def $i=1}
                {foreach $stats as $stat}
    
                            <tr {if mod($i,2)}class="bglight"{else} class="bgdark"{/if}>
                                <td id="ezrecommendation_stat_date">{$stat.timespanBegin|wash()}</td>
                                
                                
                                {*<td>{$stat.timespanBegin}</td>
                                  <td>{$stat.timespanDuration}</td>
                                *}
                            
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
                {set $i=inc( $i )}
                {/foreach}
                
                    </table>
                
            {else}
                <div class="message-error">
                    <h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span>No statistics received.</h2>
                </div>
             
            {/if} 

    </div>

</div>


{foreach $class_attributes as $attribute}
                    {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                        <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.price)}selected="selected"{/if}>{$attribute.name|wash}</option>
                    {/if}
                {/foreach}

