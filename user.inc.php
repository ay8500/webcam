<?php
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';
include_once Config::$lpfw.'ltools.php';
include_once Config::$lpfw.'appl.class.php';

use \maierlabs\lpfw\Appl as Appl;

if (isActionParam("save") && (Config::isUserRoot() || Config::isUserView()) ) {
    Config::saveConfigJson();
}
?>

<div id="password_div" style="display:none" >
    <input id="password"  type="password" placeholder="password" value=""/>
    <button onclick="Cookie('password',$('#password').val());$('#password_div').slideUp('slow');location.reload();"><?php Appl::_("Save")?></button>
    <button onclick="$('#password').attr('type', 'text');"><?php Appl::_("Show")?></button>
    <button onclick="$('#password_div').slideUp('slow');"><?php Appl::_("Cancel")?></button>
</div>
<div id="settings_div" style="display:none" >
    <input id="password1"  type="password" placeholder="<?php Appl::_("new password")?>" value=""/>
    <input id="password2"  type="password" placeholder="<?php Appl::_("repeat password")?>" value=""/>
    <button onclick="$('#password1').attr('type', 'text');$('#password2').attr('type', 'text');"><?php Appl::_("Show")?></button>
    <hr/>
    <table class="settings">
        <tr>
            <td><?php Appl::_("Camera")?></td>
            <td><?php Appl::_("Rename")?></td>
            <td><?php Appl::_("Alert e-mail")?></td>
            <td><?php Appl::_("Public")?></td>
            <?php if (Config::isUserRoot()) {?>
                <td><?php Appl::_("Bcc")?></td>
            <?php }?>
        </tr>
    <?php foreach (Config::ja()["cameras"] as $camn=>$camPropertys) {
        echo('<tr>');
        echo('<td>'.$camn.'</td>');
        echo('<td><input id="cam_name_'.$camn.'" value="'.$camn.'"/></td>');
        echo('<td><input id="cam_alertEmail_'.$camn.'" value="'.(isset($camPropertys["alertEmail"])?$camPropertys["alertEmail"]:'').'" onkeyup="validateEmailInput(this);"/></td>');
        echo('<td><input id="cam_webcam_'.$camn.'" type="checkbox" '.(isset($camPropertys["webcam"]) && $camPropertys["webcam"]?'checked':'').' /></td>');
        if (Config::isUserRoot())
            echo('<td><input id="cam_alertBccEmail_'.$camn.'" value="'.(isset($camPropertys["alertBccEmail"])?$camPropertys["alertBccEmail"]:'').'" onkeyup="validateEmailInput(this);"/></td>');
        echo('</tr>');
    } ?>
    </table>
    <hr/>
    <button onclick="$('#settings_div').slideUp('slow');saveSettings();"><?php Appl::_("Save")?></button>
    <button onclick="$('#settings_div').slideUp('slow');"><?php Appl::_("Cancel")?></button>
</div>


<script>
    $(function() {
        validateEmailInput();
    });

    function saveSettings() {
        if ($("#password1").val()!==$("#password2").val()) {
            showDbMessage("<?php Appl::_("Passwords are not the same")?>","danger");
            return false;
        }
        var password=$("#password1").val();
        var cameras=new Object();
        var aktcam ="";
        $( "input[id*='cam_']" ).each(function (index) {
            var ca = $( this )[0].id.split("_");
            if (aktcam!==ca[2]) {
                aktcam=ca[2];
                cameras[aktcam]=new Object();
            }
            if (ca[1]=="webcam")
                cameras[aktcam][ca[1]]=$(this)[0].checked;
            else
                cameras[aktcam][ca[1]]=$(this)[0].value;
        });
        $.ajax({
            url: "ajaxSetConfig?cameras="+JSON.stringify(cameras)+"&newpassword="+password,
            success:function(data){
                showDbMessage("<?php Appl::_("Settings saved successfully.")?>","success");
            },
            error:function(data){
                showDbMessage("<?php Appl::_("Settings not saved!")?>","warning");
            }
        });

    }

    function validateEmail(mail) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(mail);
    }

    function validateEmailInput(sender) {
        $( "input[id*='Email']" ).each(function (index) {
            if (validateEmail($(this)[0].value)) {
                $(this)[0].style.color="green";
            } else {
                $(this)[0].style.color="red";
            }
        });
    }
</script>