/**
 * Takes an image url and refreshes that image so that the browser cache can be refreshed
 * @param {type} param1
 * @param {type} param2
 */
angular.module('app').service('refreshImage', ['$q', function ($q) {
    return function (uri) {
        var deferred = $q.defer();
        //create an iframe
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        //set the url for the image
        iframe.src = uri;

        //listen for the iframe to load or error
        iframe.addEventListener('load', onload, false);
        iframe.addEventListener('error', onerror, false);

        try {
            //add the iframe to the page
            document.body.appendChild(iframe);
        } catch (e) {
            onerror(e);
        }

        return deferred.promise;

        function onload() {
            cleanUp();
            deferred.resolve();
        }

        function onerror(e) {
            cleanUp()
            console.error('Error refreshing image', e);
            deferred.reject(e);
        }

        function cleanUp() {
            iframe.removeEventListener('load', onload, false);
            iframe.removeEventListener('error', onerror, false);
            document.body.removeChild(iframe);
        }

    }
}]);