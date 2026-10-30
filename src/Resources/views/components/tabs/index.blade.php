@props(['position' => 'left'])

<v-tabs
    position="{{ $position }}"
    {{ $attributes }}
>
    <x-portal::shimmer.tabs />
</v-tabs>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-tabs-template"
    >
        <div>
            <div
                class="flex flex-row justify-center gap-8 bg-[var(--bg-surface-elevated)] border-b border-[var(--border-subtle)] max-sm:gap-1.5"
                :style="positionStyles"
            >
                <div
                    role="button"
                    tabindex="0"
                    v-for="tab in tabs"
                    class="cursor-pointer px-8 py-4 text-base font-medium text-[var(--text-secondary)] hover:text-[var(--text-primary)] transition-colors max-md:px-4 max-md:py-3 max-md:text-sm max-sm:px-2.5 max-sm:py-2"
                    :class="{'border-b-2 border-[var(--brand-primary)] !text-[var(--brand-primary)] font-semibold transition': tab.isActive }"
                    :id="tab.$attrs.id + '-button'"
                    @click="change(tab)"
                >
                    @{{ tab.title }}
                </div>
            </div>

            <div>
                {{ $slot }}
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-tabs', {
            template: '#v-tabs-template',

            props: ['position'],

            data() {
                return {
                    tabs: []
                }
            },

            computed: {
                positionStyles() {
                    return [
                        `justify-content: ${this.position}`
                    ];
                },
            },

            methods: {
                change(selectedTab) {
                    this.tabs.forEach(tab => {
                        tab.isActive = (tab.title == selectedTab.title);
                    });
                },
            },
        });
    </script>
@endPushOnce
