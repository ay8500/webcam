<?PHP
include 'config.php';
include_once 'zipImages.php';
include_once 'logger.class.php';
include_once 'bifi.class.php';
setLoggerType(loggerType::file, Constants::IMAGE_ROOT_PATH.'log');

if (isset($_GET["cam"])) $camName = $_GET["cam"]; else	$camName="all";
if (isset($_GET["type"])) $camType = $_GET["type"]; else $camType="";
if (isset($_GET['day']) && $_GET['day']!="" ) $day=new DateTime($_GET['day']); else $day=new DateTime();
if (isset($_GET["action"])) $action = $_GET["action"]; else $action="";

$daydec=clone $day; $daydec->modify('+1 day');
$dayinc=clone $day; $dayinc->modify('-1 day');

$scriptArray=explode("/",$_SERVER["SCRIPT_NAME"]);
$script=$scriptArray[sizeof($scriptArray)-1];

$systemMessage="";
$userRight=isUserRoot()?'R':'';
$userRight.=isUserView()?'W':'';
logger("Date:".$day->format("Ymd")."\tType:".$camType."\tCam:".$camName."\tUser:".$userRight,loggerLevel::info);


//Zip the files in one zipfile per day
if ($action=="zipImages" && isUserRoot()) {
    $zip=zipImages($camName);
    $systemMessage="Files zipped date:".$day->format("Ymd")." files:".$zip->filesZipped." deleted:".$zip->deleted." days:".$zip->daysZipped;
    logger($systemMessage,loggerLevel::info);
}

if ($action=="reorganizeImages" && isUserRoot()) {
    $zip= new BiFi();
    //Server path
    $fileName=Constants::IMAGE_ROOT_PATH.Constants::getCameras()[$camName]["path"]."cam".$day->format('Ymd').".zip";
    $zip->open($fileName);
    $ret=$zip->reorganize(false);
    if($ret!==false) {
        $systemMessage="Archive reorganized ".$ret["count"]." files and reduced file size by ".$ret["freeSize"]. " bytes.";
        logger($systemMessage,loggerLevel::info);
    } else {
        $systemMessage="Error while reorganize archive!";
    }
}

if ($action=="deleteday" && isUserRoot()) {
    $path=Constants::IMAGE_ROOT_PATH.Constants::getCameras()[$camName]["path"];
    if (Constants::getCameras()[$camName]["zip"]) {
        $fileName=$path."cam".$day->format('Ymd').".zip";
        unlink($fileName.".bfi");
        unlink($fileName.".bfd");
        $fileDeletedCount=2;
    } else {
        $directory = dir($path);$fileDeletedCount=0;
        while ($file = $directory->read()) {
            if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) &&
                ($camType=="" || strstr($file,$camType)) && (new DateTime())->setTimestamp(filemtime($path.$file))->format("Ymd")===$day->format("Ymd")  	) {
                unlink($path.$file);
                $fileDeletedCount++;
            }
        }
        $directory->close();
    }
    $systemMessage="Files deleted:".$fileDeletedCount;
    logger("DeleteFiles Date:".$day->format("Ymd")." Count:".$fileDeletedCount,loggerLevel::info);
}


?>
<html>
<head>
    <title>Webcam Viewer by Levi</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="webcam.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js" ></script>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
</head>
<body>
<?php if ($systemMessage!="") :?>
    <div>
        <?php echo ($systemMessage);?>
    </div>
<?php endif;?>
<div id="title"><?php echo Constants::TITLE?>
    <div id="type">
        <form>
            <input type="hidden" name="day" value="<?php echo date_format($day, 'Y-n-j') ?>" />
            Cam:

            <select id="camname" name="cam" onchange="submit()">
                <option value="all" >all</option>
                <?php foreach (Constants::getCameras() as $camn=>$propertys) {
                    if ($camn==$camName) {
                        $camPath=$propertys["path"];
                        echo('<option selected value="'.$camn.'">'.$camn.'</option>');
                    } else {
                        echo('<option value="'.$camn.'">'.$camn.'</option>');
                    }
                }
                ?>
            </select>
            <span id="imagetype">
				<?php if (""!=$camType) :?>
                    <span>All:</span><span><input type="radio" name="type" value="" onclick="submit()"/></span>
                <?php  endif; ?>
                <?php if (""==$camType) :?>
                    <span>All:</span><span><input type="radio" name="type" value="" checked onclick="submit()"/></span>
                <?php  endif; ?>
				<?php if (Constants::SNAP!="" && Constants::SNAP!=$camType) :?>
                    <span>Snapshot:</span><span><input type="radio" name="type" value="Schedule_" onclick="submit()"/></span>
                <?php  endif; ?>
                <?php if (Constants::SNAP!="" && Constants::SNAP==$camType) :?>
                    <span>Snapshot:</span><span><input type="radio" name="type" value="Schedule_" checked onclick="submit()"/></span>
                <?php  endif; ?>
                <?php if (Constants::ALERT!=""  && Constants::ALERT!=$camType) :?>
                    <span>Alert:</span><span><input type="radio" name="type" value="MDAlarm_"  onclick="submit()"/></span>
                <?php  endif; ?>
                <?php if (Constants::ALERT!="" && Constants::ALERT==$camType) :?>
                    <span>Alert:</span><span><input type="radio" name="type" value="MDAlarm_"  checked onclick="submit()"/></span>
                <?php  endif; ?>
				</span>
        </form>
    </div>
</div>
<div class="toolbar" id="tollbardate">
    Date:<span id="akt_date">...</span>Time:<span id="akt_image">...</span>
</div>
<?php if ($camName!="all") {?>
    <div class="calendarDiv">
        <?php
        require_once("calendar.class.php");
        $cal = new calendar();
        for ($i=Constants::CALENDAR_MIN_DISPLAY;$i<=Constants::CALENDAR_MAX_DISPLAY;$i++) {?>
            <div class="calendarBody">
                <?php $cal->showCalendar(0,$i,$camType,$camName,getBookedDays($camName,$camPath,$camType,0,$i),array(),$day); ?>
            </div>
        <?php }  ?>
    </div>
<?php } ?>
<div class="toolbar" id="tollbartop">
    <div style="display: inline-block;">
        <button name="action" value="daybefore" onclick="dayBefore();">One day before</button>
        <button name="action" value="dayafter" onclick="dayAfter();">One day later</button>
        <button name="action" value="today" onclick="dayToday();">Today</button>
    </div>
    <div style="display: inline-block;">
        <button name="action" value="prev" onclick="imageNewer()">Newer</button>
        <button name="action" value="last" onclick="imageLast()">Last</button>
        <button name="action" value="next" onclick="imageOlder()">Older</button>
    </div>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <?php if (isUserRoot()):?>
        <?php if (isset(Constants::getCameras()[$camName]) && Constants::getCameras()[$camName]["zip"]) {?>
            <button name="action" value="reorganizeImages" onclick="reorganizeImages();" title="Attention: all pictures will be reorganized!">Reorganize</button>
            <button name="action" value="zipImages" onclick="zipImages();" title="Attention: all pictures will be zipped!">Zip images</button>
        <?php } ?>
        <button name="action" value="delete" onclick="deleteImage();" title="Attention: the aktual picture will be deleted!">Delete Image</button>
        <button name="action" value="deleteday" onclick="deleteDay();" title="Attention: all pictures for the actual day will be deleted!">Delete Day</button>
    <?php endif;?>
    <span id="count" title="The aktual picture and the number of pictures">0</span>
</div>
<div id="clearboth"></div>
<div id="camimage">
    <img id="image" src=""  title="" />
</div>
<div class="footer" id="tollbarfooter">
    <div id="slider"></div>
    <?php if (isUserRoot()):?>
        <div id="actionslider"></div>
        <span title="Range" id="range">0</span>
        <button onclick="deleteRange();">Delete range</button>
        <button onclick="animateRange();">Animate range</button>
        <button onclick="showLogs()">Show logs</button>
    <?php endif;?>
    <button onclick="$('#password').attr('type','password');$('#password_div').slideDown('slow');$('#password').val(Cookie('password'))">Enter password</button>
    <button onclick="showCamImages()">Refresh pictures</button>
    <div id="message"></div>
</div>
<div id="password_div" style="display:none" >
    <input id="password"  type="password" placeholder="password" value=""/>
    <button onclick="Cookie('password',$('#password').val());$('#password_div').slideUp('slow');location.reload();">Save</button>
    <button onclick="$('#password').attr('type', 'text');">Show</button>
    <button onclick="$('#password_div').slideUp('slow');">Cancel</button>
</div>
</body>
</html>


<script>
    var date = new Date(<?php echo date_format($day, 'Y') ?>,<?php echo date_format($day, 'n') ?>-1,<?php echo date_format($day, 'j') ?>);
    var aktualImageIdx=0;
    var imageList = new Array;
    var deleteBegin=-1;
    var deleteEnd=-1;
    var animateBegin=-1;
    var animateEnd=-1;
    var animateTimer;

    $( document ).ready(function() {
        showCamImages();
        $( "#slider" ).slider({
            slide: function( event, ui ) {
                aktualImageIdx = ui.value;
                showImage();
            }
        });
        <?php if (isUserRoot()):?>
        $( "#actionslider" ).slider({
            range: true,
            min: 0,	max: 0, values: [ 0, 0 ],
            slide: function( event, ui ) {
                aktualImageIdx = ui.value;
                showImage();
                $( "#range" ).html( (ui.values[ 0 ]+1) + " - " + (ui.values[ 1 ]+1) );
            }
        });
        <?php endif;?>
        deleteOldImages();
    });


    function animateRange() {
        if ($( "#actionslider" ).slider("values").length==2) {
            animateBegin=$( "#actionslider" ).slider("values")[0];
            animateEnd=$( "#actionslider" ).slider("values")[1];
            animateImage();
        }
    }

    function animateImage() {
        if (animateTimer!=null)
            clearTimeout(animateTimer);
        if (animateBegin>=0 && animateEnd>=0 && animateBegin<=animateEnd) {
            aktualImageIdx=animateEnd--;
            showImage();
            if (animateBegin<=animateEnd)
                animateTimer = setTimeout(animateImage, 100);
        }
    }

    function deleteRange() {
        if ($( "#actionslider" ).slider("values").length==2) {
            deleteBegin=$( "#actionslider" ).slider("values")[0];
            deleteEnd=$( "#actionslider" ).slider("values")[1];
            deleteImage();
        }
    }

    <?php if ($camName!="all") {?>

    function showImage() {
        $("#akt_image").html(getTime(imageList[aktualImageIdx]));
        if (imageList.length>aktualImageIdx) {
            <?php if (Constants::getCameras()[$camName]["zip"]) { ?>
            $("#image").attr("src","getZipCamImage.php?camname=<?php echo $camName;?>&date=<?php echo $day->format("Ymd")?>&imagename="+imageList[aktualImageIdx]);
            <?php } else {?>
            $("#image").attr("src",imageList[aktualImageIdx]);
            <?php } ?>
        } else {
            $("#image").attr("src","");
        }
        $("#count").html((aktualImageIdx+1)+"/"+imageList.length);
        $( "#slider" ).slider( "option", "value", aktualImageIdx );
    }

    function deleteImage() {
        if (deleteBegin>=0 && deleteEnd>=0 && deleteBegin<=deleteEnd)
            aktualImageIdx=deleteBegin;
        $.ajax({
            <?php if (Constants::getCameras()[$camName]["zip"]) {?>
            url: "deleteZipImage.php?day=<?php echo $day->format("Ymd");?>&camname=<?php echo $camName;?>&filename="+imageList[aktualImageIdx]+"&password="+Cookie("password"),
            <?php } else {?>
            url: "deleteImage.php?filename="+imageList[aktualImageIdx]+"&password="+Cookie("password"),
            <?php } ?>
            success:function(data){
                imageList.splice(aktualImageIdx, 1);
                if (aktualImageIdx>imageList.length-1) //darauf achten, dass der Index des aktuellen Bildes immer im bereich des Arrays liegt.
                    aktualImageIdx=imageList.length-1;
                $("#count").html((aktualImageIdx+1)+"/"+imageList.length);
                if (deleteBegin==deleteEnd) {
                    $( "#slider" ).slider("option", "max", imageList.length-1);
                    <?php if (isUserRoot()):?>
                    $( "#actionslider" ).slider("option", "max", imageList.length-1);
                    <?php endif;?>
                }
                $( "#range" ).html( "0 - " + (imageList.length) );
                deleteEnd--;
                if (deleteBegin>=0 && deleteEnd>=0 && deleteBegin<=deleteEnd)
                    deleteImage(); //Rekursiv löschen
                else {
                    showImage(); //am Ende des Löschens das Bild zeigen was noch da ist

                }
            }
        });
    }
    <?php }?>

    <?php if (isUserRoot()) {?>
    //delete day images
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
        if (confirm("Please confirm, that you want reorganize all images from the selected camera?") ) {
            window.location.href="<?php echo ( $script.'?action=reorganizeImages&cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
        }
    }
    <?php }?>

    //check if old images should be deleted
    function deleteOldImages() {
        <?php if (Constants::AUTO_DELETE_OLDER_THAN_DAYS>0) {?>
        var olderThanDays=<?php echo Constants::AUTO_DELETE_OLDER_THAN_DAYS?>;
        $("#message").html("Checking for older images then "+olderThanDays+" days");
        var deleteList= new Array;
        $.ajax({
            url: "getImageListOlderThen.php",
            success:function(data){
                deleteList=data;
                if (deleteList.length>0) {
                    $("#message").html(deleteList.length+" files to be Deleted");
                    deleteFiles();
                } else {
                    $("#message").html("");
                }
            }
        });
        <?php } else {?>
        ;
        <?php } ?>
    }

    <?php if (Constants::AUTO_DELETE_OLDER_THAN_DAYS>0) {?>
    //calls an ajax funtion to delete old pictures defined in config.php
    function deleteFiles() {
        $.ajax({
            url: "getImageListOlderThen.php?action=delete",
            success:function(data){
                $("#message").html("");
            }
        });
    }
    <?php } ?>


    //Show images called by changing the camera or image type
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

    //loads over ajax the list of images for the selected camera an type
    function loadImageList(camname) {
        $("#akt_date").html(DateToString.dmy(date));
        $("#akt_image").html("");
        $("#count").html("0");
        $( "#range").html( "0 - 0");
        aktualImageIdx=0;
        $.ajax({
            url: "getImageList.php?day="+DateToString.ymd(date)+"&type="+$("input[name='type']:checked").val()+"&camname="+camname,
            success:function(data){
                imageList=data.reverse();
                if (imageList.length>0) {
                    showImage();
                    $( "#slider" ).slider("option", "max", imageList.length-1);
                    <?php if (isUserRoot()):?>
                    $( "#actionslider" ).slider("option", "max", imageList.length-1);
                    $( "#actionslider" ).slider("option", "values", [0,imageList.length-1]);
                    $( "#range" ).html( "1 - " + (imageList.length) );
                    <?php endif;?>
                }
            }
        })
    }

    function showAllLastImages() {
        $.ajax({
            url: "getAllLastImages.php",
            success:function(data){
                $("#tollbardate").hide();
                $("#tollbartop").hide();
                $("#slider").hide();
                $("#actionslider").hide();
                $("#range").hide();
                $("#imagetype").hide();
                var cams= new Array;
                var zipped= new Array;
                <?php foreach (Constants::getCameras() as $cn=>$propertys) {?>
                    cams.push('<?php echo $cn ?>');zipped.push(<?php echo $propertys["zip"]?"true":"false" ?>);
                <?php } ?>
                $("#image").css("display","none");
                for (var i=0; i<cams.length; i++) {
                    $("#image_"+cams[i]).remove();
                    $( "#image" ).after( '<a href="<?php "./".$_SERVER["SCRIPT_NAME"]?>?cam='+cams[i]+'" title="'+cams[i]+'"><img class="allimages" id="image_'+cams[i]+'" ></a>' );
                    if (zipped[i]) {
                        $("#image_"+cams[i]).attr("src","getZipCamImage.php?camname="+cams[i]+"&date="+data[cams[i]]["date"]+"&imagename="+data[cams[i]]["name"]);
                    } else {
                        $("#image_"+cams[i]).attr("src",data[cams[i]]["name"]);
                    }
                }
            }
        });
    }

    function hideAllLastImages() {
        var cams= new Array;
        <?php foreach (Constants::getCameras() as $cn=>$propertys) {?>
        cams.push('<?php echo $cn ?>');
        <?php } ?>
        $("#image").css("display","block");
        $("#tollbardate").show();
        $("#tollbartop").show();
        //$("#slider").show();
        $("#imagetype").show();
        for (var i=0; i<cams.length; i++) {
            $("#image_"+cams[i]).remove();
        }
    }

    function showLogs() {
        window.location.href="<?php echo ( 'viewLogs.php?cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
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
            $("#image").css("opacity","0.2");
            aktualImageIdx++;
            showImage();
        }
    }

    function imageNewer() {
        if (aktualImageIdx>0) {
            $("#image").css("opacity","0.2");
            aktualImageIdx--;
            showImage();
        }
    }

    function imageLast() {
        $("#image").css("opacity","0.2");
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

    //read (value=null) or write cookies
    function Cookie(name,value) {
        if (value==null) {
            a = document.cookie +";";
            while(a != "")
            {
                var cookiename = a.substring(0,a.search("="));
                cookiename = regTrim(cookiename);
                var cookiewert = a.substring(a.search("=")+1,a.search(";"));
                cookiewert = regTrim(cookiewert);
                //if(cookiewert == "")
                //	{cookiewert = a.substring(a.search("=")+1,a.length);}
                if(name === cookiename) {
                    return (decodeURIComponent(cookiewert));
                }
                i = a.search(";")+1;
                if(i == 0)
                    i = a.length;
                a = a.substring(i,a.length);
            }
            return(null);
        }
        else {
            document.cookie = name + "=" + escape (value) + "; expires=Mon, 23 Jul 2040 22:00:00 GMT";
        }
    }

    function regTrim(s) {
        if (s.substring(0,1)==" ") {
            return s.substring(1,s.length);
        }
        else {
            return s;
        }
    }


</script>


<?php

/**
 * Array of image occurences in one mounth
 * @param unknown $path
 * @param unknown $type
 * @param number $year
 * @param number $month
 */

function getBookedDays($camName,$path,$type,$year=0,$month=0){

    if ($year == 0) {
        $referenceDay    = new DateTime(date("Y")."-".date("n")."-1");
        if ($month>0)
            $referenceDay->modify('+'.$month.' month');
        else
            $referenceDay->modify($month.' month');
    } else {
        $referenceDay    = mktime(0,0,0,$month,1,$year);
    }

    return getFileCount($camName,$referenceDay,$path,$type);
}

/**
 * Count Images fpr one day
 * @param unknown $day
 * @param unknown $path
 * @param unknown $type
 * @return number
 */
function getFileCount($camName,$startday,$path,$type) {
    $ret = array();
    $dateStart=$startday->format('Ym');
    if (Constants::getCameras()[$camName]["zip"]) {
        $zip = new BiFi();
        for($day=1;$day<32;$day++) {
            $fzip=Constants::IMAGE_ROOT_PATH.$path."cam".$dateStart.($day<10?"0".$day:$day).".zip";
            $zip->open($fzip);
            $c=$zip->getArchiveFileCount($type);
            if ($c>0) $ret[$day]=$c;
        }
    } else {
        $directory = dir(Constants::IMAGE_ROOT_PATH.$path);
        while (false !== ($file = $directory->read())) {
            $fdate = (new DateTime())->setTimestamp(filemtime(Constants::IMAGE_ROOT_PATH . $path . $file));
            if (in_array(strtolower(substr($file, -4)), array(".jpg",".gif",".png")) &&
                ($type=="" || strstr($file,$type)) &&
                $dateStart == $fdate->format("Ym"))
            {
                if (isset($ret[$fdate->format("j")])) $ret[$fdate->format("j")] += 1; else $ret[$fdate->format("j")] = 1;
            }
        }
        $directory->close();
    }
    return $ret;
}

/*
 * Check if the user is root
 */
function isUserRoot() {
    return isset($_COOKIE["password"]) && $_COOKIE["password"]==Constants::PASSW_ROOT;
}

function isUserView() {
    return isset($_COOKIE["password"]) && $_COOKIE["password"]==Constants::PASSW_VIEW;
}


?>
