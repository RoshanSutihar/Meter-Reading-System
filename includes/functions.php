<?php  require_once('session.php');  ?>

<?php

function Redirect_to($New_Location){
    header("Location:".$New_Location);
    exit;
}

function compressImage($sourceFile, $destination, $outputQuality)
    {
        $imageInfo = getimagesize($sourceFile);
        if ($imageInfo['mime'] == 'image/gif')
        {
            $imageLayer = imagecreatefromgif($sourceFile);
        }
        else if ($imageInfo['mime'] == 'image/jpeg')
        {
            $imageLayer = imagecreatefromjpeg($sourceFile);
        }
        else if ($imageInfo['mime'] == 'image/png')
        {
            $imageLayer = imagecreatefrompng($sourceFile);
        }
        $response = imagejpeg($imageLayer, $destination, $outputQuality);
        return $response;
    }
?>