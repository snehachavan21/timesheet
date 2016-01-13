myApp.controller('reportController', ['$scope', 'timeEntry', '$timeout', 'projectFactory', 'userFactory', 'action','$location',
    function($scope, timeEntry, $timeout, projectFactory, userFactory, action,$location) {

        $scope.perPage = 5;
        $scope.page = 0;

        $scope.clientLimit = 250;
        $scope.postUrl = baseUrl+'api/time-report';
        $scope.postData = {};

        $scope.$on('pagination:loadStart', function (event, status, config) {
            $scope.page = event.currentScope.page;
            $scope.perPage = event.currentScope.perPage;
        });

        /*check if clients are loaded*/
        if (action && action.clients != undefined) {
            action.clients.success(function(response) {
                console.log('all clients', response);
                $scope.clients = response;
            });
        }

        userFactory.getUserList().then(function(response) {
            console.log('user list', response.data);
            angular.forEach(response.data, function(value, key) {
                $scope.users.push(value);
            });
            //});
        }).then(function() {
            projectFactory.getProjectList().then(function(response) {
                console.log('project list', response.data);
                angular.forEach(response.data, function(value, key) {
                    $scope.projects.push(value);
                });

                $timeout(function() {
                    $scope.showData = true;
                }, 500);
            });
        });

        angular.extend($scope, {
            totalTime: 0,
            showData: false,
            filters: {},
            users: [],
            projects: [],
            clients: {},
            dt: new Date()
        });

        angular.extend($scope, {
            filterTime: function(filterTimeFrm) {
                console.log($scope.filters);
                var queryParams = {};

                if ($scope.filters.desc != "") {
                    queryParams.desc = $scope.filters.desc;
                }

                if ($scope.filters.users !== undefined && $scope.filters.users.length > 0) {
                    queryParams.users = [];
                    angular.forEach($scope.filters.users, function(value, key) {
                        queryParams.users.push(value.id);
                    });
                }

                if ($scope.filters.clients !== undefined && $scope.filters.clients.length > 0) {
                    queryParams.clients = [];
                    angular.forEach($scope.filters.clients, function(value, key) {
                        queryParams.clients.push(value.id);
                    });
                }

                if ($scope.filters.project !== undefined && $scope.filters.project.length > 0) {
                    queryParams.projects = [];
                    angular.forEach($scope.filters.project, function(value, key) {
                        queryParams.projects.push(value.id);
                    });
                }

                if ($scope.filters.startDate !== undefined) {
                    queryParams.startDate = $scope.filters.startDate;
                }

                if ($scope.filters.endDate !== undefined) {
                    queryParams.endDate = $scope.filters.endDate;
                    var startDateOfYear = moment(queryParams.startDate).dayOfYear();
                    var endDateOfYear = moment(queryParams.endDate).dayOfYear();

                    if ($scope.filters.startDate !== undefined && endDateOfYear < startDateOfYear) {
                        alert('End date is before start date.');
                        return false;
                    }
                }

                $scope.postData.filters = angular.copy(queryParams);
            },
            clearFilters: function() {
                $scope.filters = {};
            }
        });
    }
]);
