angular.module('app').controller('SearchController', ['globals', 'Video', '$stateParams', function(globals, Video, $stateParams) {
        var vm = angular.extend(this, {
            videos: []
        });
        
        globals.title = 'Home';
        var searchTerm = $stateParams.q;
        
        Video.search(searchTerm).then(function(videos){
            vm.videos = videos;
        });
    }]);