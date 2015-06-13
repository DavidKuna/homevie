angular.module('instant-search', [])
	.controller('initialSearchCtrl', function ($scope, $http) {
		$scope.results = [];
		$scope.maxResults = 3;
		
		$scope.init = function(results)
		{		  
		  $scope.maxResults = results;
		};

		$scope.$watch('searchQuery', function (search) {
			console.log(search);
			if (typeof search === 'undefined') {
				$scope.results = [];
			} else {
				var url = 'https://www.googleapis.com/youtube/v3/search';										
				var responsePromise = $http.jsonp( url, 
					{
						params : {
							q: search,
							part: 'snippet',
							key: 'AIzaSyA8rrT_VF7jGNmEyM1pPvCT-6AS_D0xFXk',
							format: 5,
							'max-results': $scope.maxResults,
							alt: "json",
							callback : "JSON_CALLBACK"
						}
					} 
				);

				responsePromise.success(function(response) {
					if(response.pageInfo && response.items) {

						$scope.results = response.items;		
					} else {
						$scope.results = [];
					}		
				});
			}
		});						
	});