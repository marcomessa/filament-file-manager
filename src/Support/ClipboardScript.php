<?php

namespace MmesDesign\FilamentFileManager\Support;

use Illuminate\Support\Js;

class ClipboardScript
{
    /**
     * Build the Alpine expression that copies a file URL to the clipboard.
     *
     * The copy runs directly in the click handler: browsers (Safari in particular)
     * only allow clipboard writes during a user gesture, so it must not wait for a
     * server round-trip. Relative URLs are resolved against the current origin, and
     * the notification reflects whether the write actually succeeded.
     *
     * @param  string  $urlExpression  A JavaScript expression evaluating to the URL.
     */
    public static function copyUrl(string $urlExpression): string
    {
        $copied = Js::from(__('filament-file-manager::file-manager.messages.url_copied'));
        $failed = Js::from(__('filament-file-manager::file-manager.messages.url_copy_failed'));

        return "navigator.clipboard.writeText(new URL({$urlExpression}, window.location.origin).href)"
            .".then(() => new FilamentNotification().title({$copied}).success().send())"
            .".catch(() => new FilamentNotification().title({$failed}).danger().send())";
    }
}
