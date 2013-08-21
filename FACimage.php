<?php
$__img = imagecreate( 100, 100 );
$background = imagecolorallocate( $__img, 255, 255, 255 );
$text_colour = imagecolorallocate( $__img, 0, 0, 0 );
//$line_colour = imagecolorallocate( $__img, 128, 255, 0 );
imagestring( $__img, 4, 20, 40,  "NO IMAGE",  $text_colour );
imagesetthickness ( $__img, 5 );
//imageline( $__img, 30, 45, 165, 45, $line_colour );

header( "Content-type: image/png" );
imagepng( $__img );
imagecolordeallocate( $line_color );
imagecolordeallocate( $text_color );
imagecolordeallocate( $background );
imagedestroy( $__img );
?>