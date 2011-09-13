 $(document).ready(function(){
//You can keep going with the args

/*var  dataSent = {arg1: "123", arg2: "456"};
$.ez(  'MyAJAX::ajaxFunc', dataSent, function( ezp_data )
{
    if ( ezp_data.error_text )
    {
        alert( ezp_data.error_text );
    }
    else
    {
        alert( ezp_data.content );
    }
});
*/



	var data_sent = { message: "testing!"};
	$.ajax({
		url: "/ezyoochoose/post",
		type: 'POST',
		data: ( data_sent),
		dataType: 'json',
		success: function( data ){
			if( data == "ok" )
			{
				alert( data );
			}else{
			
						alert( 'error' );
			}
		}
	});

});