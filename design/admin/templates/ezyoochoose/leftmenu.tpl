                                      
<div class="box-header">
	<div class="box-tc">
		<div class="box-ml">
			<div class="box-mr">
				<div class="box-tl">
					<div class="box-tr">
						<h4>ezyoochoose</h4>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="box-bc">
	<div class="box-ml">
		<div class="box-mr">
			<div class="box-bl">
				<div class="box-br">
					<div class="box-content">
						<ul>
							{if $permission_activate}
					    		<li><div><a href={'/odoscope/activate'|ezurl()}>{"Activation"|i18n('ezodoscope/admin/leftmenu')|wash()}</a></div></li>
					    	{/if}
					    	{if $permission_view}
					    		<li><div><a href={'/odoscope/view'|ezurl()}>{"odoscope Web Viewer"|i18n('ezodoscope/admin/leftmenu')|wash()}</a></div></li>
					    	{/if}
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="" id="widthcontrol-handler">
	<div class="widthcontrol-grippy"></div>
</div>