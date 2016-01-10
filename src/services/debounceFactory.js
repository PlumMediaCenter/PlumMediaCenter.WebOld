angular.module('app').factory('debounce', ['$rootScope', function ($rootScope) {

        var registry = [];

        function registryIndexOf(identifier) {
            for (var i in registry) {
                var registryItem = registry[i];
                if (registryItem.identifier === identifier) {
                    return i;
                }
            }
            return -1;
        }

        function getFromRegistry(identifier) {
            var idx = registryIndexOf(identifier);
            return registry[idx];
        }

        function setRegistryItem(identifier, callback, expirationMilliseconds) {
            var registryItemIndex = registryIndexOf(identifier);
            var registryItem;
            //if there is no item in the registry with this identifier, make a new item
            if (registryItemIndex === -1) {
                registryItem = {
                    identifier: identifier,
                    callback: callback,
                    expirationDate: undefined
                };
            } else {
                //get the item from the registry
                registryItem = registry[registryItemIndex];
            }

            registryItem.expirationDate = new Date(Date.now() + expirationMilliseconds);

            if (registryItemIndex === -1) {
                registry.push(registryItem);
            }
            return registryItem;
        }

        function debounce(identifier, callback, expirationMilliseconds) {
            expirationMilliseconds = typeof expirationMilliseconds === 'number' ? expirationMilliseconds : 300;

            var registryItem = setRegistryItem(identifier, callback, expirationMilliseconds);


            //set a timeout and then see if the debounce has expired yet
            setTimeout(function () {
                if (registryItem.expirationDate < Date.now()) {
                    //the debounce has expired. call the callback
                    try {
                        $rootScope.$apply(function () {
                            registryItem.callback();
                        });
                    } catch (e) {
                    }
                    try {
                        //remove this item from the registry
                        registry.splice(registry.indexOf(registryItem), 1);
                    } catch (e) {

                    }
                } else {
                    //do nothing. something else has bumped the expiration date, so let that one's timeout handle it
                }
            }, expirationMilliseconds + 5);
        }
        return debounce;
    }
]);