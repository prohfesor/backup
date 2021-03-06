<?php

// Include the SDK using the Composer autoloader
require 'vendor/autoload.php';

use Aws\S3;
use Aws\S3\Model\MultipartUpload\UploadBuilder;
use Aws\Common\Enum\Region;
use Aws\Common\Enum\Size;


$folder = '/home/prohfesor/backup';

// Instantiate the s3 client with your AWS credentials
require_once 'config_s3.php';
$client = \Aws\S3\S3Client::factory(array(
  'key'    => $config_s3_key,
  'secret' => $config_s3_secret,
  'region' => $config_s3_region
));


$s = $client->getService();

$aList = scandir($folder);
foreach ($aList as $file) {
	$filename = $folder."/".$file;
	$filesize = trim(`stat -c%s $filename`); //linux only
	if(!is_file($filename)){
		echo "Skipping {$file} \n";
		continue;
	}
	$prefix = "daily";
	if('1'===date('N')){
		$prefix = "weekly";
	}
	if('1'===date('j')){
		$prefix = "monthly";
	}
	$uploader = UploadBuilder::newInstance()
	    ->setClient($client)
	    ->setSource($filename)
	    ->setBucket($config_s3_bucket)
	    ->setKey('backup_'.$prefix.'_'.basename($filename))
	    ->setMinPartSize(100 * Size::MB)
	    //->setOption('Expires', time() + 7*24*60*60)
	    ->build();
	
	print "Preparing {$file} \n";
	
	// Perform the upload. Abort the upload if something goes wrong
	try {
	    echo " Uploading ". round(filesize($filename)/Size::MB , 1) ." Mb \n";
	    $res = $uploader->upload();
	    echo " Uploaded to {$res['Location']} \n";
	} catch (MultipartUploadException $e) {
	    $uploader->abort();
	    echo "Upload failed.\n";
		echo $e->getMessage();
	}
	
	//clear file
	unlink($filename);
}

print "\nFINISHED \n\n";

