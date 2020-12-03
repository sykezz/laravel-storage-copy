<?php

namespace Sykez\StorageCopy;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class StorageCopyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:copy
                            {source : Name of the Filesystem disk you want to copy from}
                            {destination : Name of the Filesystem disk you want to copy to}
                            {--d|delete : Delete files on destination disk which aren\'t on the source disk}
                            {--o|overwrite : If files already exist on destination disk, overwrite them instead of skip}
                            {--l|log : Log all actions into Laravel log}
                            {--O|output : Output all actions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy files between Laravel Filesystem/Storage disks. By default, existing files will be skipped.';

    protected $log = [];
    protected $count = ['copied' => 0, 'skipped' => 0, 'deleted' => 0];

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
        $source = $this->argument('source');
        $sourceFiles = Storage::disk($source)->allFiles();
        $count = count($sourceFiles);
        $progress = $this->output->createProgressBar($count);
        $progress->start();
        $destination = $this->argument('destination');
        $destinationFiles = Storage::disk($destination)->allFiles();

        // Delete files in destination which aren't in source
        if ($this->option('delete')) {
            if ($difference = array_diff($destinationFiles, $sourceFiles)) {
                $count += count($difference);
                $progress->setMaxSteps($count);

                foreach ($difference as $file) {
                    Storage::disk($destination)->delete($file);
                    $this->countOutputLog('deleted', $file);
                    $progress->advance();
                }
            }
        }

        foreach ($sourceFiles as $file) {
            // If file already exists in destination
            if (in_array($file, $destinationFiles)) {
                // Overwrite file if argument is present
                if ($this->option('overwrite')) {
                    $visibility = Storage::disk($source)->getVisibility($file);
                    $content = Storage::disk($source)->get($file);
                    Storage::disk($destination)->put($file, $content, $visibility);
                    $this->countOutputLog('copied', $file);
                } else { // Skip file
                    Storage::disk($destination)->setVisibility($file, 'public');
                    $this->countOutputLog('skipped', $file);
                }
            } else {
                // File does not exist on destination, so copy
                $visibility = Storage::disk($source)->getVisibility($file);
                $content = Storage::disk($source)->get($file);
                Storage::disk($destination)->put($file, $content, $visibility);
                $this->countOutputLog('copied', $file);
            }
            
            $progress->advance();
        }

        $progress->finish();
        $this->info("\nDone! {$this->count['copied']} files copied, {$this->count['skipped']} files skipped, {$this->count['deleted']} files deleted.");
    }

    public function countOutputLog($action, $file)
    {
        $this->count[$action]++;
        $this->log[$action][] = $file;

        if ($this->option('output')) {
            $this->info("\n".strtoupper($action).": $file");
        }

        if ($this->option('log')) {
            Log::debug(strtoupper($action).": $file");
        }
    }
}
