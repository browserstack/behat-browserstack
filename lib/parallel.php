<?php

require 'vendor/autoload.php';

require 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$config_file = getenv('CONFIG_FILE');
if(!$config_file) $config_file = 'config/single.conf.yml';
$CONFIG = Yaml::parse(file_get_contents($config_file))["default"]["suites"]["default"]["contexts"][0]["FeatureContext"]["parameters"];

$procs = array();

foreach ($CONFIG['environments'] as $key => $value) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      // Windows
      $cmd = "set TASK_ID=$key& \"./bin/behat\" --config=". getenv("CONFIG_FILE")." 2>&1\n";
    } else {
      // Linux or  Mac
      $cmd = "TASK_ID=$key ./bin/behat --config=". getenv("CONFIG_FILE")." 2>&1\n";
    }
    print_r($cmd);

    $procs[$key] = popen($cmd, "r");
}

foreach ($procs as $key => $value) {
    while (!feof($value)) {
        print fgets($value, 4096);
    }
    pclose($value);
}

?>
