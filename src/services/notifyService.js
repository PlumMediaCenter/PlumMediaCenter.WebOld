angular.module('app').service('notify', function() {
    return notify;
});

 function notify(message, type) {
        type = type ? type : 'warning';
        //if danger was provided, convert to error
        type = type === 'danger' ? 'error' : type;
        
        return new PNotify({
            title: '',
            text: message,
            styling: 'bootstrap3',
            type: type,
            buttons: {
                closer: true
            }
        });
    }