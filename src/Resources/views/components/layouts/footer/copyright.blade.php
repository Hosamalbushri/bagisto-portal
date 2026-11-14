@php
    $footerSettings = portal_layout_settings('footer');
    $channelName = core()->getCurrentChannel()->name ?? config('app.name');
    $currentYear = date('Y');

    $copyrightText = data_get($footerSettings, 'copyright.text')
        ?: core()->getConfigData('general.content.footer.copyright_content');

    if (! empty($copyrightText)) {
        $formattedCopyright = strip_tags(str_replace(
            ['{year}', '{channel}'],
            [$currentYear, $channelName],
            (string) $copyrightText
        ), '<a><strong><em><span>');
    } else {
        $formattedCopyright = "&copy; {$currentYear} {$channelName}. " . trans('portal::app.footer.all-rights-reserved');
    }
@endphp

{!! view_render_event('bagisto.portal.layout.footer.copyright.before') !!}

@if (isset($slot) && $slot->isNotEmpty())
    {{ $slot }}
@else
    <p>{!! $formattedCopyright !!}</p>
@endif

{!! view_render_event('bagisto.portal.layout.footer.copyright.after') !!}
