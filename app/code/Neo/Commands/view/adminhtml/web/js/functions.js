require(["jquery"], function ($) {
	$("input[name=path]").keyup(function(){
		$("#path").text($(this).val());
	});

	$("select[name=command]").change(function(){
		console.log($(this).val());
		if($(this).val() == 'composer update')
		{
          $("#command-display").text($(this).val());
		}else{
          $("#command-display").text('php ' + $(this).val());
		}		
        $("#path").text($("input[name=path]").val());
        //alert($(this).find('option:selected').attr('id'));
        if($(this).find('option:selected').attr('id') == 'permission'){
            $('#permission_child').show();
        }else{
        	$('#permission_child').hide();
        }  
	});

    var a = [];
	$(".per_child").change(function() {
    if(this.checked) {
    	$('#error_check').hide();	
        a.push($(this).val());
    }else{
    	var index = a.indexOf($(this).val());
 
	    if (index > -1) {
	       a.splice(index, 1);
	    }
    }
     alert(a);
     var b = a.join(" ");
     console.log(b);
     $("#command-display").text($('#permission').val()+' '+b);
     $("#add_commands").val(a);
    });

	$("#reload-btn").click(function(){
		// $("#alert-success").html($(".cloner").html());
		location.reload();
	});
	// $("#use-path").click(function(e){
	// 	e.preventDefault();
	// 	$("input[name=path]").val($(this).attr('val'));
	// 	$("#path").text($(this).attr('val'));
	// });
});