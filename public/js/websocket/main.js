(function(){

	var user;
	var messages = [];

	
	function updateMessages(msg){
		//push commands!!
		 console.log("got msg! "+msg);
		 refreshView(msg);
		
	}
	var urlLink = window.location.hostname;
	console.log(' starting socket');
	var conn = new WebSocket('ws://'+urlLink+':8081/?organization='+organization);
	//weeb socket test for update real time
	conn.onopen = function(e) {
	    console.log("Connection established! "+conn);
	};

	conn.onmessage = function(e) {
		var msg = JSON.parse(e.data);
		updateMessages(msg);
	};


})();