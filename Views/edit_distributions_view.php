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

<div id="edit_distributions">
    <div id="wrapper">
        <div class="page-content" style="padding-top:15px">
            <h1>Edit old distributions</h1>
            <div id="step1">
                <h2>Choose a date</h2>
                <input id="distribution-date" type="date" max="" required></input>
                <h2>Choose a distribution point</h2>
                <div id="distribution-points"></div>
            </div>
            <div id="step2" style="display:none">
                <h2 id="distribution-name"></h2>
                <div id="actions" style="margin-top:15px">
                    <div id="edit-items"><i class="icon-plus add-button"></i>Add item</div>
                    <div id="step-backward"><i class="icon-step-backward add-button"></i></div>
                    <div id="step-forward"><i class="icon-step-forward add-button"></i></div>
                </div>
                <table id="distributions" class="table"></table>
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
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="edit-items-ok" class="btn btn-primary">Ok</button>
    </div>
</div>

<script>
    // Initialize variables
    var path = "<?php echo $path; ?>";
    var items = distribution.get_items(); // returns also deleted items just in case they have been using before deleting them
    var organizationid = <?php echo $args['organizationid'] ?>;
    var organizations = <?php echo json_encode($args['organizations']) ?>; // Organizations user belongs to
    var distributionid = 0; // Used on step 2
    var date_selected = ""; // Used on step 2
    var week_preparation = {}; // Used on step 2

    // Set latest date that can be edited: yesterday
    var yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1); //setDate also supports negative values, which cause the month to rollover.
    $('#distribution-date').attr('max', yesterday.toISOString().substr(0, 10));

    // Add distribution points
    organizations.forEach(function (org) {
        $('#distribution-points').append('<h3>' + org.name + '</h3>');
        var html = '<select class="distribution-point" organization-id="' + org.id + '">';
        org.distribution_points.forEach(function (distro_point) {
            html += '<option value="' + distro_point.id + '">' + distro_point.name + (distro_point.deleted == 1 ? ' (deleted)' : '') + '</option>';
        });
        html += "</select>";
        html += '<button class="distribution-chosen" organization-id="' + org.id + '" disabled>Go</button>';
        $('#distribution-points').append(html);
    });
    // Show Step 1 - Choose distribution point
    $('#step1').show();
    $('#step2').hide();

    /*******************
     * Actions
     *******************/
    $('#edit_distributions').on('change', '#distribution-date', function () {
        if (typeof (Date.parse($('#distribution-date').val())) == 'number')
            $('.distribution-chosen').prop('disabled', false);
        else
            $('.distribution-chosen').prop('disabled', true);
    });
    $('#edit_distributions').on('click', '.distribution-chosen', function () {
        organizationid = $(this).attr('organization-id');
        distributionid = $('select[organization-id=' + organizationid + ']').val();
        date_selected = $('#distribution-date').val();
        week_preparation = distribution.get_week_preparation(distributionid, date_selected);
        draw_preparation_table();

        // Add distribution name to title
        var org_index = 0;
        var distr_index = 0;
        organizations.forEach(function (org, index) {
            if (org.id == organizationid)
                org_index = index;
        });
        organizations[org_index].distribution_points.forEach(function (distr, index) {
            if (distr.id == distributionid)
                distr_index = index;
        });
        $('#distribution-name').html(organizations[org_index].distribution_points[distributionid].name);

        // Show Step 2 - Choose distribution point
        $('#step1').hide();
        $('#step2').show();
    });
    $('#edit_distributions').on('click', '#edit-items', function () {
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
            // Check if item is already displayed and if not add it to the table
            if ($('.item[itemid=' + item_id + ']').length == 0) {
                for (var date in week_preparation)
                    week_preparation[date][item_id] = {itemid: item_id, quantity: 0};
            }
        });
        draw_preparation_table();
        $('#edit-items-modal').modal('hide')
    });
    $('#edit_distributions').on('change', '.item-quantity', function () {
        distribution.save_distributed_item($(this).val(), $(this).attr('item-id'), distributionid, $(this).attr('date'));
    });
    $('#edit_distributions').on('click', '#step-backward', function () {
        var sorted_dates = distribution.sort_dates(week_preparation);
        delete week_preparation[sorted_dates[6]];
        var day = new Date(date_selected);
        day.setDate(day.getDate() - 1);
        date_selected = day.toISOString().substr(0, 10);
        draw_preparation_table();
        week_preparation = distribution.get_week_preparation(distributionid, date_selected);
    });
    $('#edit_distributions').on('click', '#step-forward', function () {
        var sorted_dates = distribution.sort_dates(week_preparation);
        delete week_preparation[sorted_dates[0]];
        var day = new Date(date_selected);
        day.setDate(day.getDate() + 1);
        date_selected = day.toISOString().substr(0, 10);
        draw_preparation_table();
        week_preparation = distribution.get_week_preparation(distributionid, date_selected);
    });

    /***************
     * Functions
     ***************/
    function draw_preparation_table() {
        // Variables used
        var sorted_dates = []; // used to sort the week preparation
        var week_items = [];
        var items_sorted = distribution.sort_items(items);
        // Fetch all the items distributedd in the week
        for (var date in week_preparation) {
            for (var itemid in week_preparation[date])
                if (week_items.indexOf(itemid) == -1) {
                    week_items.push(itemid);
                }
        }
        // Add empty preparations for missing days
        var date_obj = new Date(date_selected);
        var day = new Date();
        var date_str = "";
        for (var i = -2; i <= 2; i++) { // We put the day chosen by the user in the middle of 5 days
            day.setDate(date_obj.getDate() + i);
            date_str = day.toISOString().substr(0, 10);
            if (week_preparation[date_str] == undefined)
                week_preparation[date_str] = {};
        }
        // Sort preparation dates
        sorted_dates = distribution.sort_dates(week_preparation);
        // Prepare html and append
        var html = '<tr><th></th>';
        sorted_dates.forEach(function (date_str) {
            var date_obj = new Date(date_str);
            var today = (new Date());
            if (date_obj < today.setDate(today.getDate() - 1)) // ensure only days before yesterday(included) are shown
                html += '<th>' + date_obj.toString().substr(0, 15) + '</th>';
        });
        html += '</tr>';
        items_sorted.forEach(function (item) {
            var itemid = item.id;
            if (week_items.indexOf(itemid) != -1) {
                html += '<tr class="item" itemid="' + itemid + '"><td>' + items[itemid].name + (items[itemid].deleted == 1 ? ' (deleted)' : '') + '</td>';
                sorted_dates.forEach(function (date) {
                    var date_obj = new Date(date);
                    var today = (new Date());
                    if (date_obj < today.setDate(today.getDate() - 1)) {
                        if (week_preparation[date][itemid] == undefined)
                            html += '<td><input type="number" class="item-quantity" item-id="' + itemid + '" date="' + date + '" min=0 value=0></input></td>';
                        else
                            html += '<td><input type="number" class="item-quantity" item-id="' + itemid + '" date="' + date + '" min=0 value=' + week_preparation[date][itemid].quantity + '></input></td>';
                    }
                });
                html += '</tr>';
            }
        });
        $('table#distributions').html(html);
    }

    // Development
    /*setTimeout(function () {
     $('#distribution-date').val('2018-05-22').change();
     $('.distribution-chosen[organization-id="1"]').click();
     }, 0);*/
</script>

