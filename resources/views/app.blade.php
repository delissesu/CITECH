<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="google-site-verification" content="EwODKAyEs0lbvK2U7S5kZeAhXVBcSsJVckvRfRm3n8M" />

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- SEO Metadata -->
        <meta name="description" content="CITECH (Carnival Technology) adalah kompetisi Web Design nasional yang diselenggarakan oleh UKM LAOS Universitas Jember (UNEJ) untuk generasi muda kreatif dan inovatif.">
        <meta name="keywords" content="CITECH, Carnival Technology, CITECH UNEJ, Kompetisi Web Design, Lomba Web Design, Universitas Jember, CITECH 2026, Web Design UNEJ, Lomba IT">
        <meta name="author" content="UKM LAOS UNEJ">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;950&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
