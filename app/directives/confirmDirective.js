angular.module('app').directive('confirm', [function() {
        return {
            restrict: 'A',
            link: function(scope, element, attributes, controller) {
                element.on('click', function() {
                    var message = scope.$eval(attributes.confirmMessage);
                    message = message? message: 'Are you sure you want to do that?';
                    //show the confirm modal
                    var confirmed = confirm(message);
                    if (confirmed) {
                        scope.$eval(attributes.confirm);
                    }
                });
            }
        }

    }]);