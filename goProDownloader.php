#!/bin/php
<?php
define('DOWNLOADDIR','/Users/edorr/Pictures/GoPro');
define('CAMURL','http://10.5.5.9');

//$versionText = file_get_contents(CAMURL.'/videos/MISC/version.txt');
// fix version json
//$versionText = str_replace(','.PHP_EOL.'}','}',$versionText);
//$version = json_decode($versionText);

// check if we can find cam type
//if(empty($version)) {
//<F2>  echo "cam not connected".PHP_EOL;
//  exit(2);
//}

// check if sync directory exist and create
if(!is_dir(DOWNLOADDIR)) {
        mkdir(DOWNLOADDIR);
}

// echo some cam details
//echo "#########################################" . PHP_EOL;
//echo "# CONNECTED TO CAM:" . PHP_EOL;
//foreach($version as $key => $value) {
//  echo "# " . $key . ": " . $value . PHP_EOL;
//}
//echo "#########################################" . PHP_EOL;
//echo PHP_EOL;
echo "switch WLAN to DX12CAM1".PHP_EOL;
system('networksetup -setairportnetwork en0 DX12CAM1 swim0199');
sleep(5);
echo "stopping cam activity".PHP_EOL;
sleep(1);
$result = file_get_contents(CAMURL."/gp/gpControl/command/shutter?p=0");
sleep(5);
echo "download files".PHP_EOL;
// get items from cam
$gpMediaList = file_get_contents(CAMURL."/gp/gpMediaList");
$goProMediaList = json_decode($gpMediaList);
// loop thru all files and download
foreach($goProMediaList->media as $directory) {
        foreach($directory->fs as $file) {
                $filePath=$directory->d . '/' . $file->n;
                echo "donwloading " . CAMURL.'/videos/DCIM/' . $filePath . " ...";
                file_put_contents(DOWNLOADDIR.'/'.$file->n,     file_get_contents(CAMURL.'/videos/DCIM/'.$filePath));
                echo PHP_EOL . " ... done." . PHP_EOL;
                if(filesize(DOWNLOADDIR.'/'.$file->n) == $file->s) {
                        echo "filesize matched for file ". $filePath . ". Deleting file from cam." . PHP_EOL;
                        // delete image if it is the correct filesize
                        $deleteResult = json_decode(file_get_contents(CAMURL.'/gp/gpControl/command/storage/delete?p=' . $filePath));
                } else {
                        $remotesize = $file->s;
                        $localsize = filesize(DOWNLOADDIR.'/'.$file->n);
                        echo "file size for file " . $filePath . " differs (remote: $remotesize, local: $localsize). Deleting local copy and try later." . PHP_EOL;
                        unlink(DOWNLOADDIR.'/'.$file->n);
                }
                echo PHP_EOL;
        }
}
echo "starting cam activity".PHP_EOL;
sleep(1);
$result = file_get_contents(CAMURL."/gp/gpControl/command/shutter?p=1");
sleep(1);
echo "switch WLAN to decix-guests".PHP_EOL;
system('networksetup -setairportnetwork en0 decix-guests welcometodecix');
sleep(5);
echo "scripted ended successfully" . PHP_EOL;
