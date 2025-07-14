<?php

return [
    'title' => 'Feedamic Configuration',

    'main' => 'Feeds',

    'image' => [
        'width' => 'Width',
        'height' => 'Height',
    ],

    'author_type' => [
        'display' => 'Display',
        'instructions' => [
            'Select **Entry** if your author is in a related Entry or User.
Select **Field** if your author details are explicit fields in your Entry.',
        ],

        'options' => [
            'entry' => 'Entry',
            'field' => 'Field',
        ],

        'name' => [
            'display' => 'Name',
            'instructions' => 'For **Entry**, the field handles in your linked Entry used to get the name, wrapped in square brackets, such as "[name_first] [name_last]".

For **Field**, the handle in your Entry that contains the author name.',
        ],

        'email' => [
            'display' => 'Email',
            'instructions' => "For **Entry**, the field handle in your linked Entry/User that is the author's email.

For **Field**, the handle in your Entry that is the author's email.

Leave **blank** to exclude the email.",
        ],
    ],

    'feeds' => [
        'display' => 'Configuration',
        'button_label' => 'Add Feed...',

        'sets' => [
            'types' => 'Types',
            'feed' => 'Feed',
        ],

        'sites' => [
            'display' => 'Generate for...',

            'options' => [
                'all' => 'All Sites',
                'specific' => 'Specific Sites',
            ],
        ],

        'sites_specific' => 'Specific Sites',

        'title' => [
            'display' => 'Title',
            'instructions' => 'The title for the feed.',
        ],

        'handle' => [
            'display' => 'Handle',
            'instructions' => 'A unique handle for the feed.',
        ],

        'description' => [
            'display' => 'Description',
            'instructions' => 'The description (RSS) or subtitle (Atom) for the feed.',
        ],

        'routes' => [
            'display' => 'Routes',
            'instructions' => "Define the routes and views for your feed. Your routes should be relative to the Site's root.",

            'atom' => [
                'display' => 'Atom',
                'instructions' => 'Leave blank to disable the Atom feed.',
            ],

            'atom_view' => [
                'display' => 'Atom View',
                'instructions' => "Leave blank to use Feedamic's default.",
            ],

            'rss' => [
                'display' => 'RSS',
                'instructions' => 'Leave blank to disable the RSS feed.',
            ],

            'rss_view' => [
                'display' => 'RSS View',
                'instructions' => "Leave blank to use Feedamic's default.",
            ],
        ],

        'collections' => 'Collections',

        'taxonomies' => [
            'display' => 'Taxonomies',
            'instructions' => 'The Taxonomies and Terms to be used to filter the feed. Each set will be joined using "and" logic.',

            'add_row' => 'Add set...',

            'fields' => [
                'terms' => 'Terms',
                'logic' => [
                    'display' => 'Logic',

                    'options' => [
                        'and' => 'All',
                        'or' => 'Any',
                    ],
                ],
            ],
        ],

        'show' => [
            'display' => 'Show',

            'options' => [
                'all' => 'All Entries',
                'limit' => 'Limited Entries',
            ],
        ],

        'show_limit' => 'Limit',

        'advanced' => 'Show advanced config?',

        'override_options' => [
            'custom' => 'Custom',
            'default' => 'Default',
            'disabled' => 'Disabled',
        ],

        'mappings' => [
            'display' => 'Field Mappings',
            'instructions' => "Map field handles from your Blueprints of the feed's Entries to different feed properties. If you need more advanced control, you can create your own FeedEntry model (see Advanced Configuration).",

            'title_mode' => 'Title',
            'title' => 'Title Handles',

            'summary_mode' => 'Summary',
            'summary' => 'Summary Handles',

            'image_mode' => 'Image',
            'image' => 'Image Handles',

            'image_dimensions_mode' => 'Image Dimensions',

            'author_mode' => 'Author',
        ],

        'copyright_mode' => 'Copyright',
        'copyright' => 'New Copyright',

        'scope' => [
            'display' => 'Scope',
            'instructions' => 'You can provide a Query Scope to help filter feeds based on additional Query Builder logic specific to your needs and Blueprints. 

See https://statamic.dev/extending/query-scopes-and-filters#scopes',
        ],

        'model' => [
            'display' => 'Model',
            'instructions' => 'Select the Model to use for the feed generation.',
        ],

        'alt_url' => [
            'display' => 'Alt URL',
            'instructions' => "The alternative link used. If omitted, will be the Site's URL.",
        ],
    ],

    'defaults' => [
        'title' => 'Defaults',

        'sections' => [
            'title' => [
                'display' => 'Title',

                'default_title' => [
                    'display' => 'Handles',
                    'instructions' => 'The Title of each entry will be determined by working through each of these field handles in order until a value is found.<br>If there no value is found, "title" will always be used, even if you do not include it in your list.',
                ],
            ],

            'summary' => [
                'display' => 'Summary',

                'default_summary' => [
                    'display' => 'Handles',
                    'instructions' => 'A list of fields that looked at, in order, to determine the "summary" for each entry.',
                ],
            ],

            'image' => [
                'display' => 'Image',

                'default_image_enabled' => [
                    'display' => 'Default Image Enabled',
                    'inline_label' => 'Include image in Summary?',
                ],

                'default_image' => [
                    'display' => 'Handles',
                    'instructions' => 'A list of fields that looked at, in order, to determine the "image" for each entry.',
                ],
            ],

            'author' => [
                'display' => 'Author',

                'default_author_enabled' => [
                    'display' => 'Default Author Enabled',
                    'inline_label' => 'Include author?',
                ],
            ],

            'copyright' => [
                'display' => 'Copyright',

                'default_copyright' => [
                    'display' => 'Copyright',
                    'instructions' => 'A string to output to the <code>&lt;copyright&gt;</code> (RSS) or <code>&lt;rights&gt;</code> (Atom) feed.

Keep blank to exclude this element.',
                ],
            ],

            'model' => [
                'display' => 'Model',

                'default_model' => [
                    'display' => 'Model',
                    'instructions' => 'Select the default Model to use for the feed generation.',
                ],
            ],
        ],
    ],

];
