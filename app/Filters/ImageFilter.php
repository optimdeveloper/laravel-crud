<?php


namespace App\Filters;


use Intervention\Image\Filters\FilterInterface;
use Intervention\Image\Image;

class ImageFilter implements FilterInterface
{
    public function applyFilter(Image $image): Image
    {
        //->encode('jpg', 20)
        return $image->fit(250, 250);
    }
}
