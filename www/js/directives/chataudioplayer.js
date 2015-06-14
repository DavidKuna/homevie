angular.module('Homevie')
  .directive('chatAudioPlayer', function ($sce) {
    return {
      template: '<div class="audioStream" data-id="{{clientId()}}"><div>{{name()}}</div><audio ng-src="{{trustSrc()}}" autoplay></audio>' + 
				'<canvas></canvas></div>',
      restrict: 'E',
      replace: true,
      scope: {
        vidSrc: '@',
		peerName: '@',
		peerId: '@'
      },
      link: function (scope) {
        console.log('Initializing audio-player', $sce, scope);
        scope.trustSrc = function () {
          if (!scope.vidSrc) {
			  console.log("TRUST AS RESOURCE URL UNDEFINED", scope.vidSrc);
            return undefined;
          }
          return $sce.trustAsResourceUrl(scope.vidSrc);
        };
		
		scope.name = function () {
          if (!scope.peerName) {
            return undefined;
          }
          return scope.peerName;
        };
		
		scope.clientId = function () {
          if (!scope.peerId) {			 
            return 0;
          }
          return scope.peerId;
        };
      }
    };
  });