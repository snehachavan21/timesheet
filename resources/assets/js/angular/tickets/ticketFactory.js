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

    ticketFactory.getAllTickets = function() {
        return $http.get(baseUrl + 'api/get-ticket');
    }

    ticketFactory.getTicketById = function(id) {
        return $http.get(baseUrl + 'api/get-ticket-by-id/' + id);
    }

    ticketFactory.getTickeType = function() {
        return $http.get(baseUrl + 'api/get-ticket-types');
    }

    return ticketFactory;
}])
