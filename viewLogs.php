<?PHP 
include 'config.php';

if (isset($_GET["cam"])) $camName = $_GET["cam"]; else	$camName="all";

if (isset($_GET["type"])) $camType = $_GET["type"]; else $camType=Constants::SNAP;
	
if (isset($_GET['day']) && $_GET['day']!="" ) $day=new DateTime($_GET['day']); else $day=new DateTime();

if (isset($_GET["action"])) $action = $_GET["action"]; else $action="";

$daydec=clone $day; $daydec->modify('+1 day');
$dayinc=clone $day; $dayinc->modify('-1 day');
$year=$day->format('Y');
$month=$day->format('j');

$scriptArray=explode("/",$_SERVER["SCRIPT_NAME"]);
$script=$scriptArray[sizeof($scriptArray)-1];

$systemMessage="";
include_once 'logger.class.php';
setLoggerType(loggerType::file);
logger("LogDate:".$day->format("Ymd"),loggerLevel::debug);

if ($action=="deletelogday" && isUserRoot()) {
	$count=0;
	logger("deleteLog Date:".$day->format("Ymd")." Count:".$count,loggerLevel::debug);
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
	<div id="title"><?php echo Constants::TITLE?> logs</div>
	<div class="calendarDiv">
		<?php
			require_once("calendar.class.php");
			$cal = new calendar();
			for ($i=Constants::CALENDAR_MIN_DISPLAY;$i<=Constants::CALENDAR_MAX_DISPLAY;$i++) {?>
				<div class="calendarBody"> 
					<?php $cal->showCalendar(0,$i,$camType,$camName,getBookedDays(0,$i),array(),$day); ?>
				</div>
		<?php }  ?>
	</div>
	<div class="toolbar" id="tollbartop">
		<?php if (isUserRoot()):?>
			<button name="action" value="deleteday" onclick="deleteDay();" title="Attention: all pictures for the actual day will be deleted!">Delete logs for this day</button>
		<?php endif;?>
		<span id="count" title="Log entrys">0</span>
	</div>
	<div id="clearboth"></div>
	<div class="toolbar" >
	<table id="logs">
		<th><td>Date</td></th>
	</table>
	</div>
	<div class="footer" id="tollbarfooter">
		<?php if (isUserRoot()):?>
			<button onclick="showImages()">Show Images</button>
		<?php endif;?>
		<button onclick="$('#password').attr('type','password');$('#password_div').slideDown('slow');$('#password').val(Cookie('password'))">Enter password</button>
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
	var imageList = new Array;

	$( document ).ready(function() {
	    loadLogList();
	});		



	//delete day images
	function deleteDay() {
		if (confirm("Please confirm, thas you want to delete all images from the selected day?") ) {
		    window.location.href="<?php echo ( $script.'?action=deleteday&cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
		}
	}


	//loads over ajax the list of logs for the selected date
	function loadLogList() {
	    $("#akt_date").html(DateToString.dmy(date));
		$.ajax({
		    url: "getLogList.php?day="+DateToString.ymd(date),
		    success:function(data){
				fillHitList(data);
		    }
		})
	}

	function fillHitList(data){
	    var table=document.getElementById("logs");
	    if (data["0"]!=null) {
		 	while(table.tBodies[0].rows.length>0) {table.tBodies[0].deleteRow(0);}
			for(var i=0; i<data.length; i++) { 
			    var j = 0;
			    var row=table.tBodies[0].insertRow(table.tBodies[0].rows.length);
			    if (i%2==0) row.className="trx"; else row.className="try";
			    
			    var cell1=row.insertCell(j++); cell1.innerHTML=data[i].date;
			    var cell2=row.insertCell(j++); cell2.innerHTML='<a href="http://freegeoip.net/json/'+data[i].ip+'" target="_new">'+data[i].ip+'</a>';
			    var cell3=row.insertCell(j++); cell3.innerHTML=data[i].text; 
			}
		}
	}
	
	function showImages() {
	    window.location.href="<?php echo ( 'viewAjax.php?cam='.$camName.'&type='.$camType.'&day='.date_format($day, 'Y-n-j'))?>";
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
 * Array of log occurences in one mounth 
 * @param number $year
 * @param number $month
 */

function getBookedDays($year=0,$month=0){
	
	if ($year == 0) {
		$referenceDay    = new DateTime(date("Y")."-".date("n")."-1");
		if ($month>0)
			$referenceDay->modify('+'.$month.' month');
		else
			$referenceDay->modify($month.' month');
	} else {
		$referenceDay    = mktime(0,0,0,$month,1,$year);
	}

	$ret = array();
	
	$f = fopen ("log", "r");
	$ln= 0;
	while ($line= fgets ($f)) {
		++$ln;
		$rr=explode("\t", $line,4);
		$time=substr($rr[0],0,7);
		$akttime=$referenceDay->format('Y-m');
		if($akttime==$time && $rr[2]==loggerLevel::info) {
			$time=substr($rr[0],0,10);
			for ($i=1;$i<10;$i++) {
				if ($time==$akttime.'-0'.$i )
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
