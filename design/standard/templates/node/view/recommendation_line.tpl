<div>
	<a{if eq($track_click, true())} {generate_common_event($node, $solution, 'clickrecommended')}{/if} href={$node.url_alias|ezurl()}>{$node.name}</a>
</div>