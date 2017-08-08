<?php
$json = array('SVGDefines' => array());
$svgs = glob(__DIR__.'/*.min.svg', GLOB_NOSORT);

foreach($svgs as $s) {
    $code = file_get_contents($s);
    $code = rawurlencode($code);
    $code = str_replace('%25s', '%s', $code);
    $json['SVGDefines'][str_replace('.min.svg', '', basename($s))] = $code;
}

file_put_contents(__DIR__.'/svg.json', json_encode($json));