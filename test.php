<?php
require_once '../AWSSDKforPHP/sdk.class.php';

$ec2 = new AmazonEC2();
$ec2->set_region(AmazonEC2::REGION_APAC_NE1);

/*
$describe = $ec2->describe_instances(array('Filter' => array(array('Name' => 'instance-id', 'Value' => 'i-e21f83e3'))));
if (!$describe->isOK()) {
    echo 'failed to fetch instance information.';
}
$availability_zone = (string) $describe->body->availabilityZone(0);

$res = $ec2->create_volume($availability_zone, array('Size' => 1));
if (!$res->isOK()) {
    echo 'failed to create volume';
}
echo 'result: ' . $res->isOK();
echo 'volume_id: ' . (string) $res->body->volumeId(0);
*/
$describe = $ec2->describe_volumes(array('Filter' => array(array('Name' => 'volume-id', 'Value' => 'vol-946cbffe'))));
echo 'volume_id: ' . (string) $describe->body->status(0);

?>
