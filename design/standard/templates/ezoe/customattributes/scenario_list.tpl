<select name="{$custom_attribute}" id="{$custom_attribute_id}_source"{if $custom_attribute_disabled} disabled="disabled"{/if} title="{$custom_attribute_title|wash}" class="{$custom_attribute_classes|implode(' ')}">
    {foreach fetch( 'ezrecommendation', 'scenario_list' ) as $scenario}
        <option value="{$scenario.id|wash}"{if $scenario.id|eq( $custom_attribute_default )} selected="selected"{/if} title="{$scenario.description|wash}">{$scenario.title|wash}</option>
    {/foreach}
</select>