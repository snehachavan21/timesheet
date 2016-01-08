/**
 * Created by amitav on 12/13/15.
 */
myApp.factory('commentFactory', ['$http', function($http) {
    var commentFactory = {};

    commentFactory.getProjectComments = function(projectId) {
        return $http.get(baseUrl + 'api/get-project-comments/' + projectId);
    }

    commentFactory.saveComment = function(commentData) {
        return $http({
            headers: {
                'Content-Type': 'application/json'
            },
            url: baseUrl + 'api/save-project-comment',
            method: 'POST',
            data: commentData
        });
    }

    commentFactory.getTicketComments = function(ticketId) {
        return $http.get(baseUrl + 'api/get-ticket-comments/' + ticketId);
    }

    commentFactory.saveTicketConversation = function(conversationData) {
        return $http({
            headers: {
                'Content-Type': 'application/json'
            },
            url: baseUrl + 'api/save-ticket-conversation',
            method: 'POST',
            data: conversationData
        });
    }

    return commentFactory;
}]);
