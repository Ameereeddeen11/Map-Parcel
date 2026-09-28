<?php
require 'vendor/autoload.php';

$xml = file_get_contents($argv[1]);
$t = microtime(true);
$parcely = (new Amir\MapParcel\Infrastructure\Cuzk\CuzkParcelaParser())->parsuj($xml);
printf("%d parcel, %.2f s, peak %.1f MB\n", count($parcely), microtime(true) - $t, memory_get_peak_usage(true) / 1048576);