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

            // Real-time live design token updates without full reload
            if (event.data?.type === 'update-token') {
                const { name, value } = event.data;
                if (name && value !== undefined) {
                    document.documentElement.style.setProperty(name, value);
                    if (name === '--header-bg') {
                        document.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.backgroundColor = value);
                    } else if (name === '--header-text') {
                        document.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.color = value);
                    } else if (name === '--footer-bg') {
                        document.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.backgroundColor = value);
                    } else if (name === '--footer-text') {
                        document.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.color = value);
                    } else if (name === '--card-radius') {
                        document.querySelectorAll('.portal-card, [data-portal-card], .glass-card, [data-spotlight], .rounded-card').forEach(el => el.style.borderRadius = value);
                    } else if (name === '--btn-radius') {
                        document.querySelectorAll('.btn-primary, .btn-secondary, .btn-accent, .primary-button, .secondary-button, input, select, textarea').forEach(el => el.style.borderRadius = value);
                    }
                }
            }

            if (event.data?.type === 'update-tokens') {
                const { tokens } = event.data;
                if (tokens && typeof tokens === 'object') {
                    for (const [k, v] of Object.entries(tokens)) {
                        document.documentElement.style.setProperty(k, v);
                    }
                    if (tokens['--header-bg']) {
                        document.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.backgroundColor = tokens['--header-bg']);
                    }
                    if (tokens['--header-text']) {
                        document.querySelectorAll('header, .portal-header, [data-portal-header]').forEach(el => el.style.color = tokens['--header-text']);
                    }
                    if (tokens['--footer-bg']) {
                        document.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.backgroundColor = tokens['--footer-bg']);
                    }
                    if (tokens['--footer-text']) {
                        document.querySelectorAll('footer, .portal-footer, [data-portal-footer]').forEach(el => el.style.color = tokens['--footer-text']);
                    }
                    if (tokens['--card-radius']) {
                        document.querySelectorAll('.portal-card, [data-portal-card], .glass-card, [data-spotlight], .rounded-card').forEach(el => el.style.borderRadius = tokens['--card-radius']);
                    }
                    if (tokens['--btn-radius']) {
                        document.querySelectorAll('.btn-primary, .btn-secondary, .btn-accent, .primary-button, .secondary-button, input, select, textarea').forEach(el => el.style.borderRadius = tokens['--btn-radius']);
                    }
                }
            }

            if (event.data?.type === 'toggle-dark') {
                const isDark = Boolean(event.data.dark);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
            }

            if (event.data?.type === 'load-font') {
                const { url } = event.data;
                if (url && !document.querySelector(`link[href="${url}"]`)) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = url;
                    document.head.appendChild(link);
                }
            }

            if (event.data?.type === 'custom-css') {
                let customStyleTag = document.getElementById('portal-custom-css-live');
                if (! customStyleTag) {
                    customStyleTag = document.createElement('style');
                    customStyleTag.id = 'portal-custom-css-live';
                    document.head.appendChild(customStyleTag);
                }
                customStyleTag.textContent = event.data.css || '';
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

