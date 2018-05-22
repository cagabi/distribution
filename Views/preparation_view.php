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

<div id="preparation">
    <div id="wrapper">
        <div class="page-content" style="padding-top:15px">
            <div id="step1">
                <h1>Choose a distribution point</h1>
                <div id="distribution-points"></div>
            </div>
            <div id="step2" style="display:none">
                <h3>Regular items</h3>
                <div id="actions" style="margin-top:15px">
                    <div id="edit-items" class="pointer"><i class="icon-edit add-button"></i> Edit items</div>
                </div>
                <table id="preparation-regular-items" class="table">
                    <tr><th>Item</th><th>Out yesterday</th><th>Returned from yesterday</th><th>Out today</th></tr>
                </table>
                <h3>Non-regular items </h3>
                <table id="preparation-non-regular-items" class="table">
                    <tr><th>Item</th><th>Out yesterday</th><th>Returned from yesterday</th><th>Out today</th></tr>
                </table>
                <div id="last-week-distribution">
                    <h3>Last week distribution</h3>
                    <table id="last-week-distribution" class="table"></table>
                </div>
            </div>


        </div>
    </div>
</div>

<!-------------------------------------------------------------------------------------------
MODALS
-------------------------------------------------------------------------------------------->


<script>
    // Initialize variables
    var path = "<?php echo $path; ?>";
    var items = distribution.get_items();
    var organizationid = <?php echo $args['organizationid'] ?>;
    var organizations = <?php echo json_encode($args['organizations']) ?>; // Organizations user belongs to
    var distributionid = 0; // Used on step 2
    var today_preparation = {}; // Used on step 2
    var last_week_preparation = [];

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
    setTimeout(function () {
        $('p[distribution_id=1]').click();
        $('#edit-items').click();
    }, 0);

    /*******************
     * Actions
     *******************/
    $('#preparation').on('click', '.distribution-point', function () {
        // Variables
        distributionid = $(this).attr('distribution_id');
        organizationid = $(this).attr('distribution_id');

        // Add title
        $('#step2').prepend('<h2>' + $(this).attr('organization') + ' - ' + $(this).attr('name') + '</h2>');

        // Draw tables
        draw_preparation_table();
        draw_last_week_preparation_table();

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
        // Fetch data
        var items = distribution.get_items(); // update the list of itmes in case somebody has added one in the meantime
        var yesterday_preparation = distribution.get_yesterday_preparation(distributionid);
        var today_preparation = distribution.get_today_preparation(distributionid);

        // Update view yesterday_preparation
        for (var item in yesterday_preparation) {
            if (yesterday_preparation[item].quantity_returned != $('[source="yesterday-returned-item"][itemid="' + yesterday_preparation[item].itemid + '"]').val())
                $('[source="yesterday-returned-item"][itemid="' + yesterday_preparation[item].itemid + '"]').val(yesterday_preparation[item].quantity_returned);
        }
        // Update view today preparatioin
        for (var item in today_preparation) {
            if (today_preparation[item].quantity_out != $('[source="today-preparation-item"][itemid="' + today_preparation[item].itemid + '"]').val())
                $('[source="today-preparation-item"][itemid="' + today_preparation[item].itemid + '"]').val(today_preparation[item].quantity_out);
        }
    }

    function draw_preparation_table() {
        var yesterday = distribution.get_yesterday_preparation(distributionid);
        var today_prep = distribution.get_today_preparation(distributionid);

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
    }

    function draw_last_week_preparation_table() {
        // Detch last week distributions
        var last_week_preparation = distribution.get_last_week_preparation(distributionid);
        var last_week_items = [];
        for (var date in last_week_preparation) {
            for (var itemid in last_week_preparation[date])
                if (last_week_items.indexOf(itemid) == -1) {
                    last_week_items.push(itemid);
                }
        }

        // Prepare html and append
        var html = '<tr><th></th>';
        for (var date in last_week_preparation) {
            html += '<th>' + date + '</th>';
        }
        html += '</tr>';
        last_week_items.forEach(function (itemid) {
            html += '<tr><td>' + items[itemid].name + '</td>';
            for (var date in last_week_preparation) {
                if (last_week_preparation[date][itemid] == undefined)
                    html += '<td>0</td>';
                else
                    html += '<td>' + last_week_preparation[date][itemid].quantity + '</td>';
            }
            html += '</tr>';
        });
        $('table#last-week-distribution').html(html);
    }
    /*
     $('#preparation').on('click', '', function(){
     
     })
     * 
     */
</script>

