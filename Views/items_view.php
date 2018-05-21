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

<div id="items">
    <div id="wrapper">
        <div class="page-content" style="padding-top:15px">
            <div>
                <h1>Items</h1>
                <p>All the items are available for all the distributions</p>
                <div id="actions" style="margin-top:15px">
                    <div id="add-item"><i class="icon-plus add-button"></i>Add item</div>
                </div>
                <h2>Regular</h2>
                <div id="regular-items-alert" style="display:none" class="alert alert-primary"role="alert">There are no items :(</div>
                <table id="regular-items" class="table" style="display:none">
                    <tr><th>Name</th><th></th></tr>
                </table>
                <h2>Non regular</h2>
                <div id="non-regular-items-alert" style="display:none" class="alert alert-primary"role="alert">There are no items :(</div>
                <table id="non-regular-items" class="table" style="display:none">
                    <tr><th>Name</th><th></th></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-------------------------------------------------------------------------------------------
MODALS
-------------------------------------------------------------------------------------------->
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

<!-- Delete item -->
<div id="delete-item-modal" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="delete-item-modal-label" aria-hidden="true" data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="delete-item-modal-label">Delete item</h3>
    </div>
    <div class="modal-body">
        <p>Are you sure you want to delete the item?</p>
        <div class="alert alert-primary" id="delete-item-message" role="alert"></div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button id="delete-item-ok" class="btn btn-primary">Delete</button>
    </div>
</div>


<script>
    // Initialize variables
    var path = "<?php echo $path; ?>";
    var items = distribution.get_items();

    update_view();

    // Development


    /*******************
     * Actions
     *******************/
    $('#items').on('click', '#add-item', function () {
        $('#add-item-message').hide();
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
            items[type][item_id] = {id: item_id, name: name, regular: regular}
            update_view();
            $('#add-item-modal').modal('hide');
        }
    });
    $('#items').on('click','.icon-trash',function(){
        $('#delete-item-message').hide();
        var item_id = $(this).attr('id');
        $('#delete-item-modal').attr('item-id',item_id).modal('show');
    });
    $('#delete-item-ok').on('click',function(){
        var item_id = $('#delete-item-modal').attr('item-id');
        var result=distribution.delete_item(item_id);
        if (result.error!=undefined)
            $('#delete-item-message').html(result.error).show();
        else{
            for (var id in items.regular){
                if (id ==item_id)
                    delete items.regular[id];
            }
            for (var id in items.non_regular){
                if (id ==item_id)
                    delete items.non_regular[id];
            }
            update_view();
            $('#delete-item-modal').modal('hide');
        }
            
    });
    
    /***************
     * Functions
     ***************/
    function update_view() {
        $('#regular-items .item').remove();
        $('#non-regular-items .item').remove();
        // display regular items
        if (Object.keys(items.regular).length == 0) {
            $('#regular-items-alert').show();
            $('#regular-items').hide();
        } else {
            var out = '';
            for (var itemid in items.regular) {
                out = '<tr class="item"><td>' + items.regular[itemid].name + '</td><td><i class="icon-trash pointer" id=' + itemid + ' /></td></tr>';
                $('#regular-items').append(out);
            }
            $('#regular-items-alert').hide();
            $('#regular-items').show();
        }
        // display non-regular items
        if (Object.keys(items.regular).length == 0) {
            $('#non-regular-items-alert').show();
            $('#non-regular-items').hide();
        } else {
            var out = '';
            for (var itemid in items.non_regular) {
                out = '<tr class="item"><td>' + items.non_regular[itemid].name + '</td><td><i class="icon-trash pointer" id=' + itemid + ' /></td></tr>';
                $('#non-regular-items').append(out);
            }
            $('#non-regular-items-alert').hide();
            $('#non-regular-items').show();
        }
    }
</script>

