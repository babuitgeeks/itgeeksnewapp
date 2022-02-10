if(!window.jQuery){ 
	(function() {
    // Load the script
		var script = document.createElement("SCRIPT");
		script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js';
		script.type = 'text/javascript';
		script.onload = function() {
			var $ = window.jQuery;
			$(document).ready(function(){
			});
		};
		document.getElementsByTagName("head")[0].appendChild(script);
	})();
}else{
    var $ = window.jQuery;
    $(document).ready(function(){
    });
}

