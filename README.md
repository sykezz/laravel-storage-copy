# Storage Copy for Laravel

Laravel Artisan command for copying files between Laravel Filesystem/Storage disks.

## Install

```
composer require edwardcaret/laravel-storage-copy
```

## Usage

```
storage:copy [options] [--] <source> <destination>
```

### Arguments

```
source                Name of the Filesystem disk you want to copy from
destination           Name of the Filesystem disk you want to copy to
```

### Options

```
-d, --delete          Delete files on destination disk which aren't on the source disk
-o, --overwrite       If files already exist on destination disk, overwrite them instead of skip
-l, --log             Log all actions into Laravel log
-O, --output          Output all actions
```

## Examples

- Copy `public` disk to `s3`. By default, existing files will be skipped if modified time is same.

  ```
  php artisan storage:copy public s3
  ```

- Copy `s3` disk to `custom`. `delete` option would delete any files on the destination disk (`custom`), which aren't in the source disk (`s3`). `overwrite` option would overwrite the files if exists on the destination disk instead of skipping.

  ```
  php artisan storage:copy --delete --overwrite s3 custom
  ```

- Copy from S3 bucket to another S3 buket  
   Since there is only one S3 disk in Laravel by default, in order to copy one bucket to another, another disk needs to be added into `config/filesystems.php`:

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

  After adding the new disk's details (`ANOTHER_AWS`) into `.env`, `s3` disk can now be copied to `another-s3` disk:

  ```
  php artisan storage:copy -od s3 another-s3
  ```

- Log and debugging
  `log` option would log all actions taken on the files into Laravel's log, while `debug` option would output these actions into the console instead.
  ```
  php artisan storage:copy --log --output s3 rackspace
  ```
