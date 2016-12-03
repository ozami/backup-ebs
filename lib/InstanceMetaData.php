<?php
use Guzzle\Http\Client;

class InstanceMetaData
{
    public function __construct()
    {
        $this->_http = new Client("http://169.254.169.254/latest/meta-data/");
    }
    
    public function getInstanceId()
    {
        return $this->_get("instance-id");
    }
    
    public function getRegion()
    {
        return substr($this->getAvailabilityZone(), 0, -1);
    }
    
    public function getAvailabilityZone()
    {
        return $this->_get("placement/availability-zone");
    }
    
    public function _get($path)
    {
        $req = $this->_http->get($path);
        $res = $req->send();
        return (string)$res->getBody();
    }
}
