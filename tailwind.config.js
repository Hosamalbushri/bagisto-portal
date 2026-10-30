/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',

    content: ["./src/Resources/**/*.blade.php", "./src/Resources/**/*.js"],

    theme: {
        container: {
            center: true,

            screens: {
                "2xl": "1440px",
            },

            padding: {
                DEFAULT: "90px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            1180: "1180px",
            1060: "1060px",
            991: "991px",
            868: "868px",
        },

        extend: {
            colors: {
                navyBlue: "#060C3B",
                lightOrange: "#F6F2EB",
                darkGreen: '#40994A',
                darkBlue: '#0044F2',
                darkPink: '#F85156',

                // Roa Soft Design System Tokens
                brand: {
                    primary: 'var(--brand-primary, #1c66f6)',
                    'primary-hover': 'var(--brand-primary-hover, #1551c6)',
                    'primary-light': 'var(--brand-primary-light, #e0edff)',
                    accent: 'var(--brand-accent, #fe8312)',
                    'accent-hover': 'var(--brand-accent-hover, #e0700a)',
                    'accent-light': 'var(--brand-accent-light, #fff3e6)',
                },
                roa: {
                    blue: '#1c66f6',
                    'blue-hover': '#1551c6',
                    'blue-light': '#e0edff',
                    orange: '#fe8312',
                    'orange-hover': '#e0700a',
                    'orange-light': '#fff3e6',
                    dark: '#080b11',
                    'dark-surface': '#0f141f',
                    'dark-elevated': '#161e2e',
                    'dark-muted': '#1f2a3f',
                },
                surface: {
                    app: 'var(--bg-app, #f8fafc)',
                    DEFAULT: 'var(--bg-surface, #ffffff)',
                    elevated: 'var(--bg-surface-elevated, #f1f5f9)',
                    muted: 'var(--bg-surface-muted, #e2e8f0)',
                },
                content: {
                    primary: 'var(--text-primary, #090d16)',
                    secondary: 'var(--text-secondary, #475569)',
                    muted: 'var(--text-muted, #64748b)',
                    inverse: 'var(--text-inverse, #ffffff)',
                },

                // Semantic Status Tokens
                semantic: {
                    success: 'var(--color-success, #10b981)',
                    'success-bg': 'var(--color-success-bg, rgba(16, 185, 129, 0.12))',
                    'success-border': 'var(--color-success-border, rgba(16, 185, 129, 0.25))',
                    warning: 'var(--color-warning, #f59e0b)',
                    'warning-bg': 'var(--color-warning-bg, rgba(245, 158, 11, 0.12))',
                    'warning-border': 'var(--color-warning-border, rgba(245, 158, 11, 0.25))',
                    danger: 'var(--color-danger, #ef4444)',
                    'danger-bg': 'var(--color-danger-bg, rgba(239, 68, 68, 0.12))',
                    'danger-border': 'var(--color-danger-border, rgba(239, 68, 68, 0.25))',
                    info: 'var(--color-info, #1c66f6)',
                    'info-bg': 'var(--color-info-bg, rgba(28, 102, 246, 0.12))',
                    'info-border': 'var(--color-info-border, rgba(28, 102, 246, 0.25))',
                },
            },

            boxShadow: {
                card: 'var(--shadow-card)',
                glow: 'var(--shadow-glow)',
                'glow-accent': 'var(--shadow-glow-accent)',
                glass: '0 8px 32px 0 rgba(0, 0, 0, 0.12)',
            },

            fontFamily: {
                poppins: ["Poppins", "sans-serif"],
                dmserif: ["DM Serif Display", "serif"],
                cairo: ["Cairo", "system-ui", "-apple-system", "sans-serif"],
                inter: ["Inter", "system-ui", "-apple-system", "sans-serif"],
                mono: ["JetBrains Mono", "monospace"],
            },
        }
    },

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,
        }
    ]
};
