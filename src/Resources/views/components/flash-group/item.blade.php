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
                <!-- Success Icon -->
                <svg v-if="flash.type === 'success'" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <!-- Error Icon -->
                <svg v-else-if="flash.type === 'error'" class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <!-- Warning Icon -->
                <svg v-else-if="flash.type === 'warning'" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>

                <!-- Info Icon -->
                <svg v-else class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

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
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
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
