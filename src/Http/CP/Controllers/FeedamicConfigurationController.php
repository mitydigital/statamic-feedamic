<?php

namespace MityDigital\Feedamic\Http\CP\Controllers;

use Illuminate\Http\Request;
use MityDigital\Feedamic\Facades\Feedamic;
use Statamic\Fields\Blueprint;
use Statamic\Http\Controllers\Controller;

class FeedamicConfigurationController extends Controller
{
    protected Blueprint $blueprint;

    public function __construct()
    {
        $this->blueprint = Feedamic::blueprint();
    }

    public function show(Request $request)
    {
        $this->authorize('feedamic.config');

        // get the fields
        $fields = $this->blueprint
            ->fields()
            ->addValues(Feedamic::load()->toArray())
            ->preProcess();

        // render the view
        return view('feedamic::cp.show', [
            'title' => __('feedamic::cp.config.name'),
            'action' => cp_route('feedamic.config.update'),
            'blueprint' => $this->blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('feedamic.config');

        // for the blueprint fields, add the request values
        $fields = $this->blueprint
            ->fields()
            ->addValues($request->only([
                'feeds',
                'default_title',
                'default_summary',
                'default_image_enabled',
                'default_image',
                'default_image_width',
                'default_image_height',
                'default_author_enabled',
                'default_author_type',
                'default_author_name',
                'default_author_email',
                'default_copyright',
                'default_model',
            ]));

        // validate
        $fields->validator()->validate();

        // save
        Feedamic::save($fields->values()->toArray());
    }
}
