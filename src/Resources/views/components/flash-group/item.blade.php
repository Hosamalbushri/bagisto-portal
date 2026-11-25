<v-flash-item
    v-for='flash in flashes'
    :key='flash.uid'
    :flash="flash"
    @onRemove="remove($event)"
/>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-flash-item-template"
    >
        <div
            class="flex w-max max-w-[420px] items-center justify-between gap-4 rounded-xl px-4 py-3 shadow-lg backdrop-blur-md transition-all duration-300 border"
            :class="itemClasses[flash.type]"
        >
            <div class="flex items-center gap-3">
                <span
                    class="text-2xl shrink-0"
                    :class="iconClasses[flash.type]"
                    aria-hidden="true"
                ></span>

                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    @{{ flash.message }}
                </p>
            </div>

            <button
                type="button"
                class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                @click="remove"
                aria-label="Close"
            >
                <span class="icon-cancel text-base cursor-pointer" aria-hidden="true"></span>
            </button>
        </div>
    </script>

    <script type="module">
        if (typeof app !== 'undefined' && app.component) {
            app.component('v-flash-item', {
                template: '#v-flash-item-template',

                props: ['flash'],

                data() {
                    return {
                        itemClasses: {
                            success: 'bg-emerald-50 dark:bg-emerald-950/80 border-emerald-200 dark:border-emerald-800 text-emerald-900',
                            error: 'bg-red-50 dark:bg-red-950/80 border-red-200 dark:border-red-800 text-red-900',
                            warning: 'bg-amber-50 dark:bg-amber-950/80 border-amber-200 dark:border-amber-800 text-amber-900',
                            info: 'bg-blue-50 dark:bg-blue-950/80 border-blue-200 dark:border-blue-800 text-blue-900',
                        },

                        iconClasses: {
                            success: 'icon-toast-done text-emerald-600 dark:text-emerald-400',
                            error: 'icon-toast-error text-red-600 dark:text-red-400',
                            warning: 'icon-toast-exclamation-mark text-amber-600 dark:text-amber-400',
                            info: 'icon-toast-info text-blue-600 dark:text-blue-400',
                        },
                    };
                },

                mounted() {
                    const self = this;
                    setTimeout(function() {
                        self.remove();
                    }, 4000);
                },

                methods: {
                    remove() {
                        this.$emit('onRemove', this.flash);
                    }
                }
            });
        }
    </script>
@endpushOnce
