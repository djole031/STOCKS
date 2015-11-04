<?php
include("../includes/connect.php");

function masterLoop(){
	$mainTickerFile = fopen("../tickerMaster.txt", "r");
	while(!feof($mainTickerFile)){
		$companyTicker = fgets($mainTickerFile);
		$companyTicker = trim($companyTicker);
		
		$nextDayIncrease = 0;
		$nextDayDecrease = 0;
		$nextDayNoChange = 0;
		$total = 0;
		
		$sumOfIncreases = 0;
		$sumOfDecreases = 0;
		
		$sql = "SELECT date, percent_change FROM $companyTicker WHERE percent_change < '0' ORDER BY date DESC";
		$result = mysql_query($sql);
		
		if($result){
			while($row = mysql_fetch_array($result)){ //asocijativni array vraca
				$date = $row['date'];
				$percent_change = $row['percent_change'];
				$sql2 = "SELECT date, percent_change FROM $companyTicker WHERE date>'$date' ORDER BY date ASC LIMIT 1";
				$result2 = mysql_query($sql2);
				$numberOfRows = mysql_num_rows($result2);
				
				if($numberOfRows==1){
					$row2 = mysql_fetch_row($result2);
					$tom_date = $row2[0];
					$tom_percent_change = $row2[1];
					
					if($tom_percent_change > 0){
						$nextDayIncrease++;
						$sumOfIncreases+=$tom_percent_change;
						$total++;
					}else if($tom_percent_change < 0){
						$nextDayDecrease++;
						$sumOfDecreases+=$tom_percent_change;
						$total++;
					}else{
						$nextDayNoChange++;
						$total++;
					}
				}else if($numberOfRows==0){
					//nema podataka za taj dan sledeci
				}else{
					echo "You have an error in analysis_a.";
				}
			}
		}else{
			echo "unable to select $companyTicker <br / >";
		}
		
		$nextDayIncreasePercent = ($nextDayIncrease/$total)*100;
		$nextDayDecreasePercent = ($nextDayDecrease/$total)*100;
		$averageIncreasePercent = $sumOfIncreases/$nextDayIncrease;
		$averageDecreasePercent = $sumOfDecreases/$nextDayDecrease;
		
		insertIntoResultTable();
	}
}

function insertIntoResultTable($companyTicker, $nextDayIncrease, $nextDayIncreasePercent, $averageIncreasePercent, $nextDayDecrease, $nextDayDecreasePercent, $averageDecreasePercent){
	
	$djolesBuyValue = $nextDayIncreasePercent * $averageIncreasePercent;
	$djolesSellValue = $nextDayDecreasePercent * $averageDecreasePercent;
	
	$query = "SELECT * FROM analysisA WHERE ticker='$companyTicker' ";
	$result = mysql_query($query);
	$numberOfRows = mysql_num_rows($result);
	
	if($numberOfRows==1){
		$sql = "UPDATE analysisA SET ticker='$companyTicker',dayInc='$nextDayIncrease', pctOfDayInc='$nextDayIncreasePercent', avgIncPct='$averageIncreasePercent', dayDec='nextDayDecrease', pctOfDayDec='$nextDayDecreasePercent', avgDecPct='$averageDecreasePercent', djolesBuyValue='$djolesBuyValue', djolesSellValue='$djolesSellValue' WHERE ticker='$companyTicker' ";
		mysql_query($sql);
	}else{
		$sql = "INSERT INTO analysisA (ticker, dayInc, pctOfDayInc, avgIncPct, dayDec, pctOfDayDec, avgDecPct, djolesBuyValue, djolesSellValue) VALUES('$companyTicker', '$nextDayIncrease', '$nextDayIncreasePercent', '$averageIncreasePercent', 'nextDayDecrease', '$nextDayDecreasePercent', '$averageDecreasePercent', '$djolesBuyValue', '$djolesSellValue')";
		mysql_query($sql);
	}
}

masterLoop();

?>