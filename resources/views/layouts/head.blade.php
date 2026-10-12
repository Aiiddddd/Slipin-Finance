<meta name="theme-color" content="#22492a">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" type="image/png" href="/icons/favicon-64.png">
<link rel="apple-touch-icon" href="/icons/icon-192.png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Slipin Coffee">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#f3f7f3',
                        100: '#e4eee5',
                        200: '#c8ddca',
                        300: '#a5c5a8',
                        400: '#80ad85',
                        500: '#4f7f5a',
                        600: '#3d6143',
                        700: '#31603b',
                        800: '#22492a',
                        900: '#1a3a23',
                        950: '#112a18',
                        DEFAULT: '#22492a',
                    },
                },
            },
        },
    };
</script>
<style>
    html { color-scheme: dark; }
    input[type=checkbox] { accent-color: #4f7f5a; }
    ::selection { background: #3d6143; color: #fff; }
</style>
<style type="text/tailwindcss">
    @layer components {
        .card   { @apply rounded-xl border border-neutral-800 bg-neutral-900 p-4; }
        .btn    { @apply inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition disabled:opacity-50; }
        .btn-primary { @apply bg-brand-500 text-white hover:bg-brand-600; }
        .btn-ghost   { @apply border border-neutral-600 text-neutral-100 hover:bg-neutral-700/50; }
        .btn-danger  { @apply border border-red-900 text-red-400 hover:bg-red-950; }
        .input  { @apply w-full rounded-lg border border-neutral-600 bg-neutral-950 px-3 py-2 text-sm text-neutral-100 placeholder-neutral-500 focus:border-neutral-400 focus:outline-none; }
        .label  { @apply mb-1 block text-sm font-medium text-neutral-300; }
        .hint   { @apply mt-1 text-xs text-neutral-500; }
        .th     { @apply whitespace-nowrap px-3 py-2 text-left text-xs font-medium uppercase tracking-wide text-neutral-400; }
        .td     { @apply whitespace-nowrap px-3 py-2 text-sm; }
        .badge  { @apply inline-block rounded-full px-2 py-0.5 text-xs font-medium; }
        .stat   { @apply text-2xl font-semibold tracking-tight; }
        .stat-l { @apply text-xs uppercase tracking-wide text-neutral-400; }
    }
</style>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
</script>
