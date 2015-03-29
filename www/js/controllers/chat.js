/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function addToChat(message, client, time) {
	if(client) {
		client = "ID: "+client;
	} else {
		client = "Já";
	}
	
	if(!time){		
		var currentdate = new Date(); 
		time = ('0' + currentdate.getHours()).slice(-2) + ":" + ('0' + currentdate.getMinutes()).slice(-2);
	}
	
	message = {
		client: client,
		text: message,
		time: time
	};
	angular.element($("#msgCtrl")).scope().chat.messages.push(message);
	angular.element($("#msgCtrl")).scope().$apply();	
}


function jquery_receive(msg) {
	message = msg.msg;
	client = msg.client_id;
	
	addToChat(message, client);
}

$(function () {
	myScope = angular.element($("#msgCtrl")).scope();

	$('#message').keypress(function(e) {
		if(e.which == 13) {
			e.preventDefault();
			data = {};
			data.msg = this.value;
			cmd = "chat";
			a = myScope.sendChat(cmd, data);

			addToChat(data.msg);

			this.value = '';
		}
	});
});