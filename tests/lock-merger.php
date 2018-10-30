<?php
// fast merge two composer.lock
$conf1 = json_decode(file_get_contents($argv[1]), true); $conf2 = json_decode(file_get_contents($argv[2]), true);
$deps = ['okvpn/*', 'graze/dog-statsd'];
$pack = array_filter($conf1['packages'], function (array $pack) use ($deps) {
    foreach ($deps as $dep) if (fnmatch($dep, $pack['name'])) return true;
    return false;
});
$conf2['packages'] = array_merge($conf2['packages'], array_values($pack));
file_put_contents($argv[2], json_encode($conf2, 448));
