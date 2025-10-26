/** @type {import('tailwindcss').Config} */

/**
 * Monologue Design System - Tailwind Configuration
 * Extracted from https://www.monologue.to/
 *
 * This configuration extends Tailwind CSS with the Monologue brand design tokens.
 * Import this config in your project or merge it with your existing tailwind.config.js
 */

export default {
  darkMode: 'class', // or 'media' if you prefer system preference
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.jsx',
    './resources/**/*.ts',
    './resources/**/*.tsx',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
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
        // Semantic aliases for easier use
        background: {
          DEFAULT: '#010101',
          secondary: '#141414',
          elevated: '#282828',
          muted: '#545454',
        },
        foreground: {
          DEFAULT: '#ffffff',
          secondary: 'rgba(255, 255, 255, 0.64)',
          muted: 'rgba(255, 255, 255, 0.48)',
          inverse: '#282828',
        },
        border: {
          DEFAULT: 'rgba(255, 255, 255, 0.12)',
          muted: 'rgba(255, 255, 255, 0.1)',
        },
        link: {
          DEFAULT: '#19d0e8',
          hover: '#44ccff',
        },
      },
      fontFamily: {
        serif: ['"Instrument Serif"', '"Instrument Serif Placeholder"', 'serif'],
        mono: ['"DM Mono"', 'monospace'],
        sans: ['system-ui', '-apple-system', '"Segoe UI"', 'Roboto', 'sans-serif'],
        geist: ['"Geist"', '"Geist Placeholder"', 'sans-serif'],
      },
      fontSize: {
        xs: ['12px', { lineHeight: '14.4px', letterSpacing: '0.3px' }],
        sm: ['14px', { lineHeight: '19.6px', letterSpacing: '0.3px' }],
        base: ['16px', { lineHeight: '12.8px', letterSpacing: '0.3px' }],
        lg: ['40px', { lineHeight: '48px', letterSpacing: '0.3px' }],
        xl: ['70.4px', { lineHeight: '84.48px', letterSpacing: '-0.2px' }],
        '2xl': ['296.637px', { lineHeight: '326.301px', letterSpacing: '5.93275px' }],
      },
      spacing: {
        0: '0px',
        1: '10px',
        2: '14px',
        3: '16px',
        4: '18px',
        5: '20px',
        6: '40px',
        7: '154px',
      },
      borderRadius: {
        none: '0px',
        sm: '6px',
        DEFAULT: '6px',
        md: '8px',
      },
      boxShadow: {
        none: 'none',
        // Add custom shadows if needed
      },
      opacity: {
        0: '0',
        10: '0.1',
        12: '0.12',
        36: '0.36',
        48: '0.48',
        64: '0.64',
        100: '1',
      },
      screens: {
        // Mobile-first approach (default)
        'tablet': '810px',     // min-width: 810px
        'desktop': '1200px',   // min-width: 1200px
        'wide': '1440px',      // min-width: 1440px
      },
      transitionDuration: {
        fast: '200ms',
      },
      transitionTimingFunction: {
        smooth: 'cubic-bezier(0.44, 0, 0.56, 1)',
      },
      keyframes: {
        // Add custom animations if needed
      },
      animation: {
        // Add custom animations if needed
      },
    },
  },
  plugins: [
    // @tailwindcss/typography can be added if needed for rich text content
    // require('@tailwindcss/typography'),

    // Custom plugin to add component classes
    function({ addComponents, theme }) {
      addComponents({
        '.btn': {
          padding: `${theme('spacing.2')} ${theme('spacing.3')}`,
          borderRadius: theme('borderRadius.sm'),
          fontSize: theme('fontSize.xs[0]'),
          fontFamily: theme('fontFamily.mono').join(', '),
          transition: `all ${theme('transitionDuration.fast')} ${theme('transitionTimingFunction.smooth')}`,
          cursor: 'pointer',
          display: 'inline-flex',
          alignItems: 'center',
          gap: theme('spacing.1'),
        },
        '.btn-primary': {
          backgroundColor: theme('colors.neutral.white'),
          color: theme('colors.neutral.700'),
          '&:hover': {
            backgroundColor: theme('colors.neutral.100'),
          },
        },
        '.btn-secondary': {
          backgroundColor: 'rgba(255, 255, 255, 0.12)',
          color: theme('colors.neutral.white'),
          '&:hover': {
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
          },
        },
        '.btn-ghost': {
          backgroundColor: 'transparent',
          color: theme('colors.brand.primary'),
          '&:hover': {
            backgroundColor: 'rgba(25, 208, 232, 0.1)',
          },
        },
        '.card': {
          backgroundColor: theme('colors.background.secondary'),
          borderRadius: theme('borderRadius.md'),
          padding: theme('spacing.5'),
          border: `1px solid ${theme('colors.border.muted')}`,
        },
        '.link': {
          color: theme('colors.link.DEFAULT'),
          textDecoration: 'none',
          transition: `color ${theme('transitionDuration.fast')} ${theme('transitionTimingFunction.smooth')}`,
          '&:hover': {
            color: theme('colors.link.hover'),
          },
        },
        '.text-display': {
          fontFamily: theme('fontFamily.serif').join(', '),
          fontSize: theme('fontSize.2xl[0]'),
          lineHeight: theme('fontSize.2xl[1].lineHeight'),
          letterSpacing: theme('fontSize.2xl[1].letterSpacing'),
          fontWeight: '400',
        },
        '.text-h1': {
          fontFamily: theme('fontFamily.serif').join(', '),
          fontSize: theme('fontSize.xl[0]'),
          lineHeight: theme('fontSize.xl[1].lineHeight'),
          letterSpacing: theme('fontSize.xl[1].letterSpacing'),
          fontWeight: '400',
        },
        '.text-h2': {
          fontFamily: theme('fontFamily.serif').join(', '),
          fontSize: theme('fontSize.lg[0]'),
          lineHeight: theme('fontSize.lg[1].lineHeight'),
          letterSpacing: theme('fontSize.lg[1].letterSpacing'),
          fontWeight: '400',
        },
        '.text-body': {
          fontFamily: theme('fontFamily.mono').join(', '),
          fontSize: theme('fontSize.base[0]'),
          lineHeight: theme('fontSize.base[1].lineHeight'),
          letterSpacing: theme('fontSize.base[1].letterSpacing'),
          fontWeight: '400',
        },
        '.text-body-sm': {
          fontFamily: theme('fontFamily.mono').join(', '),
          fontSize: theme('fontSize.sm[0]'),
          lineHeight: theme('fontSize.sm[1].lineHeight'),
          letterSpacing: theme('fontSize.sm[1].letterSpacing'),
          fontWeight: '400',
        },
        '.text-caption': {
          fontFamily: theme('fontFamily.mono').join(', '),
          fontSize: theme('fontSize.xs[0]'),
          lineHeight: theme('fontSize.xs[1].lineHeight'),
          letterSpacing: theme('fontSize.xs[1].letterSpacing'),
          fontWeight: '400',
        },
      });
    },
  ],
};
