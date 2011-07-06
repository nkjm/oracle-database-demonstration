<?php
require_once '../AWSSDKforPHP/sdk.class.php';

class Aws {
    private $ec2;
    private $instance_id;
    public $availability_zone;

    function __construct($instance_id, $region) {
        $ec2 = new AmazonEC2();
        switch ($region) {
            case 'tokyo':
                $ec2->set_region(AmazonEC2::REGION_APAC_NE1);
                break;
            case 'singapore':
                $ec2->set_region(AmazonEC2::REGION_APAC_SE1);
                break;
            case 'ireland':
                $ec2->set_region(AmazonEC2::REGION_EU_W1);
                break;
            case 'california':
                $ec2->set_region(AmazonEC2::REGION_US_W1);
                break;
            case 'virginia':
                $ec2->set_region(AmazonEC2::REGION_US_E1);
                break;
            default:
                echo 'default';
                break;
        }
        $this->ec2 = $ec2;
        $this->instance_id = $instance_id;
        $this->availability_zone = self::fetch_availability_zone($this->instance_id);
    }

    public function fetch_availability_zone($instance_id) {
        global $error;

        $describe = $this->ec2->describe_instances(array('Filter' => array(array('Name' => 'instance-id', 'Value' => $this->instance_id))));
        if (!$describe->isOK()) {
            $error->set_msg("Failed to fetch instance information.");
            return(ERROR);
        }
        return((string) $describe->body->availabilityZone(0));
    }

    public function fetch_volume_id_by_disk_path($disk_path) {
        global $error;

        $describe = $this->ec2->describe_volumes(array('Filter' => array(array('Name' => 'attachment.instance-id', 'Value' => $this->instance_id),array('Name' => 'attachment.device', 'Value' => $disk_path))));
        if (!$describe->isOK()) {
            $error->set_msg("Failed to fetch volume information.");
            return(ERROR);
        }
        $volume_id = (string) $describe->body->volumeId(0);
        return($volume_id);
    }

    public function fetch_volume_status($aws_volume_id) {
        global $error;

        $describe = $this->ec2->describe_volumes(array('Filter' => array(array('Name' => 'volume-id', 'Value' => $aws_volume_id))));
        if (!$describe->isOK()) {
            $error->set_msg("Failed to fetch volume information.");
            return(ERROR);
        }
        $aws_volume_status = (string) $describe->body->status(0);
        return($aws_volume_status);
    }

    public function create_volume($size) {
        global $error;
        $res = $this->ec2->create_volume($this->availability_zone, array('Size' => $size));
        if (!$res->isOK()) {
            $error->set_msg("Failed to create volume.");
            return(ERROR);
        }
        $volume_id = (string) $res->body->volumeId(0);
        return($volume_id);
    }

    public function attach_volume($volume_id, $device) {
        global $error;

        $res = $this->ec2->attach_volume($volume_id, $this->instance_id, '/dev/' . $device);
        if (!$res->isOK()) {
            $error->set_msg("Failed to attach volume.");
            return(ERROR);
        }
        // Need to be fix
        sleep(5);
    }

    public function detach_volume($volume_id) {
        global $error;

        $res = $this->ec2->detach_volume($volume_id);
        if (!$res->isOK()) {
            $error->set_msg("Failed to detach volume.");
            return(ERROR);
        }
    }

    public function delete_volume($volume_id) {
        global $error;

        $res = $this->ec2->delete_volume($volume_id);
        if (!$res->isOK()) {
            $error->set_msg("Failed to delete volume.");
            return(ERROR);
        }
    }
} 
