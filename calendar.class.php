<?php
class calendar{
	
	
    function showCalendar($year,$month,$type,$cam,$booktDays){
    	
		$weekDays=array("So","Mo","Th","We","Th","Fr","Sa");   
	
	    // Get today, reference day, first day and last day info
	    if (($year == 0) || ($month == 0)){
	       $referenceDay    = getdate();
	    } else {
	       $referenceDay    = getdate(mktime(0,0,0,$month,1,$year));
	    }
	    $firstDay = getdate(mktime(0,0,0,$referenceDay['mon'],1,$referenceDay['year']));
		$lastDay  = getdate(mktime(0,0,0,$referenceDay['mon']+1,0,$referenceDay['year']));
		
		// Create a table with the necessary header informations
		echo '<table class="month">';
		echo '  <tr><th colspan="7">'.$referenceDay['month']." - ".$referenceDay['year']."</th></tr>";
	
		//fist day for english language
		$firstDayOfTheWeek = 1;
		
		//Display the weekdays So Mo Th .... 
		echo '<tr class="calendarCell calendarDays" >';
		for ($i=$firstDayOfTheWeek;$i<$firstDayOfTheWeek+7;$i++) {
				echo '<td>'.$weekDays[$i % 7].'</td>';
		}
		echo '</tr>' ;
		
		
		// Display the first calendar row with correct positioning so start with empty cells
		echo '<tr>';
		if ($firstDay['wday'] != $firstDayOfTheWeek)
		{ 
			if ($firstDay['wday']==0) $firstDay['wday']=7;
			for($i=$firstDayOfTheWeek;$i<$firstDay['wday'];$i++){
				echo '<td>&nbsp;</td>';
			}
	    }
		$actday = 0;
		
		//Display the first week
		for($i=$firstDay['wday'];$i<=6+$firstDayOfTheWeek;$i++){
			$this->displayDay(++$actday, $month, $booktDays, $cam, $type);
		}
		echo '</tr>';
		
		//Get how many complete weeks are in the actual month
		$fullWeeks = floor(($lastDay['mday']-$actday)/7);
		
		for ($i=0;$i<$fullWeeks;$i++){
			echo '<tr>';
			for ($j=0;$j<7;$j++){
				$this->displayDay(++$actday, $month, $booktDays, $cam, $type);			
			}
			echo '</tr>';
		}
		
		//Now display the rest of the month
		if ($actday < $lastDay['mday']){
			echo '<tr>';
			
			for ($i=0; $i<7;$i++){
				if ($actday <= $lastDay['mday']){
					$this->displayDay(++$actday, $month, $booktDays, $cam, $type);				
				} else {
					echo '<td>&nbsp;</td>';
				}
			}
			
			
			echo '</tr>';
		}
		
		echo '</table>';
	}

	/**
	 * Display the actual day as link of the day has "bookt days"
	 * @param unknown $actday
	 * @param unknown $today
	 * @param unknown $month
	 * @param unknown $booktDays
	 * @param unknown $cam
	 * @param unknown $type
	 */
	private function displayDay($actday,$month,$booktDays,$cam,$type) {
		$scriptArray=explode("/",$_SERVER["SCRIPT_NAME"]);
		$script=$scriptArray[sizeof($scriptArray)-1];
		$today    = getdate();
		
		if (($actday == $today['mday']) && ($today['mon'] == $month)) {
			$class = 'class="calendarToday ';
		} else {
			$class = 'class="';
		}
		if (array_key_exists($actday, $booktDays)) {
			$class .='calendarCell ';
			if ($booktDays[$actday]<10) {
				$class .='calendarDayNo1"';
			} elseif ($booktDays[$actday]<300) {
				$class .='calendarDayNo2"';
			} else {
				$class .='calendarDayNo3"';
			}
			echo '<td '.$class.'><a title="Images:'.$booktDays[$actday].'" href="'.$script.'?cam='.$cam.'&type='.$type.'&day='.date("Y").'-'.$month.'-'.$actday.'">'.$actday.'</a></td>';
		} else {
			$class .='calendarCell calendarDay"';
			echo '<td '.$class.'>'.$actday.'</a></td>';
		}
	}
}
?>