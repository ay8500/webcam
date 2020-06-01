<?php
$d = dir (__dir__."\\test\\jpg\\");
while (false !== ($entry = $d->read())) {
    $pi = pathinfo($entry);
    if (isset($pi["extension"]) && strtolower($pi["extension"])==="jpg" /*&& $entry=="badimage.jpg"*/) {
        $file=__dir__."\\test\\jpg\\".$entry;
        echo  checkImage($file).'  '.$file. "<br/>";
    }
}

die ( "DONED" );

function checkImage($file,$checkColor = 8421504,$maxLines=50,$maxWidth=50): int {
    $image = imagecreatefromjpeg($file);
    assert ( is_resource ( $image ) );
    $imgWidth = imagesx ( $image );
    $imgHeight = imagesy ( $image );
    if ($imgWidth<$maxWidth)
        $maxWidth=$imgWidth;

    $linesFound=0;
    for($y = $imgHeight-1; $y >=0; -- $y) {
        $lineSamecolor = true;
        for($x = 0; $x < $maxWidth; ++ $x) {
            if($checkColor!==imagecolorat($image,$x,$y))
            {
                $lineSamecolor=false;
                continue ;
            }
        }
        if ($lineSamecolor)
            $linesFound++;
        if ($linesFound>$maxLines) {
            imagedestroy($image);
            return $linesFound;
        }
    }
    imagedestroy($image);
    return $linesFound;
}

