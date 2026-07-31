<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface BrowseInterface
{
    /**
     * Search and return a collection of matching items formatted for quick search browsing.
     * This is called server-side with the user's query string.
     *
     * @param  string  $query
     * @return \Illuminate\Support\Collection
     */
    public static function searchBrowseItems(string $query): Collection;
}
