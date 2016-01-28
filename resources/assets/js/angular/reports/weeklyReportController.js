myApp.controller('weeklyReportController', ['$scope', 'timeEntry', '$timeout', 'projectFactory', 'userFactory', 'action','$location',
    function($scope, timeEntry, $timeout, projectFactory, userFactory, action,$location) {
        $scope.perPage = 100;
        $scope.page = 0;

        $scope.clientLimit =250;
        $scope.postUrl = baseUrl+'manager/weekly-report-search';
        $scope.postData = {};

        $scope.$on('pagination:loadStart', function (event, status, config) {
            $scope.page = event.currentScope.page;
            $scope.perPage = event.currentScope.perPage;
        });



        userFactory.getUserList().then(function(response) {
            console.log('user list', response.data);
            angular.forEach(response.data, function(value, key) {
                $scope.users.push(value);
            });
            $timeout(function() {
                $scope.showData = true;
            }, 500);
            });


        angular.extend($scope, {
            totalTime: 0,
            showData: false,
            filters: {},
            users: [],
            projects: [],
            clients: {},
            dt: new Date(),
            csrf: csrf
        });

        angular.extend($scope, {
            filterWeeklyReport: function(filterWeeklyFrm) {
                console.log($scope.filters);
                var queryParams = {};


                if ($scope.filters.users !== undefined && $scope.filters.users.length > 0) {
                    queryParams.users = [];
                    angular.forEach($scope.filters.users, function(value, key) {
                        queryParams.users.push(value.id);
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
                $scope.postFormFilters = angular.toJson($scope.postData.filters); // as download does not require "xls" param
            },
            clearFilters: function() {
                $scope.filters = {};
            }
        });
    }
]);