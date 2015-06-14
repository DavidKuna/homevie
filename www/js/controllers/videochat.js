angular.module('controllers')
  .controller('VideoChatCtrl', function ($sce, VideoStream, $scope, Room, $rootScope) {
	
	$scope.peers = [];
	$scope.myWebCam = {
		isVisible: false
	};
	var stream;
	
	$scope.initVideo = function(){
		if (!window.RTCPeerConnection || !navigator.getUserMedia) {
		  $scope.error = 'WebRTC is not supported by your browser. You can try the app with Chrome and Firefox.';
		  return;
		}

		VideoStream.get()
		.then(function (s) {
			stream = s;
	/*
			var audioContext = new AudioContext();
			var analyser = audioContext.createAnalyser();
			var source = audioContext.createMediaStreamSource(stream);			
			var javascriptNode = audioContext.createScriptProcessor(2048, 1, 1);
			
			analyser.smoothingTimeConstant = 0.3;
			analyser.fftSize = 1024;
			
			source.connect(analyser);
			analyser.connect(javascriptNode);
			javascriptNode.connect(audioContext.destination);

			var cnvs = $('#myWebCam canvas')[0];
			var canvasContext = cnvs.getContext("2d");					

			javascriptNode.onaudioprocess = function(){
				
				var array =  new Uint8Array(analyser.frequencyBinCount);
				analyser.getByteFrequencyData(array);
				var values = 0;

				var length = array.length; 
				for (var i = 0; i < length; i++) {
					values += array[i];
				}
				var average = values / length;
				
				canvasContext.clearRect(0, 0, 500, 500);
				canvasContext.fillStyle = '#00ff00';
				canvasContext.fillRect(0,120-average,500,500);				
			}
*/

			Room.init(stream);
			stream = URL.createObjectURL(stream);
			Room.joinRoom(1);
		}, function () {
		  $scope.error = 'No audio/video permissions. Please refresh your browser and allow the audio/video capturing.';
		});
	};	    
	
	$scope.$on('VideoChatCtrl.receive', function ($scope, cmd, data) {
		console.log('VideoChatCtrl.receive', cmd, data);
		if(data.method === 'init') {
			console.log('call Room.makeOffer(' + data.client_id + ')');
			Room.makeOffer(data.client_id);
		} else {
			Room.handleMessage(data);
		}
		
	});
    Room.on('peer.stream', function (peer) {
      console.log('Client connected, adding new stream', peer);	  		
      $scope.peers.push({
        id: peer.id,
        stream: URL.createObjectURL(peer.stream)
      });
    });
    Room.on('peer.disconnected', function (peer) {
      console.log('Client disconnected, removing stream');
      $scope.peers = $scope.peers.filter(function (p) {
        return p.id !== peer.id;
      });
    });

    $scope.getLocalVideo = function () {
      return $sce.trustAsResourceUrl(stream);
    };
	
	$scope.startStream = function() {
		$scope.initVideo();
		$scope.myWebCam.isVisible = true;
	};
	
	$scope.stopStream = function() {		
		Room.closeMyStream();
		$scope.myWebCam.isVisible = false;		
	};
  });