{def $is_dynamic = false()
     $is_custom = false()
     $fetch_params = array()
     $action = $block.action}

{if and( eq( ezini( $block.type, 'ManualAddingOfItems', 'block.ini' ), 'disabled' ),
         ezini_hasvariable( $block.type, 'FetchClass', 'block.ini' ) )}
    {set $is_dynamic = true()}
{elseif and( eq( ezini( $block.type, 'ManualAddingOfItems', 'block.ini' ), 'disabled' ),
             ezini_hasvariable( $block.type, 'FetchClass', 'block.ini' )|not )}
    {set $is_custom = true()}
{/if}

{if is_set( $block.fetch_params )}
    {set $fetch_params = unserialize( $block.fetch_params )}
{/if}

<div id="id_{$block.id}" class="block-container">

<div class="block-header float-break">
    <div class="button-left">
        <em id="block-expand-{$block_id}" class="trigger {if $action|eq( 'add' )}collapse{else}expand{/if}"></em> {ezini( $block.type, 'Name', 'block.ini' )} {if ne( $block.name, '' )}- {$block.name|wash()}{/if}
    </div>
    <div class="button-right">
        <input id="block-up-{$block_id}" class="block-control" type="image" src="{'ezpage/block_up.gif'|ezimage(no)}" name="CustomActionButton[{$attribute.id}_move_block_up-{$zone_id}-{$block_id}]" alt="{'Move up'|i18n( 'design/standard/block/edit' )}" title="{'Move up'|i18n( 'design/standard/block/edit' )}" /> <input id="block-down-{$block_id}" class="block-control" type="image" src="{'ezpage/block_down.gif'|ezimage(no)}" name="CustomActionButton[{$attribute.id}_move_block_down-{$zone_id}-{$block_id}]" alt="{'Move down'|i18n( 'design/standard/block/edit' )}" title="{'Move down'|i18n( 'design/standard/block/edit' )}" /> <input id="block-remove-{$block_id}" class="block-control" type="image" src="{'ezpage/block_del.gif'|ezimage(no)}" name="CustomActionButton[{$attribute.id}_remove_block-{$zone_id}-{$block_id}]" title="{'Remove'|i18n( 'design/standard/block/edit' )}" alt="{'Remove'|i18n( 'design/standard/block/edit' )}" value="{'Remove'|i18n( 'design/standard/block/edit' )}" onclick="return confirmDiscard( '{'Are you sure you want to remove this block?'|i18n( 'design/standard/block/edit' )}' );" />
    </div>
</div>
<div class="block-content {if $action|eq( 'add' )}expanded{else}collapsed{/if}">

<div class="block-controls float-break">
    <div class="left blockname">
    <label>{'Name:'|i18n( 'design/standard/block/edit' )}</label>
    <input id="block-name-{$block_id}" class="textfield block-control" type="text" name="ContentObjectAttribute_ezpage_block_name_array_{$attribute.id}[{$zone_id}][{$block_id}]" value="{$block.name|wash()}" size="35" />
    </div>
    <div class="right">
    {if $is_custom|not}
        <select id="block-overflow-control-{$block_id}" class="list block-control" name="ContentObjectAttribute_ezpage_block_overflow_{$attribute.id}[{$zone_id}][{$block_id}]">
            <option value="">{'Set overflow'|i18n( 'design/standard/block/edit' )}</option>
            {foreach $zone.blocks as $index => $overflow_block}
                {if eq( $overflow_block.id, $block.id )}
                    {skip}
                {/if}
            <option value="{$overflow_block.id}" {if eq( $overflow_block.id, $block.overflow_id )}selected="selected"{/if}>{$index|inc}. {if is_set( $overflow_block.name )}{$overflow_block.name|wash()}{else}{ezini( $overflow_block.type, 'Name', 'block.ini' )}{/if}</option>
            {/foreach}
        </select>
     {/if}
        <select id="block-view-{$block_id}" class="list block-control" name="ContentObjectAttribute_ezpage_block_view_{$attribute.id}[{$zone_id}][{$block_id}]">
        {def $view_name = ezini( $block.type, 'ViewName', 'block.ini' )}
        {foreach ezini( $block.type, 'ViewList', 'block.ini' ) as $view}
            <option value="{$view}" {if eq( $block.view, $view )}selected="selected"{/if}>{$view_name[$view]}</option>
        {/foreach}
        </select>
    </div>
</div>

<div class="block-parameters float-break">
    <div>
    {if $is_dynamic}
        {foreach ezini( $block.type, 'FetchParameters', 'block.ini' ) as $fetch_parameter => $value}
        {if eq( $fetch_parameter, 'Source' )}
            <input id="block-fetch-parameter-choose-source-{$block_id}" class="button block-control" name="CustomActionButton[{$attribute.id}_new_source_browse-{$zone_id}-{$block_id}]" type="submit" value="{'Choose source'|i18n( 'design/standard/block/edit' )}" />
            <div class="source">
            {'Current source:'|i18n( 'design/standard/block/edit' )}
            {if is_set( $fetch_params['Source'] )}
                {if is_array( $fetch_params['Source'] )}
                    {foreach $fetch_params['Source'] as $source}
                        {def $source_node = fetch( 'content', 'node', hash( 'node_id', $source ) )}
                        <a href={$source_node.url_alias|ezurl} target="_blank" title="{$source_node.name|wash()} [{$source_node.object.content_class.name|wash()}]">{$source_node.name|wash()}</a>{delimiter}, {/delimiter}
                        {undef $source_node}
                    {/foreach}
                {else}
                    {def $source_node = fetch( 'content', 'node', hash( 'node_id', $fetch_params['Source'] ) )}
                    <a href={$source_node.url_alias|ezurl} target="_blank" title="{$source_node.name|wash()} [{$source_node.object.content_class.name|wash()}]">{$source_node.name|wash()}</a>
                    {undef $source_node}
                {/if}
            {/if}
            </div>
            <div class="break"></div>
        {else}
        <label>{$fetch_parameter}:</label> <input id="block-fetch-parameter-{$fetch_parameter}-{$block_id}" class="textfield block-control" type="text" name="ContentObjectAttribute_ezpage_block_fetch_param_{$attribute.id}[{$zone_id}][{$block_id}][{$fetch_parameter}]" value="{$fetch_params[$fetch_parameter]}" />
        {/if}
        {/foreach}
    {elseif $is_custom}
        {def $custom_attributes = array()
             $custom_attribute_types = array()
             $custom_attribute_names = array()
             $loop_count = 0}
        {if ezini_hasvariable( $block.type, 'CustomAttributes', 'block.ini' )}
            {set $custom_attributes = ezini( $block.type, 'CustomAttributes', 'block.ini' )}
        {/if}
        {if ezini_hasvariable( $block.type, 'CustomAttributeTypes', 'block.ini' )}
            {set $custom_attribute_types = ezini( $block.type, 'CustomAttributeTypes', 'block.ini' )}
        {/if}
        {if ezini_hasvariable( $block.type, 'CustomAttributeNames', 'block.ini' )}
            {set $custom_attribute_names = ezini( $block.type, 'CustomAttributeNames', 'block.ini' )}
        {/if}
        {foreach $custom_attributes as $custom_attrib}
            {def $use_browse_mode = array()}
            {if ezini_hasvariable( $block.type, 'UseBrowseMode', 'block.ini' )}
                {set $use_browse_mode = ezini( $block.type, 'UseBrowseMode', 'block.ini' )}
            {/if}
            {if eq( $use_browse_mode[$custom_attrib], 'true' )}
                <input id="block-choose-source-{$block_id}" class="button block-control" name="CustomActionButton[{$attribute.id}_custom_attribute_browse-{$zone_id}-{$block_id}-{$custom_attrib}]" type="submit" value="{'Choose source'|i18n( 'design/standard/block/edit' )}" />
                <div class="source">
                {'Current source:'|i18n( 'design/standard/block/edit' )}
                {if is_set( $block.custom_attributes )}
                    {foreach $block.custom_attributes as $custom_attrib => $value}
                        {if eq( $use_browse_mode[$custom_attrib], 'true' )}
                            {fetch( 'content', 'node', hash( 'node_id', $value ) ).name|wash()}
                        {/if}
                    {/foreach}
                {/if}
                </div>
            {else}
                <label>{if is_set( $custom_attribute_names[$custom_attrib] )}{$custom_attribute_names[$custom_attrib]}{else}{$custom_attrib}{/if}:</label> 
                {if is_set( $custom_attribute_types[$custom_attrib] )}
                    {switch match = $custom_attribute_types[$custom_attrib]}
                        {case match = 'text'}
                        <textarea id="block-custom_attribute-{$block_id}-{$loop_count}" class="textbox block-control" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]" rows="7">{$block.custom_attributes[$custom_attrib]|wash()}</textarea>
                        {/case}
                        {case match = 'checkbox'}
                        <input id="block-custom_attribute-{$block_id}-{$loop_count}-a" class="block-control" type="hidden" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]" value="0" />
                        <input id="block-custom_attribute-{$block_id}-{$loop_count}-b" class="block-control" type="checkbox" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]"{if eq( $block.custom_attributes[$custom_attrib], '1')} checked="checked"{/if} value="1" />
                        {/case}
                        {case match = 'string'}
                        <input id="block-custom_attribute-{$block_id}-{$loop_count}" class="textfield block-control" type="text" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]" value="{$block.custom_attributes[$custom_attrib]}" />
                        {/case}
                        {case match = 'scenarios'}
                        <select id="block-custom_attribute-{$block_id}-{$loop_count}" class="textfield block-control" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]">
                            {foreach fetch( 'ezrecommendation', 'scenario_list' ) as $scenario}
                                <option value="{$scenario.id|wash}"{if $scenario.id|eq( $block.custom_attributes[$custom_attrib] )} selected="selected"{/if} title="{$scenario.description|wash}">{$scenario.title|wash}</option>
                            {/foreach}
                        </select>
                        {/case}
                        {case match = 'limit'}
                        {def $default_item_limit = ezini( 'BackendSettings', 'DefaultItemLimit', 'ezrecommendation.ini' )}
                        <input id="block-custom_attribute-{$block_id}-{$loop_count}" class="textfield block-control" type="text" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]" value="{if eq( $block.custom_attributes[$custom_attrib], '')}{$default_item_limit}{else}{$block.custom_attributes[$custom_attrib]}{/if}" />
                        {undef $default_item_limit}
                        {/case}
                        {case}
                        <input id="block-custom_attribute-{$block_id}-{$loop_count}" class="textfield block-control" type="text" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]" value="{$block.custom_attributes[$custom_attrib]}" />
                        {/case}
                    {/switch}
                {else}
                <input id="block-custom_attribute-{$block_id}-{$loop_count}" class="textfield block-control" type="text" name="ContentObjectAttribute_ezpage_block_custom_attribute_{$attribute.id}[{$zone_id}][{$block_id}][{$custom_attrib}]" value="{$block.custom_attributes[$custom_attrib]}" />
                {/if}
            {/if}
            {undef $use_browse_mode}
            {set $loop_count=inc( $loop_count )}
        {/foreach}
        {undef $loop_count}
    {else}
        <input id="block-add-item-{$block_id}" class="button block-control" name="CustomActionButton[{$attribute.id}_new_item_browse-{$zone_id}-{$block_id}]" type="submit" value="{'Add item'|i18n( 'design/standard/block/edit' )}" />
    {/if}
    </div>
</div>

{if $is_custom|not}
<table border="0" cellspacing="1" class="items queue" id="z:{$zone_id}_b:{$block_id}_q">
    <tbody>
    {if $block.waiting|count()}
    {foreach $block.waiting as $index => $item sequence array( 'bglight', 'bgdark') as $style}
    {def $item_object = fetch( 'content', 'object', hash( 'object_id', $item.object_id ) )}
    <tr id="z:{$zone_id}_b:{$block_id}_i:{$item.object_id}" class="{if $item.ts_publication|lt($current_time)}tbp{/if}">
        <td class="tight"><input type="checkbox" value="{$item.object_id}" name="DeleteItemIDArray[]" /></td>
        <td id="z:{$zone_id}_b:{$block_id}_i:{$item.object_id}_h" class="handler">{$item_object.name|wash()}</td>
            <td class="time">
                {if $block.rotation.interval}
                      <span>{'Rotating item.'|i18n( 'design/standard/block/edit' )}</span>
                      {def $number_of_valid_setting = ezini( $block.type, 'NumberOfValidItems', 'block.ini' )
                           $last_valid_time = $block.last_valid_item.ts_visible
                           $interval_time = $block.rotation.interval
                           $time_left_latest = $last_valid_time|sub( $current_time )|sum( $interval_time )
                           $position_left = $block.waiting|count()|sub( $index )|sub('1')
                           $time_left = sum( $position_left|div( $number_of_valid_setting )|floor|mul( $interval_time ),$time_left_latest )
                      }
                      {if $time_left|gt( '0' )}
                       <span class="rotation-time-left">
                             {def $days = $time_left|div( '86400' )|floor()
                                  $hours = $time_left|mod( '86400' )|div( '3600' )|floor()
                                  $minutes = $time_left|mod( '86400' )|mod( '3600' )|div( '60' )|floor()
                                  $seconds = $time_left|mod( '86400' )|mod( '3600' )|mod( '60' )|round()
                             }
                             
                             {if $days|gt( '0' )}
                                 {$days} {'d'|i18n( 'design/standard/block/edit' )}
                             {/if}
                                 
                             {if $hours|gt( '0' )}
                                 {$hours} {'h'|i18n( 'design/standard/block/edit' )}
                             {/if}
                                 
                             {if $minutes|gt( '0' )}
                                 {$minutes} {'m'|i18n( 'design/standard/block/edit' )}
                             {/if}
                                 
                             {if $seconds|gt( '0' )}
                                 {$seconds} {'s'|i18n( 'design/standard/block/edit' )} {'left'|i18n( 'design/standard/block/edit' )}
                             {/if}
                       </span>
                      {/if}
                      {undef $time_left}
                {else}
                    <span class="ts-publication">{$item.ts_publication|l10n( 'shortdatetime' )}</span>
                        {if $item.ts_publication|lt( $current_time )|not()}
                            (
                            {def $time_diff = $item.ts_publication|sub( $current_time )
                                 $days = $time_diff|div( '86400' )|floor()
                                 $hours = $time_diff|mod( '86400' )|div( '3600' )|floor()
                                 $minutes = $time_diff|mod( '86400' )|mod( '3600' )|div( '60' )|floor()
                                 $seconds = $time_diff|mod( '86400' )|mod( '3600' )|mod( '60' )|round()}
                                 
                             {if $days|gt( '0' )}
                                 {$days} {'d'|i18n( 'design/standard/block/edit' )}
                             {/if}
                                 
                             {if $hours|gt( '0' )}
                                 {$hours} {'h'|i18n( 'design/standard/block/edit' )}
                             {/if}
                                 
                             {if $minutes|gt( '0' )}
                                 {$minutes} {'m'|i18n( 'design/standard/block/edit' )}
                             {/if}
                                 
                             {if $seconds|gt( '0' )}
                                 {$seconds} {'s'|i18n( 'design/standard/block/edit' )} {'left'|i18n( 'design/standard/block/edit' )}
                             {/if}
                            )
                          {/if}
                    <input class="block-control" type="hidden" name="ContentObjectAttribute_ezpage_item_ts_published_value_{$attribute.id}[{$zone_id}][{$block_id}][{$item.object_id}]" value="{$item.ts_publication}" />
                    <img class="schedule-handler" src="{'ezpage/clock_ico.gif'|ezimage(no)}" alt="{concat( 'Publishing schedule for: '|i18n( 'design/standard/block/edit' ), $item_object.name|wash() )|shorten( '50' )}" title="{concat( 'Publishing schedule for: '|i18n( 'design/standard/block/edit' ), $item_object.name|wash() )|shorten( '50' )}" />
                {/if}
            </td>
    </tr>
    {undef $item_object}
    {/foreach}
    {else}
     <tr class="empty">
         <td colspan="3">{'Queue: no items.'|i18n( 'design/standard/block/edit' )}</td>
     </tr>
     {/if}
     </tbody>
</table>
<table border="0" cellspacing="1" class="items online" id="z:{$zone_id}_b:{$block_id}_o">
    <tbody>
    {if $block.valid|count()}
    {foreach $block.valid as $item sequence array( 'bglight', 'bgdark') as $style}
    <tr id="z:{$zone_id}_b:{$block_id}_i:{$item.object_id}">
        <td class="tight"><input type="checkbox" value="{$item.object_id}" name="DeleteItemIDArray[]" /></td>
        <td id="z:{$zone_id}_b:{$block_id}_i:{$item.object_id}_h" colspan="2" class="handler">{fetch( 'content', 'object', hash( 'object_id', $item.object_id ) ).name|wash()}</td>
    </tr>
    {/foreach}
    {else}
    <tr class="empty">
        <td colspan="3">{'Online: no items.'|i18n( 'design/standard/block/edit' )}</td>
    </tr>
    {/if}
    <tr class="rotation">
        <td colspan="3">{'Rotation:'|i18n( 'design/standard/block/edit' )} <input id="block-rotation-value-{$block_id}" class="textfield block-control" type="text" name="RotationValue_{$block_id}" value="{$block.rotation.value}" size="5" />
            <select id="block-rotation-unit-{$block_id}" class="list block-control" name="RotationUnit_{$block_id}">
                <option value="2" {if eq( $block.rotation.unit, 2 )}selected="selected"{/if}>{'min'|i18n( 'design/standard/block/edit' )}</option>
                <option value="3" {if eq( $block.rotation.unit, 3 )}selected="selected"{/if}>{'hour'|i18n( 'design/standard/block/edit' )}</option>
                <option value="4" {if eq( $block.rotation.unit, 4 )}selected="selected"{/if}>{'day'|i18n( 'design/standard/block/edit' )}</option>
            </select>

        {'Shuffle'|i18n( 'design/standard/block/edit' )} <input id="block-rotation-shuffle-{$block_id}" class="block-control" type="checkbox" {if eq( $block.rotation.type, 2 )}checked="checked"{/if} name="RotationShuffle_{$block_id}" /> <input id="block-set-rotation-{$block_id}" class="button block-control" type="submit" name="CustomActionButton[{$attribute.id}_set_rotation-{$zone_id}-{$block_id}]" value="{'Set'|i18n( 'design/standard/block/edit' )}" /></td>
    </tr>
</table>
<table border="0" cellspacing="1" class="items history" id="z:{$zone_id}_b:{$block_id}_h">
    {if $block.archived|count()}
    {foreach $block.archived as $item sequence array( 'bglight', 'bgdark') as $style}
    <tr>
        <td class="tight"><input type="checkbox" value="{$item.object_id}" name="DeleteItemIDArray[]" /></td>
        <td>{fetch( 'content', 'object', hash( 'object_id', $item.object_id ) ).name|wash()}</td>
        <td class="status">
            {if ne( $item.moved_to , '' )}
                {'Moved to:'|i18n( 'design/standard/block/edit' )}

                {foreach $zone.blocks as $index => $dest_block}
                {if eq( $dest_block.id, $item.moved_to )}
                    {if ne( $dest_block.name, '' )}
                        {$dest_block.name|wash()}
                    {else}
                        {ezini( $dest_block.type, 'Name', 'block.ini' )}
                    {/if}
                {/if}
                {/foreach}
            {else}
                {'Not visible'|i18n( 'design/standard/block/edit' )}
            {/if}
        </td>
    </tr>
    {/foreach}
    {else}
    <tr class="empty">
        <td colspan="3">{'History: no items.'|i18n( 'design/standard/block/edit' )}</td>
    </tr>
    {/if}
    </tbody>
</table>

<div class="block-controls float-break">
    <div class="left">
        <input id="block-remove-selected-{$block_id}" class="button block-control" type="submit" name="CustomActionButton[{$attribute.id}_remove_item-{$zone_id}-{$block_id}]" value="{'Remove selected'|i18n( 'design/standard/block/edit' )}" />
    </div>
    <div class="right legend">
        <div class="queue">&nbsp;</div> {'Queue:'|i18n( 'design/standard/block/edit' )} {$block.waiting|count()} <div class="online">&nbsp;</div> {'Online:'|i18n( 'design/standard/block/edit' )} {$block.valid|count()} <div class="history">&nbsp;</div> {'History:'|i18n( 'design/standard/block/edit' )} {$block.archived|count()}
    </div>
</div>
{/if}
</div>
</div>
