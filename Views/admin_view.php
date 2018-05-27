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
            <h1>Admin - Organizations, distribution points and users</h1>
            <div id="actions">
                <div id="add_organization"><i class="icon-plus"></i>Add organization</div>
                <div id="add_user"><i class="icon-plus add-button"></i>Add user</div>
                <div id="add_distribution_point"><i class="icon-plus add-button"></i>Add distribution point</div>
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
            <select id="add-user-organizations" class="organizations-select"></select>
        </p>
        <p>Role:<br>
            <select id="add-user-role" class="roles-select"></select>
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

<!-- Add distribution point -->
<div id="add-distribution-point-modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="add-distribution-point-modal-label" aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="add-distribution-point-modal-label">Add new distribution point</h3>
    </div>
    <div class="modal-body">
        <p>Name:<br>
            <input id="add-distribution-point-name" type="text" maxlength="64">
        </p>
        <p>Organization:<br>
            <select id="add-distribution-point-organizations" class="organizations-select"></select>
        </p>
        <div class="alert alert-primary" id="add-distribution-point-message" role="alert"></div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="add-distribution-point-action" class="btn btn-primary">Add</button>
    </div>
</div>


<!-- Delete item -->
<div id="delete-distribution-point-modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="delete-distribution-point-modal-label" aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="delete-distribution-point-modal-label">Delete distribution point</h3>
    </div>
    <div class="modal-body">
        <p>Are you sure you want to delete the distribution point?</p>
        <div class="alert alert-primary" id="delete-distribution-point-message" role="alert"></div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="delete-distribution-point-ok" class="btn btn-primary">Delete</button>
    </div>
</div>

<script>
    var path = "<?php echo $path; ?>";
    var organizations = [];
    update_view();

    /**************************
     * Functions
     **************************/
    /**
     * Updates the view by laoding all the organizations, if there is none a message is displayed.
     * If there are then: 
     *   - display buttons to add distribution and users
     *   - populate organizations selects in modals (.organizations-select)
     *   - populate roles selects in modals (.roles-select)
     *   - Draw organizations with its users and distributions
     * @returns nothing to return
     */
    function update_view() {
        $('#organizations').html('');
        organizations = distribution.list_organizations();
        if (organizations === false) {
            $('#no-org-message').show();
            $('#add_user').hide();
        } else {
            $('#no-org-message').hide();

            // Show "Add user", "Add distribution" and more if there are more
            $('.add-button').show();

            // Add organizations to selects
            $('select.organizations-select').html('');
            organizations.forEach(function (org) {
                $('select.organizations-select').append('<option value="' + org.id + '">' + org.name + '</option>');
            });
            // Add roles to selects
            $('select.roles-select').html('');
            $('select.roles-select').append('<option value="org_administrator">Organization administrator</option>');
            $('select.roles-select').append('<option value="prepvol">Prep volunteer</option>');

            // Draw organizations
            organizations.forEach(function (org) {
                var html = '<h2 class="organization" orgid="' + org.id + '" style="cursor:pointer"><i class="icon-chevron-down" style="margin-top:10px"></i> ' + org.name + '</h2>';
                // Draw distribution points
                html += '<div orgid="' + org.id + '">';
                html += '<h3>Distribution points</h3>'
                html += '<table class="distribution-points table">';
                if (distribution.any_active_distribution_point(org.distribution_points) == false)
                    html += '<tr><td colspan=3><div class="alert alert-primary"role="alert">There are no distribution points :(</div></td></tr>';
                else {
                    html += '<tr><th>Name</th><th></th></tr>';
                    org.distribution_points.forEach(function (distr) {
                        if (distr.deleted == 0)
                            html += '<tr><td>' + distr.name + '</td><td><i class="icon-trash pointer" id="' + distr.id + '"></i>ToDo - edit distribution point</td></tr>';
                    });
                }
                html += '</table>';
                // Draw users
                html += '<h3>Users</h3>'
                html += '<table class="users table">';
                if (org.users.length == 0)
                    html += '<tr><td colspan=3><div class="alert alert-primary"role="alert">There are no users :(</div></td></tr>';
                else {
                    html += '<tr><th>Name</th><th>Role</th><th></th></tr>';
                    org.users.forEach(function (user) {
                        html += '<tr><td>' + user.name + '</td><td>' + user.role + '</td><td>ToDo - delete and edit user</td></tr>';
                    });
                }
                html += '</table>';
                html += '</div>';
                $('#organizations').append(html);
            });

            // Toggle rganizations if there are more than one
            if (organizations.length === 1)
                $('div[orgid]').show();
            else
                $('div[orgid]').hide();
        }
    }


    /**************************
     * Actions
     **************************/
    $('#distribution').on('click', '#add_organization', function () {
        $('#add-organization-name').val('');
        $('#add-organization-message').hide();
        $('#add-organization-modal').modal('show');
    });

    $('#distribution').on('click', '#add_distribution_point', function () {
        $('#add-distribution-point-name').val('');
        $('#add-distribution-point-message').hide();
        $('#add-distribution-point-modal').modal('show');
    });

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
        $('#add-user-email').val('');
        $('#add-user-password').val('');
        $('#add-user-role').val('prepvol');
        $('#add-user-confirm-password').val('');
        $('#add-user-message').hide();
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
            }
        }
    });

    $('#add-distribution-point-action').on('click', function () {
        $('#add-distribution-point-message').hide();
        var result = distribution.create_distribution_point($('#add-distribution-point-name').val(), $('#add-distribution-point-organizations').val());
        if (result.error != undefined)
            $('#add-distribution-point-message').html(result.error).show();
        else {
            $('#add-distribution-point-modal').modal('hide');
            update_view();
        }
    });

    $('#distribution').on('click', '.organization', function () {
        var orgid = $(this).attr('orgid');
        $('div[orgid=' + orgid + ']').toggle();
        //$('table.users[orgid=' + orgid + ']').toggle();
    });

    $('#distribution').on('click', '.distribution-points .icon-trash', function () {
        $('#delete-distribution-point-message').hide();
        $('#delete-distribution-point-ok').attr('id', $(this).attr('id'));
        $('#delete-distribution-point-modal').modal('show');
    });

    $('#delete-distribution-point-ok').on('click', function () {
        var distroid = $(this).attr('id');
        var result = distribution.delete_distribution_point(distroid);
        if (result == true) {
            update_view();
            $('#delete-distribution-point-modal').modal('hide');
        } else
            $('#delete-distribution-point-message').html(result.error).show();
    });

    /*
     $('#distribution').on('click', '', function(){
     
     })
     * 
     */
</script>

