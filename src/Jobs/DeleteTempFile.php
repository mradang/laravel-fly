<?php

namespace mradang\LaravelFly\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class DeleteTempFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pathname;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $pathname)
    {
        $this->pathname = realpath($pathname);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Str::startsWith($this->pathname, storage_path('app/'))) {
            @unlink($this->pathname);
        }
    }
}
