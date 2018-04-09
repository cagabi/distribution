<?php
defined('EMONCMS_EXEC') or die('Restricted access');
global $path;
$domain2 = "process_messages";
bindtextdomain($domain2, "Modules/distribution/locale");
bind_textdomain_codeset($domain2, 'UTF-8');
?>
<style>
</style>
<script type="text/javascript" src="<?php echo $path; ?>Modules/user/user.js"></script>
<script type="text/javascript" src="<?php echo $path; ?>Modules/distribution/Views/distribution.js"></script>
<link href="<?php echo $path; ?>Modules/distribution/Views/distribution.css" rel="stylesheet">

<div id="content"></div>

<script>
    var path = '<?php echo $path; ?>';
    var result_html = "";
    $.ajax({url: path + "user/login", async: false, cache: false, success: function (data) {
            result_html = data;
        }});
    $('#content').html(result_html);

    //result_html = '<div id"token-login"><p>Day token</p><p><input type="text" tabindex="2" name="token"></p></div>';
    result_html = '<form id="login-form" method="post" action="http://localhost/utopia56DMS/distribution/tokenlogin">';
    result_html += '<label>Day token<input type="text" tabindex="2" name="day_token"></label>';
    result_html += '<button id="token-login" class="btn-primary" type="submit">Token login</button>';
    result_html += '</form>';
    $('.main .well').append(result_html);
</script>

