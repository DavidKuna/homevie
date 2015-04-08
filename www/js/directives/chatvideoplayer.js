angular.module('Homevie')
  .directive('chatVideoPlayer', function ($sce) {
    return {
      template: '<div><video ng-src="{{trustSrc()}}" autoplay></video></div>',
      restrict: 'E',
      replace: true,
      scope: {
        vidSrc: '@'
      },
      link: function (scope) {
        console.log('Initializing video-player', $sce, scope);
        scope.trustSrc = function () {
          if (!scope.vidSrc) {
			  console.log("TRUST AS RESOURCE URL UNDEFINED", scope.vidSrc);
            return undefined;
          }
		  console.log("TRUST AS RESOURCE URL ", scope.vidSrc);
          return $sce.trustAsResourceUrl(scope.vidSrc);
        };
      }
    };
  });