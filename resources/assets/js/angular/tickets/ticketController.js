/**
 * Created by amitav on 12/29/15.
 */
myApp.controller('ticketController', ['$scope', 'action', 'ticketFactory', '$location', 'snackbar', '$routeParams', 'commentFactory', 'hotkeys',
    function($scope, action, ticketFactory, $location, snackbar, $routeParams, commentFactory, hotkeys) {

        /*Adding hotkeys*/
        hotkeys.add({
            combo: 'ctrl+s+d',
            description: 'This one goes to 11',
            callback: function() {
                $scope.saveNewConversation();
            }
        });

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

        /*load ticket status*/
        if (action && action.status != undefined) {
            action.status.success(function(response) {
                console.log('all status', response);
                $scope.ticketStatus = response;
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

        /*loading user's tickets*/
        if (action && action.myTickets != undefined) {
            action.myTickets.success(function(response) {
                console.log('myTickets', response);
                $scope.myTickets = response.data;
                $scope.viewMyTickets = true;
            });
        }

        if (action && action.ticketsFollowing != undefined) {
            action.ticketsFollowing.success(function(response) {
                console.log('tickets following', response);
                $scope.ticketsFollowing = response.data;
                $scope.viewTicketsFollowing = true;
            });
        }

        /*check if the ticket time entries have loaded*/
        if (action && action.timeEntries != undefined) {
            action.timeEntries.success(function(response) {
                console.log('tickets time entries', response);
                $scope.timeEntries = response;
                $scope.showTicketTimeEntries = true;
                $scope.ticketTotalTime = 0;
                angular.forEach($scope.timeEntries, function(value, key) {
                    $scope.ticketTotalTime = $scope.ticketTotalTime + parseFloat(value.time);
                });
            });
        }

        /*loading ticket comments*/
        if (action && action.comments != undefined) {
            action.comments.success(function(response) {
                console.log('ticket comments', response);
                $scope.ticketComments = response.data;
                $scope.showComments = true;
            });
        }

        /**
         * This check is required to get the active link on the tab 
         * because ticket id is coming after the $http request
         * and so the route function does not get ticket id
         */
        if ($routeParams.ticketId != undefined) {
            $scope.ticketNum = $routeParams.ticketId;
        }

        /*model*/
        angular.extend($scope, {
            formUrl: baseUrl + 'templates/tickets/ticket-form.html',
            showTicketForm: false,
            newTicket: {
                type: 'none',
                status: 'none'
            },
            projects: {},
            ticketType: {},
            ticketStatus: {},
            tickets: {},
            myTickets: {},
            timeEntries: {},
            showTicketTimeEntries: false,
            viewMyTickets: false,
            showComments: false,
            viewTickets: true,
            viewTicketsFollowing: false,
            newConversation: ""
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
                        type: $scope.newTicket.type,
                        status: $scope.newTicket.status
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
                    var ticketData = {
                        title: $scope.newTicket.title,
                        description: $scope.newTicket.comment,
                        complete_date: $scope.newTicket.completeDate,
                        project_id: $scope.newTicket.project[0].id,
                        assigned_to: $scope.newTicket.users[0].id,
                        followers: [],
                        type: $scope.newTicket.type,
                        status: $scope.newTicket.status,
                        id: $routeParams.ticketId
                    };

                    /*Adding follower ids*/
                    angular.forEach($scope.newTicket.followers, function(value, key) {
                        ticketData.followers.push(value.id);
                    });

                    ticketFactory.updateTicket(ticketData).success(function(response) {
                        console.log(response);
                        $location.path('/ticket/list');
                        snackbar.create("Ticket updated.", 1000);
                    });
                }
            },
            saveNewConversation: function() {
                if ($scope.newConversation != "") {
                    var data = {
                        comment: $scope.newConversation,
                        ticketId: $routeParams.ticketId
                    };

                    commentFactory.saveTicketConversation(data).success(function(response) {
                        console.log('Conversation saved');
                        console.log(response);
                        $scope.newConversation = "";
                        $scope.ticketComments = response.data;
                    });
                } else {
                    snackbar.create("Add some text before saving the discussion.", 1000);
                }
            }
        });

    }
]);
