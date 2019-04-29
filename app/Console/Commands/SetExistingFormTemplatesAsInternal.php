<?php

namespace App\Console\Commands;

use \App\Models\AccessLevel;
use \App\Models\FormTemplate;
use \App\Services\AclFacade as Acl;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class SetExistingFormTemplatesAsInternal extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'informed365:correct-existing-templates';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Set existing form templates as internal";
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Setting existing form templates as internal');

        $accessLevel = AccessLevel::whereValue('internal')->first();

        FormTemplate::doesntHave('accessSettings')->get()->map(function($formTemplate) use ($accessLevel)
        {
            $formTemplate->accessSettings()->create([
                'access_level_id' => $accessLevel->id,
            ]);
        });
    }
}