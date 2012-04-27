{* Article - Flyout view *}
{default create_clickrecommended_event=true()}
<div class="{$scenario}">

<section class="widget relatedContents flyout noPrint active" style="left: 1154px;"><span class="close">X</span>
<header class="header"><h2>{'Read also'|i18n( 'design/flyout/header' )}</h2></header>
<div class="content">
    <h2><a{if eq( $create_clickrecommended_event, true() )} {generate_common_event($node, 'clickrecommended')}{/if} href={$node.url_alias|ezurl()}>{$node.data_map.title.content|wash}</a></h2>

    {*if $node.data_map.image.has_content}
        <div class="attribute-image">
            {attribute_view_gui image_class=small attribute=$node.data_map.image}
        </div>
    {/if*}

    {if $node.data_map.intro.content.is_empty|not}
        <p>{attribute_view_gui attribute=$node.data_map.intro}</p>
    {/if}
</div>
</section>

</div>
{/default}