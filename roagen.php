<?php

// Before we begin. Ensure registry repository is up-to-date
echo shell_exec ("/usr/bin/git -C ../registry/ pull origin master:master 2>&1");

/*
 * Function:
 * startsWith ($string, "word", $length)
 *
 * Find lines beginning with "word". Optionally
 * give the length of the string you are looking for.
 */
function startsWith ($haystack, $needle, $length = "0")
{
  if ($length <= 0 || $length > (strlen ($needle)))
    $length = strlen ($needle);

  return (substr ($haystack, 0, $length) === $needle);
}

/*
 * Function:
 * endsWith ($string, "word")
 *
 * Find lines ending with "word".
 */
function endsWith ($haystack, $needle)
{
  $length = strlen ($needle);

  if ($length == 0)
    return true;

  return (substr( $haystack, -$length) === $needle);
}

/*
 * Function:
 * trim_special_chars ($string)
 *
 * Remove special characters.
 */
function trim_special_chars ($string)
{
  return (trim ($string, " \t\n\r\0\x0B"));
}

// Define array() we are going to populate with data.
$roas = array();

// Set folders we need to scan.
$files6 = scandir ("../registry/data/route6/");
$files4 = scandir ("../registry/data/route/");

/*
 *
 * IPv6
 *
 */

$i = 0; // Counter used with tmp $raw_array.
$raw_array = array();  // tmp array() used for storing data to be processed
foreach ($files6 as $file)
{
  $j = 0;

  /*
   * route6 with maxLength value set:
   * - fd42:5d71:219::/48
   *
   * $ cat ../registry/data/route6/fd42:5d71:219::_48
   * route6:             fd42:5d71:219::/48
   * origin:             AS4242420119
   * max-length:         48
   * mnt-by:             JRB0001-MNT
   * source:             DN42
   */

  $data = file ("../registry/data/route6/$file");

  foreach ($data as $str)
  {
    $str = trim_special_chars ($str);

    if     (startsWith ($str, "max",    3)) $raw_array[$i]["max"]       = $str;
    elseif (startsWith ($str, "source", 6)) $raw_array[$i]["source"]    = $str;
    elseif (startsWith ($str, "route",  5)) $raw_array[$i]["route"]     = $str;
    elseif (startsWith ($str, "origin", 6)) $raw_array[$i]["asn"][$j++] = $str;

    // Catch max-length not set in route object.
    if (empty ($raw_array[$i]["max"])) $raw_array[$i]["max"] = -1;
  }
  $i++;
}

$k = 0;

foreach ($raw_array as $sub_array)
{
  // Extract prefix and subnet size
  // Match prefix sizes 29-64, 80.
  $prefix = array();
  preg_match ("/([a-f0-9\:]{0,128})\/(29|[3-5][0-9]|6[0-4]|80)/",
   explode ("6: ", $sub_array["route"])[1],
   $prefix);

  // Extract ta information
  $source = array();
  preg_match ("/([A-Z0-4]+)/",
   explode (":", $sub_array["source"])[1],
   $source);

  // Try to extract max-length information
  $maxlength = array();
  if (($sub_array["max"]) != -1)
    preg_match ("/([0-9]+)/",
     explode (":", $sub_array["max"])[1],
     $maxlength);

  // Store extracted values
  $_prefix = $prefix[0];
  $_ta  = (isset ($source[0]) ? $source[0] : "");

  // We need to do conditional setting of maxLength to avoid errornous output.
  if (($sub_array["max"]) != -1)
    $_maxlength = (isset ($maxlength[0]) ? $maxlength[0] : "");
  else
    // Do fallback to default prefix size if max-length was not set.
    $_maxlength = $prefix[2];

  // Loop through each asn in single route6 object and assign
  // other values accordingly.
  foreach ($sub_array["asn"] as $asn)
  {
    // Extract ASxxxxx from string.
    preg_match ("/AS[0-9]+/", explode (":", $asn)[1], $_asn);

    $roas["roas"][$k]["asn"] = $_asn[0];
    $roas["roas"][$k]["prefix"] = $_prefix;
    $roas["roas"][$k]["maxLength"] = $_maxlength;
    $roas["roas"][$k]["ta"] = $_ta;

    $k++;
  }
}

/*
 *
 * IPv4
 *
 */

$i = 0; // Counter used with tmp $raw_array.
$raw_array = array();  // tmp array() used for storing data to be processed
foreach ($files4 as $file)
{
  $j = 0;

  /*
   * route with maxLength value set:
   * - 172.20.1.0/24
   *
   * $ cat ../registry/data/route/172.20.1.0_24
   * route:              172.20.1.0/24
   * origin:             AS4242420119
   * max-length:         24
   * mnt-by:             JRB0001-MNT
   * source:             DN42
   */

  $data = file ("../registry/data/route/$file");

  foreach ($data as $str)
  {
    $str = trim_special_chars ($str);

    if     (startsWith ($str, "max",    3)) $raw_array[$i]["max"]       = $str;
    elseif (startsWith ($str, "source", 6)) $raw_array[$i]["source"]    = $str;
    elseif (startsWith ($str, "route",  5)) $raw_array[$i]["route"]     = $str;
    elseif (startsWith ($str, "origin", 6)) $raw_array[$i]["asn"][$j++] = $str;

    // Catch max-length not set in route object.
    if (empty ($raw_array[$i]["max"])) $raw_array[$i]["max"] = -1;
  }
  $i++;
}

foreach ($raw_array as $sub_array)
{
  // Extract prefix and subnet size
  // Match prefix sizes 8-32.
  $prefix = array();
  preg_match ("/([0-9\.]{7,15})\/([8-9]|[1-2][0-9]|3[0-2])/",
   explode (":", $sub_array["route"])[1],
   $prefix);

  // Extract ta information
  $source = array();
  preg_match ("/([A-Z0-4]+)/",
   explode (":", $sub_array["source"])[1],
   $source);

  // Try to extract max-length information
  $maxlength = array();
  if (($sub_array["max"]) != -1)
    preg_match ("/([0-9]+)/",
     explode (":", $sub_array["max"])[1],
     $maxlength);

  // Store extracted values
  $_prefix = $prefix[0];
  $_ta = (isset ($source[0]) ? $source[0] : "");

  // We need to do conditional setting of maxLength to avoid errornous output.
  if (($sub_array["max"]) != -1)
    $_maxlength = (isset ($maxlength[0]) ? $maxlength[0] : "");
  else
    // Do fallback to default prefix size if max-length was not set.
    $_maxlength = $prefix[2];

  // Loop through each asn in single route6 object and assign
  // other values accordingly.
  foreach ($sub_array["asn"] as $asn)
  {
    // Extract ASxxxxx from string.
    preg_match ("/AS[0-9]+/", explode (":", $asn)[1], $_asn);

    $roas["roas"][$k]["asn"] = $_asn[0];
    $roas["roas"][$k]["prefix"] = $_prefix;
    $roas["roas"][$k]["maxLength"] = $_maxlength;
    $roas["roas"][$k]["ta"] = $_ta;

    $k++;
  }
}

// Do JSON encoding before writing result to file
$json = json_encode ($roas, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

// Write JSON to file
$fp = fopen ('roa/export_dn42.json', 'w');
fwrite ($fp, $json);
fclose ($fp);

// Write TEXT to file in bird format
$fq = fopen ('roa/bird_roa_dn42.conf', 'w');
$fr = fopen ('roa/bird4_roa_dn42.conf', 'w');
$fs = fopen ('roa/bird6_roa_dn42.conf', 'w');

foreach ($roas["roas"] as $roa)
{
  $prfx = $roa["prefix"];
  $mxLngth = $roa["maxLength"];
  $sn = $roa["asn"];

  $strng = "roa $prfx max $mxLngth as $sn;\n";

  fwrite ($fq, $strng);
  
  if (strpos ($prfx, ":") !== false)
    fwrite ($fr, $strng);
  else
    fwrite ($fs, $strng);
}

fclose ($fq);
fclose ($fr);
fclose ($fs);

// Commit and push to all git remote repositories
echo shell_exec ("./update.sh 2>&1");

?>
