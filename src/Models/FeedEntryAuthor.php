<?php

namespace MityDigital\Feedamic\Models;

class FeedEntryAuthor
{
    public $author;
    public $tokens;

    protected $template;
    protected $name;

    public function __construct($author)
    {
        // set the author
        $this->author = $author;

        // get the name template
        $this->template = config('feedamic.author.name');

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

    public function email()
    {
        return $this->getProperty('email');
    }

    public function getProperty($handle)
    {
        $author = $this->author;

        if (!$author) {
            return '';
        }

        if (get_class($author) == \Illuminate\Support\Collection::class) {
            $author = $author->first();
        }

        switch ($handle) {
            case 'email':
                return $author->email();
            case 'id':
                return $author->id();
            default:
                return $author->value($handle);
        }
    }
}