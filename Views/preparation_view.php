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


<script>
    var path = "<?php echo $path; ?>";
    var items = [];
    update_view();

    function update_view() {
        items = distribution.get_items();
    }

    /*
     $('#preparation').on('click', '', function(){
     
     })
     * 
     */
</script>

