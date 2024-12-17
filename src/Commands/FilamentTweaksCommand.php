<?php

namespace Dowhile\FilamentTweaks\Commands;

use Illuminate\Console\Command;

class FilamentTweaksCommand extends Command
{
    public $signature = 'filament-tweaks';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
