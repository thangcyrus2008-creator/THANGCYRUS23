<?php

namespace App\Services;

use Illuminate\Routing\UrlGenerator as BaseUrlGenerator;

class CustomUrlGenerator extends BaseUrlGenerator
{
    /**
     * Generate the URL to an application asset.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if (is_string($path) && (str_starts_with($path, 'data:') || str_starts_with($path, 'blob:'))) {
            return $path;
        }

        return parent::asset($path, $secure);
    }
}
