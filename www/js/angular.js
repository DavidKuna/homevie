var source = ""; //TODO
angular.module('Homevie', [
	'ngRoute',
	'angular-websocket',
	'angular-video',
	'controllers',
	'instant-search'
])
		.config(function (WebSocketProvider) {
			WebSocketProvider
					.prefix('')
					.uri('ws://' + settings.domain + ':8080');
		}).config(function (VideoPlayerProvider) {
	VideoPlayerProvider
			.src(source);
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

			WebSocket.onopen(function () {
				var join = {
					cmd: 'join',
					data: token
				};
				WebSocket.send(JSON.stringify(join));
				_d('connected');
			});

			WebSocket.onmessage(function (event) {
				var msg = JSON.parse(event.data);
				WebSocket.receive(msg.cmd, msg.data);
				_d('message: ', msg);
			});

			$scope.$on('MessageCtrl.send', function ($scope, cmd, data) {
				var message = constructMessage(cmd, data);
				_d('MessageCtrl.send', message);
				WebSocket.send(message);
			});

			$scope.sendChat = function (cmd, data) {
				msg = constructMessage(cmd, data);
				_d('WS: Send CHATmsg: ', msg);
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
