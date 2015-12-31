var myApp = angular.module('myApp', [
    'ngRoute',
    'ngCookies',
    'oi.select',
    '720kb.datepicker',
    'chart.js',
    'angular.snackbar',
    'angular-loading-bar',
    'textAngular'
]);

myApp.run(['userFactory', '$cookies', '$rootScope', '$location',
    function(userFactory, $cookies, $rootScope, $location) {
        /*check if auth object is available*/
        if ($cookies.get('userObj') === undefined) {
            userFactory.getUserObj().success(function(response) {
                console.log('created user object', response);
                $cookies.putObject('userObj', response);
            });
        }

        /*handling the route change to check if the current url is access based*/
        $rootScope.$on("$routeChangeStart",
            function(event, next, current) {
                if (next.$$route.roles !== undefined) {
                    var access = false;
                    var userObj = $cookies.getObject('userObj');
                    console.log('userObj', userObj);
                    angular.forEach(next.$$route.roles, function(roleValue, roleKey) {
                        console.log(roleValue);
                        angular.forEach(userObj.roles, function(userValue, userKey) {
                            console.log(userValue);
                            if (roleValue == userValue.roleName) {
                                access = true;
                            }
                        });
                    });

                    console.log(access);
                    if (access == false) {
                        $location.path('access-denied');
                    }
                }
            });
    }
]);

myApp.filter('unsafe', function($sce) {
    return $sce.trustAsHtml;
});

myApp.controller('globalController', ['$scope', '$location',
    function($scope, $location) {
        angular.extend($scope, {
            reportTabUrl: '/templates/manager/reportTabs.html',
            singleProjectTab: '/templates/projects/singleProjectTab.html',
            checkActiveLink: function(currLink) {
                if ($location.path() == currLink) {
                    return 'active';
                }
            },
            timeAgo: function(string) {
                return moment(string).fromNow();
            },
            momentTime: function(string, format) {
                return moment(string).format(format);
            }
        })
    }
]);

/*Routes*/
myApp.config(['$routeProvider', '$locationProvider',
    function($routeProvider, $locationProvider) {
        $routeProvider.when('/', {
            templateUrl: '/templates/manager/managerReports.html',
            controller: 'dashboardController'
        });

        $routeProvider.when('/logout', {
            templateUrl: '/templates/users/user-logout.html',
            controller: 'logoutController'
        });

        $routeProvider.when('/access-denied', {
            templateUrl: '/templates/admin/access-denied.html',
            controller: 'dashboardController'
        });

        $routeProvider.when('/report', {
            templateUrl: '/templates/manager/reports.html',
            controller: 'reportController',
            roles: ['Admin', 'Project Manager'],
            resolve: {
                action: function(clientFactory) {
                    return {
                        clients: clientFactory.getClientList()
                    }
                }
            }
        });

        $routeProvider.when('/projects', {
            templateUrl: '/templates/projects/projects-listing.html',
            controller: 'projectController',
            resolve: {
                action: function(projectFactory) {
                    return {
                        projects: projectFactory.getProjectList()
                    }
                }
            }
        });

        $routeProvider.when('/projects/add', {
            templateUrl: '/templates/projects/add-project.html',
            controller: 'projectController',
            roles: ['Admin', 'Project Manager'],
            resolve: {
                action: function(clientFactory) {
                    return {
                        clients: clientFactory.getClientList()
                    }
                }
            }
        });

        $routeProvider.when('/projects/:id', {
            templateUrl: '/templates/projects/projects-details.html',
            controller: 'projectController',
            resolve: {
                action: function() {
                    return 'single';
                }
            }
        });

        $routeProvider.when('/projects/:pid/comments', {
            templateUrl: '/templates/projects/project-comments.html',
            controller: 'projectController',
            roles: ['Admin', 'Project Manager'],
            resolve: {
                action: function(commentFactory, $route) {
                    return {
                        comments: commentFactory.getProjectComments($route.current.params.pid)
                    };
                }
            },
            roles: ['Admin', 'Project Manager']
        });

        $routeProvider.when('/projects/:id/estimate/add', {
            templateUrl: '/templates/projects/project-estimate-add.html',
            controller: 'projectController',
            resolve: {
                action: function() {
                    return 'single';
                }
            },
            roles: ['Admin', 'Project Manager']
        });

        $routeProvider.when('/projects/estimate/:estimateId', {
            templateUrl: '/templates/projects/estimate-edit.html',
            controller: 'projectController',
            resolve: {
                action: function() {
                    return 'single';
                }
            },
            roles: ['Admin', 'Project Manager']
        });

        /*Management URLs*/
        $routeProvider.when('/manage/back-date-entry', {
            templateUrl: '/templates/admin/backdateentry.html',
            controller: 'adminController',
            resolve: {
                action: function(userFactory, timeEntry) {
                    return {
                        users: userFactory.getUserList(),
                        allEntries: timeEntry.getBackDateEntries()
                    };
                }
            },
            roles: ['Admin', 'Project Manager']
        });

        $routeProvider.when('/manage/view-back-date-entry/:backdateentryId', {
            templateUrl: '/templates/admin/view-backdateentry.html',
            controller: 'adminController',
            resolve: {
                action: function(userFactory, timeEntry) {
                    return {
                        users: userFactory.getUserList(),
                        allEntries: timeEntry.getBackDateEntries()
                    };
                }
            },
            roles: ['Admin', 'Project Manager']
        });

        $routeProvider.when('/manage/view-back-date-entry/:backdateentryId', {
            templateUrl: '/templates/admin/view-backdateentry.html',
            controller: 'adminController',
            resolve: {
                action: function(userFactory, timeEntry) {
                    return {
                        users: userFactory.getUserList(),
                        allEntries: timeEntry.getBackDateEntries()
                    };
                }
            },
            roles: ['Admin', 'Project Manager']
        });

        $routeProvider.when('/user/request-backdate-entry', {
            templateUrl: '/templates/users/request-backdate.html',
            controller: 'userController',
            resolve: {
                action: function(userFactory, timeEntry) {
                    return {
                        users: userFactory.getUserListByRole(),
                        allEntries: timeEntry.getRequestBackDateEntries()
                    };

                }
            }
        });

        $routeProvider.when('/user/view-request-backdate/:backdateentryId', {
            templateUrl: '/templates/users/view-request-backdate.html',
            controller: 'userController',
            resolve: {
                action: function(userFactory, timeEntry, $route) {
                    return {
                        singleEntry: timeEntry.getRequestBackDateEntriesById($route.current.params.backdateentryId)

                    };
                }
            }
        });

        /*Ticket section*/
        $routeProvider.when('/ticket/list', {
            templateUrl: '/templates/tickets/list-ticket.html',
            controller: 'ticketController',
            roles: ['Admin', 'Project Manager'],
            resolve: {
                action: function(projectFactory, userFactory, ticketFactory) {
                    return {
                        tickets: ticketFactory.getAllTickets(),
                        projects: projectFactory.getProjectList(),
                        users: userFactory.getUserList(),
                        type: ticketFactory.getTickeType()
                    }
                }
            }
        });

        $routeProvider.when('/ticket/add', {
            templateUrl: '/templates/tickets/add-ticket.html',
            roles: ['Admin', 'Project Manager'],
            controller: 'ticketController',
            resolve: {
                action: function(projectFactory, userFactory, ticketFactory) {
                    return {
                        projects: projectFactory.getProjectList(),
                        users: userFactory.getUserList(),
                        type: ticketFactory.getTickeType()
                    }
                }
            }
        });

        $routeProvider.when('/ticket/view/:ticketId', {
            templateUrl: '/templates/tickets/view-ticket.html',
            controller: 'ticketController',
            resolve: {
                action: function(projectFactory, userFactory, ticketFactory, $route) {
                    return {
                        projects: projectFactory.getProjectList(),
                        users: userFactory.getUserList(),
                        type: ticketFactory.getTickeType(),
                        ticket: ticketFactory.getTicketById($route.current.params.ticketId)
                    }
                }
            }
        });

        $routeProvider.when('/ticket/my-tickets', {
            templateUrl: '/templates/tickets/my-tickets.html',
            controller: 'ticketController',
            resolve: {
                action: function(ticketFactory) {
                    return {
                        myTickets: ticketFactory.getMyTickets(),
                    }
                }
            }
        });

        $routeProvider.otherwise('/');
    }
]);
