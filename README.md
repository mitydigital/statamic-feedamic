# Atom and RSS Feed for Statamic 3

![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge&link=https://statamic.com)

## Installation

Install it via the composer command

```
composer require mitydigital/statamic-rss-feed
```

## Configuration

You will need to publish the configuration file:

```
php artisan vendor:publish --provider="MityDigital\StatamicRssFeed\ServiceProvider" --tag=config
```

You **must** set the `title` and `description` values for the feed.

By default, the "blog" `collection` is configured to have its entries in the feed. You can adjust the array to only
include the collections you want in the feed.

You will get two feeds by default:

- **atom** at `/feed/atom`
- **rss** (v2) at `/feed`

These can be routed differently with the `routes` configuration option.

When the routes are visited, they are cached forever, or until an Entry in one of the `collections` is saved, or you use
the `please` command to clear the cache. You can configure the cache key if you want.

The `summary` gives you an array of fields to look at to get the content for the `<summary>` attribute of the Atom feed.
These are checked in order, and when content is found, it will be used for the `<summary>` and the other fields will not
be checked. The default is looking for `introduction` then `meta_description`.

The `image` gives you the ability to pick a field (or array of fields, like `summary`) to look for to find the image to
be included with the summary content. You can also set the width and height of the rendered image, otherwise will
default to 1280 x 720. Set `image` to false or null to not include images.

You can also adjust the `<author>` behaviour. Set this to `false` to disable outputting an author at all.

Within the array, set `email` to `true` to output the `<email>` in your Atom feed. When `false` (the default), `<email>`
will not be output.

The `name` attribute allows you to configure how the name is output. Each User field handle is in square brackets, and
by default is looking for the "name". If you were to have a first and last name (two fields), your template may look
like: `[name_first] [name_first]`. If you're using the default Statamic User, `[name]` will just work.

The `language` option allows you to define a valid language code for your feeds. Given XML can accept more values than
the RSS specification, refer to the [RSS-Specific Language Codes](https://www.rssboard.org/rss-language-codes) for valid
options. The default is `en`.

The `copyright` option is a string that will output the copyright (or rights for Atom) to the start of the feed. False,
default, will exclude this tag.

## Auto-discovery for your site

A Statamic tag is included that will output your configured feeds to your site's markup for auto-discovery assistance.

In your `<head>` tag of your layout, simply call the tag:

```{{ rss_auto_discovery }} ```

This will output the configured feed URLs and mimetypes to assist with readers auto-discovering the feed.

## Clearing the cache

Your feeds when rendered are cached forever

To clear the cache, you can do one of three things:

- save an entry in one of the configured `collections` for the feed
- run a ``please`` command

Saving an entry in a configured collection will automatically clear the feed cache.

You can force the cache to clear by running:

```
php please rss-cache:clear
```

## Upgrade Notes

If upgrading from v1.2 or below, ensure you add the `language` and `copyright` configuration options to your config
file.

## License

This addon is licensed under the MIT license.