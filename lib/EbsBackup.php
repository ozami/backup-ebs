<?php

class EbsBackup
{
    public $ec2;
    
    public function __construct($profile, $region)
    {
        $this->ec2 = Aws\Ec2\Ec2Client::factory([
            "profile" => $profile,
            "region" => $region,
        ]);
    }
    
    public function run($instance_id, $count)
    {
        $instance_name = $this->getName($instance_id);
        if ($instance_name === null) {
            $instance_name = $instance_id;
        }
        
        $vols = $this->getVolumesOfInstance($instance_id);
        foreach ($vols as $vol) {
            $name = "$instance_name-" . basename($vol["device"]) . date("-Ymd-Hi");
            $this->createSnapshot($vol["id"], $name);
            $this->deleteOldSnapshot($vol["id"], $count);
        }
    }
    
    public function getName($resouce_id)
    {
        $reply = $this->ec2->describeTags([
            "Filters" => [
                ["Name" => "key", "Values" => ["Name"]],
                ["Name" => "resource-id", "Values" => [$resouce_id]],
            ],
        ]);
        if (!$reply["Tags"]) {
            return null;
        }
        return $reply["Tags"][0]["Value"];
    }
    
    public function getVolumesOfInstance($instance_id)
    {
        $reply = $this->ec2->describeInstances([
            "InstanceIds" => [$instance_id],
        ]);
        $vols = $reply["Reservations"][0]["Instances"][0]["BlockDeviceMappings"];
        return array_map(function($vol) {
            return [
                "id" => $vol["Ebs"]["VolumeId"],
                "device" => $vol["DeviceName"],
            ];
        }, $vols);
    }
    
    public function createSnapshot($vol_id, $name)
    {
        $reply = $this->ec2->createSnapshot([
            "VolumeId" => $vol_id,
        ]);
        $snap_id = $reply["SnapshotId"];
        
        // Add tags
        $this->ec2->createTags([
            "Resources" => [$snap_id],
            "Tags" => [
                ["Key" => "Name", "Value" => $name],
                ["Key" => "AutoDelete", "Value" => "yes"],
            ]
        ]);
    }
    
    public function deleteOldSnapshot($vol_id, $count)
    {
        $reply = $this->ec2->describeSnapshots([
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
        $old_snaps = array_slice($snaps, $count);
        
        foreach ($old_snaps as $snap) {
            $reply = $this->ec2->deleteSnapshot([
                "SnapshotId" => $snap["SnapshotId"],
            ]);
        }
    }
}
