(function() {
    /*Populating the estimate drop down for a project on Time tracker add page*/
    $("#project-select").change(function() {
        var projectId = $(this).val();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: "POST",
            url: baseUrl + 'project/get-estimates',
            cache: false,
            data: {
                project_id: projectId
            }
        }).done(function(result) {
            $("#estimate-wrapper").html(result);
        });
    });

    /*User delete confirm and actual redirect code*/
    $('a.user-delete').click(function() {
        var deleteConfirm = confirm("Are you sure?");
        var userId = $(this).data('user-id');
        if (deleteConfirm == true) {
            window.location.replace(baseUrl + 'user/delete/' + userId);
        }
    });
    /*Client delete confirm and actual redirect code*/
    $("a.delete-client").click(function(){
        var deleteConfirm = confirm("Are you sure you want to delete this client?");
        var clientId = $(this).data('id');
        if (deleteConfirm == true) {
            window.location.replace(baseUrl + 'clients/delete/' + clientId);
        }
    });
    /*Project delete confirm and actual redirect code*/
    $("a.delete-project").click(function(){
        var deleteConfirm = confirm("Are you sure you want to delete this project?");
        var projectId = $(this).data('id');
        if (deleteConfirm == true) {
            window.location.replace(baseUrl + 'project/delete/' + projectId);
        }
    });
    /*Role delete confirm and actual redirect code*/
    $("a.delete-role").click(function(){
        var deleteConfirm = confirm("Are you sure you want to delete this role?");
        var roleId = $(this).data('id');
        if (deleteConfirm == true) {
            window.location.replace(baseUrl + 'role/delete/' + roleId);
        }
    });

    $('.delete-tracker').click(function() {
        var deleteConfirm = confirm("Are you sure?");
        if (deleteConfirm == true) {
            var trackerId = $(this).data('tracker-id');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: baseUrl + 'time-tracker-delete',
                cache: false,
                data: {
                    trackerId: trackerId
                }
            }).success(function(result) {
                $("#tracker-" + trackerId).remove();
            });
        }
    });
})();
