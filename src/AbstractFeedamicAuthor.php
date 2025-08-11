<?php

namespace MityDigital\Feedamic;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use MityDigital\Feedamic\Exceptions\UnknownAuthorTypeException;
use MityDigital\Feedamic\Models\FeedamicConfig;
use Statamic\Auth\User;
use Statamic\Entries\Entry;
use Statamic\Fields\Value;

abstract class AbstractFeedamicAuthor
{
    use ForwardsCalls;

    protected ?string $name = null;

    protected ?string $email = null;

    public function __construct(public Entry|User $resource, protected FeedamicConfig $config) {}

    public function name(): null|string|Value
    {
        if (! $this->config->hasAuthor()) {
            $this->name = null;
        } elseif (! isset($this->name)) {
            $this->name = null;
            $fields = $this->config->getAuthorName();
            switch ($this->config->getAuthorType()) {
                case 'entry':
                    // tokenise it
                    $template = $this->config->getAuthorName();

                    preg_match_all('/\\[(.*?)\\]/', $template, $matches);

                    $replacements = [];
                    foreach ($matches[1] as $handle) {
                        if (method_exists($this->resource, $handle)) {
                            $replacements[$handle] = $this->resource->{$handle}();
                        } else {
                            $replacements[$handle] = $this->resource->get($handle);
                        }
                    }

                    $this->name = Str::replace($matches[0], array_values($replacements), $template);
                    break;
                case 'field':
                    // simply get it
                    if ($this->resource->{$fields}) {
                        $this->name = $this->resource->{$fields};
                    } elseif (method_exists($this->resource, $fields)) {
                        $this->name = $this->resource->{$fields}();
                    } else {
                        $this->name = $this->resource->get($fields);
                    }
                    break;
                default:
                    throw new UnknownAuthorTypeException(__('feedamic::exceptions.unknown_author_type', [
                        'type' => $this->config->getAuthorType(),
                    ]));
            }
        }

        return $this->name;
    }

    public function email(): null|string|Value
    {
        if (! $this->config->hasAuthor()) {
            $this->email = null;
        } elseif (! isset($this->email)) {
            $this->email = null;
            if ($field = $this->config->getAuthorEmail()) {
                if ($this->resource->{$field}) {
                    $this->email = $this->resource->{$field};
                } elseif (method_exists($this->resource, 'email')) {
                    $this->email = $this->resource->email();
                } else {
                    $this->email = $this->resource->get($field);
                }
            }
        }

        return $this->email;
    }

    public function __call(string $name, array $arguments)
    {
        return $this->forwardCallTo($this->resource, $name, $arguments);
    }

    public function resource(): Entry|User
    {
        return $this->resource;
    }

    public function __get($name)
    {
        if (property_exists($this->resource, $name)) {
            return $this->resource->{$name};
        }

        throw new Exception("Property {$name} does not exist.");
    }

    public function __set($name, $value)
    {
        if (property_exists($this->resource, $name)) {
            $this->resource->{$name} = $value;

            return;
        }

        throw new Exception("Property {$name} does not exist.");
    }
}
