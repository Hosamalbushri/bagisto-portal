<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Design Token Categories & Definitions
    |--------------------------------------------------------------------------
    |
    | Defines the structural design token registry. Each token has a category,
    | label, description, type, default light value, and default dark value.
    |
    */
    'tokens' => [
        'brand' => [
            '--brand-primary' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.brand-primary',
                'description' => 'portal::app.admin.tokens.brand-primary-desc',
                'default' => '#1c66f6',
                'dark' => '#1c66f6',
            ],
            '--brand-primary-hover' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.brand-primary-hover',
                'description' => 'portal::app.admin.tokens.brand-primary-hover-desc',
                'default' => '#1551c6',
                'dark' => '#3b82f6',
            ],
            '--brand-primary-light' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.brand-primary-light',
                'description' => 'portal::app.admin.tokens.brand-primary-light-desc',
                'default' => '#e0edff',
                'dark' => 'rgba(28, 102, 246, 0.15)',
            ],
            '--brand-accent' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.brand-accent',
                'description' => 'portal::app.admin.tokens.brand-accent-desc',
                'default' => '#fe8312',
                'dark' => '#fe8312',
            ],
            '--brand-accent-hover' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.brand-accent-hover',
                'description' => 'portal::app.admin.tokens.brand-accent-hover-desc',
                'default' => '#e0700a',
                'dark' => '#ff9431',
            ],
            '--brand-accent-light' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.brand-accent-light',
                'description' => 'portal::app.admin.tokens.brand-accent-light-desc',
                'default' => '#fff3e6',
                'dark' => 'rgba(254, 131, 18, 0.15)',
            ],
        ],

        'surfaces' => [
            '--bg-app' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.bg-app',
                'description' => 'portal::app.admin.tokens.bg-app-desc',
                'default' => '#f8fafc',
                'dark' => '#080b11',
            ],
            '--bg-surface' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.bg-surface',
                'description' => 'portal::app.admin.tokens.bg-surface-desc',
                'default' => '#ffffff',
                'dark' => '#0f141f',
            ],
            '--bg-surface-elevated' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.bg-surface-elevated',
                'description' => 'portal::app.admin.tokens.bg-surface-elevated-desc',
                'default' => '#f1f5f9',
                'dark' => '#161e2e',
            ],
            '--bg-surface-muted' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.bg-surface-muted',
                'description' => 'portal::app.admin.tokens.bg-surface-muted-desc',
                'default' => '#e2e8f0',
                'dark' => '#1f2a3f',
            ],
        ],

        'typography' => [
            '--text-primary' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.text-primary',
                'description' => 'portal::app.admin.tokens.text-primary-desc',
                'default' => '#090d16',
                'dark' => '#f8fafc',
            ],
            '--text-secondary' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.text-secondary',
                'description' => 'portal::app.admin.tokens.text-secondary-desc',
                'default' => '#475569',
                'dark' => '#94a3b8',
            ],
            '--text-muted' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.text-muted',
                'description' => 'portal::app.admin.tokens.text-muted-desc',
                'default' => '#64748b',
                'dark' => '#64748b',
            ],
            '--font-arabic' => [
                'type' => 'font',
                'label' => 'portal::app.admin.tokens.font-arabic',
                'description' => 'portal::app.admin.tokens.font-arabic-desc',
                'default' => "'Cairo', system-ui, -apple-system, sans-serif",
                'dark' => "'Cairo', system-ui, -apple-system, sans-serif",
            ],
            '--font-latin' => [
                'type' => 'font',
                'label' => 'portal::app.admin.tokens.font-latin',
                'description' => 'portal::app.admin.tokens.font-latin-desc',
                'default' => "'Inter', system-ui, -apple-system, sans-serif",
                'dark' => "'Inter', system-ui, -apple-system, sans-serif",
            ],
            '--font-mono' => [
                'type' => 'font',
                'label' => 'portal::app.admin.tokens.font-mono',
                'description' => 'portal::app.admin.tokens.font-mono-desc',
                'default' => "'JetBrains Mono', monospace",
                'dark' => "'JetBrains Mono', monospace",
            ],
        ],

        'borders' => [
            '--border-subtle' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.border-subtle',
                'description' => 'portal::app.admin.tokens.border-subtle-desc',
                'default' => '#e2e8f0',
                'dark' => 'rgba(255, 255, 255, 0.08)',
            ],
            '--border-strong' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.border-strong',
                'description' => 'portal::app.admin.tokens.border-strong-desc',
                'default' => '#cbd5e1',
                'dark' => 'rgba(255, 255, 255, 0.16)',
            ],
            '--border-focus' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.border-focus',
                'description' => 'portal::app.admin.tokens.border-focus-desc',
                'default' => '#1c66f6',
                'dark' => '#3b82f6',
            ],
        ],

        'radii' => [
            '--radius-base' => [
                'type' => 'dimension',
                'label' => 'portal::app.admin.tokens.radius-base',
                'description' => 'portal::app.admin.tokens.radius-base-desc',
                'default' => '0.75rem',
                'dark' => '0.75rem',
            ],
        ],

        'semantics' => [
            '--color-success' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.color-success',
                'default' => '#10b981',
                'dark' => '#34d399',
            ],
            '--color-warning' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.color-warning',
                'default' => '#f59e0b',
                'dark' => '#fbbf24',
            ],
            '--color-danger' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.color-danger',
                'default' => '#ef4444',
                'dark' => '#f87171',
            ],
            '--color-info' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.color-info',
                'default' => '#1c66f6',
                'dark' => '#60a5fa',
            ],
        ],

        'components' => [
            '--header-bg' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.header-bg',
                'default' => '#ffffff',
                'dark' => '#0f141f',
            ],
            '--header-text' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.header-text',
                'default' => '#090d16',
                'dark' => '#f8fafc',
            ],
            '--header-height' => [
                'type' => 'dimension',
                'label' => 'portal::app.admin.tokens.header-height',
                'default' => '4.5rem',
                'dark' => '4.5rem',
            ],
            '--footer-bg' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.footer-bg',
                'default' => '#0f172a',
                'dark' => '#080b11',
            ],
            '--footer-text' => [
                'type' => 'color',
                'label' => 'portal::app.admin.tokens.footer-text',
                'default' => '#94a3b8',
                'dark' => '#94a3b8',
            ],
            '--btn-radius' => [
                'type' => 'dimension',
                'label' => 'portal::app.admin.tokens.btn-radius',
                'default' => '0.5rem',
                'dark' => '0.5rem',
            ],
            '--card-radius' => [
                'type' => 'dimension',
                'label' => 'portal::app.admin.tokens.card-radius',
                'default' => '1rem',
                'dark' => '1rem',
            ],
        ],

        'shadows' => [
            '--shadow-card' => [
                'type' => 'shadow',
                'label' => 'portal::app.admin.tokens.shadow-card',
                'default' => '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                'dark' => '0 10px 30px -5px rgba(0, 0, 0, 0.5)',
            ],
            '--shadow-glow' => [
                'type' => 'shadow',
                'label' => 'portal::app.admin.tokens.shadow-glow',
                'default' => '0 0 25px rgba(28, 102, 246, 0.25)',
                'dark' => '0 0 35px rgba(28, 102, 246, 0.4)',
            ],
        ],

        'layout' => [
            '--container-max-width' => [
                'type' => 'dimension',
                'label' => 'portal::app.admin.tokens.container-max-width',
                'description' => 'portal::app.admin.tokens.container-max-width-desc',
                'default' => '1280px',
                'dark' => '1280px',
            ],
            '--page-padding-x' => [
                'type' => 'dimension',
                'label' => 'portal::app.admin.tokens.page-padding-x',
                'description' => 'portal::app.admin.tokens.page-padding-x-desc',
                'default' => '1rem',
                'dark' => '1rem',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Curated Fonts Catalog (Arabic & Latin)
    |--------------------------------------------------------------------------
    */
    'fonts' => [
        'arabic' => [
            'Cairo' => [
                'name' => 'Cairo',
                'family' => "'Cairo', system-ui, sans-serif",
                'weights' => '400;500;600;700;800;900',
            ],
            'Tajawal' => [
                'name' => 'Tajawal',
                'family' => "'Tajawal', system-ui, sans-serif",
                'weights' => '400;500;700;800',
            ],
            'Almarai' => [
                'name' => 'Almarai',
                'family' => "'Almarai', system-ui, sans-serif",
                'weights' => '400;700;800',
            ],
            'Readex Pro' => [
                'name' => 'Readex Pro',
                'family' => "'Readex Pro', system-ui, sans-serif",
                'weights' => '400;500;600;700',
            ],
            'Alexandria' => [
                'name' => 'Alexandria',
                'family' => "'Alexandria', system-ui, sans-serif",
                'weights' => '400;500;600;700',
            ],
            'IBM Plex Sans Arabic' => [
                'name' => 'IBM Plex Sans Arabic',
                'family' => "'IBM Plex Sans Arabic', system-ui, sans-serif",
                'weights' => '400;500;600;700',
            ],
        ],
        'latin' => [
            'Inter' => [
                'name' => 'Inter',
                'family' => "'Inter', system-ui, sans-serif",
                'weights' => '400;500;600;700;800',
            ],
            'Plus Jakarta Sans' => [
                'name' => 'Plus Jakarta Sans',
                'family' => "'Plus Jakarta Sans', system-ui, sans-serif",
                'weights' => '400;500;600;700;800',
            ],
            'Outfit' => [
                'name' => 'Outfit',
                'family' => "'Outfit', system-ui, sans-serif",
                'weights' => '400;500;600;700;800',
            ],
            'Poppins' => [
                'name' => 'Poppins',
                'family' => "'Poppins', system-ui, sans-serif",
                'weights' => '400;500;600;700;800',
            ],
            'Roboto' => [
                'name' => 'Roboto',
                'family' => "'Roboto', system-ui, sans-serif",
                'weights' => '400;500;700;900',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Built-in Design Presets (Curated Themes)
    |--------------------------------------------------------------------------
    */
    'presets' => [
        'default' => [
            'name' => 'portal::app.admin.tokens.presets.default',
            'description' => 'portal::app.admin.tokens.presets.default-desc',
            'preview' => ['#1c66f6', '#fe8312', '#080b11'],
            'tokens' => [
                '--brand-primary' => '#1c66f6',
                '--brand-primary-hover' => '#1551c6',
                '--brand-primary-light' => '#e0edff',
                '--brand-accent' => '#fe8312',
                '--brand-accent-hover' => '#e0700a',
                '--brand-accent-light' => '#fff3e6',
                '--bg-app' => '#f8fafc',
                '--bg-surface' => '#ffffff',
                '--bg-surface-elevated' => '#f1f5f9',
                '--bg-surface-muted' => '#e2e8f0',
                '--text-primary' => '#090d16',
                '--text-secondary' => '#475569',
                '--border-subtle' => '#e2e8f0',
                '--radius-base' => '0.75rem',
                '--font-arabic' => "'Cairo', system-ui, sans-serif",
                '--font-latin' => "'Inter', system-ui, sans-serif",
            ],
            'dark' => [
                '--bg-app' => '#080b11',
                '--bg-surface' => '#0f141f',
                '--bg-surface-elevated' => '#161e2e',
                '--bg-surface-muted' => '#1f2a3f',
                '--text-primary' => '#f8fafc',
                '--text-secondary' => '#94a3b8',
                '--border-subtle' => 'rgba(255, 255, 255, 0.08)',
            ],
        ],

        'emerald_tech' => [
            'name' => 'portal::app.admin.tokens.presets.emerald-tech',
            'description' => 'portal::app.admin.tokens.presets.emerald-tech-desc',
            'preview' => ['#059669', '#06b6d4', '#061412'],
            'tokens' => [
                '--brand-primary' => '#059669',
                '--brand-primary-hover' => '#047857',
                '--brand-primary-light' => '#d1fae5',
                '--brand-accent' => '#06b6d4',
                '--brand-accent-hover' => '#0891b2',
                '--brand-accent-light' => '#cffafe',
                '--bg-app' => '#f0fdf4',
                '--bg-surface' => '#ffffff',
                '--bg-surface-elevated' => '#e6f7ec',
                '--bg-surface-muted' => '#dcfce7',
                '--text-primary' => '#06281e',
                '--text-secondary' => '#335c4f',
                '--border-subtle' => '#d1fae5',
                '--radius-base' => '0.625rem',
                '--font-arabic' => "'Alexandria', 'Cairo', sans-serif",
                '--font-latin' => "'Plus Jakarta Sans', sans-serif",
            ],
            'dark' => [
                '--bg-app' => '#040d0a',
                '--bg-surface' => '#071712',
                '--bg-surface-elevated' => '#0d241d',
                '--bg-surface-muted' => '#133329',
                '--text-primary' => '#ecfdf5',
                '--text-secondary' => '#a7f3d0',
                '--border-subtle' => 'rgba(5, 150, 105, 0.15)',
            ],
        ],

        'minimal_slate' => [
            'name' => 'portal::app.admin.tokens.presets.minimal-slate',
            'description' => 'portal::app.admin.tokens.presets.minimal-slate-desc',
            'preview' => ['#0f172a', '#64748b', '#020617'],
            'tokens' => [
                '--brand-primary' => '#0f172a',
                '--brand-primary-hover' => '#1e293b',
                '--brand-primary-light' => '#f1f5f9',
                '--brand-accent' => '#64748b',
                '--brand-accent-hover' => '#475569',
                '--brand-accent-light' => '#f8fafc',
                '--bg-app' => '#f8fafc',
                '--bg-surface' => '#ffffff',
                '--bg-surface-elevated' => '#f1f5f9',
                '--bg-surface-muted' => '#e2e8f0',
                '--text-primary' => '#020617',
                '--text-secondary' => '#475569',
                '--border-subtle' => '#e2e8f0',
                '--radius-base' => '0.25rem',
                '--font-arabic' => "'IBM Plex Sans Arabic', sans-serif",
                '--font-latin' => "'Inter', sans-serif",
            ],
            'dark' => [
                '--bg-app' => '#020617',
                '--bg-surface' => '#090d16',
                '--bg-surface-elevated' => '#0f172a',
                '--bg-surface-muted' => '#1e293b',
                '--text-primary' => '#f8fafc',
                '--text-secondary' => '#94a3b8',
                '--border-subtle' => 'rgba(255, 255, 255, 0.1)',
            ],
        ],

        'cyber_violet' => [
            'name' => 'portal::app.admin.tokens.presets.cyber-violet',
            'description' => 'portal::app.admin.tokens.presets.cyber-violet-desc',
            'preview' => ['#7c3aed', '#ec4899', '#0d0b18'],
            'tokens' => [
                '--brand-primary' => '#7c3aed',
                '--brand-primary-hover' => '#6d28d9',
                '--brand-primary-light' => '#ede9fe',
                '--brand-accent' => '#ec4899',
                '--brand-accent-hover' => '#db2777',
                '--brand-accent-light' => '#fce7f3',
                '--bg-app' => '#faf5ff',
                '--bg-surface' => '#ffffff',
                '--bg-surface-elevated' => '#f3e8ff',
                '--bg-surface-muted' => '#e9d5ff',
                '--text-primary' => '#2e1065',
                '--text-secondary' => '#581c87',
                '--border-subtle' => '#e9d5ff',
                '--radius-base' => '1rem',
                '--font-arabic' => "'Cairo', sans-serif",
                '--font-latin' => "'Poppins', sans-serif",
            ],
            'dark' => [
                '--bg-app' => '#090614',
                '--bg-surface' => '#110b24',
                '--bg-surface-elevated' => '#1b1238',
                '--bg-surface-muted' => '#27194f',
                '--text-primary' => '#faf5ff',
                '--text-secondary' => '#d8b4fe',
                '--border-subtle' => 'rgba(124, 58, 237, 0.2)',
            ],
        ],

        'royal_gold' => [
            'name' => 'portal::app.admin.tokens.presets.royal-gold',
            'description' => 'portal::app.admin.tokens.presets.royal-gold-desc',
            'preview' => ['#18181b', '#d97706', '#09090b'],
            'tokens' => [
                '--brand-primary' => '#18181b',
                '--brand-primary-hover' => '#27272a',
                '--brand-primary-light' => '#f4f4f5',
                '--brand-accent' => '#d97706',
                '--brand-accent-hover' => '#b45309',
                '--brand-accent-light' => '#fef3c7',
                '--bg-app' => '#fafaf9',
                '--bg-surface' => '#ffffff',
                '--bg-surface-elevated' => '#f5f5f4',
                '--bg-surface-muted' => '#e7e5e4',
                '--text-primary' => '#1c1917',
                '--text-secondary' => '#57534e',
                '--border-subtle' => '#e7e5e4',
                '--radius-base' => '0.5rem',
                '--font-arabic' => "'Cairo', sans-serif",
                '--font-latin' => "'Inter', sans-serif",
            ],
            'dark' => [
                '--bg-app' => '#09090b',
                '--bg-surface' => '#121215',
                '--bg-surface-elevated' => '#1c1c21',
                '--bg-surface-muted' => '#27272f',
                '--text-primary' => '#fafaf9',
                '--text-secondary' => '#a8a29e',
                '--border-subtle' => 'rgba(217, 119, 6, 0.15)',
            ],
        ],

        'crimson_passion' => [
            'name' => 'portal::app.admin.tokens.presets.crimson-passion',
            'description' => 'portal::app.admin.tokens.presets.crimson-passion-desc',
            'preview' => ['#dc2626', '#f43f5e', '#0f090a'],
            'tokens' => [
                '--brand-primary' => '#dc2626',
                '--brand-primary-hover' => '#b91c1c',
                '--brand-primary-light' => '#fee2e2',
                '--brand-accent' => '#f43f5e',
                '--brand-accent-hover' => '#e11d48',
                '--brand-accent-light' => '#ffe4e6',
                '--bg-app' => '#fff5f5',
                '--bg-surface' => '#ffffff',
                '--bg-surface-elevated' => '#fee2e2',
                '--bg-surface-muted' => '#fecaca',
                '--text-primary' => '#450a0a',
                '--text-secondary' => '#7f1d1d',
                '--border-subtle' => '#fecaca',
                '--radius-base' => '0.875rem',
                '--font-arabic' => "'Cairo', sans-serif",
                '--font-latin' => "'Plus Jakarta Sans', sans-serif",
            ],
            'dark' => [
                '--bg-app' => '#0d0405',
                '--bg-surface' => '#17080a',
                '--bg-surface-elevated' => '#240d10',
                '--bg-surface-muted' => '#361419',
                '--text-primary' => '#fff1f2',
                '--text-secondary' => '#fca5a5',
                '--border-subtle' => 'rgba(220, 38, 38, 0.18)',
            ],
        ],

        'nordic_ocean' => [
            'name' => 'portal::app.admin.tokens.presets.nordic-ocean',
            'description' => 'portal::app.admin.tokens.presets.nordic-ocean-desc',
            'preview' => ['#0284c7', '#14b8a6', '#081018'],
            'tokens' => [
                '--brand-primary' => '#0284c7',
                '--brand-primary-hover' => '#0369a1',
                '--brand-primary-light' => '#e0f2fe',
                '--brand-accent' => '#14b8a6',
                '--brand-accent-hover' => '#0d9488',
                '--brand-accent-light' => '#ccfbf1',
                '--bg-app' => '#f0f9ff',
                '--bg-surface' => '#ffffff',
                '--bg-surface-elevated' => '#e0f2fe',
                '--bg-surface-muted' => '#bae6fd',
                '--text-primary' => '#082f49',
                '--text-secondary' => '#0369a1',
                '--border-subtle' => '#bae6fd',
                '--radius-base' => '1.25rem',
                '--font-arabic' => "'Tajawal', 'Cairo', sans-serif",
                '--font-latin' => "'Inter', sans-serif",
            ],
            'dark' => [
                '--bg-app' => '#040d14',
                '--bg-surface' => '#081622',
                '--bg-surface-elevated' => '#0d2235',
                '--bg-surface-muted' => '#13324d',
                '--text-primary' => '#f0f9ff',
                '--text-secondary' => '#7dd3fc',
                '--border-subtle' => 'rgba(2, 132, 199, 0.18)',
            ],
        ],
    ],
];
