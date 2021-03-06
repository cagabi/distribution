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
            <h1>Preparation</h1>
            <div id="step1">
                <h2>Choose a distribution point</h2>
                <div id="distribution-points"></div>
            </div>
            <div id="step2" style="display:none">
                <div id="actions" style="margin-top:15px">
                    <div id="edit-items" class="pointer"><i class="icon-edit add-button"></i> Edit items</div>
                </div>
                <h3>Regular items</h3>
                <table id="preparation-regular-items" class="table"></table>
                <h3>Non-regular items </h3>
                <table id="preparation-non-regular-items" class="table"></table>
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
<!-- Edit items -->
<div id="edit-items-modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="edit-items-modal-label" aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="edit-items-modal-label">Edit items to show</h3>
    </div>
    <div class="modal-body">
        <h4>Regular</h4>
        <div id="edit-items-regular" style="margin-left:25px"></div>
        <h4 style="margin-top:25px">Non-regular</h4>
        <div id="edit-items-non-regular" style="margin-left:25px"></div>
    </div>
    <div class="modal-footer">
        <button id="create-item" class="btn" style="margin-right:15px">Create item</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="edit-items-ok" class="btn btn-primary">Ok</button>
    </div>
</div>

<!-- Add item -->
<div id="add-item-modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="add-item-modal-label" aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="add-item-modal-label">Add new item</h3>
    </div>
    <div class="modal-body">
        <p>Name:<br>
            <input id="add-item-name" type="text" maxlength="64">
        </p>
        <p>Type:<br>
            <select id="add-item-type" type="text" maxlength="64">
                <option value="regular">Regular</option>
                <option value="non-regular">Non-regular</option>
            </select>
        </p>
        <div class="alert alert-primary" id="add-item-message" role="alert"></div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="add-item-ok" class="btn btn-primary">Add</button>
    </div>
</div>


<script>
    // Initialize variables
    var path = "<?php echo $path; ?>";
    var items = distribution.get_items(); // returns also deleted items just in case they have been using before deleting them
    var sorted_items = distribution.sort_items(items);
    var organizationid = <?php echo $args['organizationid'] ?>;
    var organizations = <?php echo json_encode($args['organizations']) ?>; // Organizations user belongs to
    var distributionid = 0; // Used on step 2
    var today_preparation = {}; // Used on step 2
    var yesterday_preparation = {}; // Used on step 2
    var last_week_preparation = []; // Used on step 2

    // Add distribution points
    organizations.forEach(function (org) {
        if (distribution.any_active_distribution_point(org.distribution_points) == true) {
            $('#distribution-points').append('<h3>' + org.name + '</h3>');
            org.distribution_points.forEach(function (distro_point) {
                if (distro_point.deleted == 0) {
                    var html = '<p class="distribution-point" distribution_id="' + distro_point.id + '" organization_id="' + org.id + '" name="' + distro_point.name + '" organization="' + org.name + '">' + distro_point.name + '</p>';
                    $('#distribution-points').append(html);
                }
            });
        }
    });
    // Show Step 1 - Choose distribution point
    $('#step1').show();
    $('#step2').hide();


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
        items = distribution.get_items();
        sorted_items = distribution.sort_items(items);
        yesterday_preparation = distribution.get_yesterday_preparation(distributionid);
        today_preparation = distribution.get_today_preparation(distributionid);
        draw_preparation_table();
        draw_last_week_preparation_table();

        // trigger auto-update to allow for simultaneous edition in more than one device
        setInterval(function () {
            update_preparation();
        }, 2000);
        $('#step1').hide();
        $('#step2').show();
    });

    $('#preparation').on('change keypress', 'input', function () {
        if ($(this).attr('source') == 'yesterday-preparation-item')
            var result = distribution.save_returned_item($(this).val(), $(this).attr('itemid'), distributionid);
        else
            var result = distribution.save_going_out_item($(this).val(), $(this).attr('itemid'), distributionid);
        if (result === false) {
            $(this).val('');
            window.alert('There was a problem saving your last change');
        }
    });
    $('#preparation').on('click', '#edit-items', function () {
        var items_not_deleted = distribution.get_items_not_deleted();
        var sorted_items_not_deleted = distribution.sort_items(items_not_deleted);
        $('#edit-items-regular').html('');
        $('#edit-items-non-regular').html('');
        var html = '';
        for (var i in sorted_items_not_deleted) {
            if (sorted_items_not_deleted[i].regular == '1')
                $('#edit-items-regular').append('<p><input type="checkbox" item-id="' + sorted_items_not_deleted[i].id + '" type="regular" /> ' + sorted_items_not_deleted[i].name + '</p>');
            else
                $('#edit-items-non-regular').append('<p><input type="checkbox" item-id="' + sorted_items_not_deleted[i].id + '" type="non-regular" /> ' + sorted_items_not_deleted[i].name + '</p>');
            if ($('tr[itemid=' + sorted_items_not_deleted[i].id + ']').length > 0)
                $('#edit-items-modal input[item-id=' + sorted_items_not_deleted[i].id + ']').prop('checked', true);
        }
        $('#edit-items-modal').modal('show');
    });
    $('#edit-items-ok').on('click', function () {
        $('#edit-items-modal input[type=checkbox]:checked').each(function () {
            var item_id = $(this).attr('item-id');
            var a = $('.item[itemid=' + item_id + ']');
            if ($('.item[itemid=' + item_id + ']').length == 0) {
                yesterday_preparation[item_id] = {itemid: item_id, quantity_out: 0, quantity_returned: 0};
                today_preparation[item_id] = {itemid: item_id, quantity_out: 0, quantity_returned: 0};
            }
        });
        draw_preparation_table();
        $('#edit-items-modal').modal('hide')
    });
    $('#create-item').on('click', function () {
        $('#add-item-message').hide();
        $('#edit-items-modal').modal('hide');
        $('#add-item-modal').modal('show');
    });
    $('#add-item-ok').on('click', function () {
        $('#add-item-message').hide();
        var name = $('#add-item-name').val();
        var type = $('#add-item-type').val() == 'regular' ? 'regular' : 'non_regular';
        var regular = $('#add-item-type').val() == 'regular' ? 1 : 0;
        var item_id = distribution.create_item(name, regular);
        if (item_id.error != undefined)
            $('#add-item-message').html(item_id.error).show();
        else {
            items[item_id] = {id: item_id, name: name, regular: regular};
            $('#add-item-modal').modal('hide');
            $('#edit-items').click();
        }
    });


    /***************
     * Functions
     ***************/
    function update_preparation() {
        // Fetch data
        items = distribution.get_items(); // update the list of itmes in case somebody has added one in the meantime
        yesterday_preparation = distribution.get_yesterday_preparation(distributionid);
        today_preparation = distribution.get_today_preparation(distributionid);
        // Update view yesterday_preparation
        for (var item in yesterday_preparation) {
            if ($('[source="yesterday-preparation-item"][itemid="' + yesterday_preparation[item].itemid + '"]').is(':focus') == false) {
                if (yesterday_preparation[item].quantity_returned != $('[source="yesterday-preparation-item"][itemid="' + yesterday_preparation[item].itemid + '"]').val())
                    $('[source="yesterday-preparation-item"][itemid="' + yesterday_preparation[item].itemid + '"]').val(yesterday_preparation[item].quantity_returned);
            }
        }
        // Update view today preparatioin
        for (var item in today_preparation) {
            if ($('[source="today-preparation-item"][itemid="' + today_preparation[item].itemid + '"]').is(':focus ') == false) {
                if (today_preparation[item].quantity_out != $('[source="today-preparation-item"][itemid="' + today_preparation[item].itemid + '"]').val())
                    $('[source="today-preparation-item"][itemid="' + today_preparation[item].itemid + '"]').val(today_preparation[item].quantity_out);
            }
        }
        // Last week distributions
        draw_last_week_preparation_table();
    }

    function draw_preparation_table() {
        $('#preparation-regular-items').html('<tr><th>Item</th><th>Out yesterday</th><th>Returned from yesterday</th><th>Out today</th></tr>');
        $('#preparation-non-regular-items').html('<tr><th>Item</th><th>Out yesterday</th><th>Returned from yesterday</th><th>Out today</th></tr>');

        sorted_items.forEach(function (item) {
            if (yesterday_preparation[item.id] != undefined || today_preparation[item.id] != undefined) {
                var itemid = 1.0 * item.id;
                if (items[itemid] != undefined) {
                    var type = items[itemid].regular != 0 ? 'regular' : 'non-regular';
                    var html = '<tr itemid=' + itemid + '>';

                    // Add item to yesterday columns
                    html += '<td>' + items[itemid].name + '</td>';
                    if (yesterday_preparation[itemid] == undefined) {
                        html += '<td>0</td>';
                        html += '<td><input type="number" source="yesterday-preparation-item" itemid=' + itemid + ' class="item" min=0 value="0" /></td>';

                    } else {
                        html += '<td>' + (yesterday_preparation[itemid].quantity_out != undefined ? yesterday_preparation[itemid].quantity_out : 0) + '</td>;'
                        html += '<td><input type="number" source="yesterday-preparation-item" itemid=' + itemid + ' min=0 value=' + yesterday_preparation[itemid].quantity_returned + ' /></td>';
                    }

                    // Add item to today columns
                    if (today_preparation[itemid] == undefined)
                        html += '<td><input type="number" source="today-preparation-item" itemid=' + itemid + ' class="item" min=0 value="0" /></td>';
                    else
                        html += '<td><input type="number" source="today-preparation-item" itemid=' + itemid + ' min=0 value=' + today_preparation[itemid].quantity_out + ' /></td>';
                    html += '</tr>';
                    $('#preparation-' + type + '-items').append(html);
                }
            }
        });
    }

    function draw_last_week_preparation_table() {
        // Fetch last week distributions
        var last_week_preparation = distribution.get_last_week_preparation(distributionid);
        var sorted_dates = distribution.sort_dates(last_week_preparation);
        var last_week_items = [];
        for (var date in last_week_preparation) {
            for (var itemid in last_week_preparation[date])
                if (last_week_items.indexOf(itemid) == -1) {
                    last_week_items.push(itemid);
                }
        }

        // Prepare html and append
        var html = '<tr><th></th>';
        for (var date in sorted_dates) {
            var date_obj = new Date(sorted_dates[date]);
            html += '<th>' + date_obj.toString().substr(0, 15) + '</th>';
        }
        html += '</tr>';
        last_week_items.forEach(function (itemid) {
            html += '<tr><td>' + items[itemid].name + '</td>';
            for (var date in sorted_dates) {
                if (last_week_preparation[sorted_dates[date]][itemid] == undefined)
                    html += '<td>0</td>';
                else
                    html += '<td>' + last_week_preparation[sorted_dates[date]][itemid].quantity + '</td>';
            }
            html += '</tr>';
        });
        $('table#last-week-distribution').html(html);
    }

    // Development
    setTimeout(function () {
        $('p[distribution_id=1]').click();
        //$('#edit-items').click();
        //$('#create-item').click();
    }, 0);
</script>

