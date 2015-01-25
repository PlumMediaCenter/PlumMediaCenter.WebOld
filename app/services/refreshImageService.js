/**
 * Takes an image url and refreshes that image so that the browser cache can be refreshed
 * @param {type} param1
 * @param {type} param2
 */
angular.module('app').service('refreshImage', ['$q', function($q) {
        function refreshImage(uri) {
            var deferred = $q.defer();
            var reload = function() {
                // Force a reload of the iframe
                this.contentWindow.location.reload(true);

                // Remove `load` event listener and remove iframe
                this.removeEventListener('load', reload, false);
                this.parentElement.removeChild(this);

               deferred.resolve();
            };

            var iframe = document.createElement('iframe');
            iframe.style.display = 'none';

            // Reload iframe once it has loaded
            iframe.addEventListener('load', reload, false);

            // Only call callback if error occured while loading
            iframe.addEventListener('error', deferred.reject, false);
            iframe.src = uri;
            document.body.appendChild(iframe);
            return deferred.promise;
        }

        return refreshImage;
    }]);