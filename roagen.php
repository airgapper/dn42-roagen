<?php

// Load lib files.
require ("lib/constants.php");
require ("lib/define.php");
require ("lib/functions.php");

// Define array() we are going to populate with data.
$roas["_comments"]["modified"]["commit"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%H'");
$roas["_comments"]["modified"]["merge"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%p'");
$roas["_comments"]["modified"]["author"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%an <%ae>'");
$roas["_comments"]["modified"]["date"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%aD'");
$roas["_comments"]["modified"]["subject"] = shell_exec ("/usr/bin/git -C ../registry/ log -n 1  --merges --pretty='format:%s'");
$roas["_comments"]["modified"]["url"] = "https://git.dn42.us/dn42/registry/commit/";
$roas["_comments"]["modified"]["url"] .= $roas["_comments"]["modified"]["commit"];

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
    elseif (startsWith ($str, "route6", 6)) $raw_array[$i]["route"]     = $str;
    elseif (startsWith ($str, "origin", 6)) $raw_array[$i]["asn"][$j++] = $str;
    elseif (startsWith ($str, "mnt",    3)) $raw_array[$i]["mnt"]       = $str;

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

  // Extract mnt-by information
  $mnt = array();
  preg_match ("/([A-Z0-9\-]+)/",
   explode (":", $sub_array["mnt"])[1],
   $mnt);

  // Store extracted values
  $_prefix = $prefix[0];
  $_ta  = (isset ($source[0]) ? $source[0] : "");

  // We need to do conditional setting of maxLength to avoid errornous output.
  if (($sub_array["max"]) != -1)
    $_maxlength = (isset ($maxlength[0]) ? $maxlength[0] : "");
  else
    // Do fallback to default prefix size if max-length was not set.
    $_maxlength = ($prefix[2] < MAX_LEN_IPV6 ? MAX_LEN_IPV6 : $prefix[2]);

  $_mnt = $mnt[0];

  // Loop through each asn in single route6 object and assign
  // other values accordingly.
  foreach ($sub_array["asn"] as $asn)
  {
    // Extract ASxxxxx from string.
    preg_match ("/AS[0-9]+/", explode (":", $asn)[1], $_asn);

    $roas["roas"][$k]["asn"] = trim ($_asn[0], "AS");
    $roas["roas"][$k]["prefix"] = $_prefix;
    $roas["roas"][$k]["maxLength"] = ($_asn[0] != "AS0" ? $_maxlength : MAX_LEN_IPV6_AS0);
    $roas["roas"][$k]["ta"] = $_ta;
    $roas["roas"][$k]["mnt-by"] = $_mnt;

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
    elseif (startsWith ($str, "mnt",    3)) $raw_array[$i]["mnt"]       = $str;

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

  // Extract mnt-by information
  $mnt = array();
  preg_match ("/([A-Z0-9\-]+)/",
   explode (":", $sub_array["mnt"])[1],
   $mnt);

  // Store extracted values
  $_prefix = $prefix[0];
  $_ta = (isset ($source[0]) ? $source[0] : "");

  // We need to do conditional setting of maxLength to avoid errornous output.
  if (($sub_array["max"]) != -1)
    $_maxlength = (isset ($maxlength[0]) ? $maxlength[0] : "");
  else
    // Do fallback to default prefix size if max-length was not set.
    $_maxlength = ($prefix[2] < MAX_LEN_IPV4 ? MAX_LEN_IPV4 : $prefix[2]);

  $_mnt = $mnt[0];

  // Loop through each asn in single route6 object and assign
  // other values accordingly.
  foreach ($sub_array["asn"] as $asn)
  {
    // Extract ASxxxxx from string.
    preg_match ("/AS[0-9]+/", explode (":", $asn)[1], $_asn);

    $roas["roas"][$k]["asn"] = trim ($_asn[0], "AS");
    $roas["roas"][$k]["prefix"] = $_prefix;
    $roas["roas"][$k]["maxLength"] = ($_asn[0] != "AS0" ? $_maxlength : MAX_LEN_IPV4_AS0);
    $roas["roas"][$k]["ta"] = $_ta;
    $roas["roas"][$k]["mnt-by"] = $_mnt;

    $k++;
  }
}

/*
 * Function: Add metadata
 *
 * Add info
 * 1. generation time (now),
 * 2. expire time (now + 3 days),
 * 3. number of routes
 *
 * Numbers must be unquoted integers, and timeformat must
 * be epoch format. TImezone is set to Etc/UTC.
 */
$roas["metadata"]["counts"] = (int)count($roas["roas"]);
$roas["metadata"]["generated"] = (int)(date_format(new \DateTime("now",  new \DateTimeZone("UTC")), "U"));
$roas["metadata"]["valid"] = (int)(date_format(date_modify(new \DateTime("now",  new \DateTimeZone("UTC")), "+3 day"), "U"));
/*
$roas["metadata"]["signature"] = "";
$roas["metadata"]["signatureData"] = "";
*/

// Additional human readbable DateTime format, example: 2013-04-12T15:52:01+00:00
$roas["metadata"]["human"]["generated"] = date_format(new \DateTime("now",  new \DateTimeZone("UTC")), "c");
$roas["metadata"]["human"]["valid"] = date_format(date_modify(new \DateTime("now",  new \DateTimeZone("UTC")), "+3 day"), "c");

writeExportJSON($roas);
writeBirdConfig($roas);

?>
