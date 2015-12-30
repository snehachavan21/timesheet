/**
 * Created by amitav on 12/29/15.
 */
myApp.controller('ticketController', ['$scope', 'action', 'ticketFactory', '$location', 'snackbar',
    function($scope, action, ticketFactory, $location, snackbar) {

        /*check if projects are loaded*/
        if (action && action.projects != undefined) {
            action.projects.success(function(response) {
                console.log('all projects', response);
                $scope.projects = response;
                $scope.showTicketForm = true;
            });
        }

        /*check if users are loaded*/
        if (action && action.users != undefined) {
            action.users.success(function(response) {
                console.log('all users', response);
                $scope.users = response;
            });
        }

        /*load ticket type*/
        if (action && action.type != undefined) {
            action.type.success(function(response) {
                console.log('all type', response);
                $scope.ticketType = response;
            });
        }

        /*load tickets*/
        if (action && action.tickets != undefined) {
            action.tickets.success(function(response) {
                console.log('all tickets', response);
                $scope.tickets = response;
                $scope.viewTickets = true;
            });
        }

        /*loading single ticket*/
        if (action && action.ticket != undefined) {
            $scope.showTicketForm = false;
            action.ticket.success(function(response) {
                console.log('thisTicket', response);
                $scope.newTicket = response.data;
                $scope.showTicketForm = true;
            });
        }

        /*model*/
        angular.extend($scope, {
            formUrl: baseUrl + 'templates/tickets/ticket-form.html',
            showTicketForm: false,
            newTicket: {
                type: 'none'
            },
            projects: {},
            ticketType: {},
            tickets: {},
            viewTickets: true
        });

        /*methods*/
        angular.extend($scope, {
            saveNewTicket: function(addTicketForm) {
                if (addTicketForm.$valid) {
                    // console.log($scope.newTicket);
                    var ticketData = {
                        title: $scope.newTicket.title,
                        description: $scope.newTicket.comment,
                        complete_date: $scope.newTicket.completeDate,
                        project_id: $scope.newTicket.project[0].id,
                        assigned_to: $scope.newTicket.users[0].id,
                        followers: [],
                        type: $scope.newTicket.type
                    };

                    /*Adding follower ids*/
                    angular.forEach($scope.newTicket.followers, function(value, key) {
                        ticketData.followers.push(value.id);
                    });

                    ticketFactory.saveTicket(ticketData).success(function(response) {
                        console.log(response);
                        $location.path('/ticket/list');
                        snackbar.create("New ticket added.", 1000);
                    });
                }
            },
            updateTicket: function(updateTicketForm) {
                if (updateTicketForm.$valid) {
                    console.log($scope.newTicket);
                }
            }
        });

    }
]);
