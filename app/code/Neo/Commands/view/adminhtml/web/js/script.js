require(['jquery', 'mage/url'], function ($, url) {
	// process the form
	$('#command_submit').click(function(event) {
		event.preventDefault();

		$('.form-group').removeClass('has-error'); // remove the error class
		$('.help-block').remove(); // remove the error text
		var path = $('input[name=path]').val();
		var command = $('select[name=command]').val();
		//var command = $('#command-display').text();
		var request_url = $('#request_url').val();
		// alert(command);
		 //alert(request_url);
		// var base_url = 'http://10.0.11.80/qdos/shell/shell/console';
		// alert(base_url);
		//alert(url.build('shell/shell/console'));

		
		if($('select[name=command]').find('option:selected').attr('id') == 'permission'){
			if(!$('input:checkbox[class=per_child]').is(':checked')){
              $('#error_check').show();
              return false;
			}else{
			  $('#error_check').hide();	
			}
		    var add_commands = $('#add_commands').val();
		    alert(add_commands);
		   // return false
		}else{
			var add_commands = "";
		}

		if($('select[name=command]').find('option:selected').attr('class') == 'php_shell'){
			var shell_type = 1; //1 for linux shell command
		}else{
			var shell_type = 2; //2 for magento shell command
		}


		if(path.length && command.length) {
			var formData = {
				'path' : path,
				'command' 	: command,
				'shell_type': shell_type,
				'add_commands':add_commands
			};

			// process the form
			$.ajax({
				type 		: 'POST', // define the type of HTTP verb we want to use (POST for our form)
				url 		: request_url, // the url where we want to POST
				data 		: formData, // our data object
				dataType 	: 'json', // what type of data do we expect back from the server
				encode 		: true,
				showLoader	: true,
				//async: false,
				//processData: false
			}).done(function(data) {
                //alert('asdgasdg');
				// log data to the console so we can see
				console.log(data); 
				//alert('wait');

				// here we will handle errors and validation messages
				if (!data.success) {
					
					// handle errors for path ---------------
					if (data.errors.path) {
						$('#path-group').addClass('has-error'); // add the error class to show red input
						$('#path-group').append('<div class="help-block">' + data.errors.path + '</div>'); // add the actual error message under our input
					}

					// handle errors for command ---------------
					if (data.errors.command) {
						$('#command-group').addClass('has-error'); // add the error class to show red input
						$('#command-group').append('<div class="help-block">' + data.errors.command + '</div>'); // add the actual error message under our input
					}

				} else {
					$("#alert-success").html(data.message);
					$("#alert-success").removeClass('hidden');

				}
				event.preventDefault();
				return false;
			}).fail(function(data) {
				//alert(data.message);
				console.log(data);
				alert('ffsdafads');
			});
		}
		return false;	
		event.preventDefault();
	});
});
