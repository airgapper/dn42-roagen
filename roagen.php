<?php

echo shell_exec("/usr/bin/git -C ../registry/ pull origin master:master 2>&1");

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

$roas = array();
$files = scandir('../registry/data/route6/');
$h = 0; // count up when reading new file
$i = 0;

foreach($files as $file)
{
    $handle = fopen("../registry/data/route6/$file", "r");
    while (($line = fgets($handle)) !== false) {
        // extract var $ta
        if (startsWith($line,'source')) {
            $source = array();
            preg_match('/([A-Z0-4]+)/',explode(':', $line)[1], $source);
            $ta = $source[1];
        }
        // extract var $route
        // extract var $maskLength
        if (startsWith($line,'route6')) {
            $prefix = array();
            $line2 = explode('6: ', $line);
            // validate v6 netmasks with following cmd
            // cmd: ls -1 ../registry/data/route6/ | egrep -o "\b\_[0-9]+\b" | sed 's/\_//' | sort -n | uniq
            preg_match('/([a-f0-9\:]{0,128})\/(32|40|4[4-9]|5[0-9]|6[0-4]|80)/', $line2[1], $prefix);
            $route = $prefix[0];
            $mask = $prefix[2];
        }
        // extract var $asn
        if (startsWith($line, 'origin')) {
            $asn = array();
            preg_match('/AS[0-9]+/', explode(':', $line)[1], $asn);
            if (count($asn) > 1) {
                foreach ($asn as $key => $value) {
                    if (!empty($value)) {
                        $roas['roas'][$i]['asn'] = $asn[0];
                        $roas['roas'][$i]['prefix'] = $route;
                        if (isset($ta)) {
                            if ($ta != 'ICVPN') {
                                $mask = ($mask <= 64 ? '64' : $mask);
                            }
                        } else {
                            $ta = 'NULL';
                        }
                        $roas['roas'][$i]['maxLength'] = $mask;
                        $roas['roas'][$i]['ta'] = $ta;
                        $i++;
                    }
                }
            } else {
                $roas['roas'][$i]['asn'] = $asn[0];
                $roas['roas'][$i]['prefix'] = $route;
                if (isset($ta)) {
                    if ($ta != 'ICVPN') {
                        $mask = ($mask <= 64 ? '64' : $mask);
                    }
                } else {
                    $ta = 'NULL';
                }
                $roas['roas'][$i]['maxLength'] = $mask;
                $roas['roas'][$i]['ta'] = $ta;
                $i++;
            }
        }
    }
    fclose($handle);
    $h++;
}

$files = scandir('../registry/data/route/');

foreach($files as $file)
{
    $handle = fopen("../registry/data/route/$file", "r");
    while (($line = fgets($handle)) !== false) {
    // extract var $ta
        if (startsWith($line,'source')) {
            $source = array();
            preg_match('/([A-Z0-4]+)/',explode(':', $line)[1], $source);
            $ta = $source[1];
        }
        // extract var $route
        // extract var $maskLength
        if (startsWith($line,'route')) {
            $prefix = array();
            preg_match('/(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/(3[0-2]|[0-2]?[0-9])/', explode(':', $line)[1], $prefix);
            $route = $prefix[0];
            $mask = $prefix[5];
        }
        // extract var $asn
        if (startsWith($line, 'origin')) {
            $asn = array();
            preg_match('/AS[0-9]+/', explode(':', $line)[1], $asn);
            if (count($asn) > 1) {
                foreach ($asn as $key => $value) {
                    if (!empty($value)) {
                        $roas['roas'][$i]['asn'] = $asn[0];
                        $roas['roas'][$i]['prefix'] = $route;
                        if (isset($ta)) {
                            if ($ta != 'ICVPN') {
                                $mask = ($mask <= 28 ? '28' : $mask);
                            }
                        } else {
                            $ta = 'NULL';
                        }
                        $roas['roas'][$i]['maxLength'] = $mask;
                        $roas['roas'][$i]['ta'] = $ta;
                        $i++;
                    }
                }
            } else {
                $roas['roas'][$i]['asn'] = $asn[0];
                $roas['roas'][$i]['prefix'] = $route;
                if (isset($ta)) {
                    if ($ta != 'ICVPN') {
                        $mask = ($mask <= 28 ? '28' : $mask);
                    }
                } else {
                    $ta = 'NULL';
                }
                $roas['roas'][$i]['maxLength'] = $mask;
                $roas['roas'][$i]['ta'] = $ta;
                $i++;
            }
        }
    }
    fclose($handle);
    $h++;
}

// Do JSON encoding before writing result to file
$json = json_encode($roas, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

// Write JSON to file
$fp = fopen('dn42-rpki-export.json', 'w');
fwrite($fp, $json);
fclose($fp);

// Commit update JSON file
echo shell_exec("");

// Push to all git remote repositories
echo shell_exec("./update.sh 2>&1");

?>
