<?php
if(isset($_GET['lat']) && isset($_GET['lon']) && !empty($_GET['lat']) && !empty($_GET['lon'])){$lat=$_GET['lat']; $lon=$_GET['lon'];}
if(isset($lat) && isset($lon))echo'<meta http-equiv="refresh" content="5; URL='.$_SERVER['PHP_SELF'].'?r='.microtime(true).'&lat='.$lat.'&lon='.$lon.'">';
  else echo'<meta http-equiv="refresh" content="5; URL='.$_SERVER['PHP_SELF'].'?r='.microtime(true).'">';
echo'<link rel="stylesheet" media="all" href="https://molitor-eu.de/.include/stylesheet.css" type="text/css">';

if (isset($_GET['v']))echo'<meta name="robots" content="noindex" />';





   // Geographische Koordinaten des Objekts  !!! müssen angepasst werden !!!
   
   if(!isset($lat))$lat=number_format(51.6002554, 6, '.', '');
   if(!isset($lon))$lon=number_format(7.4476309, 6, '.', '');
   $latitude  =  $lat;    // Breitengrad
   $longitude =  $lon;    // Längengrad
   echo'<p class="fussnote">'.$lat.', '.$lon.'</p>';

   //$timestamp = mktime($hour,$minute,$second,$month,$day,$year);
   $timestamp = time();

   // Zerlege Datum und Uhrzeit, Umrechnung in Weltzeit
   $monat        = intval(gmdate("n",$timestamp));
   $tag          = intval(gmdate("j",$timestamp));
   $jahr         = intval(gmdate("Y",$timestamp));
   $stunde       = intval(gmdate("G",$timestamp));
   $minute       = intval(gmdate("i",$timestamp));
   $sekunde = intval(gmdate("s",$timestamp));

   // Berechnungen
   // Ekliptikalkoordinaten der Sonne
   $jd12 = gregoriantojd($monat,$tag, $jahr);
   $stundenanteil = $stunde >= 12 ? (($stunde)/24+$minute/(60*24)+$sekunde/(60*60*24))-0.5 : (-1*($stunde/24-$minute/(24*60)-$sekunde/(24*60*60)));
   $jd12h = $jd12 + $stundenanteil;
   $n = $jd12h - 2451545;
   $L = 280.460 + 0.9856474 * $n;
   $g = 357.528 + 0.9856003 * $n;
   $i = intval($L / 360);
   $L = $L - $i*360;
   $i = intval($g / 360);
   $g = $g - $i*360;
   $e = 0.0167;
   $eL = $L + (2*$e * sin($g/180*M_PI)+ 5/4*$e*$e*sin(2*$g/180*M_PI))*180/pi();
   $epsilon = 23.439 - 0.0000004 * $n;
   $alpha = atan(cos($epsilon/180*M_PI)*sin($eL/180*M_PI)/cos($eL/180*M_PI))*180/M_PI;


   if ((cos($eL/180*M_PI)<0)) $alpha += 180;

   $delta = asin(sin($epsilon/180*M_PI)*sin($eL/180*M_PI))*180/M_PI;
   $jd0 = $jd12 - 0.5;
   $T0 = ($jd0 - 2451545.0) / 36525;
   $mittlere_sternzeit_greenwich = 6.697376 + 2400.05134 * $T0 + 1.002738 * ($stunde+$minute/60+$sekunde/3600);
   $i = intval($mittlere_sternzeit_greenwich / 24);
   $mittlere_sternzeit_greenwich = $mittlere_sternzeit_greenwich - $i*24;
   $stundenwinkel_fruehling_greenwich = $mittlere_sternzeit_greenwich * 15;
   $stundenwinkel_fruehling = $stundenwinkel_fruehling_greenwich + $longitude;
   $stundenwinkel_sonne = $stundenwinkel_fruehling - $alpha;
   $nenner = cos($stundenwinkel_sonne/180*M_PI)*sin($latitude/180*M_PI)-tan($delta/180*M_PI)*cos($latitude/180*M_PI);
   $azimut = atan(sin($stundenwinkel_sonne/180*M_PI)/$nenner)*180/M_PI;


   if ($nenner<0) $azimut+=180;
           if ($azimut>180) $azimut -= 360;
           $h = asin(cos($delta/180*M_PI)*cos($stundenwinkel_sonne/180*M_PI)*cos($latitude/180*M_PI)+sin($delta/180*M_PI)*sin($latitude/180*M_PI))*180/M_PI;
           $R = 1.02 / (tan(($h+10.3/($h+5.11))/180*M_PI));
           $elevation = round($h + ($R/60),2);

        // Von Norden ( 0 Grad) an berechnen
        $azimut = round ( $azimut += 180,2);



        // Himmelsrichtung der Sonne
        if (isset($_GET["lang"]) and $_GET["lang"]=="de")$SunDirectionNames = array("N", "NNO", "NO", "ONO", "O", "OSO", "SO", "SSO", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW");
           else $SunDirectionNames = array("N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW", "SW", "WSW", "W", "WNW", "NW", "NNW");
        $SunDirectionName = $SunDirectionNames[(int)((round($azimut)/ 22.5))];
        $kompass=$azimut;

        echo '&#128336; '.date("D M j G:i:s T Y").' &#127774; <abbr data-title="Kompass">compass</abbr> &nbsp;<img style="transform: rotate('.$kompass.'deg); z-index:2;" src="https://molitor-eu.de/graphics/icon/kompasspfeil-sw.svg?v=2.1" height="17" width="auto" type="image/svg+xml">&nbsp; <a href="kompass-sonnenstand.php?kompass='.$azimut.'&sonnenhoehe='.$elevation.'" class="link" rel="noopener" target="_blank" onclick="FensterOeffnen(this.href); return false" data-title="Aktueller Sonnenstand im Kompass">'.(string) $azimut.' Grad ['.$SunDirectionName.']</a> - <abbr data-title="Sonnenh&ouml;he">Sun altitude</abbr> &#8737; '.(string) $elevation.' Grad';
?>
