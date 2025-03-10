<?php

namespace Callcocam\Raptor\Commands;

use Illuminate\Console\Command;

class RaptorCommand extends Command
{
    public $signature = 'raptor';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
