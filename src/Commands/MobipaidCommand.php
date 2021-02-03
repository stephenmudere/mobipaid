<?php

namespace Stephenmudere\Mobipaid\Commands;

use Illuminate\Console\Command;

class MobipaidCommand extends Command
{
    public $signature = 'mobipaid';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
