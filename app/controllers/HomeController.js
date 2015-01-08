angular.module('app').controller('HomeController', ['globals', 'Video', function(globals, Video) {
        var vm = this;
        globals.title = 'Home';
        
        Video.getAll().then(function(videos){
            vm.videos = videos;
        });
    }]);