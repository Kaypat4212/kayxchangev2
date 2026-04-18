<?php

namespace App\View\Composers;

use App\Models\SiteContent;
use Illuminate\View\View;

class SiteContentComposer
{
    public function compose(View $view): void
    {
        $view->with('siteContent', SiteContent::allKeyed());
    }
}
