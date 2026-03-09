<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface BrowseInterface
{
    /**
     * Return a collection of items formatted for quick search browsing.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function toBrowseItems(): Collection;
}
