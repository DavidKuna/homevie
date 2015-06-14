/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
HomevieChat = {
	sendToAll: function (chatItem) {
		var cmd = chatItem.cmd;

		if (cmd === "chat") {
			text = chatItem.data.text;
			user_name = chatItem.data.user_name;
		}

		var time = chatItem.time;
		if (!time) {
			var currentdate = new Date();
			time = ('0' + currentdate.getHours()).slice(-2) + ":" + ('0' + currentdate.getMinutes()).slice(-2);
		}

		var message = {user_name: user_name, text: text, time: time};
		angular.element($("#msgCtrl")).scope().chat.messages.push(message);
		angular.element($("#msgCtrl")).scope().$apply();
	}
};

$(function () {
	myScope = angular.element($("#msgCtrl")).scope();

	$('#message').keypress(function (e) {
		if (e.which === 13) {
			e.preventDefault();

			var chatItem = {};
			var chatText = $(this).val();
			var user_name = global_userName;

			chatItem.cmd = "chat";
			chatItem.data = {text: chatText, user_name: user_name};
			a = myScope.sendChat(chatItem);
			HomevieChat.sendToAll(chatItem);
			$(this).val("");
		}
	});

	if (messages) {
		messages.forEach(function (message) {
			HomevieChat.sendToAll(message);
		});
	}

});

function setCookie(cname, cvalue, exdays) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	var expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}