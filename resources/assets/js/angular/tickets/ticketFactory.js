myApp.factory('ticketFactory', ['$http', function($http) {
    var ticketFactory = {};

    ticketFactory.saveTicket = function(ticketData) {
        return $http({
            headers: {
                'Content-Type': 'application/json'
            },
            url: baseUrl + 'api/save-new-ticket',
            method: 'POST',
            data: ticketData
        });
    }

    ticketFactory.updateTicket = function(ticketData) {
        return $http({
            headers: {
                'Content-Type': 'application/json'
            },
            url: baseUrl + 'api/update-ticket',
            method: 'POST',
            data: ticketData
        });
    }

    ticketFactory.getAllTickets = function() {
        return $http.get(baseUrl + 'api/get-ticket');
    }

    ticketFactory.getTicketById = function(id) {
        return $http.get(baseUrl + 'api/get-ticket-by-id/' + id);
    }

    ticketFactory.getTickeType = function() {
        return $http.get(baseUrl + 'api/get-ticket-types');
    }

    ticketFactory.getTickeStatus = function() {
        return $http.get(baseUrl + 'api/get-ticket-status');
    }

    ticketFactory.getMyTickets = function() {
        return $http.get(baseUrl + 'api/get-my-tickets');
    }

    ticketFactory.getTicketsFollowing = function() {
        return $http.get(baseUrl + 'api/get-tickets-following');
    }

    ticketFactory.getTicketTimeEntries = function(id) {
        return $http.get(baseUrl + 'api/get-tickets-time-entries/' + id);
    }

    return ticketFactory;
}])
