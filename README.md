# Storage Copy for Laravel

Simple Artisan command for copying files between Laravel Filesystem/Storage disks.

## Install
```
composer require sykez/laravel-storage-copy
```

## Usage
```
StorageCopy [options] [--] <source> <destination>

source                Name of the Filesystem disk you want to copy from
destination           Name of the Filesystem disk you want to copy to

--delete          Delete files on destination disk which aren't on the source disk
--overwrite       If files already exist on destination disk, overwrite them instead of skip
```

## Examples
```
php artisan StorageCopy public s3
```
Copies your `public` disk to `s3`. By default, existing files will be skipped if modified time is same.
```
php artisan StorageCopy s3 custom --delete --overwrite
```
Copies your `s3` disk to `custom`. The `delete` option would delete any files on the destination disk (`custom` in this case) which aren't on your source disk (`s3`). `overwrite` options would overwrite the files if exists on the destination disk instead of skipping.

### Copy from S3 bucket to another
Since you have only one S3 disk on Laravel by default, in order to copy one bucket to another, you'll have to create another disk in `config/filesystems.php`:
```
    'disks' => [

        // ... other disks

        'another-s3' => [
            'driver' => 's3',
            'key' => env('ANOTHER_AWS_ACCESS_KEY_ID'),
            'secret' => env('ANOTHER_AWS_SECRET_ACCESS_KEY'),
            'region' => env('ANOTHER_AWS_DEFAULT_REGION'),
            'bucket' => env('ANOTHER_AWS_BUCKET'),
            'url' => env('ANOTHER_AWS_URL'),
        ],
    ],
```
After adding your `ANOTHER_AWS_` details to your `.env` file, you can now copy from your `s3` disk to `another-s3` disk:
```
php artisan StorageCopy s3 another-s3
```