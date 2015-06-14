angular.module('Homevie')
  .directive('chatAudioPlayer', function ($sce) {
    return {
      template: '<div class="audioStream"><div>{{name()}}</div><audio ng-src="{{trustSrc()}}" autoplay></audio></div>',
      restrict: 'E',
      replace: true,
      scope: {
        vidSrc: '@',
		peerName: '@'
      },
      link: function (scope) {
        console.log('Initializing audio-player', $sce, scope);
        scope.trustSrc = function () {
          if (!scope.vidSrc) {
			  console.log("TRUST AS RESOURCE URL UNDEFINED", scope.vidSrc);
            return undefined;
          }
		  console.log("TRUST AS RESOURCE URL ", scope.vidSrc);
          return $sce.trustAsResourceUrl(scope.vidSrc);
        };
		
		scope.name = function () {
          if (!scope.peerName) {
			  console.log("TRUST AS RESOURCE URL UNDEFINED", scope.peerName);
            return undefined;
          }
          return scope.peerName;
        };
      }
    };
  });