<?php

function main()
{
    require_once __DIR__ . "/vendor/autoload.php";
    $ec2 = Aws\Ec2\Ec2Client::factory([
        "profile" => "test1",
        "region" => "ap-northeast-1",
    ]);
    $reply = $ec2->describeInstances([
        "InstanceIds" => ["i-507dbfce"],
    ]);
    $vols = $reply["Reservations"][0]["Instances"][0]["BlockDeviceMappings"];
    print_r($vols);
    foreach ($vols as $vol) {
        $vol_id = $vol["Ebs"]["VolumeId"];
        $reply = $ec2->createSnapshot([
            "VolumeId" => $vol_id,
        ]);
        $snap_id = $reply["SnapshotId"];
        print_r($reply);
        $ec2->createTags([
            "Resources" => [$snap_id],
            "Tags" => [
                ["Key" => "Name", "Value" => "snap" . date("Ymd-Hi")],
                ["Key" => "AutoDelete", "Value" => "yes"],
            ]
        ]);
    }
    
    $snaps = $ec2->describeSnapshots([
        "Filters" => [
            ["Name" => "tag:AutoDelete", "Values" => ["yes"]]
        ]
    ]);
    print_r($snaps);
}

main();
