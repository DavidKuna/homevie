angular.module('instant-search', [])
	.controller('initialSearchCtrl', function ($scope, $http) {
		$scope.results = [];
		$scope.maxResults = 3;
		
		$scope.init = function(results)
		{		  
		  $scope.maxResults = results;
		};

		$scope.$watch('searchQuery', function (search) {			
			var url = 'http://gdata.youtube.com/feeds/api/videos';										
			var responsePromise = $http.jsonp( url, 
				{ 
					params : {
						q: search,
						v: 2,
						format: 5,
						'max-results': $scope.maxResults,
						alt: "jsonc",
						callback : "JSON_CALLBACK"
					}
				} 
			);

			responsePromise.success(function(response) {
				if(response.data.items) {
					$scope.results = response.data.items;					
				} else {
					$scope.results = [];
				}		
			});													
		});						
	});