// Tailwind untuk halaman publik — dikompilasi menjadi assets/css/site.css (tidak lagi lewat CDN di browser).
// Setelah menambah/mengubah class Tailwind di file PHP, build ulang:
//   npm run build:css
module.exports = {
  content: ['./*.php', './includes/**/*.php'],
  theme: {
    extend: {
      fontFamily: {
        'playfair': ['"Libre Caslon Text"', 'Georgia', 'serif'],
        'jakarta': ['Arial', 'Helvetica', 'sans-serif'],
      },
      colors: {
        'brand': { DEFAULT: '#860524', dark: '#6a041c' },
        'dark': '#242424',
        'muted': '#686561',
        'cream': '#faf8f5',
        'cream-dark': '#f4f1ea',
        'border': '#ece8e1',
      },
      letterSpacing: {
        'widest-2': '1.65px',
        'widest-3': '1.8px',
        'widest-4': '2.64px',
      },
    },
  },
};
