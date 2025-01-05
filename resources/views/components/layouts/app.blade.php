<!DOCTYPE html>
<html lang="ar" dir="rtl" class="fi min-h-screen dark">
<head>

    <meta charset="utf-8"/>
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <title>{{ config('app.name') }}</title>

    <style>
        [x-cloak=''],
        [x-cloak='x-cloak'],
        [x-cloak='1'] {
            display: none !important;
        }

        @media (max-width: 1023px) {
            [x-cloak='-lg'] {
                display: none !important;
            }
        }

        @media (min-width: 1024px) {
            [x-cloak='lg'] {
                display: none !important;
            }
        }
    </style>

    @filamentStyles

    {{ filament()->getTheme()->getHtml() }}
    {{ filament()->getFontHtml() }}

    @vite('resources/css/app.css')

    <style>
        :root {
            --font-family: '{!! filament()->getFontFamily() !!}';
            --sidebar-width: {{ filament()->getSidebarWidth() }};
            --collapsed-sidebar-width: {{ filament()->getCollapsedSidebarWidth() }};
            --default-theme-mode: {{ filament()->getDefaultThemeMode()->value }};
        }
    </style>

    @stack('styles')

    <script>
		const theme = localStorage.getItem('theme') ??
        @js(filament()->getDefaultThemeMode()->value)

		if (
			theme === 'dark' ||
			(theme === 'system' &&
				window.matchMedia('(prefers-color-scheme: dark)')
					.matches)
		) {
			document.documentElement.classList.add('dark')
		}
    </script>
</head>

<body class="antialiased">

<main class="container mx-auto p-8">
    {{ $slot }}
</main>

@filamentScripts
@vite('resources/js/app.js')
</body>
</html>