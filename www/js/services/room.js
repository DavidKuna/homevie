/* global RTCIceCandidate, RTCSessionDescription, RTCPeerConnection, EventEmitter */
'use strict';

/**
 * @ngdoc service
 * @name publicApp.Room
 * @description
 * # Room
 * Factory in the publicApp.
 */
angular.module('Homevie')
  .factory('Room', function ($rootScope, $q, eventEmitter) {

    var iceConfig = { 'iceServers': [{ 'url': 'stun:stun.l.google.com:19302' }]},
        peerConnections = {},
        currentId, roomId,
        stream;
		
	var wsCmd = 'videoChat';
	var connected = false;

    function getPeerConnection(id) {
      if (peerConnections[id]) {
        return peerConnections[id];
      }
      var pc = new RTCPeerConnection(iceConfig);
      peerConnections[id] = pc;
	  console.log("pc.addStream", stream);
      pc.addStream(stream);
      pc.onicecandidate = function (evnt) {
		var data = { by: currentId, to: id, ice: evnt.candidate, type: 'ice' };
		$rootScope.$broadcast('MessageCtrl.send', wsCmd, data);      
      };
      pc.onaddstream = function (evnt) {
        console.log('Received new stream', evnt);
        api.emit('peer.stream', {
          id: id,
          stream: evnt.stream
        });
		if (!$rootScope.$$digest) {
			$rootScope.$apply();
		}
      };
      return pc;
    }

    function makeOffer(id) {
      var pc = getPeerConnection(id);
      pc.createOffer(function (sdp) {
        pc.setLocalDescription(sdp);
        console.log('Creating an offer for', id);
        var data = {method: 'msg', by: currentId, to: id, sdp: sdp, type: 'sdp-offer' };
		$rootScope.$broadcast('MessageCtrl.send', wsCmd, data); 
      }, function (e) {
        console.log(e);
      },
      { mandatory: { OfferToReceiveVideo: true, OfferToReceiveAudio: true }});
    }

    function handleMessage(data) {
		console.log("handleMessage", data);
      var pc = getPeerConnection(data.client_id);	  
      switch (data.type) {
        case 'sdp-offer':
          pc.setRemoteDescription(new RTCSessionDescription(data.sdp), function () {
            console.log('Setting remote description by offer');
			pc.createAnswer(function (sdp) {
            pc.setLocalDescription(sdp);              
			var msg = {method: 'msg', by: currentId, to: data.client_id, sdp: sdp, type: 'sdp-answer' };
			$rootScope.$broadcast('MessageCtrl.send', wsCmd, msg);
            });
          });
          break;
        case 'sdp-answer':
			console.log('SDP-ANSWER', data, pc);
          pc.setRemoteDescription(new RTCSessionDescription(data.sdp), function () {
            console.log('Setting remote description by answer');
          }, function (e) {
            console.error(e);
          });
          break;
        case 'ice':
          if (data.ice) {
            console.log('Adding ice candidates');
            pc.addIceCandidate(new RTCIceCandidate(data.ice));
          }
          break;
      }
    }

    var api = {
      joinRoom: function (r) {
        if (!connected) {
      
		  
		  var videochat = {
			cmd: 'videoChat',
			data: {
				method: 'init',
				params: {
					id: 1
				}
			}
		};
		$rootScope.$broadcast('MessageCtrl.send', videochat.cmd, videochat.data);
		//$scope.$broadcast('VideoChatCtrl.receive', videochat.cmd, videochat.data);
		
          connected = true;
        }
      },
      createRoom: function () {
        var d = $q.defer();
		connected = true;
        return d.promise;
      },
      init: function (s) {
        stream = s;
      },
	  handleMessage: function(data) {
		  handleMessage(data);
	  },
	  makeOffer: function(id) {
		makeOffer(id);
	  }
    };
	
    eventEmitter.inject(api);

    return api;
  });