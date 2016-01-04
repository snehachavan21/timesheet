myApp.controller('logoutController', ['$scope', 'userFactory',
    function($scope, userFactory) {
        userFactory.logoutUser().success(function(response) {
            console.log('logout', response);
            window.location = baseUrl;
        });
    }
]);
