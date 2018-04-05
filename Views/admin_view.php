<?php
defined('EMONCMS_EXEC') or die('Restricted access');
global $path;
$domain2 = "process_messages";
bindtextdomain($domain2, "Modules/distribution/locale");
bind_textdomain_codeset($domain2, 'UTF-8');
?>
<style>
</style>
<script type="text/javascript" src="<?php echo $path; ?>Modules/distribution/Views/distribution.js"></script>
<link href="<?php echo $path; ?>Modules/distribution/Views/distribution.css" rel="stylesheet">

<div id="distribution">
    <div id="wrapper">
        <div class="page-content" style="padding-top:15px">
            <h1>Admin - Organizations and users</h1>
            <div id="actions">
                <div id="add_organization"><i class="icon-plus"></i>Add Organization</div>
                <div id="add_user"><i class="icon-plus"></i>Add User</div>
            </div>
            <div id="organizations"></div>
            <div class="alert alert-primary" id="no-org-message" role="alert">
                There are no organizations :(
            </div>

        </div>
    </div>
</div>

<!-------------------------------------------------------------------------------------------
MODALS
-------------------------------------------------------------------------------------------->
<!-- Add organization -->
<div id="add-organization-modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="add-organization-modal-label" aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="add-organization-modal-label">Add New Organization</h3>
    </div>
    <div class="modal-body">
        <p>Name:<br>
            <input id="add-organization-name" type="text" maxlength="64">
        </p>
        <div class="alert alert-primary" id="add-organization-message" role="alert"></div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="add-organization-action" class="btn btn-primary">Add</button>
    </div>
</div>

<!-- Add user -->
<div id="add-user-modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="add-user-modal-label" aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="add-user-modal-label">Add New user</h3>
    </div>
    <div class="modal-body">
        <p>Name:<br>
            <input id="add-user-name" type="text" maxlength="64">
        </p>
        <p>Organization:<br>
            <select id="add-user-organizations"></select>
        </p>
        <p>Role:<br>
            <select id="add-user-role">
                <option value="administrator">Administrator</option>
                <option value="prepvol">Prep volunteer</option>
            </select>
        </p>
        <p>Email:<br>
            <input id="add-user-email" type="email" maxlength="64">
        </p>        
        <p>Password:<br>
            <input id="add-user-password" type="password" maxlength="64">
        </p>        
        <p>Confirm password:<br>
            <input id="add-user-confirm-password" type="password" maxlength="64">
        </p>
        <div class="alert alert-primary" id="add-user-message" role="alert"></div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="add-user-action" class="btn btn-primary">Add</button>
    </div>
</div>


<script>
    var path = "<?php echo $path; ?>";
    var organizations = [];
    update_view();
    $('table.users').hide();

    /**************************
     * Functions
     **************************/
    function update_view() {
        $('#organizations').html('');
        organizations = distribution.list_organizations();
        if (organizations === false) {
            $('#no-org-message').show();
            $('#add_user').hide();
        }
        else {
            $('#no-org-message').hide();
            $('#add_user').show();
            organizations.forEach(function (org) {
                var html = '<h2 class="organization" orgid="' + org.id + '" style="cursor:pointer"><i class="icon-chevron-down" style="margin-top:10px"></i> ' + org.name + '</h2>';
                html += '<table class="users table" orgid="' + org.id + '">';
                if (org.users.length == 0)
                    html += '<tr><td colspan=3><div class="alert alert-primary"role="alert">There are no users :(</div></td></tr>';
                else {
                    html += '<tr><th>Name</th><th>Role</th><th></th></tr>';
                    org.users.forEach(function (user) {
                        html += '<tr><td>' + user.name + '</td><td>' + user.role + '</td><td>ToDo - delete and edit user</td></tr>';
                    });
                }
                html += '</table>';
                $('#organizations').append(html);
            });
        }
    }


    /**************************
     * Actions
     **************************/
    $('#distribution').on('click', '#add_organization', function () {
        $('#add-organization-name').val('');
        $('#add-organization-message').hide();
        $('#add-organization-modal').modal('show');
    }
    );
    $('#add-organization-action').on('click', function () {
        $('#add-organization-message').hide();
        var result = distribution.create_organization($('#add-organization-name').val());
        if (result.error != undefined)
            $('#add-organization-message').html(result.error).show();
        else {
            $('#add-organization-modal').modal('hide');
            update_view();
        }
    });
    $('#distribution').on('click', '#add_user', function () {
        $('#add-user-name').val('');
        $('#add-user-message').hide();
        $('select#add-user-organizations').html('');
        organizations.forEach(function (org) {
            $('select#add-user-organizations').append('<option value="' + org.id + '">' + org.name + '</option>');
        });
        $('#add-user-modal').modal('show');
    });
    $('#add-user-action').on('click', function () {
        if ($('#add-user-password').val() != $('#add-user-confirm-password').val())
            $('#add-user-message').html('Passwords don\'t match ').show();
        else {
            $('#add-user-message').hide();
            var result = distribution.create_user($('#add-user-name').val(), $('#add-user-organizations').val(), $('#add-user-email').val(), $('#add-user-password').val(), $('#add-user-role').val());
            if (result.error != undefined)
                $('#add-user-message').html(result.error).show();
            else {
                $('#add-user-modal').modal('hide');
                update_view();
                console.log('yeahhh  ' + result);
            }
        }
    });

    $('#distribution').on('click', '.organization', function () {
        var orgid = $(this).attr('orgid');
        $('table.users[orgid=' + orgid + ']').toggle();
    });

    /*
     $('#distribution').on('click', '', function(){
     
     })
     * 
     */
</script>

