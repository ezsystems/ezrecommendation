<div class="block">    
    <div class="element">
        {*Recommend Flag*}
        <label>{'Recommend'|i18n( 'design/standard/class/datatype' )}:</label>
         <p>{$class_attribute.data_int2|choose( 'Unchecked'|i18n( 'design/standard/class/datatype' ), 'Checked'|i18n( 'design/standard/class/datatype' ) )}</p>
    </div>
    
    <div class="element">
        {*Content to de exported to eZ Recommendation Service *}
        <label>{'Export content (for recommendation)'|i18n( 'design/standard/class/datatype' )}:</label>
         <p>{$class_attribute.data_int3|choose( 'Unchecked'|i18n( 'design/standard/class/datatype' ), 'Checked'|i18n( 'design/standard/class/datatype' ) )}</p>
         
    </div>
</div>

<br />
<br />

{*ezrecommendation Item Type ID*}
<div class="block">
    <label>{'Item type (for recommendation)'|i18n( 'design/standard/class/datatype' )}: </label>
      <p>{$class_attribute.data_int1}</p>

</div>    

{*TTL*}
<div class="block">    
    <label>{'Time to trigger consumption event'|i18n( 'design/standard/class/datatype' )}:</label>
     <p>{$class_attribute.data_int4}</p>
</div>

