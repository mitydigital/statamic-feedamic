<?php

namespace MityDigital\Feedamic\Models;

use MityDigital\Feedamic\Contracts\FeedamicEntry;
use Statamic\Tags\Glide;

class FeedEntry implements FeedamicEntry
{
    public $author;
    public $published;
    public $summary;
    public $image;
    public $title;
    public $updated;
    public $uri;
    public $entry;

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

        // if it is an array, it is a bard block with sets that needs processing
        if (is_array($summary)) {
            $summaryContent = [];

            // add text sets to the summary content
            foreach ($summary as $bardSet) {
                if (!empty($bardSet['type']) && $bardSet['type'] === 'text') {
                    $summaryContent[] = $bardSet['text'];
                }
            }

            // bring the content together to the summary variable as a string
            $summary = implode(' ', $summaryContent);
        } else {
            // if the summary is not a paragraph already, wrap it
            if ($summary && substr($summary, 0, 3) != '<p>') {
                $summary = '<p>'.$summary.'</p>';
            }
        }

        // do we have an image?
        if ($this->image) {
            $glide = new Glide();
            $glide->setContext([]);
            $glide->setParameters([
                'absolute' => true,
                'src'      => $this->image->url(),
                'alt'      => $this->title,
                'width'    => config('feedamic.image.width', 1280),
                'height'   => config('feedamic.image.height', 720)
            ]);
            $glide->generate();

            $summary = '<p><img src="'.$glide->index().'" alt="'.$this->title(true).'" width="'.config('feedamic.image.width', 1280).'" height="'.config('feedamic.image.height', 720).'" style="display:block; width:100%; max-width:100%; height:auto;" /></p>'.$summary;
        }

        // do we encode?
        if ($encode) {
            return htmlspecialchars($summary, ENT_XML1, 'UTF-8', true);
        } else {
            return $summary;
        }
    }
}
