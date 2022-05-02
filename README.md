# Feedamic

<!-- statamic:hide -->

![Statamic 3.3+](https://img.shields.io/badge/Statamic-3.3+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Feedamic on Packagist](https://img.shields.io/packagist/v/mitydigital/feedamic?style=for-the-badge)](https://packagist.org/packages/mitydigital/iconamic/stats)

---

<!-- /statamic:hide -->

> Feedamic is an Atom and RSS feed for Statamic 3

## Installation

Install it via the composer command

```
composer require mitydigital/feedamic
```

## Configuration

You will need to publish the configuration file:

```
php artisan vendor:publish --provider="MityDigital\Feedamic\ServiceProvider" --tag=config
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

```{{ feedamic }} ```

This will output the configured feed URLs and mimetypes to assist with readers auto-discovering the feed.

## Clearing the cache

Your feeds when rendered are cached forever

To clear the cache, you can do one of three things:

- save an entry in one of the configured `collections` for the feed
- run a ``please`` command

Saving an entry in a configured collection will automatically clear the feed cache.

You can force the cache to clear by running:

```
php please feedamic:clear
```

## Upgrade Notes

### Upgrading from version 1.*

Version 2 brings with it a new name. Isn't it great?

However there are some ***breaking changes*** with this update.

First of all, update your composer.json file:

```json
# Change from:
"mitydigital/statamic-rss-feed": "^1.0",

# Update to:
"mitydigital/feedamic": "^2.0",
```

The run `composer update`.

You will need to check some things to get yourself up and running:

- the config file
- the views
- tag usage
- command usage

Please note that from v2, Statamic 3.3 or later is required.

#### Config

Rename your config file from `rss.php` to `feedamic.php`. You'll find this in the `config/statamic`
folder, if you are using your own config file.

#### Views

If you have published the `atom.blade.php` or `rss.blade.php` views you will need to make sure that any references to
the configuration file is correctly updated now to be `feedamic` instead of `rss`.

You may want to also update the `<generator>` tag to include the new name and version. Check out the views in the source
of the component for what we've set as the defaults.

#### Tag

If you were using the `{{ rss_auto_discovery }}` tag, this has been renamed to be `{{ feedamic }}`.

#### Command

If you used the `rss-cache` command previously, this has been renamed to `feedamic`.

```bash
# v1.* command only
php please rss-cache:clear

# v2 command
php please feedamic:clear
```

### Upgrading from v1.3

When upgrading from v1.3, ensure you add the `image` configuration options to your config file.

### Upgrading from v1.2 or below

If upgrading from v1.2 or below, ensure you add the `language` and `copyright` configuration options to your config
file.

## Support

We love to share work like this, and help the community. However it does take time, effort and work.

The best thing you can do is [log an issue](../../issues).

Please try to be detailed when logging an issue, including a clear description of the problem, steps to reproduce the
issue, and any steps you may have tried or taken to overcome the issue too. This is an awesome first step to helping us
help you. So be awesome - it'll feel fantastic.

## Credits

- [Marty Friedel](https://github.com/martyf)

## License

This addon is licensed under the MIT license.