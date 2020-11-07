<?PHP
/**
 * Webcam by Maierlabs (c) 2016-2020
 * Vers: 1.3.0
 */
include_once 'config.class.php';
include_once Config::$lpfw.'logger.class.php';      //Logger class from Levi's PHP FrameWork lpfw
include_once Config::$lpfw.'appl.class.php';        //Application class from Levi's PHP FrameWork lpfw
include_once 'bifi.class.php';                      //Big File class
include_once 'cameraTools.php';

use maierlabs\lpfw\Appl as Appl;

\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Config::jc()->IMAGE_ROOT_PATH.'log');
\maierlabs\lpfw\Logger::setLoggerLevel(\maierlabs\lpfw\LoggerLevel::info);

if (isset($_GET["cam"])) $camName = $_GET["cam"]; else	$camName="all";
if (isset($_GET["type"])) $camType = $_GET["type"]; else $camType="";
if (isset($_GET['day']) && $_GET['day']!="" ) $day=new DateTime($_GET['day']); else $day=new DateTime();
if (isset($_GET["action"])) $action = $_GET["action"]; else $action="";

$daydec=clone $day; $daydec->modify('-1 day');
$dayinc=clone $day; $dayinc->modify('+1 day');
$monthdec=clone $day; $monthdec->modify('-1 month');
$monthinc=clone $day; $monthinc->modify('+1 month');

$scriptArray=explode("/",$_SERVER["SCRIPT_NAME"]);
$script=pathinfo($scriptArray[sizeof($scriptArray)-1],PATHINFO_FILENAME);


$systemMessage="";

if ($camName!="all") {
    $camera = Config::ja()["cameras"][$camName];
    if (!isset($camera["snap"]) && !isset($camera["alert"]))
        $camType="";
}

if (Config::isUserRoot()) $_SESSION["uRole"]='admin'; // need for the lpfw LeviPhpFrameWork

$userRightTextForLogging=(Config::isUserRoot()?'R':'').(Config::isUserView()?'W':'');
\maierlabs\lpfw\Logger::_("View:".$day->format("Y.m.d")."\tType:".$camType."\tCam:".$camName."\tUser:".$userRightTextForLogging,\maierlabs\lpfw\LoggerLevel::info);

//Testmail
if ($action=="testmail" && Config::isUserRoot()) {
    $text = "<html><body><h2>Testmail</h2>Date:".date("l Y.F.d H:i:s");
    $text .= "<p>Disk free space:".number_format(disk_free_space('./')/1024/1024,2,',','.')." Mbyte</p>";
    $text .= "</body></html>";
    $ret=sendSmtpMail("levi@blue-l.de",$text);
    $systemMessage="Sending result of test mail:".($ret?"Ok":"Error");
}

//Zip the files in one zipfile per day
if ($action=="zipImages" && Config::isUserRoot()) {
    error_reporting(E_ALL);
    $zip=zipImages($camName,true,false);
    $systemMessage="Files zipped date:".$day->format("Ymd")." files:".$zip->filesZipped." deleted:".$zip->deleted." days:".$zip->daysZipped;
    \maierlabs\lpfw\Logger::_($systemMessage,\maierlabs\lpfw\LoggerLevel::info);
}

if ($action=="reorganizeImages" && Config::isUserRoot()) {
    $zip= new BiFi();
    //Server path
    $fileName=Config::jc()->IMAGE_ROOT_PATH.$camera["path"]."cam".$day->format('Ymd').".zip";
    $zip->open($fileName);
    $ret=$zip->reorganize(false);
    if($ret!==false) {
        $systemMessage="Archive reorganized ".$ret["count"]."files Cam:".$camName." and reduced file size by ".$ret["freeSize"]. " bytes.";
        \maierlabs\lpfw\Logger::_($systemMessage,\maierlabs\lpfw\LoggerLevel::info);
    } else {
        $systemMessage="Error while reorganize archive!";
        \maierlabs\lpfw\Logger::_($systemMessage,\maierlabs\lpfw\LoggerLevel::error);

    }
}

if ($action=="deleteday" && Config::isUserRoot()) {
    $systemMessage = deleteImagesFromDay($camType,$camName,$day);
}
?>

<html>
    <head>
        <title>Webcam app by MaierLabs</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js" ></script>
        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="webcam.css?vers=09.03.2020">
    </head>
<body>
<div class="cam-left">
    <div id="title"><?php echo Config::jc()->TITLE?>
        <div id="type">
            <form>
                <input type="hidden" name="day" value="<?php echo date_format($day, 'Y-n-j') ?>" />
                <?php echo Appl::_("Cam")?>
                <select id="camname" name="cam" onchange="submit()">
                    <option value="all" ><?php echo Appl::_("all")?></option>
                    <?php foreach (Config::ja()["cameras"] as $camn=>$camPropertys) {
                        if ($camPropertys["webcam"] || Config::isUserView() || Config::isUserRoot() || $camPropertys["webcam"]) {
                            if ($camn == $camName) {
                                echo('<option selected value="' . $camn . '">' . $camn . '</option>');
                            } else {
                                echo('<option value="' . $camn . '">' . $camn . '</option>');
                            }
                        }
                    }
                    ?>
                </select>
                <span id="imagetype">
                    <?php if ((isset($camera["snap"]) || isset($camera["alert"])) && ""!=$camType) :?>
                        <br/><span><input type="radio" name="type" value="" onclick="submit()"/></span>
                        <span><?php echo Appl::_("all")?></span>
                    <?php  endif; ?>
                    <?php if ((isset($camera["snap"]) || isset($camera["alert"])) && ""==$camType) :?>
                        <br/><span><input type="radio" name="type" value="" checked onclick="submit()"/></span>
                        <span><?php echo Appl::_("all")?></span>
                    <?php  endif; ?>
                    <?php if (isset($camera["snap"]) && $camera["snap"]!=$camType) :?>
                        <br/><span><input type="radio" name="type" value="<?php echo $camera["snap"]?>" onclick="submit()"/></span>
                        <span><?php echo Appl::_("Snapshot")?></span>
                    <?php  endif; ?>
                    <?php if (isset($camera["snap"]) && $camera["snap"]==$camType) :?>
                        <br/><span><input type="radio" name="type" value="<?php echo $camera["snap"]?>" checked onclick="submit()"/></span>
                        <span><?php echo Appl::_("Snapshot")?></span>
                    <?php  endif; ?>
                    <?php if (isset($camera["alert"]) && $camera["alert"]!=$camType) :?>
                        <br/><span><input type="radio" name="type" value="<?php echo $camera["alert"]?>"  onclick="submit()"/></span>
                        <span><?php echo Appl::_("Alert")?></span>
                    <?php  endif; ?>
                    <?php if (isset($camera["alert"]) && $camera["alert"]==$camType) :?>
                        <br/><span><input type="radio" name="type" value="<?php echo $camera["alert"]?>"  checked onclick="submit()"/></span>
                        <span><?php echo Appl::_("Alert")?></span>
                    <?php  endif; ?>
                    </span>
            </form>
        </div>
    </div>
    <div style="clear: both"></div>
    <div class="resultDBoperation"></div>
    <?php if ($systemMessage!="") {?>
        <div id="systemMessage" style="background-color: lightgray;padding-bottom: 10px">
            <div style="background-color: #ffe030;padding: 5px;margin: 0px 10px 0px 10px;border-radius: 5px;">
                <?php echo ($systemMessage);?>
                <button style="height:23px;float: right" onclick="$('#systemMessage').hide('show');"><span class="glyphicon glyphicon-remove-circle" style="position: relative;top: -4px;"></span></button>
            </div>
        </div>
    <?php }?>
    <?php if ($camName!="all") {?>
        <div class="calendarDiv">
            <div>
            <form style="display: inline-block;vertical-align: top; width: 50px;z-index: 10">
                <input type="hidden" name="cam" value="<?php echo $camName ?>"/>
                <input type="hidden" name="type" value="<?php echo $camType?>"/>
                <button name="day" value="<?php echo $monthdec->format('Y-m-d')?>"> <span class="glyphicon glyphicon-backward"> </span> </button>
            </form>
                <span style="background-color: #f0f0f0;padding: 7px;margin: -21px;position: relative;top: 8px;z-index: 0;"><?php echo Appl::_("month")?></span>
            <form style="display: inline-block;vertical-align: top;width: 50px;">
                <input type="hidden" name="cam" value="<?php echo $camName ?>"/>
                <input type="hidden" name="type" value="<?php echo $camType?>"/>
                <button name="day" value="<?php echo $monthinc->format('Y-m-d')?>"> <span class="glyphicon glyphicon-forward"> </span> </button>
            </form>
            </div>
            <div>
                <?php
                require_once("calendar.class.php");
                $cal = new calendar();
                $calendarDate = clone ($day);
                $calendarDate =$calendarDate ->modify(Config::jc()->CALENDAR_MIN_DISPLAY." month");
                for ($i=Config::jc()->CALENDAR_MIN_DISPLAY;$i<=Config::jc()->CALENDAR_MAX_DISPLAY;$i++) {?>
                    <div class="calendarBody">
                        <?php $cal->showCalendar($calendarDate->format("Y"),$calendarDate->format("n"),$camType,$camName,getBookedDays($camName,$camType,$calendarDate->format("Y"),$calendarDate->format("n")),array(),$day,Appl::__("TIMEZONE")); ?>
                    </div>
                    <?php $calendarDate->modify("1 month");  ?>
                <?php }  ?>
            </div>
        </div>
        <div class="toolbar">
            <div style="display: inline-block;">
                <button name="action" value="daybefore" onclick="dayBefore();"><span class="glyphicon glyphicon-backward"></span> <?php Appl::_("Day")?></button>
                <button name="action" value="dayafter" onclick="dayAfter();"><?php Appl::_("Day")?> <span class="glyphicon glyphicon-forward"></span></button>
                <button name="action" value="today" onclick="dayToday();"><?php Appl::_("Today")?></button>
            </div>
            <div style="display: inline-block;">
                <button name="action" value="next" onclick="imageOlder();"> <span class="glyphicon glyphicon-arrow-left"> </span> </button>
                <button name="action" value="prev" onclick="imageNewer();"> <span class="glyphicon glyphicon-arrow-right"> </span> </button>
                <button name="action" value="last" onclick="imageLast();"> <span class="glyphicon glyphicon-fast-forward"> </span> </button>
            </div>
            <div style="margin:5px;">&nbsp;&nbsp;&nbsp;&nbsp;
                <span id="count" title="<?php Appl::_("The actual picture and the number of pictures")?>">0</span>
                <span id="countDeleted" style="background-color: red;border-radius: 14px;padding: 3px;" title="<?php Appl::_("Deleted pictures in the archive")?>"></span>
                <?php Appl::_("Date")?>:<span id="akt_date">...</span> <?php Appl::_("File")?>:<span id="akt_image">...</span>
            </div>
        </div>
    <?php } ?>
</div>
<div class="cam-right">
    <div id="camimage">
        <img style="z-index: 100; left:0px; top:0px; position: relative;" id="image" src=""  title="" />
    </div>
</div>
<div class="footer">
    <?php if ($camName!="all") {?>
        <div style="display: flow-root;padding-left: 10px;"><div id="slider"></div></div>
        <?php if(Config::isUserView() || Config::isUserRoot() || ($camName!="all") ) {?>
            <?php if (!isset(Config::ja()["cameras"][$camName]["slides"])  || !Config::ja()["cameras"][$camName]["slides"]) {?>
                <button id="animate" onclick="$.ajax({url: 'ajaxLogger?text=CreateVideo&cam=<?php echo $camName?>'});createVideo(); ">
                    <span class="glyphicon glyphicon-film"></span> <?php Appl::_("Create Video")?>
                </button>
                <button id="video" onclick="$.ajax({url: 'ajaxLogger?text=ShowVideo&cam=<?php echo $camName?>'});showVideo(); ">
                    <span class="glyphicon glyphicon-film"></span> <?php Appl::_("Show Video")?>
                </button>
            <?php }?>
            <?php if (Config::isUserRoot()):?>
                <?php if (isset(Config::ja()["cameras"][$camName]) && Config::ja()["cameras"][$camName]["zip"]) {?>
                     <button name="action" value="reorganizeImages" onclick="reorganizeImages();" title="Attention: all pictures will be reorganized!"><span class="glyphicon glyphicon-retweet"></span> Reorganize</button>
                     <button name="action" value="zipImages" onclick="zipImages();" title="Attention: all pictures will be zipped!"><span class="glyphicon glyphicon-compressed"></span> Zip images</button>
                <?php } ?>
                <button name="action" value="delete" onclick="deleteImage();" title="Attention: the actual picture will be deleted!">  <span class="glyphicon glyphicon-remove-circle"></span> Delete image</button>
                <button name="action" value="deleteday" onclick="deleteDay();" class="btn-warning" title="Attention: all pictures for the actual day will be deleted!"> <span class="glyphicon glyphicon-remove-circle"></span> Delete day</button>
                <button name="action" value="deleteolder" onclick="deleteOldImages();" class="btn-danger" title="Attention: all older pictures as the actual showed day will be deleted!"><span class="glyphicon glyphicon-remove-circle"></span> Delete older</button>
                <form style="display: inline"><button name="action" value="testmail" type="submit"><span class="glyphicon glyphicon-envelope"></span> Testmail</button></form>
            <?php endif;?>
        <?php }?>
       <button onclick="showCamImages()"><span class="glyphicon glyphicon-refresh"></span> <?php Appl::_("Refresh pictures")?></button>
    <?php }?>
    <?php if (Config::isUserRoot() || Config::isUserView()) {?>
        <button onclick="$('#password').attr('type','password');$('#password_div').slideDown('slow');$('#password').val(Cookie('password'))"><span class="glyphicon glyphicon-log-out"></span> <?php Appl::_("Log out")?></button>
        <button onclick="$('#settings_div').slideDown('slow');"><span class="glyphicon glyphicon-cog"></span> <?php Appl::_("Settings")?></button>
        <button onclick="showLogs()"><span class="glyphicon glyphicon-list-alt"></span> <?php Appl::_("Show logs")?></button>
    <?php } else {?>
        <button onclick="$('#password').attr('type','password');$('#password_div').slideDown('slow');$('#password').val(Cookie('password'))"><span class="glyphicon glyphicon-log-in"></span> <?php Appl::_("Log in")?></button>
    <?php }?>
</div>
<?php include "user.inc.php"; ?>
<?php \maierlabs\lpfw\Appl::setApplJScript();?>
<?php \maierlabs\lpfw\Appl::includeJs();?>

<canvas id="canvas" width="1024" height="800" style="display: none"></canvas>
</body>
</html>


<script>
    var date = new Date(<?php echo date_format($day, 'Y') ?>,<?php echo date_format($day, 'n') ?>-1,<?php echo date_format($day, 'j') ?>);
    var aktualImageIdx=0;
    var imageList = new Array;

    $( document ).ready(function() {
        $("#video").hide();
        showCamImages();
        $( "#slider" ).slider({
            slide: function( event, ui ) {
                aktualImageIdx = ui.value;
                showImage();
            }
        });
        <?php if ($camName=="all") {?>
            setInterval(function(){showAllLastImages(); }, 15000);
        <?php }?>
        setTimeout(function(){ $("#systemMessage").hide("slow"); }, 10000);

    });

<?php if ($camName!="all" && (!isset(Config::ja()["cameras"][$camName]["slides"])  || !Config::ja()["cameras"][$camName]["slides"])  ) {?>
    var animateBegin = -2;
    var animateEnd = -2;
    var animateTimer = null;
    var imageArray = Array();
    var videoIdx = 0;


    function createVideo() {
        if (animateTimer!=null)
            clearTimeout(animateTimer);
        if( animateEnd==-2) {
            imageArray = [];videoIdx=0;
            animateBegin=$( "#slider" ).slider("value");
            animateEnd=$( "#slider" ).slider("option","max");
            if (animateEnd-animateBegin>250)
                animateEnd = animateBegin+250;
            createVideo(true);
        } else {
            if (animateBegin >= 0 && animateEnd >= 0 && animateBegin <= animateEnd) {
                aktualImageIdx = animateEnd--;
                var img = new Image();
                img.src = showImage();
                imageArray.push(img);
                $("#video").show();
                animateTimer = setTimeout(createVideo, 1);
            } else {
                animateEnd=-2;
            }
        }
    }

    function showVideo() {
        if (videoIdx<imageArray.length) {
            $("#akt_image").html(getTime(imageList[videoIdx]));
            $("#count").html((animateBegin + imageArray.length - videoIdx) + "/" + imageList.length);
            $("#slider").slider("option", "value", animateBegin + imageArray.length - videoIdx - 1);
            $("#image").attr("src", imageArray[videoIdx].src);
            videoIdx++;
            setTimeout(showVideo, 70);
        } else {
            videoIdx=0;
        }
    }
<?php }?>
<?php if ($camName!="all") {?>

    function showImage() {
        var src;
        if (imageList.length>aktualImageIdx) {
            $("#akt_image").html(getTime(imageList[aktualImageIdx]));
            $("#count").html((aktualImageIdx+1)+"/"+imageList.length);
            $("#slider").slider( "option", "value", aktualImageIdx );
            <?php if (Config::ja()["cameras"][$camName]["zip"]) { ?>
                src="getZipCamImage?camname=<?php echo $camName;?>&date=<?php echo $day->format("Ymd")?>&imagename="+imageList[aktualImageIdx];
            <?php } else {?>
                src = imageList[aktualImageIdx];
            <?php } ?>
            $("#image").attr("src",src);
        } else {
            $("#image").attr("src",src);
        }
        return src;
    }

<?php }?>

<?php if (Config::isUserRoot() || Config::isUserView() ){?>
    function showLogs() {
        window.location.href="<?php echo ( 'viewLogs?cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
    }
<?php }?>

<?php if (Config::isUserRoot() && $camName!="all") {?>

    function deleteImage() {
        $.ajax({
            <?php if ($camera["zip"]) {?>
            url: "ajaxDeleteZipImage?day=<?php echo $day->format("Ymd");?>&camname=<?php echo $camName;?>&filename="+imageList[aktualImageIdx]+"&password="+Cookie("password"),
            <?php } else {?>
            url: "ajaxDeleteImage?filename="+imageList[aktualImageIdx]+"&password="+Cookie("password"),
            <?php } ?>
            success:function(data){
                imageList.splice(aktualImageIdx, 1);
                if (aktualImageIdx>imageList.length-1) //darauf achten, dass der Index des aktuellen Bildes immer im bereich des Arrays liegt.
                    aktualImageIdx=imageList.length-1;
                $("#count").html((aktualImageIdx+1)+"/"+imageList.length-1);
                $("#slider" ).slider("option", "max", imageList.length-1);
                 showImage(); //am Ende des Löschens das Bild zeigen was noch da ist
            }
        });
    }

    <?php //delete day images?>
    function deleteDay() {
        if (confirm("Please confirm, that you want to delete all images from the selected day?") ) {
            window.location.href="<?php echo ( $script.'?action=deleteday&cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
        }
    }

    function zipImages() {
        if (confirm("Please confirm, that you want to zip all images from the selected camera?") ) {
            window.location.href="<?php echo ( $script.'?action=zipImages&cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
        }
    }

    function reorganizeImages() {
        if (confirm("Please confirm, that you want to reorganize the images archive from the selected camera?") ) {
            window.location.href="<?php echo ( $script.'?action=reorganizeImages&cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
        }
    }

    <?php //check if old images should be deleted?>
    function deleteOldImages() {
        $.ajax({
            url: "ajaxDeleteImagesOlderThen?day=<?php echo $day->format('Y-m-d') ?>&cam=<?php echo $camName ?>",
            success:function(data){
                if (data.files>0) {
                    if (confirm("Confirm "+data.files+" files to be deleted")) {
                        deleteFiles();
                    }
                }
            }
        });
    }

    <?php //calls ajax function to delete old pictures?>
    function deleteFiles() {
        $.ajax({url: "ajaxLogger?text=action_delete+day_<?php echo $day->format('Y-m-d') ?>+cam_<?php echo $camName ?>"});
        $.ajax({
            url: "ajaxDeleteImagesOlderThen?action=delete&day=<?php echo $day->format('Y-m-d') ?>&cam=<?php echo $camName ?>",
            success:function(data){
                if (confirm(data.files+" files deleted, do you want to refresch the site?")) {
                    location.reload();
                }
            }
        });
    }
<?php }?>

    <?php //Show images called by changing the camera or image type?>
    function showCamImages(camname) {
        if (camname==undefined) {
            camname=$("#camname").val();
        } else {
            $("#camname").val(camname);
        }
        if (camname=="all") {
            showAllLastImages();
        } else {
            hideAllLastImages();
            loadImageList(camname);
        }
    }

    <?php //loads over ajax the list of images for the selected camera an type?>
    function loadImageList(camname) {
        $("#akt_date").html(DateToString.dmy(date));
        $("#akt_image").html("");
        $("#count").html("0");
        aktualImageIdx=0;
        var type=$("input[name='type']:checked").val();
        if(type==null)
            type="";
        $.ajax({
            url: "ajaxGetImageList?day="+DateToString.ymd(date)+"&type="+type+"&camname="+camname,
            success:function(data){
                imageList=data.reverse();
                if (imageList.length>0) {
                    showImage();
                    $( "#slider" ).slider("option", "max", imageList.length-1);
                }
            }
        })
        $.ajax({
            url: "ajaxGetImageList?day="+DateToString.ymd(date)+"&type="+type+"&camname="+camname+"&deleted=ask",
            success:function(data){
                if (data>0) {
                    $("#countDeleted").show().html(data);
                } else {
                    $("#countDeleted").hide();
                }
            }
        })
    }

    function showAllLastImages() {
        $.ajax({
            url: "ajaxGetAllNewestImages",
            success:function(data){
                var cams= new Array;
                var zipped= new Array;
                <?php foreach (Config::ja()["cameras"] as $cn=>$camPropertys) {?>
                    cams.push('<?php echo $cn ?>');zipped.push(<?php echo $camPropertys["zip"]?"true":"false" ?>);
                <?php  } ?>
                $("#image").css("display","none");
                for (var i=0; i<cams.length; i++) {
                    if( data[cams[i]].name!=null) {
                        $("#aimage_"+cams[i]).remove();
                        $( "#image" ).after( '<a id="aimage_'+cams[i]+'" href="<?php echo $script?>?cam='+cams[i]+'" title="'+cams[i]+'"><img class="allimages" id="image_'+cams[i]+'"></a>' );
                        if (zipped[i]) {
                            $("#image_"+cams[i]).attr("src","getZipCamImage" +
                                "?camname="+cams[i]+"&date="+data[cams[i]]["date"]+"&imagename="+data[cams[i]]["name"]);
                        } else {
                            $("#image_"+cams[i]).attr("src",data[cams[i]]["name"]);
                        }
                    }
                }
            }
        });
    }

    function hideAllLastImages() {
        var cams= new Array;
        <?php foreach (Config::ja()["cameras"] as $cn=>$camera) {?>
        cams.push('<?php echo $cn ?>');
        <?php } ?>
        $("#image").css("display","inline");
        for (var i=0; i<cams.length; i++) {
            $("#image_"+cams[i]).remove();
        }
    }

    function dayBefore() {
        window.location.href="<?php echo ( $script.'?cam='.$camName.'&type='.$camType.'&day='.date_format($daydec, 'Y-n-j'))?>";
    }

    function dayAfter() {
        window.location.href="<?php echo ( $script.'?cam='.$camName.'&type='.$camType.'&day='.date_format($dayinc, 'Y-n-j'))?>";
    }

    function dayToday() {
        window.location.href="<?php echo ( $script.'?cam='.$camName.'&type='.$camType)?>";
    }

    function imageOlder() {
        if (aktualImageIdx<imageList.length-1) {
            aktualImageIdx++;
            showImage();
        }
    }

    function imageNewer() {
        if (aktualImageIdx>0) {
            aktualImageIdx--;
            showImage();
        }
    }

    function imageLast() {
        aktualImageIdx=0;
        showImage();
    }

    function getTime(s) {
        k=s.split("-");
        if (k.length==2) {
            return k[1].substr(0,2)+":"+k[1].substr(2,2)+":"+k[1].substr(4,2);
        }
        else return s;
    }

    var DateToString = {
        ymd: function (d) {
            var s=d.getFullYear().toString()+"-";
            if (d.getMonth()<9) s=s+"0";
            s=s+(d.getMonth()+1).toString() + "-"
            if (d.getDate()<9) s=s+"0";
            s=s+d.getDate().toString();
            return s;
        },
        dmy: function (d) {
            var s=d.getDate().toString()+".";
            if (d.getMonth()<9) s=s+"0";
            s=s+(d.getMonth()+1).toString() + "."+
                d.getFullYear().toString() ;
            return s;
        }
    };

    Date.prototype.addDays = function(days) {
        this.setDate(this.getDate() + days);
        return this;
    };
</script>

<?php

function getBookedDays($camName,$type,$year=0,$month=0){

    if ($year == 0) {
        $referenceDay    = new DateTime(date("Y")."-".date("n")."-1");
        if ($month>0)
            $referenceDay->modify('+'.$month.' month');
        else
            $referenceDay->modify($month.' month');
    } else {
        $referenceDay    = (new DateTime)->setTimestamp(mktime(0,0,0,$month,1,$year));
    }

    return getFileCount($camName,$referenceDay,$type);
}

/**
 * Count Images for one day
 * @return number
 */
function getFileCount($camName,$date,$type) {
    $ret = array();
    $dateSelectedMonth=$date->format('Ym');
    if (Config::ja()["cameras"][$camName]["zip"]) {
        $zip = new BiFi();
        for($day=1;$day<32;$day++) {
            $fzip=Config::jc()->IMAGE_ROOT_PATH.Config::ja()["cameras"][$camName]["path"]."cam".$dateSelectedMonth.($day<10?"0".$day:$day).".zip";
            $zip->open($fzip);
            $c=$zip->getArchiveFileCount($type);
            if ($c>0) $ret[$day]=$c;
        }
    } else {
        $path = Config::ja()["cameras"][$camName]["path"];
        $files=getFileList($path,Config::ja()["cameras"][$camName]["patternRegEx"]);
        foreach ($files as $f) {
            $d =(new DateTime())->setTimestamp($f["lastmod"] );
            if (($type=="" || strstr($f["name"],$type)) &&
                $dateSelectedMonth == $d->format("Ym")) {
                if (isset($ret[$d->format("j")]))
                    $ret[$d->format("j")] += 1;
                else
                    $ret[$d->format("j")] = 1;
            }

        }
    }
    return $ret;
}