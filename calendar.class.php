<?php
class calendar{
	
	
    function showCalendar($year,$month,$type,$cam,$booktDays=array(),$disabledDays=array(),$selectedDay=null){
    	
		$weekDays=array("So","Mo","Th","We","Th","Fr","Sa");   
		
		// Get today, reference day, first day and last day info
		
		if ($year == 0) {
			$referenceDay    = new DateTime(date("Y")."-".date("n")."-1");
			if ($month>0)
				$referenceDay->modify('+'.$month.' month');
			else
				$referenceDay->modify($month.' month');
			$month=$referenceDay->format('n');
			$year=$referenceDay->format('Y');
		} else {
				$referenceDay    = (new DateTime)->setTimestamp(mktime(0,0,0,$month,1,$year));
		}
		
	    $firstDay = new DateTime($referenceDay->format('n').'/01/'.$referenceDay->format('Y'));
		$lastDay  = clone($firstDay); $lastDay->modify('+1 month');$lastDay->modify('-1 day');
		
		// Create a table with the necessary header informations
		echo '<table class="month">';
		echo '  <tr><th colspan="7">'.$referenceDay->format('F')." - ".$referenceDay->format('Y')."</th></tr>";
	
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
		$firstDayW=$firstDay->format('N');
		if ($firstDayW != $firstDayOfTheWeek)
		{ 
			if ($firstDayW==0) $firstDayW=7;
			for($i=$firstDayOfTheWeek;$i<$firstDayW;$i++){
				echo '<td>&nbsp;</td>';
			}
	    }
		$actday = 0;
		
		//Display the first week
		for($i=$firstDayW;$i<=6+$firstDayOfTheWeek;$i++){
			$this->displayDay(++$actday, $month,$year, $booktDays,$disabledDays,$selectedDay, $cam, $type);
		}
		echo '</tr>';

		//Get how many complete weeks are in the actual month
		$fullWeeks = floor(($lastDay->format('j')-$actday)/7);
		
		for ($i=0;$i<$fullWeeks;$i++){
			echo '<tr>';
			for ($j=0;$j<7;$j++){
				$this->displayDay(++$actday, $month,$year, $booktDays,$disabledDays,$selectedDay, $cam, $type);			
			}
			echo '</tr>';
		}
		
		//Now display the rest of the month
		if ($actday < $lastDay->format('j')){
			echo '<tr>';
			
			for ($i=0; $i<7;$i++){
				if ($actday < $lastDay->format('j')){
					$this->displayDay(++$actday, $month,$year, $booktDays,$disabledDays,$selectedDay, $cam, $type);				
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
	private function displayDay($actday,$month,$year,$booktDays,$disabledDays,$selectedDay,$cam,$type) {
		$scriptArray=explode("/",$_SERVER["SCRIPT_NAME"]);
		$script=$scriptArray[sizeof($scriptArray)-1];
		$today    = new DateTime();
		
		if (($actday == $today->format('d')) && ($today->format('n') == $month)) {
			$class = 'class="calendarToday ';
		} else {
			$class = 'class="';
		}
		if (($actday == $selectedDay->format('d')) && ($selectedDay->format('n') == $month)) {
			$class .= 'calenderAktDay ';
		} 

		if (array_key_exists($actday, $disabledDays)) {
			echo '<td class="calendarCell calendarDayDisabled">'.$actday.'</td>';
		} else {
			if (array_key_exists($actday, $booktDays)) {
				$class .='calendarCell ';
				if ($booktDays[$actday]<10) {
					$class .='calendarDayNo1"';
				} elseif ($booktDays[$actday]<300) {
					$class .='calendarDayNo2"';
				} else {
					$class .='calendarDayNo3"';
				}
				echo '<td '.$class.'><a title="Images:'.$booktDays[$actday].'" href="'.$script.'?cam='.$cam.'&type='.$type.'&day='.$year.'-'.$month.'-'.$actday.'">'.$actday.'</a></td>';
			} else {
				$class .='calendarCell calendarDay"';
				echo '<td '.$class.'>'.$actday.'</td>';
			}
		}
	}
}
?>