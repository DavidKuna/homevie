/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function addToChat(message, client) {
	if (client) {
		client = "ID: " + client;
	} else {
		client = "Já";
	}

	$("#chatBox").append("<br><br><div><b>" + client + "</b><div>" + message + "</div></div>");

}

function jquery_receive(msg) {
	message = msg.msg;
	client = msg.client_id;

	addToChat(message, client);
}

$(function () {
	myScope = angular.element($("#msgCtrl")).scope();

	$('#message').change(function () {
		data = {};
		data.msg = this.value;
		cmd = "chat";
		a = myScope.sendChat(cmd, data);

		addToChat(data.msg);

		this.value = '';
	});
	
	if(messages) {

		messages.forEach(function(message) {
				addToChat(message.msg, message.user);
		});
		
	}
	
});