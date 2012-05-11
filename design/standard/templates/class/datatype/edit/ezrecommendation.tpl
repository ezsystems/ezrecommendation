{def $class_attributes=fetch( 'ezrecommendation', 'attribute_list', hash( 'class_id', $class_attribute.contentclass_id ) )}

<div class="block">
    <div class="element">
        {*Recommend Flag*}
        <label for="ContentClass_ezrecommendation_page_reco_recommend_value_{$class_attribute.id}">
        <input type="checkbox" id="ContentClass_ezrecommendation_page_reco_recommend_value_{$class_attribute.id}" name="ContentClass_ezrecommendation_page_reco_recommend_value_{$class_attribute.id}" {if eq( $class_attribute.data_int2, 1 ) } checked="checked" {/if}  />
        {'Recommend'|i18n( 'design/standard/class/datatype' )}
         <input type="hidden" name="ContentClass_ezrecommendation_page_reco_recommend_value_{$class_attribute.id}_exists" value="1" />
        </label>

    </div>

    <div class="element">
        {*Content to de exported to eZ Recommendation Service *}
        <label for="ContentClass_ezrecommendation_page_reco_export_value_{$class_attribute.id}">
        <input type="checkbox" id="ContentClass_ezrecommendation_page_reco_export_value_{$class_attribute.id}" name="ContentClass_ezrecommendation_page_reco_export_value_{$class_attribute.id}" {if eq( $class_attribute.data_int3, 1 )} checked="checked" {/if} />
        {'Export content (for recommendation)'|i18n( 'design/standard/class/datatype' )}
         <input type="hidden" name="ContentClass_ezrecommendation_page_reco_export_value_{$class_attribute.id}_exists" value="1" />
        </label>

    </div>
</div>
<br />
{*ezrecommendation Item Type ID*}
<div class="block">
    <label for="ContentClass_ezrecommendation_class_reco_item_type_value_{$class_attribute.id}">{'Item type (for recommendation)'|i18n( 'design/standard/class/datatype' )}: </label>
      <select id="ContentClass_ezrecommendation_class_reco_item_type_value_{$class_attribute.id}" name="ContentClass_ezrecommendation_class_reco_item_type_value_{$class_attribute.id}" size="1">
          <option value="1" {if eq( $class_attribute.data_int1, 1 )}selected="selected"{/if}>1</option>
          <option value="2" {if eq( $class_attribute.data_int1, 2 )}selected="selected"{/if}>2</option>
          <option value="3" {if eq( $class_attribute.data_int1, 3 )}selected="selected"{/if}>3</option>
          <option value="4" {if eq( $class_attribute.data_int1, 4 )}selected="selected"{/if}>4</option>
          <option value="5" {if eq( $class_attribute.data_int1, 5 )}selected="selected"{/if}>5</option>
          <option value="6" {if eq( $class_attribute.data_int1, 6 )}selected="selected"{/if}>6</option>
        </select>
</div>

<br />

{*TTL*}
<div class="block">
    <label for="ContentClass_ezrecommendation_page_reco_ttl_value_{$class_attribute.id}">{'Time to trigger consumption event'|i18n( 'design/standard/class/datatype' )}:</label>
    <input type="text" id="ContentClass_ezrecommendation_page_reco_ttl_value_{$class_attribute.id}" name="ContentClass_ezrecommendation_page_reco_ttl_value_{$class_attribute.id}" value="{$class_attribute.data_int4|wash}" size="10" maxlength="9" /> sec.
</div>

{*attribute mapping section*}
<label for="ContentClass_ezrecommendation_attribute_map_value_{$class_attribute.id}">{'Export attributes mapping'|i18n( 'design/standard/class/datatype' )}:</label><br />

{if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('shop')}

    <fieldset class="mapping_block_required">
    <legend>{'Shop solution (required)'|i18n( 'design/standard/class/datatype' )}</legend>
        <div class="block">

            <div class="element_mapping">
            <label for="ContentClass_ezrecommendation_attribute_mapping_price_{$class_attribute.id}">{'Price'|i18n( 'design/standard/class/datatype' )} </label>
            </div>
            <div class="element_mapping">

                <select id="ContentClass_ezrecommendation_attribute_mapping_price_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_price_{$class_attribute.id}">
                <option value="0"></option>

                {foreach $class_attributes as $attribute}
                    {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                        <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.price)}selected="selected"{/if}>{$attribute.name|wash}</option>
                    {/if}
                {/foreach}
                </select>
            </div>
        </div>

        <div class="block">
            <div class="element_mapping">
                <label>{'Currency'|i18n( 'design/standard/class/datatype' )}:</label>
            </div>
        {def $currenyList=fetch( 'ezrecommendation', 'currency_list' )}

            <div class="element_mapping">
                <select name="ContentClass_ezrecommendation_attribute_mapping_currency_{$class_attribute.id}"  name=ContentClass_ezrecommendation_attribute_mapping_currency_{$class_attribute.id}}>
                    {foreach $currenyList as $attribute}
                        <option value="{$attribute|wash}" {if $attribute|compare($class_attribute.content.currency)} selected="selected" {/if} >{$attribute|wash}</option>
                    {/foreach}
                </select>

            </div>
        </div>
        {if eq($currenyList|count(), 0)}<br /> <small>{'Please Specify defaultCurrency in ezrecommendation.ini'|i18n( 'design/standard/class/datatype' )}</small>{/if}

    </fieldset>
{/if}
<fieldset class="mapping_block_required">
{if ezini('SolutionSettings','solution','ezrecommendation.ini')|eq('shop')}
    <legend>{'Optional'|i18n( 'design/standard/class/datatype' )}</legend>
{else}
    <legend>{'Publisher solution (required)'|i18n( 'design/standard/class/datatype' )}</legend>
{/if}
    <div class="block">

        <div class="element_mapping">
        <label for="ContentClass_ezrecommendation_attribute_mapping_validfrom_{$class_attribute.id}">{'Valid from'|i18n( 'design/standard/class/datatype' )} </label>
        </div>
        <div class="element_mapping">

            <select id="ContentClass_ezrecommendation_attribute_mapping_validfrom_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_validfrom_{$class_attribute.id}">
            <option value="0"></option>

            {foreach $class_attributes as $attribute}
                {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                    <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.validfrom )}selected="selected"{/if}>{$attribute.name|wash}</option>
                {/if}
            {/foreach}
            </select>
        </div>
    </div>

    <div class="block">

        <div class="element_mapping">
        <label for="ContentClass_ezrecommendation_attribute_mapping_validto_{$class_attribute.id}">{'Valid to'|i18n( 'design/standard/class/datatype' )} </label>
        </div>
        <div class="element_mapping">

            <select id="ContentClass_ezrecommendation_attribute_mapping_validto_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_validto_{$class_attribute.id}">
            <option value="0"></option>

            {foreach $class_attributes as $attribute}
                {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                    <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.validto)}selected="selected"{/if}>{$attribute.name|wash}</option>
                {/if}
            {/foreach}
            </select>
        </div>
    </div>
</fieldset>



<div style="clear:both;">
<br />
<fieldset class="mapping_block_second">
<legend>{'Optional attributes for content export'|i18n( 'design/standard/class/datatype' )}</legend>
<div class="block">

    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_title_{$class_attribute.id}">{'Title'|i18n( 'design/standard/class/datatype' )} </label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_title_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_title_{$class_attribute.id}">
        <option value="0"></option>

        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.title )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

<div class="block">
    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_abstract_{$class_attribute.id}">{'Abstract'|i18n( 'design/standard/class/datatype' )}</label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_abstract_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_abstract_{$class_attribute.id}">
        <option value="0"></option>
        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.abstract )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

<div class="block">
    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_author_{$class_attribute.id}">{'Author'|i18n( 'design/standard/class/datatype' )}</label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_author_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_author_{$class_attribute.id}">
        <option value="0"></option>
        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.author )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

<div class="block">
    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_newsagency_{$class_attribute.id}">{'Newsagency'|i18n( 'design/standard/class/datatype' )}</label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_newsagency_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_newsagency_{$class_attribute.id}">
        <option value="0"></option>
        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.newsagency )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

<div class="block">
    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_vendor_{$class_attribute.id}">{'Vendor'|i18n( 'design/standard/class/datatype' )}</label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_vendor_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_vendor_{$class_attribute.id}">
        <option value="0"></option>
        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.vendor )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

<div class="block">
    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_geolocation_{$class_attribute.id}">{'Geolocation'|i18n( 'design/standard/class/datatype' )}</label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_geolocation_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_geolocation_{$class_attribute.id}">
        <option value="0"></option>
        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.geolocation )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

<div class="block">
    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_date_{$class_attribute.id}">{'Date'|i18n( 'design/standard/class/datatype' )}</label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_date_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_date_{$class_attribute.id}">
        <option value="0"></option>
        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.date )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

<div class="block">
    <div class="element_mapping">
    <label for="ContentClass_ezrecommendation_attribute_mapping_tags_{$class_attribute.id}">{'Tags'|i18n( 'design/standard/class/datatype' )}</label>
    </div>
    <div class="element_mapping">

        <select id="ContentClass_ezrecommendation_attribute_mapping_tags_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_tags_{$class_attribute.id}">
        <option value="0"></option>
        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <option value="{$attribute.identifier|wash}" {if $attribute.identifier|compare($class_attribute.content.tags )}selected="selected"{/if}>{$attribute.name|wash}</option>
            {/if}
        {/foreach}
        </select>
    </div>
</div>

{*Other Attributes to be added*}
</fieldset>
<br />
<fieldset class="mapping_block_second">
<legend>{'Additional attributes for content export'|i18n( 'design/standard/class/datatype' )}</legend>
<div class="block">
    <small>{'Check only if not already selected in the attribute mapping'|i18n( 'design/standard/class/datatype' )}</small>
    <div class="element">

        {def $attrCount=$class_attributes|count()}
        {def $i=1}

        <input type="hidden" name="ContentClass_ezrecommendation_attribute_mapping_counter_{$class_attribute.id}" value="{$attrCount|sub( 1 )|wash}" />

        {foreach $class_attributes as $attribute}
            {if or($attribute.identifier|compare(''),$attribute.data_type_string|compare('ezrecommendation'))}{else}
                <p>
                    <input type="checkbox" id="ContentClass_ezrecommendation_attribute_mapping_addtomap{$i}_{$class_attribute.id}" name="ContentClass_ezrecommendation_attribute_mapping_addtomap{$i}_{$class_attribute.id}" value="{$attribute.identifier|wash}"
                    {if $attribute.identifier|compare($class_attribute.content.concat( 'addtomap', $i ))} checked="checked" {/if}/> {$attribute.name|wash}
                </p>
                {set $i=inc( $i )}
            {/if}
        {/foreach}

    </div>
</div>

</fieldset>

