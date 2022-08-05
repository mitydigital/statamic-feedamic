<?php

namespace MityDigital\Feedamic\Models;

use Illuminate\Support\Collection;
use Statamic\Data\DataCollection;

class FeedEntryAuthor
{
    public $author;
    public $tokens;

    protected $template;
    protected $name;

    public function __construct($author, $feed = null)
    {
        // set the author
        $this->author = $author;

        // get the name template
        if (config()->has('feedamic.feeds.'.$feed.'.author')) {
            $this->template = config('feedamic.feeds.'.$feed.'.author.name');
        } else {
            $this->template = config('feedamic.author.name');
        }

        // find the tokens needed for the template
        preg_match_all("/\[[^\]]*\]/", $this->template, $authorNameTokens);
        $this->tokens = $authorNameTokens[0];
    }

    public function name()
    {
        // if we have rendered the name, return it
        if ($this->name) {
            return $this->name;
        }

        // build the name
        $name = $this->template;
        foreach ($this->tokens as $token) {
            // convert to the handle too
            $handle = str_replace(['[', ']'], '', $token);

            // get the property
            $name = str_replace($token, $this->getProperty($handle), $name);
        }

        // set the name
        $this->name = $name;

        return $this->name;
    }

    public function getProperty($handle)
    {
        $author = $this->author;

        if (!$author) {
            return '';
        }

        // if it is a simple collection, or a Statamic Data Collection (i.e. Taxonomy Terms, Entries, Users, etc)
        if (get_class($author) == Collection::class || is_subclass_of($author, DataCollection::class)) {
            $author = $author->first();
        }

        if (!$author) {
            return '';
        }

        switch ($handle) {
            case 'email':
                if (method_exists($author, 'email')) {
                    return $author->email();
                } else {
                    return $author->value($handle);
                }
            case 'id':
                if (method_exists($author, 'id')) {
                    return $author->id();
                } else {
                    return $author->value($handle);
                }
            default:
                return $author->value($handle);
        }
    }

    public function email()
    {
        return $this->getProperty('email');
    }
}