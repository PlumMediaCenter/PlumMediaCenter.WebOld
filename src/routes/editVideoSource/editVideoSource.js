angular.module('app').controller('EditVideoSourceController', [
    '$scope', 'globals', 'VideoSource', '$state', '$stateParams', 'enums',
    function ($scope, globals, VideoSource, $state, $stateParams, enums) {
        var vm = angular.extend(this, {
            isLoading: false,
            isSaving: false,
            //properties
            originalVideoSource: undefined,
            videoSource: {
                securityType: enums.securityType.public,
            },
            //api
            reset: reset,
            save: save,
            sayHi: function () {
                alert('hi');
            },
            isValidatingUrl: () => {
                return vm.form.baseUrl.$pending && vm.form.baseUrl.$pending.urlExists;
            }
        });

        globals.title = 'Edit Video Source';
        vm.originalVideoSource = angular.copy(vm.videoSource);
        loadVideoSource();

        function loadVideoSource() {
            //if an id was provided, go look up the settings for that videoSource
            if ($stateParams.id && $stateParams.id > 0) {
                vm.isLoading = true;
                VideoSource.getById($stateParams.id).then(function (videoSource) {
                    vm.videoSource = videoSource;
                    vm.originalVideoSource = angular.copy(videoSource);
                }).finally(function () {
                    vm.isLoading = false;
                });
            }
        }

        function reset() {
            vm.videoSource = vm.originalVideoSource;
            vm.form.setPristine(true);
        }

        function save() {
            vm.isSaving = true;
            VideoSource.save(vm.videoSource).then(function (videoSource) {
                vm.isSaving = false;
                loadVideoSource();
                notify('Saved video source', 'success');
                $state.go('videoSources', {}, { reload: true });
            }, function () {
                //handle the error
                vm.isSaving = false;
            });
        }
    }]);