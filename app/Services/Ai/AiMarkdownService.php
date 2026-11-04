<?php

namespace App\Services\Ai;

use Illuminate\Support\Str;

class AiMarkdownService
{
    public function render(string $markdown): string
    {
        /*
         * html_input => strip prevents raw HTML from the model
         * from being rendered.
         *
         * allow_unsafe_links => false blocks javascript: links.
         */
        return Str::markdown(
            $markdown,
            [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]
        );
    }
}
