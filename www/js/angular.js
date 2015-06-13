var source = ""; //TODO
angular.module('Homevie', [
	'ngRoute',
	'angular-websocket',
	'angular-video',
	'controllers',
	'instant-search',
	'rt.eventemitter'
])
	.config(function (WebSocketProvider) {
		WebSocketProvider
				.prefix('')
				.uri('ws://' + settings.domain + ':8080');
	})
	.config(function (VideoPlayerProvider) {
		VideoPlayerProvider
			.src(source);
	})
	.directive('schrollBottom', function () {
		return {
			scope: {
				schrollBottom: "="
			},
			link: function (scope, element) {
				scope.$watchCollection('schrollBottom', function (newValue) {
					if (newValue)
					{
						$(element).scrollTop($(element)[0].scrollHeight);
					}
				});
			}
		};
	});

function constructMessage(command, data) {
	var msg = {
		cmd: command,
		data: data,
		who: token
	};
	return JSON.stringify(msg);
}

var controllers = angular.module('controllers', [])
		.controller('MessageCtrl', function ($scope, WebSocket) {
			
			$scope.chat = {
				messages: []
			};
			
			WebSocket.onopen(function () {
				var join = {
					cmd: 'join',
					data: token
				};
				WebSocket.send(JSON.stringify(join));
				_d('connected');								
			});

			//someone sends
			WebSocket.onmessage(function (event) {
				var response = JSON.parse(event.data);
				WebSocket.receive(response.cmd, response.data);
				
				var cmd = response.cmd;
				if(cmd === "join" || cmd === "disconnect" || cmd === "chat") {
					HomevieChat.sendToAll(response);
				}
			});

			//Iam sending
			$scope.$on('MessageCtrl.send', function ($scope, cmd, data) {
				var message = constructMessage(cmd, data);
				WebSocket.send(message);
			});

			$scope.sendChat = function (chatItem) {
				
				var cmd = chatItem.cmd;
				var data = chatItem.data;
				
				msg = constructMessage(cmd, data);
				WebSocket.send(msg);
			};

		})
		.controller('VideoCtrl', function ($scope, VideoPlayer) {

			VideoPlayer.on('play', function () {
				var data = {
					time: VideoPlayer.currentTime()
				};
				if (VideoPlayer.canSend('play')) {
					VideoPlayer.send('play', data);
				}
			});

			VideoPlayer.on('pause', function () {
				var data = {
					time: VideoPlayer.currentTime()
				};
				if (VideoPlayer.canSend('pause')) {
					VideoPlayer.send('pause', data);
				}
			});

			$scope.$on('VideoCtrl.receive', function ($scope, cmd, data) {
				VideoPlayer.receive(cmd, data);
			});

			$scope.setSourceURL = function () {
				VideoPlayer.changeSource($scope.source.url);
				VideoPlayer.send('source', $scope.source.url);
			};
		});
