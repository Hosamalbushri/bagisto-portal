@props([
    'isActive' => true,
])

<div {{ $attributes->merge(['class' => 'border-b border-[var(--border-subtle)]']) }}>
    <v-accordion
        {{ $attributes->except('class') }}
        is-active="{{ $isActive }}"
    >
        @isset($header)
            <template v-slot:header="{ toggle, isOpen }">
                <div
                    {{ $header->attributes->merge(['class' => 'flex cursor-pointer select-none items-center justify-between p-4 text-[var(--text-primary)] hover:text-[var(--brand-primary)] transition-colors']) }}
                    role="button"
                    tabindex="0"
                    @click="toggle"
                >
                    {{ $header }}

                    <span
                        v-bind:class="isOpen ? 'icon-arrow-up text-2xl text-[var(--brand-primary)]' : 'icon-arrow-down text-2xl text-[var(--text-muted)]'"
                        role="button"
                        aria-label="Toggle accordion"
                        tabindex="0"
                    ></span>
                </div>
            </template>
        @endisset

        @isset($content)
            <template v-slot:content="{ isOpen }">
                <div
                    {{ $content->attributes->merge(['class' => 'z-10 rounded-xl bg-[var(--bg-surface)] p-3 text-[var(--text-secondary)]']) }}
                    v-show="isOpen"
                >
                    {{ $content }}
                </div>
            </template>
        @endisset
    </v-accordion>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-accordion-template"
    >
        <div>
            <slot
                name="header"
                :toggle="toggle"
                :isOpen="isOpen"
            >
                @lang('portal::app.components.accordion.default-content')
            </slot>

            <slot
                name="content"
                :isOpen="isOpen"
            >
                @lang('portal::app.components.accordion.default-content')
            </slot>
        </div>
    </script>

    <script type="module">
        app.component('v-accordion', {
            template: '#v-accordion-template',

            props: [
                'isActive',
            ],

            data() {
                return {
                    isOpen: this.isActive,
                };
            },

            methods: {
                toggle() {
                    this.isOpen = ! this.isOpen;

                    this.$emit('toggle', { isActive: this.isOpen });
                },
            },
        });
    </script>
@endPushOnce
