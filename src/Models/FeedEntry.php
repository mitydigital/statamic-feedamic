<?php

namespace MityDigital\StatamicRssFeed\Models;

use Statamic\Tags\Glide;

class FeedEntry
{
    public $author;
    public $published;
    public $summary;
    public $image;
    public $title;
    public $updated;
    public $uri;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Returns true when the Entry has either a summary or image
     *
     * @return bool
     */
    public function hasSummaryOrImage(): bool
    {
        if ($this->summary || $this->image) {
            return true;
        }

        return false;
    }

    /**
     * Returns the entry title.
     *
     * @param  bool  $encode  True to process the title's special characters
     *
     * @return string
     */
    public function title(bool $encode = true): string
    {
        if ($encode) {
            return htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8', true);
        } else {
            return $this->title;
        }
    }

    /**
     * Returns the summary for the entry, including the Image if used.
     *
     * @param  bool  $encode    True to process the summary's special characters
     *
     * @return string
     */
    public function summary(bool $encode = true): string
    {
        // get the summary
        $summary = $this->summary;

        // if the summary is not a paragraph already, wrap it
        if ($summary && substr($summary, 0, 3) != '<p>') {
            $summary = '<p>'.$summary.'</p>';
        }

        // do we have an image?
        if ($this->image) {
            $glide = new Glide();
            $glide->setContext([]);
            $glide->setParameters([
                'absolute' => true,
                'src'      => $this->image->url(),
                'alt'      => $this->title,
                'width'    => config('statamic.rss.image.width', 1280),
                'height'   => config('statamic.rss.image.height', 720)
            ]);
            $glide->generate();

            $summary = '<p><img src="'.$glide->index().'" alt="'.$this->title(true).'" width="'.config('statamic.rss.image.width', 1280).'" height="'.config('statamic.rss.image.height', 720).'" style="display:block; width:100%; max-width:100%; height:auto;" /></p>'.$summary;
        }

        // do we encode?
        if ($encode) {
            return htmlspecialchars($summary, ENT_XML1, 'UTF-8', true);
        } else {
            return $summary;
        }
    }
}
