<?php


namespace App\Helpers;


use Google\Cloud\Storage\StorageClient;

class GoogleStorageHelper
{
    static function upload(string $local, string $remote)
    {
        $storage = new StorageClient([
            'keyFilePath' => base_path(env('GOOGLE_CLOUD_KEY_FILE_PATH', ''))
        ]);


        $bucket = $storage->bucket(env('GOOGLE_CLOUD_BUCKET', ''));
        // dd($local);

        $bucket->upload(fopen($local, 'r'),
            [
                'name' => $remote
            ]);
    }

    static function download(string $local, string $remote): bool
    {
        $storage = new StorageClient([
            'keyFilePath' => base_path(env('GOOGLE_CLOUD_KEY_FILE_PATH', ''))
        ]);

        $bucket = $storage->bucket(env('GOOGLE_CLOUD_BUCKET', ''));

        $object = $bucket->object($remote);

        if ($object->exists())
            $object->downloadToFile($local);
        else
            return false;

        return true;
    }

    static function delete(string $file)
    {
        $storage = new StorageClient([
            'keyFilePath' => base_path(env('GOOGLE_CLOUD_KEY_FILE_PATH', ''))
        ]);

        $bucket = $storage->bucket(env('GOOGLE_CLOUD_BUCKET', ''));

        $object = $bucket->object($file);

        if ($object->exists())
            $object->delete();
    }
}
