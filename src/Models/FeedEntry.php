<?php

namespace MityDigital\StatamicRssFeed\Models;

class FeedEntry
{
    public $author;
    public $published;
    public $summary;
    public $title;
    public $updated;
    public $uri;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }
}