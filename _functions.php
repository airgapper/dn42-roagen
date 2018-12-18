<?php

/*
 * Function:
 * fetchUpstreamMaster ()
 *
 * Ensure registry repository master branch is up-to-date.
 */
function fetchUpstreamMaster ()
{
  echo shell_exec ("/usr/bin/git -C ../registry/ pull upstream master:master 2>&1");
}

/*
 * Function:
 * checkoutMaster ()
 *
 * Checkout git master branch.
 */
function checkoutMaster ()  
{
  echo shell_exec ("/usr/bin/git -C ../registry/ o master 2>&1");
}

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

/*
 * Function:
 * pushToRemotes ()
 *
 * Commit and push to all git remote repositories.
 */
function commitPushToRemotes ()
{
  echo shell_exec ("./update.sh 2>&1");
}

function writeBirdConfig ($json, $roas)
{
  $fq = fopen ('roa/bird_roa_dn42.conf', 'w');
  $fq4 = fopen ('roa/bird4_roa_dn42.conf', 'w');
  $fq6 = fopen ('roa/bird6_roa_dn42.conf', 'w');

  fwrite ($fq, shell_exec ("/usr/bin/git -C ../registry/ show | sed 's/^/# /g'"));
  fwrite ($fq4, shell_exec ("/usr/bin/git -C ../registry/ show | sed 's/^/# /g'"));
  fwrite ($fq6, shell_exec ("/usr/bin/git -C ../registry/ show | sed 's/^/# /g'"));

  foreach ($roas["roas"] as $roa)
  {
    $prfx = $roa["prefix"];
    $mxLngth = $roa["maxLength"];
    $sn = $roa["asn"];

    $strng = "roa $prfx max $mxLngth as $sn;\n";

    fwrite ($fq, $strng);
  
    if (strpos ($prfx, ":") !== false)
      fwrite ($fq6, $strng);
    else
      fwrite ($fq4, $strng);
  }

  fclose ($fq);
  fclose ($fq4);
  fclose ($fq6);
}

function writeRoutinatorExceptionFile ($json)
{
  $fp = fopen('roa/export_rfc8416_dn42.json', 'w');

  fwrite($fp, $json);

  fclose($fp);
}

function writeExportJSON ($json)
{
  $fp = fopen ('roa/export_dn42.json', 'w');

  fwrite ($fp, shell_exec ("/usr/bin/git -C ../registry/ show | sed 's/^/\/\/ /g'"));
  fwrite ($fp, $json);

  fclose ($fp);
}

?>
