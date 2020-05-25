<?PHP
include 'config.class.php';
include_once Config::$lpfw.'logger.class.php';
include_once Config::$lpfw.'appl.class.php';

use \maierlabs\lpfw\Appl as Appl;

if (isset($_GET["cam"])) $camName = $_GET["cam"]; else	$camName="all";

if (isset($_GET['day']) && $_GET['day']!="" ) $day=new DateTime($_GET['day']); else $day=new DateTime();

if (isset($_GET["action"])) $action = $_GET["action"]; else $action="";

$scriptArray=explode("/",$_SERVER["SCRIPT_NAME"]);
$script=$scriptArray[sizeof($scriptArray)-1];
$dateEarlier = clone ($day); $dateEarlier->modify("-1 month");
$dateLater = clone ($day); $dateLater->modify("1 month");

$systemMessage="";
\maierlabs\lpfw\Logger::setLoggerType(\maierlabs\lpfw\LoggerType::file, Config::jc()->IMAGE_ROOT_PATH.'log');


if ($action=="deleteday" && Config::isUserRoot()) {
    $count=0;$countAll=0;
    $f = fopen (Config::jc()->IMAGE_ROOT_PATH.'log', "r");
    $w = fopen (Config::jc()->IMAGE_ROOT_PATH.'new.log', "w");
    while ($line= fgets ($f)) {
        $rr=explode("\t", $line,5);
        $time=substr($rr[0],0,10);
        $akttime=$day->format('Y-m-d');
        if($akttime==$time && $rr[1]==maierlabs\lpfw\LoggerLevel::info) {
            $count++;
        } else {
            fputs($w,$line);
            $countAll++;
        }
    }
    fclose($f);
    fclose($w);
    if (unlink(Config::jc()->IMAGE_ROOT_PATH.'log'))
        rename(Config::jc()->IMAGE_ROOT_PATH.'new.log',Config::jc()->IMAGE_ROOT_PATH.'log');

    $systemMessage="Entries deleted:".$count. " Lines remained:".$countAll;
    maierlabs\lpfw\Logger::_("deleteLog Date:".$day->format("Ymd")." Count:".$count,maierlabs\lpfw\LoggerLevel::debug);
}

$countInfo = 0;
$countDebug = 0;
$countError = 0;
$f = fopen(Config::jc()->IMAGE_ROOT_PATH . 'log', "r");
while ($line = fgets($f)) {
    $rr = explode("\t", $line, 5);
    if ($rr[1] == maierlabs\lpfw\LoggerLevel::info) $countInfo++;
    elseif ($rr[1] == maierlabs\lpfw\LoggerLevel::debug) $countDebug++;
    elseif ($rr[1] == maierlabs\lpfw\LoggerLevel::error) $countError++;
}
fclose($f);
$systemMessage="Entries info:".$countInfo. " debug:".$countDebug." error:".$countError;

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

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body style="font-family: Arial;">
<div id="title"><?php echo Config::jc()->TITLE?> logs</div>
<?php if ($systemMessage!="") :?>
    <div id="systemMessage" style="background-color: lightgray;padding-bottom: 10px"><div style="background-color: #ffe030;padding: 5px;margin: 0px 10px 0px 10px;border-radius: 5px;">
        <?php echo ($systemMessage);?>
            <button style="height:23px;float: right" onclick="$('#systemMessage').hide('show');"><span class="glyphicon glyphicon-remove-circle" style="position: relative;top: -4px;"></span></button>
    </div></div>
<?php endif;?>
<div class="calendarDiv">
    <form style="display: inline-block;"><button name="day" value="<?php echo $dateEarlier->format('Y-m-d')?>"><span class="glyphicon glyphicon-backward"> </span></button></form>
    <?php
    require_once("calendar.class.php");
    $cal = new calendar();
    $calendarDate = clone ($day);
    $calendarDate =$calendarDate ->modify(Config::jc()->CALENDAR_MIN_DISPLAY." month");
    for ($i=-3;$i<=0;$i++) {
        ?>
        <div class="calendarBody">
            <?php $cal->showCalendar($calendarDate->format("Y"),$calendarDate->format("n"),getParam("type"),$camName,getBookedDays($calendarDate->format("Y"),$calendarDate->format("n"),getParam("type")),array(),$day,Appl::__("TIMEZONE")); ?>
        </div>
    <?php $calendarDate->modify("1 month"); } ?>
    <form style="display: inline-block;"><button name="day" value="<?php echo $dateLater->format('Y-m-d')?>"><span class="glyphicon glyphicon-forward"> </span></button></form>
</div>
<div class="toolbar" id="tollbartop">
    <?php if (Config::isUserRoot()):?>
        <button name="action" value="deleteday" onclick="deleteDay();" title="<?php Appl::_("Attention: all logs for the actual day will be deleted!")?>">
            <?php Appl::_("Delete logs for this day")?>
        </button>
        <span id="count" title="Log entrys">0</span>
        <select id="paramtype" onchange="logFilter();" style="padding: 8px; border-radius: 10px;">
            <option <?php echo getParam("type","all")=="all"?"selected":""?> value="all">All</option>
            <option <?php echo getParam("type")==""?"selected":""?> value="">Anonimous</option>
            <option <?php echo getParam("type")=="W"?"selected":""?> value="W">Viewer</option>
            <option <?php echo getParam("type")=="R"?"selected":""?> value="R">Root</option>
        </select>
    <?php endif;?>
</div>
<div id="clearboth"></div>
<div class="toolbar" >
    <table  class="table table-hover">
        <thead class="thead-light">
        <tr >
            <th><?php Appl::_("Date")?></th>
            <th><?php Appl::_("Type")?></th>
            <th><?php Appl::_("Ip")?></th>
            <th><?php Appl::_("Link")?></th>
            <th><?php Appl::_("View date")?></th>
            <th><?php Appl::_("Type")?></th>
            <th><?php Appl::_("Cam")?></th>
            <th><?php Appl::_("User")?></th>
        </tr>
        </thead>
        <?php echoLogsForDate($day,getParam("type"));?>
    </table>
</div>
<div class="footer" id="tollbarfooter">
    <button onclick="showImages()"><?php Appl::_("Show Images")?></button>
    <?php if (Config::isUserRoot() || Config::isUserView()) {?>
        <button onclick="$('#password').attr('type','password');$('#password_div').slideDown('slow');$('#password').val(Cookie('password'))"><span class="glyphicon glyphicon-log-out"></span> <?php Appl::_("Log out")?></button>
    <?php } else {?>
        <button onclick="$('#password').attr('type','password');$('#password_div').slideDown('slow');$('#password').val(Cookie('password'))"><span class="glyphicon glyphicon-log-in"></span> <?php Appl::_("Log in")?></button>
    <?php }?>
</div>
<div id="password_div" style="display:none" >
    <input id="password"  type="password" placeholder="password" value=""/>
    <button onclick="Cookie('password',$('#password').val());$('#password_div').slideUp('slow');location.reload();"><?php Appl::_("Save")?></button>
    <button onclick="$('#password').attr('type', 'text');"><?php Appl::_("Show")?></button>
    <button onclick="$('#password_div').slideUp('slow');"><?php Appl::_("Cancel")?></button>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>


<script>
    $( document ).ready(function() {
        setTimeout(function(){ $("#systemMessage").hide("slow"); }, 10000);
    });

    function deleteDay() {
        if (confirm("Please confirm, thas you want to delete all logs from the selected day?") ) {
            window.location.href="<?php echo ( $script.'?action=deleteday&cam='.$camName.'&type='.getParam("type").'&day='.date_format($day, 'Y-n-j'))?>";
        }
    }

    function logFilter() {
        window.location.href="<?php echo ( $script.'?action=view&cam='.$camName.'&day='.date_format($day, 'Y-n-j'))?>"+"&type="+$("#paramtype").val();
    }

    function showImages() {
        window.location.href="<?php echo ( 'viewAjax?cam='.$camName.'&type=&day='.date_format($day, 'Y-n-j'))?>";
    }

    function showip(ip) {
        $.ajax({
            url: "ajaxGetIpInfo?ip="+ip
        }).success(function(data) {
            $(".modal-title").html("<?php Appl::_("IP address")?>:"+ip+"<?php Appl::_("geo data")?>");
            var text = "<?php Appl::_("Country")?>:"+data.country+"<br/>";
            text +="<?php Appl::_("Zipcode")?>:"+data.zip+"<br/>";
            text +="<?php Appl::_("City")?>:"+data.city+"<br/>";
            text += "<?php Appl::_("Country")?>:"+data.x.country_name+"<br/>";
            text +="<?php Appl::_("Zipcode")?>:"+data.x.zip+"<br/>";
            text +="<?php Appl::_("City")?>:"+data.x.city+"<br/>";
            text += "<img src=\""+data.x.location.country_flag+"\" style=\"height:35px\" /><br/>";
            text += "ISP:"+data.isp+"<br/>";
            text += "ORG:"+data.org+"<br/>";
            text += "AS:"+data.as+"<br/>";
            $(".modal-body").html(text);
            $('#myModal').modal({show: 'false' });
        });
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
     * @param int $year
     * @param int $month
     * @param string $user
     * @return array Array of log occurences in one mounth
     * @throws Exception
     */
    function getBookedDays($year=0,$month=0,$type=""){

        if ($year == 0) {
            $referenceDay    = new DateTime(date("Y")."-".date("n")."-1");
            if ($month>0)
                $referenceDay->modify('+'.$month.' month');
            else
                $referenceDay->modify($month.' month');
        } else {
            $referenceDay    = (new DateTime)->setTimestamp(mktime(0,0,0,$month,1,$year));
        }

        $ret = array();

        $f = fopen (Config::jc()->IMAGE_ROOT_PATH.'log', "r");
        $ln= 0;
        while ($line= fgets ($f)) {
            ++$ln;
            $rr=explode("\t", $line);
            $time=substr($rr[0],0,7);
            $akttime=$referenceDay->format('Y-m');
            if($akttime==$time && $rr[1]==\maierlabs\lpfw\LoggerLevel::info && ((isset($rr[8]) && $rr[8]=="User:".$type) || $type=="all")) {
                $time=substr($rr[0],0,10);
                for ($i=1;$i<10;$i++) {
                    if ($time==$akttime.'-0'.$i  )
                        if (isset($ret[$i])) $ret[$i] +=1;	else $ret[$i] =1;
                }
                for ($i=10;$i<32;$i++) {
                    if ($time==$akttime.'-'.$i )
                        if (isset($ret[$i])) $ret[$i] +=1;	else $ret[$i] =1;
                }

            }
        }
        fclose ($f);
        return $ret;
    }

    function echoLogsForDate($day,$type) {
        if(Config::isUserRoot()) {
            $f = fopen (Config::jc()->IMAGE_ROOT_PATH.'log', "r");
            $ln= 0;
            while ($line= fgets ($f)) {
                ++$ln;
                $rr=explode("\t", $line);
                $time=substr($rr[0],0,10);
                $akttime=$day->format('Y-m-d');
                if($akttime==$time && $rr[1]==maierlabs\lpfw\LoggerLevel::info && ((isset($rr[8]) && $rr[8]=="User:".$type) || $type=="all")) {
                    echo('<tr><td>'.$rr[0].'</td><td>'.$rr[1].'</td><td><a href="javascript:showip(\''.$rr[2].'\')">'.$rr[2].'</a></td>');
                    echo('<td>'.$rr[3].'</td><td>'.$rr[5].'</td><td>'.$rr[6].'</td>');
                    if (sizeof($rr)>=8) {
                        echo('<td>' . $rr[7] . '</td><td>' . $rr[8] . '</td></tr>');
                    }
                }
            }
            fclose ($f);
        }
    }
?>
