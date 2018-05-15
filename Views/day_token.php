<?php
defined('EMONCMS_EXEC') or die('Restricted access');
$domain2 = "process_messages";
bindtextdomain($domain2, "Modules/distribution/locale");
bind_textdomain_codeset($domain2, 'UTF-8');
?>
<style>
</style>

<div>
    <h1 style='margin:75px 25px 0'>Today's token is</h1>
    <h1 style='margin-left:50px'><?php echo $args['day_token'] ?></h1>
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


<script>
    // Initialize variables
    var path = "<?php echo $path; ?>";
    var items = distribution.get_items();
    var organizationid = <?php echo $args['organizationid'] ?>;
    var organizations = <?php echo json_encode($args['organizations']) ?>;
    var distributionid = 0; // Used on step 2
    var today_preparation = {}; // Used on step 2

    // Add distribution points
    organizations.forEach(function (org) {
        $('#distribution-points').append('<h2>' + org.name + '</h2>');
        org.distribution_points.forEach(function (distro_point) {
            var html = '<p class="distribution-point" distribution_id="' + distro_point.id + '" organization_id="' + org.id + '" name="' + distro_point.name + '" organization="' + org.name + '">' + distro_point.name + '</p>';
            $('#distribution-points').append(html);
        });
    });

    // Show Step 1 - Choose distribution point
    $('#step1').show();
    $('#step2').hide();

    // Development
    //$('p[distribution_id="1"]').click();

    /*******************
     * Actions
     *******************/
    $('#preparation').on('click', '.distribution-point', function () {
        // Variables
        distributionid = $(this).attr('distribution_id');
        organizationid = $(this).attr('distribution_id');

        // Add title
        $('#step2').prepend('<h2>' + $(this).attr('organization') + ' - ' + $(this).attr('name') + '</h2>');
        var yesterday = distribution.get_yesterday_preparation(distributionid);
        var today_prep = distribution.get_today_preparation(distributionid);

        // Draw preparation table
        for (var item in yesterday) {
            var itemid = 1.0 * yesterday[item].itemid;
            var type = items[itemid].regular != 0 ? 'regular' : 'non-regular';
            var html = '<tr itemid=' + itemid + '>';
            html += '<td>' + items[itemid].name + '</td>';
            html += '<td>' + yesterday[item].quantity_out + '</td>';
            html += '<td><input type="number" source="yesterday-returned-item" itemid=' + itemid + ' min=0 value=' + yesterday[item].quantity_returned + ' /></td>';
            if (today_prep[itemid] == undefined)
                html += '<td><input type="number" source="today-preparation-item" itemid=' + itemid + ' min=0 value="" /></td>';
            else
                html += '<td><input type="number" source="today-preparation-item" itemid=' + itemid + ' min=0 value=' + today_prep[itemid].quantity_out + ' /></td>';
            html += '</tr>';
            $('#preparation-' + type + '-items').append(html);
        }

        // Add last week distribution quantities
        // ToDo

        // trigger auto-update
        setInterval(function () {
            update_preparation();
        }, 2000);

        $('#step1').hide();
        $('#step2').show();
    });

    $('#preparation').on('change', 'input', function () {
        if ($(this).attr('source') == 'yesterday-returned-item')
            var result = distribution.save_returned_item($(this).val(), $(this).attr('itemid'), distributionid);
        else
            var result = distribution.save_going_out_item($(this).val(), $(this).attr('itemid'), distributionid);
        if (result === false) {
            $(this).val('');
            window.alert('There was a problem saving your last change');
        }
    });


    /***************
     * Functions
     ***************/
    function update_preparation() {
        var items = distribution.get_items(); // update the list of itmes in case somebody has added one in the meantime
        var yesterday_preparation = distribution.get_yesterday_preparation(distributionid);
        var today_preparation = distribution.get_today_preparation(distributionid);
        for (var item in yesterday_preparation) {
            if (yesterday_preparation[item].quantity_returned != $('[source="yesterday-returned-item"][itemid="' + yesterday_preparation[item].itemid + '"]').val())
                $('[source="yesterday-returned-item"][itemid="' + yesterday_preparation[item].itemid + '"]').val(yesterday_preparation[item].quantity_returned);
        }
        for (var item in today_preparation) {
            if (today_preparation[item].quantity_out != $('[source="today-preparation-item"][itemid="' + today_preparation[item].itemid + '"]').val())
                $('[source="today-preparation-item"][itemid="' + today_preparation[item].itemid + '"]').val(today_preparation[item].quantity_out);
        }
    }

    /*
     $('#preparation').on('click', '', function(){
     
     })
     * 
     */
</script>
