angular.module('app').directive('episode', [function () {
        return {
            restrict: 'E',
            scope: {
                episode: '='
            },
            controller: Controller,
            controllerAs: 'vm',
            bindToController: true,
            link: function($scope, element, attributes, vm){
                
            },
            templateUrl: 'episodeDirective.html'
        };
        
        function Controller(){
            var vm = angular.extend(this, {
                //episode
            }, this);
            
            var maxTitleLength = 20;
            //truncate the title 
            
            vm.title = vm.episode.title;
            if(vm.episode.title.length > maxTitleLength){
                vm.title = vm.episode.title.substring(0, 20) + '...';
            }
            
            //calculate the runtime text
            if(typeof vm.episode.runtime === 'number' && vm.episode.runtime > -1){
                vm.runtimeText = '(' + vm.episode.runtime + ')';
            }   
        }
    }
]);