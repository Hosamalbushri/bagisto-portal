<div
    class="appearance-preview-banner"
    role="status"
    hidden
>
    {{ trans('portal::app.home.preview-banner', [
        'theme'   => $previewTheme ?? themes()->getName(),
        'channel' => core()->getCurrentChannel()->name,
    ]) }}
</div>

@pushOnce('styles')
    <style>
        .appearance-preview-banner:not([hidden]) {
            position: fixed;
            inset-inline: 0;
            bottom: 0;
            z-index: 10000;
            padding: 8px 16px;
            background: #111827;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        [data-section-id].is-editing {
            position: relative;
            z-index: 1;
            outline: 3px solid #3b82f6;
            outline-offset: -3px;
            border-radius: 4px;
            box-shadow: 0 0 0 9999px rgba(17, 24, 39, .45);
            transition: outline-color .2s ease-in-out;
        }

        [data-section-id].is-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 96px;
            margin: 12px;
            border: 2px dashed #cbd5e1;
            border-radius: 6px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
        }

        [data-section-id].is-empty::after {
            content: attr(data-section-name);
        }

        [data-section-id].is-editing::before {
            content: attr(data-section-name);
            position: absolute;
            top: 0;
            inset-inline-start: 0;
            z-index: 2;
            padding: 2px 10px;
            border-bottom-right-radius: 4px;
            background: #3b82f6;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            line-height: 20px;
            letter-spacing: .02em;
            pointer-events: none;
        }
    </style>
@endPushOnce

@pushOnce('scripts')
    <script type="module">
        const focusSection = (id) => {
            document.querySelectorAll('[data-section-id]').forEach(node => {
                node.classList.remove('is-editing');
            });

            const target = document.querySelector(`[data-section-id="${id}"]`);

            if (! target) {
                return;
            }

            target.classList.add('is-editing');

            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        };

        const markEmptySections = () => {
            document.querySelectorAll('[data-section-id]').forEach(node => {
                node.classList.toggle('is-empty', node.getBoundingClientRect().height < 4);
            });
        };

        markEmptySections();

        const banner = document.querySelector('.appearance-preview-banner');
        if (banner) {
            banner.hidden = window.self !== window.top;
        }

        window.addEventListener('load', markEmptySections);

        window.addEventListener('message', event => {
            if (event.origin !== window.location.origin) {
                return;
            }

            if (event.data?.type === 'section-focus') {
                focusSection(event.data.id);
            }
        });

        document.addEventListener('click', event => {
            if (event.target.closest('a')) {
                event.preventDefault();
            }

            const section = event.target.closest('[data-section-id]');

            if (! section) {
                return;
            }

            event.preventDefault();

            window.parent.postMessage({
                type: 'section-selected',
                id: Number(section.dataset.sectionId),
            }, window.location.origin);
        }, true);

        document.addEventListener('submit', event => event.preventDefault(), true);
    </script>
@endPushOnce

