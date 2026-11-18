<v-flash-group ref="flashes"></v-flash-group>

@pushOnce('scripts')
    @php
        $flashes = [];

        foreach (['success', 'warning', 'error', 'info'] as $type) {
            if (session()->has($type)) {
                $flashes[] = [
                    'type' => $type,
                    'message' => session($type),
                ];
            }
        }
    @endphp

    <script
        type="text/x-template"
        id="v-flash-group-template"
    >
        <transition-group
            tag="div"
            name="flash-group"
            enter-from-class="ltr:translate-x-full rtl:-translate-x-full opacity-0"
            enter-active-class="transform transition duration-300 ease-out"
            enter-to-class="ltr:translate-x-0 rtl:-translate-x-0 opacity-100"
            leave-from-class="ltr:translate-x-0 rtl:-translate-x-0 opacity-100"
            leave-active-class="transform transition duration-200 ease-in"
            leave-to-class="ltr:translate-x-full rtl:-translate-x-full opacity-0"
            class="fixed top-5 z-[9999] grid justify-items-end gap-3 max-sm:hidden ltr:right-5 rtl:left-5"
        >
            <x-portal::flash-group.item />
        </transition-group>

        <transition-group
            tag="div"
            name="flash-group"
            enter-from-class="translate-y-full opacity-0"
            enter-active-class="transform transition duration-300 ease-out"
            enter-to-class="translate-y-0 opacity-100"
            leave-from-class="translate-y-0 opacity-100"
            leave-active-class="transform transition duration-200 ease-in"
            leave-to-class="translate-y-full opacity-0"
            class="fixed bottom-6 left-1/2 z-[9999] grid -translate-x-1/2 transform justify-items-center gap-3 sm:hidden"
        >
            <x-portal::flash-group.item />
        </transition-group>
    </script>

    <script type="module">
        if (typeof app !== 'undefined' && app.component) {
            app.component('v-flash-group', {
                template: '#v-flash-group-template',

                data() {
                    return {
                        uid: 0,
                        flashes: [],
                    };
                },

                created() {
                    this.loadInitialFlashes();
                    this.registerGlobalEvents();
                },

                methods: {
                    loadInitialFlashes() {
                        const initialFlashes = @json($flashes);

                        if (Array.isArray(initialFlashes)) {
                            initialFlashes.forEach(flash => {
                                flash.uid = this.uid++;
                                this.flashes.push(flash);
                            });
                        }
                    },

                    add(flash) {
                        flash.uid = this.uid++;
                        this.flashes.push(flash);
                    },

                    remove(flash) {
                        const index = this.flashes.indexOf(flash);
                        if (index > -1) {
                            this.flashes.splice(index, 1);
                        }
                    },

                    registerGlobalEvents() {
                        if (this.$emitter) {
                            this.$emitter.on('add-flash', this.add);
                        }
                    },
                }
            });
        }
    </script>
@endpushOnce
