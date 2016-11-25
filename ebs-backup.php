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
    //print_r($vols);
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
    
    foreach ($vols as $vol) {
        $vol_id = $vol["Ebs"]["VolumeId"];
        echo "$vol_id\n";
        $reply = $ec2->describeSnapshots([
            "Filters" => [
                ["Name" => "volume-id", "Values" => [$vol_id]],
                ["Name" => "tag:AutoDelete", "Values" => ["yes"]]
            ]
        ]);
        $snaps = $reply["Snapshots"];
        usort($snaps, function($a, $b) {
            if ($a["StartTime"] > $b["StartTime"]) {
                return -1;
            }
            if ($a["StartTime"] < $b["StartTime"]) {
                return 1;
            }
            return 0;
        });
        $old_snaps = array_slice($snaps, 2);
        
        foreach ($old_snaps as $snap) {
            $reply = $ec2->deleteSnapshot([
                "SnapshotId" => $snap["SnapshotId"],
            ]);
            print_r($reply);
        }
    }
}

main();
