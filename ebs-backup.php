<?php

function main($args)
{
    require_once __DIR__ . "/vendor/autoload.php";
    
    date_default_timezone_set(@date_default_timezone_get());
    try {
        $options = new Options();
        $options->parse(); // TODO: use $args
    }
    catch (UnexpectedValueException $e) {
        file_put_contents("php://stderr", $options->getHelpText());
        exit(1);
    }
    if ($options->getOption("help")) {
        file_put_contents("php://stderr", $options->getHelpText());
        exit(0);
    }
    
    $meta_data = new InstanceMetaData();
    $instance_id = $meta_data->getInstanceId();
    $region = $meta_data->getRegion();
    
    $backup = new EbsBackup($options->getOption("profile"), $region);
    $backup->run($instance_id, $options->getOption("rotate"));
}

main($_SERVER["argv"]);
