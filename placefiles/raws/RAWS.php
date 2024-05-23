<?php
#---------------------------------------------------------------------------
/*
Program: RAWS.php

Purpose: generate a GRLevelX placefile to display RAWS data

Usage:   invoke as a placefile in the GrlevelX placefile manager

Requires: decoded RAWS data produced by get-RAWS-data.php
          RAWS-data-inc.php 

Author: Ken True - webmaster@saratoga-weather.org

Acknowledgement:
  
   Special thanks to Mike Davis, W1ARN of the National Weather Service, Nashville TN office
   for his testing/feedback during development.   

Copyright (C) 2023  Ken True - webmaster@saratoga-weather.org

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	If you enhance or bug-fix the program, please share your modifications
  to the GitHub distribution so others can enjoy your updates.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <https://www.gnu.org/licenses/>.

Version 1.00 - 28-Sep-2023 - initial release
Version 1.01 - 30-Mar-2024 - added display of get-RAWS-data error message

*/
#---------------------------------------------------------------------------

#-----------settings--------------------------------------------------------
date_default_timezone_set('UTC');
$timeFormat = "l, M d";  // time display for date() in popup
#-----------end of settings-------------------------------------------------

$Version = "RAWS.php V1.01 - 30-Mar-2024 - webmaster@saratoga-weather.org";
global $Version,$timeFormat;

// self downloader
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   //--self downloader --
   $filenameReal = __FILE__;
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   
   readfile($filenameReal);
   exit;
}

header('Content-type: text/plain,charset=ISO-8859-1');

if(file_exists("RAWS-data-inc.php")) {
	include_once("RAWS-data-inc.php");
} else {
	print "Warning: RAWS-data-inc.php file not found. Aborting.\n";
	exit;
}

if(isset($_GET['lat'])) {$latitude = $_GET['lat'];}
if(isset($_GET['lon'])) {$longitude = $_GET['lon'];}
if(isset($_GET['version'])) {$version = $_GET['version'];}

if(isset($latitude) and !is_numeric($latitude)) {
	print "Bad latitude spec.";
	exit;
}
if(isset($latitude) and $latitude >= -90.0 and $latitude <= 90.0) {
	# OK latitude
} else {
	print "Latitude outside range -90.0 to +90.0\n";
	exit;
}

if(isset($longitude) and !is_numeric($longitude)) {
	print "Bad longitude spec.";
	exit;
}
if(isset($longitude) and $longitude >= -180.0 and $longitude <= 180.0) {
	# OK longitude
} else {
	print "Longitude outside range -180.0 to +180.0\n";
	exit;
}	
if(!isset($latitude) or !isset($longitude) or !isset($version)) {
	print "This script only runs via a GRlevelX placefile manager.";
	exit();
}

/*
Sample entry annotated:


*/
#file_put_contents('debug.txt',"?version=1.5&lat=$latitude&lon=$longitude\n");
gen_header();

if(isset($RAWSData['error'])) {

	 $offset = -25;
	 print  "; Error message in RAWSData\n";
	 print  "Object: $latitude,$longitude\n";
	 print  "Color: 255 255 255\n";
	 $errLines = explode("\n",$RAWSData['error']);
	 foreach ($errLines as $i => $error) {
	   print  "Text: 1, $offset, 1, \"$error\"\n";
		 $offset = $offset-15;
	 }
	 print  "End:\n";
	 return;
	
}

foreach ($RAWSData as $idx => $M) {
	
  if(!isset($M['lat']) or !isset($M['lon'])) {
		continue;
	}
	list($miles,$km,$bearingDeg,$bearingWR) = 
	  GML_distance((float)$latitude, (float)$longitude,(float)$M['lat'], (float)$M['lon']);
	if($miles <= 250) {
		gen_entry($M,$miles,$bearingWR);
	}
}

#---------------------------------------------------------------------------
function gen_header() {
	global $Version;
	$title = "NIFC RAWS Observations";
	print '; placefile with conditions generated by '.$Version. '
; Generated on '.gmdate('r').'
;
Title: '.$title.' - '.gmdate('r').' - Saratoga-Weather.org 
Refresh: 5
Color: 255 255 255
Font: 1, 12, 1, Arial
IconFile: 1, 19, 43, 2, 43, windbarbs-kt-white.png
IconFile: 2, 17, 17, 8, 8, RAWS-icons.png
Threshold: 999

';
	
}

#---------------------------------------------------------------------------

function gen_entry($M,$miles,$bearingWR) {
/*
  Purpose: generate the detail entry with popup for the RAWS report

*/	
  global $RAWSInfo;
/*
$RAWSData = array (
  0 => 
array (
    'stationid' => 'LSGC1',
    'stationname' => 'LOS GATOS',
    'elev' => 1842,
    'lat' => '37.204170',
    'lon' => '-121.950830',
    'obsdate' => 'Thu, 28 Sep 2023 03:32:31 +0000',
    'UTC' => '2023-09-28T03:32:31+00:00',
    'age' => '2943 secs',
    'loc' => 'Santa Clara, CA',
    'owner' => 'S&PF,SANTA CLARA COUNTY, FIRE DEPT',
    'site' => 'n/a',
    'dwinddir' => '17',
    'wdir' => 'NNE',
    'dwind' => '5',
    'wind' => '5 mph',
    'dgust' => '11',
    'gust' => '11 mph',
    'dgustdir' => '29',
    'gustdir' => 'NNE',
    'dtemp' => '59',
    'temp' => '59 F',
    'dftemp' => '60',
    'ftemp' => '60 F',
    'dfmoist' => '9.9',
    'fmoist' => '9.9%',
    'dhum' => '95',
    'hum' => '95%',
    'ddew' => 58.0,
    'dew' => '58 F',
    'dheatidx' => '',
    'heatidx' => ' F',
    'dwindch' => '',
    'windch' => ' F',
    'drain' => '41.82',
    'rain' => '41.82 in (accum. total)',
    'dsolar' => '0',
    'solar' => '0 W/m2',
  ),
*/
  $RAWSid = $M['stationid'];
	
  print "; generate $RAWSid ".$M['stationname']." at ".$M['lat'].','.$M['lon']." at $miles miles $bearingWR \n";
	
  $output = 'Object: '.$M['lat'].','.$M['lon']. "\n";
  $output .= "Threshold: 999\n";
  if(isset($M['dwinddir'])) {
  	$barbno = isset($M['dwind'])?pick_wind_icon($M['dwind']):-1;
	  if($barbno > 0) {
      $output .= "Icon: 0,0,".$M['dwinddir'].",1,".$barbno."\n";
	  }
	}
	if(isset($M['dtemp']) and is_numeric($M['dtemp'])) {
    $output .= "Text: -17, 13, 1, ".round($M['dtemp'],0)."\n";
	}
  if(isset($M['ddew'])) {
    $output .= "Text: -17, -13, 1, ".$M['ddew']."\n";
	}
	if(!empty($M['dheatidx'])) {
	  $output .= "Color: 252 78 42\n";
		$output .= "Text: -30, 0, 1, ".$M['dheatidx']."\n";
    $output .= "Color: 255 255 255\n";
	}
	if(!empty($M['dwindch'])) {
	  $output .= "Color: 2 145 255\n";
		$output .= "Text: -30, 0, 1, ".$M['dwindch']."\n";
    $output .= "Color: 255 255 255\n";
	}

	$icon = 4;

  $output .= "Icon: 0,0,000,2,$icon,\"".gen_popup($M)."\"\n";
  $output .= "End:\n\n";

  print $output;	
	
}
#---------------------------------------------------------------------------

function gen_popup($M) {
	global $timeFormat;
	# note use '\n' to end each line so GRLevelX will do a new-line in the popup.
	
	$out =  $M['stationid']." (".$M['stationname'].')\n   ('.$M['lat'].",".$M['lon'];
	$out .= !empty($M['elev'])?" @ ".$M['elev'].' ft)\n':')\n';
	$out .= "County: ".$M['loc'].'\n';
	$out .= "----------------------------------------------------------".'\n';
	$obsTime = strtotime($M['UTC']);
	$out .= "Time:  ".date($timeFormat,$obsTime)." (".gmdate('H:i',$obsTime).'Z)\n';
	$out .= "Age:   ".$M['age'].'\n';
	$out .= isset($M['temp'])? "Temp:  ".$M['temp'].'\n':'';
	$out .= isset($M['hum'])?  "Hum:   ".$M['hum'].'\n':'';
	if(isset($M['dew'])) {
		$out .= 'Tdp:   '.$M['dew'].'\n';
	}
	if(isset($M['dftemp']) and $M['dftemp'] !== 'NO') {
		$out .= "Tfuel: ".$M['ftemp'].'\n';
	}
  if(isset($M['dfmoist']) and $M['dfmoist'] !== 'NO') {
		$out .= 'Fmoist:'.$M['fmoist'].'\n';
	}
	if(isset($M['rain'])) {
		$out .= 'Rain:  '.$M['rain'].'\n';
	}

  if(isset($M['wind']) ) {	
  	$out .= "Wind:  ";
		$out .= isset($M['wdir'])?$M['wdir']:'??';
		$out .= " ".$M['wind'].'\n';
  	$out .= isset($M['gust'])?'       high '.$M['gust']:'';
		$out .= isset($M['gustdir'])?' from '.$M['gustdir']:'';
		$out .= '\n';
	}

	if(isset($M['solar'])) {
		$out .= 'Solar: '.$M['solar'].'\n';
	}
	if(!empty($M['dheatidx'])) {
		$out .='\n'."Heat Index: ".$M['heatidx'].'\n';
	}
	if(!empty($M['dwindch'])) {
		$out .='\n'."Wind Chill: ".$M['windch'].'\n';
	}

	$out .= (isset($M['site']) or isset($M['owner']))?'\n':'';
	
  if(isset($M['owner'])) {
		$out .= "Owner: ".$M['owner'].'\n';
	}
  if(isset($M['site'])) {
		$out .= "Site:  ".$M['site'].'\n';
	}
	
# last line of popup
	$out .= "----------------------------------------------------------";
	$out = str_replace('"',"'",$out);
  return($out);	
}

#---------------------------------------------------------------------------

function pick_wind_icon($speed) {
	# return icon number based on speed in 5mph chunks using https://www.weather.gov/hfo/windbarbinfo
	# as a guide.for windbarbs_75_new.png image
	
	static $barbs = array(2,8,14,20,25,31,37,43,60,66,71,77,83,89,94,100,112,117/*,123*/); #in MPH
	#static $barbs = array(2,7,12,17,22,27,32,37,52,47,52,57,62,67,77,82,87,92,97,102); # in KTS
	if($speed > 117) {return(17);}
	for ($i=0;$i<count($barbs);$i++){
	  if($speed <= $barbs[$i]) {break;}
  }

	if($i > 17) {$i = 17; }
	return($i);
	
}

#---------------------------------------------------------------------------

// ------------ distance calculation function ---------------------
   
    //**************************************
    //     
    // Name: Calculate Distance and Radius u
    //     sing Latitude and Longitude in PHP
    // Description:This function calculates 
    //     the distance between two locations by us
    //     ing latitude and longitude from ZIP code
    //     , postal code or postcode. The result is
    //     available in miles, kilometers or nautic
    //     al miles based on great circle distance 
    //     calculation. 
    // By: ZipCodeWorld
    //
    //This code is copyrighted and has
	// limited warranties.Please see http://
    //     www.Planet-Source-Code.com/vb/scripts/Sh
    //     owCode.asp?txtCodeId=1848&lngWId=8    //for details.    //**************************************
    //     
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
    /*:: :*/
    /*:: This routine calculates the distance between two points (given the :*/
    /*:: latitude/longitude of those points). It is being used to calculate :*/
    /*:: the distance between two ZIP Codes or Postal Codes using our:*/
    /*:: ZIPCodeWorld(TM) and PostalCodeWorld(TM) products. :*/
    /*:: :*/
    /*:: Definitions::*/
    /*::South latitudes are negative, east longitudes are positive:*/
    /*:: :*/
    /*:: Passed to function::*/
    /*::lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees) :*/
    /*::lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees) :*/
    /*::unit = the unit you desire for results:*/
    /*::where: 'M' is statute miles:*/
    /*:: 'K' is kilometers (default):*/
    /*:: 'N' is nautical miles :*/
    /*:: United States ZIP Code/ Canadian Postal Code databases with latitude & :*/
    /*:: longitude are available at http://www.zipcodeworld.com :*/
    /*:: :*/
    /*:: For enquiries, please contact sales@zipcodeworld.com:*/
    /*:: :*/
    /*:: Official Web site: http://www.zipcodeworld.com :*/
    /*:: :*/
    /*:: Hexa Software Development Center � All Rights Reserved 2004:*/
    /*:: :*/
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
  function GML_distance($lat1, $lon1, $lat2, $lon2) { 
    $theta = $lon1 - $lon2; 
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
    $dist = acos($dist); 
    $dist = rad2deg($dist); 
    $miles = $dist * 60 * 1.1515;
//    $unit = strtoupper($unit);
	$bearingDeg = fmod((rad2deg(atan2(sin(deg2rad($lon2) - deg2rad($lon1)) * 
	   cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - 
	   sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)))) + 360), 360);

	$bearingWR = GML_direction($bearingDeg);
	
    $km = round($miles * 1.609344); 
    $kts = round($miles * 0.8684);
	$miles = round($miles);
	return(array($miles,$km,$bearingDeg,$bearingWR));
  }

#---------------------------------------------------------------------------

function GML_direction($degrees) {
   // figure out a text value for compass direction
   // Given the direction, return the text label
   // for that value.  16 point compass
   $winddir = $degrees;
   if ($winddir == "n/a") { return($winddir); }

  if (!isset($winddir)) {
    return "---";
  }
  if (!is_numeric($winddir)) {
	return($winddir);
  }
  $windlabel = array ("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S",
	 "SSW","SW", "WSW", "W", "WNW", "NW", "NNW");
  $dir = $windlabel[ (integer)fmod((($winddir + 11) / 22.5),16) ];
  return($dir);

} // end function GML_direction	

# end RAWS.php