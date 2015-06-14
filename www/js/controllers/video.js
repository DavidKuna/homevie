;
(function (module, undefined) {
	'use strict';

	module.provider('VideoPlayer', function () {
		var _setup = {
			'techOrder': ["youtube"],
			'controls': true,
			'preload': 'auto',
			'autoplay': false,
			'height': 390,
			'width': 640,
			'src': ""
		};

		this.src = function (source) {
			_setup.src = source;
		};

		this.$get = ['$rootScope', function ($rootScope) {

				var player = videojs("videoplayer", _setup);

				var received = null;

				var addListener = function (event) {
					event = event || 'onerror';
					return function (callback) {
						player.on(event, callback);
						return this;
					};
				};

				var _receive = {
					play: function (data) {
						player.currentTime(data.time);
						player.techCall('play');
					},
					pause: function (data) {
						player.currentTime(data.time);
						player.techCall('pause');
					},
					join: function (data) {

					},
					source: function (data) {
						_setup.src = data;
						player.src(data);
						player.load();
						player.play();
					},
					setting: function (data) {
						//player.src(data.source);
						player.options_['starttime'] = data.time;
						if (data.status === 1) {
							received = 'play';
							player.currentTime(data.time);
						}
					}
				};

				var wrappedVideoPlayer = {
					states: ['PAUSE', 'PLAYING'],
					on: function (event, callback) {
						return addListener(event)(callback);
					},
					currentTime: function () {
						return player.currentTime();
					},
					receive: function (cmd, data) {
						if (cmd in _receive) {
							received = cmd;
							_receive[cmd](data);
						}
					},
					canSend: function (data) {
						return data !== received;
					},
					clearReceive: function () {
						received = null;
					},
					send: function (cmd, data) {
						this.clearReceive();
						$rootScope.$broadcast('MessageCtrl.send', cmd, data);
					},
					changeSource: function (data) {
						_receive.source(data);
					}
				};

				return wrappedVideoPlayer;

			}];

	});

}(angular.module('angular-video', [])));