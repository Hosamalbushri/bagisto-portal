<x-admin::layouts>
    <x-slot:title>
        @lang('portal::app.admin.pages.customize.title', ['title' => $page->title])
    </x-slot>

    {!! view_render_event('bagisto.admin.portal.pages.customize.before') !!}

    <x-admin::appearance.sections.editor
        :sections="$sections"
        :section-types="$sectionTypes"
        :theme-name="$themeName"
        :channels="$channels"
        :channel-id="$channelId"
        :locales="$locales"
        :locale-code="$localeCode"
        :preview-url="$previewUrl"
        :reorder-url="$reorderUrl"
        :store-url="$storeUrl"
        :publish-url="$publishUrl"
        :discard-url="$discardUrl"
        :urls="$urls"
    />

    {!! view_render_event('bagisto.admin.portal.pages.customize.after') !!}
</x-admin::layouts>
