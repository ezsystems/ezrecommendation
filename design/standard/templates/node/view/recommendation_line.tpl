<div>
	<a{if eq($track_click, true())} {generate_common_event($node, 'clickrecommended')}{/if} href={$node.url_alias|ezurl()}>{$node.name|wash()}</a>
</div>
