<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class ImageHelper
{
    static function uploadImage(UploadedFile $image, $folder): array
    {
        $url = public_path("images/" . $folder . "/");
        $name = Str::random(44) . "." . $image->getClientOriginalExtension();
        if (!File::isDirectory($url)) {
            File::makeDirectory($url);
        }
        copy($image->getRealPath(), $url . $name);

        // google Storage
        $local = $url . $name;
        $remote = "images/" . $folder . "/" . $name;
        GoogleStorageHelper::upload($local, $remote);

        return ['path' => $name, 'name' => $name];
    }

    static function uploadImageUrl($url, $folder): array
    {
        $name = Str::random(44) . ".png";
        $dir = public_path("images/" . $folder . "/");
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir);
        }
        $rta = file_get_contents($url);
        file_put_contents($dir . $name, $rta);

        // google Storage
        $local = $dir . $name;
        $remote = "images/" . $folder . "/" . $name;
        GoogleStorageHelper::upload($local, $remote);

        return ['path' => $name, 'name' => $name];
    }
}
