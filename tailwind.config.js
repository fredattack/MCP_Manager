/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.{js,jsx,ts,tsx}",
    "./resources/**/*.blade.php",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
  ],
  darkMode: 'class', // Tailwind 4's improved dark mode
  theme: {
    extend: {
      fontFamily: {
        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', 'sans-serif'],
        // Monologue Design System fonts
        'monologue-serif': ['"Instrument Serif"', '"Instrument Serif Placeholder"', 'serif'],
        'monologue-mono': ['"DM Mono"', 'monospace'],
        'monologue-geist': ['"Geist"', '"Geist Placeholder"', 'sans-serif'],
      },
      colors: {
        // Atlassian color palette
        primary: {
          DEFAULT: '#0052CC',
          50: '#E6F0FF',
          100: '#B3D4FF',
          200: '#80B8FF',
          300: '#4D9CFF',
          400: '#1A80FF',
          500: '#0052CC',
          600: '#0041A3',
          700: '#00317A',
          800: '#002152',
          900: '#001029',
          foreground: '#FFFFFF',
        },
        // Monologue Design System colors
        monologue: {
          brand: {
            primary: '#19d0e8',
            accent: '#44ccff',
            success: '#a6ee98',
          },
          neutral: {
            black: '#000000',
            900: '#010101',
            800: '#141414',
            700: '#282828',
            600: '#3f3f3f',
            500: '#545454',
            100: '#fbfaf7',
            white: '#ffffff',
          },
          border: {
            muted: '#282828',      // Subtle border (neutral-700)
            default: '#545454',    // Standard border (neutral-500)
            strong: '#808080',     // High contrast border - BEAUCOUP plus visible!
            accent: '#19d0e8',     // Cyan accent border
          },
          text: {
            primary: '#ffffff',
            secondary: '#a1a1a1',
            muted: '#6b6b6b',
          },
        },
        success: {
          DEFAULT: '#00875A',
          light: '#57D9A3',
          dark: '#00633E',
          foreground: '#FFFFFF',
        },
        warning: {
          DEFAULT: '#FF991F',
          light: '#FFE380',
          dark: '#CC7A00',
          foreground: '#FFFFFF',
        },
        danger: {
          DEFAULT: '#DE350B',
          light: '#FF8F73',
          dark: '#BF2600',
          foreground: '#FFFFFF',
        },
        // Preserve existing shadcn colors
        border: "var(--color-border)",
        input: "var(--color-input)",
        ring: "var(--color-ring)",
        background: "var(--color-background)",
        foreground: "var(--color-foreground)",
        secondary: {
          DEFAULT: "var(--color-secondary)",
          foreground: "var(--color-secondary-foreground)",
        },
        destructive: {
          DEFAULT: "var(--color-destructive)",
          foreground: "var(--color-destructive-foreground)",
        },
        muted: {
          DEFAULT: "var(--color-muted)",
          foreground: "var(--color-muted-foreground)",
        },
        accent: {
          DEFAULT: "var(--color-accent)",
          foreground: "var(--color-accent-foreground)",
        },
        popover: {
          DEFAULT: "var(--color-popover)",
          foreground: "var(--color-popover-foreground)",
        },
        card: {
          DEFAULT: "var(--color-card)",
          foreground: "var(--color-card-foreground)",
        },
        sidebar: {
          DEFAULT: "var(--color-sidebar)",
          foreground: "var(--color-sidebar-foreground)",
          primary: "var(--color-sidebar-primary)",
          "primary-foreground": "var(--color-sidebar-primary-foreground)",
          accent: "var(--color-sidebar-accent)",
          "accent-foreground": "var(--color-sidebar-accent-foreground)",
          border: "var(--color-sidebar-border)",
          ring: "var(--color-sidebar-ring)",
        },
        chart: {
          1: "var(--color-chart-1)",
          2: "var(--color-chart-2)",
          3: "var(--color-chart-3)",
          4: "var(--color-chart-4)",
          5: "var(--color-chart-5)",
        },
      },
      borderRadius: {
        lg: "var(--radius-lg)",
        md: "var(--radius-md)",
        sm: "var(--radius-sm)",
      },
      boxShadow: {
        'atlassian': '0 1px 1px rgba(9,30,66,0.25), 0 0 0 1px rgba(9,30,66,0.08)',
        'atlassian-lg': '0 8px 16px -4px rgba(9,30,66,0.25), 0 0 0 1px rgba(9,30,66,0.08)',
      },
      opacity: {
        10: '0.1',
        12: '0.12',
        36: '0.36',
        48: '0.48',
        64: '0.64',
      },
      transitionDuration: {
        fast: '200ms',
      },
      transitionTimingFunction: {
        smooth: 'cubic-bezier(0.44, 0, 0.56, 1)',
      },
      animation: {
        'slide-in': 'slideIn 0.2s ease-out',
        'fade-in': 'fadeIn 0.2s ease-out',
      },
      keyframes: {
        slideIn: {
          '0%': { transform: 'translateX(-100%)', opacity: 0 },
          '100%': { transform: 'translateX(0)', opacity: 1 },
        },
        fadeIn: {
          '0%': { opacity: 0 },
          '100%': { opacity: 1 },
        },
      },
    },
  },
  plugins: [
    require("tailwindcss-animate"),
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
