$(document).ready(function () {
//change source of video
	$(".js_loadedMovies").on("click", ".appPlay", function () {
		source = $(this).attr("data-source");
		source = "https://www.youtube.com/watch?v="+source;
		
//			myScope = angular.element($("#VideoCtrl")).scope();
			myScope = angular.element($("#videoplayer")).scope();
			a = myScope.setSourceURL(source);
		
	});

});