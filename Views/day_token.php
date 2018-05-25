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
