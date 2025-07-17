<!DOCTYPE html>
<html lang="en" x-data="artisanUI()" x-init="init()">
<head>
    <meta charset="UTF-8" />
    <title>Artisan Compass</title>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#2563eb',
                            700: '#1d4ed8',
                        },
                        success: {
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <i class="fas fa-terminal text-primary-600"></i>
                    Artisan Compass
                </h1>
                <p class="text-gray-500 mt-1">Run Artisan commands from a beautiful web interface</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-sm">
                    Laravel {{ app()->version() }}
                </span>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input
                        type="text"
                        x-model="search"
                        placeholder="Search Artisan commands..."
                        class="pl-10 w-full p-3 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        @keyup.debounce="filterCommands()"
                    />
                </div>
                <div class="flex gap-2">
                    <button
                        @click="showAllCommands()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition"
                    >
                        All
                    </button>
                    <button
                        @click="filterByCategory('make')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition"
                    >
                        Generators
                    </button>
                    <button
                        @click="filterByCategory('migrate')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition"
                    >
                        Migrations
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="text-gray-500 text-sm">Total Commands</div>
                <div class="text-2xl font-bold" x-text="Object.keys(commands).length"></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="text-gray-500 text-sm">Available Now</div>
                <div class="text-2xl font-bold" x-text="filteredCommands.length"></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <div class="text-gray-500 text-sm">Recently Used</div>
                <div class="text-2xl font-bold" x-text="recentCommands.length"></div>
            </div>
        </div>

        <!-- Commands Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="commandName in filteredCommands" :key="commandName">
                <div
                    class="bg-white rounded-xl shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md"
                    :class="{ 'ring-2 ring-primary-500': currentCommand === commandName }"
                >
                    <div class="p-5">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-mono text-primary-600 font-medium">
                                    php artisan <span x-text="commandName"></span>
                                </div>
                                <div class="text-gray-600 text-sm mt-1" x-text="commands[commandName].description || 'No description available'"></div>
                            </div>
                            <button
                                @click="toggleCommand(commandName)"
                                class="text-gray-400 hover:text-primary-600 transition"
                                :class="{ 'text-primary-600': currentCommand === commandName }"
                            >
                                <i class="fas" :class="currentCommand === commandName ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                        </div>

                        <div class="mt-3 flex gap-2">
                            <button
                                @click="showCommand(commandName)"
                                class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm rounded-lg flex items-center gap-1 transition"
                            >
                                <i class="fas fa-play"></i> Run
                            </button>
                            <button
                                @click="copyCommand(commandName)"
                                class="px-3 py-1.5 border hover:bg-gray-50 text-sm rounded-lg flex items-center gap-1 transition"
                            >
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Command Form -->
                    <div
                        x-show="currentCommand === commandName"
                        x-transition
                        class="border-t p-5 bg-gray-50"
                    >
                        <form @submit.prevent="submitCommand(commandName)" class="space-y-4">
                            <!-- Arguments -->
                            <template x-if="commands[commandName].arguments.length > 0">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                        <i class="fas fa-pencil-alt text-gray-400"></i>
                                        Arguments
                                    </h3>
                                    <div class="space-y-3">
                                        <template x-for="arg in commands[commandName].arguments" :key="arg.name">
                                            <div>
                                                <label class="block text-sm text-gray-800 mb-1">
                                                    <span x-text="arg.name"></span>
                                                    <span x-show="arg.required" class="text-red-500">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    x-model="formInputs[commandName].arguments[arg.name]"
                                                    :placeholder="arg.description || arg.name"
                                                    class="w-full border px-3 py-2 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                                    :required="arg.required"
                                                />
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Options -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                    <i class="fas fa-cog text-gray-400"></i>
                                    Options
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <template x-for="opt in commands[commandName].options" :key="opt.name">
                                        <div>
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                                <template x-if="!opt.acceptValue">
                                                    <input
                                                        type="checkbox"
                                                        x-model="formInputs[commandName].options[opt.name]"
                                                        class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring focus:ring-primary-200"
                                                    />
                                                </template>
                                                <template x-if="opt.acceptValue">
                                                    <input
                                                        type="text"
                                                        x-model="formInputs[commandName].options[opt.name]"
                                                        :placeholder="opt.description || opt.name"
                                                        class="w-full border px-3 py-2 rounded-lg text-sm"
                                                    />
                                                </template>
                                                <span x-text="opt.name"></span>
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    class="w-full py-2.5 bg-success-600 hover:bg-success-700 text-white rounded-lg font-medium flex items-center justify-center gap-2 transition"
                                >
                                    <i class="fas fa-play" x-show="!loading[commandName]"></i>
                                    <i class="fas fa-spinner fa-spin" x-show="loading[commandName]"></i>
                                    Execute Command
                                </button>
                            </div>
                        </form>

                        <!-- Command Output -->
                        <div
                            x-show="outputs[commandName]"
                            x-transition
                            class="mt-4"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-terminal text-gray-400"></i>
                                    Output
                                </h4>
                                <button
                                    @click="copyOutput(commandName)"
                                    class="text-xs text-primary-600 hover:text-primary-800 flex items-center gap-1"
                                >
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <pre
                                x-text="outputs[commandName]"
                                class="bg-gray-800 text-gray-100 p-3 rounded-lg text-xs font-mono whitespace-pre-wrap overflow-auto max-h-64"
                            ></pre>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div
            x-show="filteredCommands.length === 0"
            class="bg-white rounded-xl shadow-sm p-8 text-center"
        >
            <div class="text-gray-400 mb-4">
                <i class="fas fa-search fa-3x"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-700">No commands found</h3>
            <p class="text-gray-500 mt-1">Try adjusting your search or filter</p>
            <button
                @click="showAllCommands()"
                class="mt-4 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg"
            >
                Show All Commands
            </button>
        </div>
    </div>

    <script>
        function artisanUI() {
            return {
                search: '',
                currentFilter: 'all',
                commands: @json($commands),
                filteredCommands: Object.keys(@json($commands)),
                recentCommands: [],
                currentCommand: null,
                formInputs: {},
                outputs: {},
                loading: {},

                init() {
                    // Initialize form inputs for all commands
                    Object.keys(this.commands).forEach(command => {
                        this.formInputs[command] = {
                            arguments: {},
                            options: {}
                        };
                        this.loading[command] = false;
                    });

                    // Load recent commands from localStorage
                    this.recentCommands = JSON.parse(localStorage.getItem('artisanCompassRecentCommands')) || [];
                },

                showCommand(commandName) {
                    this.currentCommand = this.currentCommand === commandName ? null : commandName;

                    // Add to recent commands
                    if (!this.recentCommands.includes(commandName)) {
                        this.recentCommands.unshift(commandName);
                        if (this.recentCommands.length > 5) {
                            this.recentCommands.pop();
                        }
                        localStorage.setItem('artisanCompassRecentCommands', JSON.stringify(this.recentCommands));
                    }
                },

                filterCommands() {
                    const searchTerm = this.search.toLowerCase();
                    this.filteredCommands = Object.keys(this.commands).filter(command => {
                        const matchesSearch = command.toLowerCase().includes(searchTerm) ||
                            this.commands[command].description.toLowerCase().includes(searchTerm);

                        if (this.currentFilter === 'all') return matchesSearch;
                        return matchesSearch && command.startsWith(this.currentFilter);
                    });
                },

                showAllCommands() {
                    this.currentFilter = 'all';
                    this.search = '';
                    this.filterCommands();
                },

                filterByCategory(category) {
                    this.currentFilter = category;
                    this.search = '';
                    this.filterCommands();
                },

                toggleCommand(commandName) {
                    this.currentCommand = this.currentCommand === commandName ? null : commandName;
                },

                async submitCommand(commandName) {
                    this.loading[commandName] = true;
                    this.outputs[commandName] = `Running "php artisan ${commandName}"...`;

                    try {
                        const params = {};

                        // Process arguments
                        for (const [key, val] of Object.entries(this.formInputs[commandName].arguments)) {
                            if (val !== null && val !== undefined && val !== '') {
                                params[key] = val;
                            }
                        }

                        // Process options
                        for (const [key, val] of Object.entries(this.formInputs[commandName].options)) {
                            if (typeof val === 'boolean' && val) {
                                params['--' + key] = null;
                            } else if (val !== null && val !== undefined && val !== '') {
                                params['--' + key] = val;
                            }
                        }

                        const response = await fetch('/artisan-compass/run', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                command: commandName,
                                parameters: params
                            }),
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.outputs[commandName] = data.output;
                        } else {
                           this.outputs[commandName] = `Error: ${data.message || 'Failed to execute command'}`;
                        }
                    } catch (error) {
                        this.outputs[commandName] = `Error: ${error.message}`;
                    } finally {
                        this.loading[commandName] = false;
                    }
                },

                copyCommand(commandName) {
                    const commandText = `php artisan ${commandName}`;
                    navigator.clipboard.writeText(commandText).then(() => {
                        this.showToast('Command copied to clipboard');
                    });
                },

                copyOutput(commandName) {
                    navigator.clipboard.writeText(this.outputs[commandName]).then(() => {
                        this.showToast('Output copied to clipboard');
                    });
                },

                showToast(message) {
                    // You could implement a proper toast notification here
                    alert(message); // Simple fallback
                }
            };
        }
    </script>
</body>
</html>
