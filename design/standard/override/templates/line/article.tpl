{* Article - Line view *}
{default create_clickrecommended_event=true()}

<div class="content-view-line">
    <div class="class-article float-break">

    <h2><a{if eq( $create_clickrecommended_event, true() )} {generate_common_event($node, 'clickrecommended', $scenario)}{/if} href={$node.url_alias|ezurl()}>{$node.data_map.title.content|wash}</a></h2>

    {section show=$node.data_map.image.has_content}
        <div class="attribute-image">
            {attribute_view_gui image_class=articlethumbnail href=$node.url_alias|ezurl attribute=$node.data_map.image}
        </div>
    {/section}

    {section show=$node.data_map.intro.content.is_empty|not}
    <div class="attribute-short">
        {attribute_view_gui attribute=$node.data_map.intro}
    </div>
    {/section}

    </div>
</div>

{/default}
