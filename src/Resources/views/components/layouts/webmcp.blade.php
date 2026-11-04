{!! view_render_event('bagisto.portal.layout.webmcp.before') !!}

<!-- WebMCP Declarative Agent Tool Schemas (Hidden from visual layout, readable by AI agents) -->
<div class="hidden" aria-hidden="true" style="display:none !important;">
    <!-- Tool 1: Search Portal Content -->
    <form
        action="{{ route('portal.search.index') }}"
        method="GET"
        toolname="search_portal"
        tooldescription="{{ trans('portal::app.components.layouts.webmcp.search-description') }}"
        toolautosubmit
    >
        <input
            type="text"
            name="query"
            aria-label="{{ trans('portal::app.components.layouts.webmcp.search-query-label') }}"
            toolparamdescription="{{ trans('portal::app.components.layouts.webmcp.search-query-label') }}"
            required
        >

        <button type="submit" class="hidden" aria-hidden="true"></button>
    </form>

    <!-- Tool 2: Navigate to Portal Page -->
    <form
        action="{{ url('/pages') }}"
        method="GET"
        toolname="view_portal_page"
        tooldescription="{{ trans('portal::app.components.layouts.webmcp.view-page-description') }}"
        toolautosubmit
    >
        <input
            type="text"
            name="slug"
            aria-label="{{ trans('portal::app.components.layouts.webmcp.view-page-slug-label') }}"
            toolparamdescription="{{ trans('portal::app.components.layouts.webmcp.view-page-slug-label') }}"
            required
        >

        <button type="submit" class="hidden" aria-hidden="true"></button>
    </form>

    <!-- Tool 3: Subscribe to Newsletter -->
    <form
        action="{{ route('portal.subscription.store') }}"
        method="POST"
        toolname="subscribe_newsletter"
        tooldescription="{{ trans('portal::app.components.layouts.webmcp.subscribe-description') }}"
        toolautosubmit
    >
        @csrf
        <input
            type="email"
            name="email"
            aria-label="{{ trans('portal::app.components.layouts.webmcp.subscribe-email-label') }}"
            toolparamdescription="{{ trans('portal::app.components.layouts.webmcp.subscribe-email-label') }}"
            required
        >

        <button type="submit" class="hidden" aria-hidden="true"></button>
    </form>
</div>

{{--
    Bridge the declarative `<form toolname>` markup to the browser WebMCP
    API (https://webmachinelearning.github.io/webmcp/). Each form is registered
    via `navigator.modelContext.provideContext()`; the execute callback fills
    the form fields from AI agent arguments and submits it.
--}}
@pushOnce('scripts')
    <script>
        (function () {
            function registerPortalWebMcpTools() {
                if (
                    ! ('modelContext' in navigator)
                    || typeof navigator.modelContext.provideContext !== 'function'
                ) {
                    return;
                }

                var tools = [];

                document.querySelectorAll('form[toolname]').forEach(function (form) {
                    var name = form.getAttribute('toolname');

                    if (! name) {
                        return;
                    }

                    var properties = {};
                    var required = [];

                    form.querySelectorAll('input[name], textarea[name], select[name]').forEach(function (field) {
                        var param = field.getAttribute('name');
                        if (param === '_token') return;

                        properties[param] = {
                            type: 'string',
                            description: field.getAttribute('toolparamdescription')
                                || field.getAttribute('aria-label')
                                || param,
                        };

                        if (field.hasAttribute('required')) {
                            required.push(param);
                        }
                    });

                    tools.push({
                        name: name,
                        description: form.getAttribute('tooldescription') || name,
                        inputSchema: {
                            type: 'object',
                            properties: properties,
                            required: required,
                        },
                        execute: function (args) {
                            args = args || {};

                            if (name === 'view_portal_page' && args.slug) {
                                window.location.href = "{{ url('/pages') }}/" + encodeURIComponent(args.slug);
                                return {
                                    content: [{ type: 'text', text: 'Navigating to page ' + args.slug }],
                                };
                            }

                            Object.keys(args).forEach(function (key) {
                                var field = form.querySelector('[name="' + key + '"]');
                                if (field) {
                                    field.value = args[key];
                                }
                            });

                            form.submit();

                            return {
                                content: [
                                    { type: 'text', text: 'Executed "' + name + '" successfully.' },
                                ],
                            };
                        },
                    });
                });

                if (tools.length) {
                    navigator.modelContext.provideContext({ tools: tools });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', registerPortalWebMcpTools);
            } else {
                registerPortalWebMcpTools();
            }
        })();
    </script>
@endPushOnce

{!! view_render_event('bagisto.portal.layout.webmcp.after') !!}

