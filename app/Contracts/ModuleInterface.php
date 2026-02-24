<?php

namespace App\Contracts;

interface ModuleInterface extends PluginInterface
{
    /**
     * Get the events and their corresponding listener methods for this module.
     *
     * @return array<class-string, string> Example: [\App\Events\SomeEvent::class => 'handleSomeEvent']
     */
    public function getSubscribedEvents(): array;
}
