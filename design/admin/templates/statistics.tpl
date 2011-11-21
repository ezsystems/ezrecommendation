<div class="ezrecommendation-statistics">

    <div class="context-block">

        <div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

            <h1 class="context-title">eZ Recommendation Service Statistics</h1>

        <div class="header-mainline"></div>

        </div></div></div></div></div></div>
	
			{if $stats_received|eq(true())}
				
				{foreach $stats as $stat}
					<h2>{$stat.timespanBegin|wash()}</h2>
					<table class="list" cellspacing="0">
						<tr class="bglight">
							<td>revenue:</td>
							<td>{$stat.revenue|wash()}</td>
						</tr>
						{*<tr class="bgdark">
							<td>timespanBegin:</td>
							<td>{$stat.timespanBegin}</td>
						</tr>
						<tr class="bglight">
							<td>timespanDuration:</td>
							<td>{$stat.timespanDuration}</td>
						</tr>*}
						<tr class="bgdark">
							<td>click events:</td>
							<td>{$stat.clickEvents|wash()}</td>
						</tr>
						<tr class="bglight">
							<td>purchase events:</td>
							<td>{$stat.purchaseEvents|wash()}</td>
						</tr>
						<tr class="bgdark">
							<td>delivered recommendations:</td>
							<td>{$stat.deliveredRecommendations|wash()}</td>
						</tr>
						<tr class="bglight">
							<td>clicked recommendations:</td>
							<td>{$stat.clickedRecommendations|wash()}</td>
						</tr>
						<tr class="bgdark">
							<td>purchased recommendations:</td>
							<td>{$stat.purchasedRecommendations|wash()}</td>
						</tr>
						{*if ne($stat.cuurency, '')*}
							<tr class="bglight">
								<td>currency:</td>
								<td>{$stat.cuurency|wash()}</td>
							</tr>
						{*/if*}
					</table>
				{/foreach}
				
			{else}
				<div class="message-error">
					<h2><span class="time">[{currentdate()|l10n( shortdatetime )}]</span>No statistics received.</h2>
				</div>
			 
			{/if} 

    </div>

</div>

