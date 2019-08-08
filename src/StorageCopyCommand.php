<?php

namespace Sykez\StorageCopy;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\MountManager;
use Illuminate\Console\Command;

class StorageCopyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'StorageCopy
							{source : Name of the Filesystem disk you want to copy from}
							{destination : Name of the Filesystem disk you want to copy to}
							{--delete : Delete files on destination disk which aren\'t on the source disk}
							{--overwrite : If files already exist on destination disk, overwrite them instead of skip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy files between Laravel Filesystem/Storage disks. By default, existing files will be skipped if the modified time is same.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$this->info('Copying...');
		$copied = 0;
		$skipped = 0;
		$source = $this->argument('source');
		$sourceFiles = Storage::disk($source)->allFiles();
		$count = count($sourceFiles);
		$progress = $this->output->createProgressBar($count);
		$progress->start();
		$destination = $this->argument('destination');
		$destinationFiles = Storage::disk($destination)->allFiles();

		// Deletes file in destination for files not in source
		if ($this->option('delete'))
		{
			// Files in destination, but not in source
			if ($difference = array_diff($destinationFiles, $sourceFiles))
			{
				$count += count($difference);
				$progress->setMaxSteps($count);
				foreach ($difference as $file)
				{
					Storage::disk($destination)->delete($file);
					$progress->advance();
				}
			}
		}

		foreach ($sourceFiles as $file)
		{
			// No overwrite and skip if file exists
			if (!$this->option('overwrite') && in_array($file, $destinationFiles)) 
			{
				// If source file last modified is same or less, skip
				if (Storage::disk($source)->lastModified($file) <= Storage::disk($destination)->lastModified($file))
				{
					$skipped++;
					$progress->advance();
					continue;
				}
			}
			//Else
			$visibility = Storage::disk($source)->getVisibility($file);
			$content = Storage::disk($source)->get($file);
			Storage::disk($destination)->put($file, $content, $visibility);
			$copied++;
			$progress->advance();
		}

		$progress->finish();
		$this->info("\nDone! $copied files copied, $skipped files skipped.");
	}
	
}
