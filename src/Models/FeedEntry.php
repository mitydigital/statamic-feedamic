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

    public function title($encode = true)
    {
        if ($encode) {
            return htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8', true);
        } else {
            return $this->title;
        }
    }

    public function summary($encode = true)
    {
        $summary = $this->summary;

        // if there are multiple paragraphs, add a space
        $summary = str_replace('</p><p>', ' </p><p>', $summary);

        // strip tags
        $summary = strip_tags($summary);

        // decode special chars as it could be from Bard which would encode them
        $summary = htmlspecialchars_decode($summary, ENT_QUOTES);

        if ($encode)
        {
            $summary = htmlspecialchars($summary, ENT_XML1, 'UTF-8', true);
        }

        return $summary;
    }
}
