<?php

function main($args)
{
    require_once __DIR__ . "/vendor/autoload.php";
    
    date_default_timezone_set(@date_default_timezone_get());
    
    $meta_data = new InstanceMetaData();
    $instance_id = $meta_data->getInstanceId();
    $region = $meta_data->getRegion();
    
    $backup = new EbsBackup("test1", $region);
    $backup->run($instance_id, 3);
}

main($_SERVER["argv"]);
