<?php

return [
    'title' => 'Feedamic Configuration',

    'main' => 'Feeds',

    'image' => [
        'width' => 'Width',
        'height' => 'Height',
    ],

    'author_type' => [
        'display' => 'Source',
        'instructions' => 'Select **Related Entry/User** if your author is in a related Entry or User.
Select **Fields** if your author details are fields in your Entry.',

        'options' => [
            'entry' => 'Related Entry/User',
            'field' => 'Fields',
        ],

        'field' => [
            'display' => 'Relation Field',
            'instructions' => 'The handle of the field in your Blueprint that references the Entry or User that is the Author.',
        ],

        'fallback_name' => [
            'display' => 'Fallback Name',
            'instructions' => 'Used as a plain-text fallback name for when your Entry does not have an author configured.',
        ],

        'fallback_email' => [
            'display' => 'Fallback Email',
            'instructions' => 'Leave blank to not include a fallback email.<br>Used as a plain-text fallback email for when your Entry does not have an author configured.',
        ],

        'name' => [
            'display' => 'Name',
            'instructions' => 'The field handle(s) that are used to display the Author\'s name. 
            
For "Fields", a single field handle (such as "name"), or for "Related Entry/User" multiple handles each in square brackets (such as "[name_first] [name_last]").',
        ],

        'email' => [
            'display' => 'Email',
            'instructions' => "The field handle that is the author's email.

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

            'content_mode' => 'Content',
            'content' => 'Content Handles',
            'content_instructions' => 'Only used in Atom feeds.',

            'image_mode' => 'Image',
            'image' => 'Image Handles',

            'image_dimensions_mode' => 'Image Dimensions',
            'image_width' => 'Width',
            'image_height' => 'Height',

            'author_mode' => 'Author',
        ],

        'copyright_mode' => 'Copyright',
        'copyright' => 'New Copyright',

        'author_fallback_mode' => 'Author',
        'author_fallback_name' => 'Author Name',
        'author_fallback_email' => 'Author Email',

        'scope' => [
            'display' => 'Scope',
            'instructions' => 'You can provide a Query Scope to help filter feeds based on additional Query Builder logic specific to your needs and Blueprints. 

See https://statamic.dev/extending/query-scopes-and-filters#scopes',
        ],

        'author_model' => [
            'display' => 'Author Model',
            'instructions' => 'Select the Author Model to use for the feed generation.',
        ],

        'entry_model' => [
            'display' => 'Entry Model',
            'instructions' => 'Select the Entry Model to use for the feed generation.',
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

            'content' => [
                'display' => 'Content',

                'default_content' => [
                    'display' => 'Handles',
                    'instructions' => 'A list of fields that looked at, in order, to determine the "content" for each entry. Only used in Atom feeds.',
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
                'display' => 'Models',

                'default_entry_model' => [
                    'display' => 'Entry Model',
                    'instructions' => 'Select the default Entry Model to use for the feed generation.',
                ],

                'default_author_model' => [
                    'display' => 'Author Model',
                    'instructions' => 'Select the default Author Model to use for the feed generation.',
                ],
            ],
        ],
    ],

];
