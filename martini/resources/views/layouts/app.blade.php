<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Breadcrumbs -->
            @if(\Diglactic\Breadcrumbs\Breadcrumbs::exists())
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 breadcrumbs">
            {{ Breadcrumbs::render() }}
            </div>
            @endif

            <!-- Alerts -->
            @if (Session::has('message'))
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
                <x-alert type="success">
                    {{ Session::get('message') }}
                </x-alert>
            </div>
            @endif

            @if(Session::has('error'))
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
                    <x-alert type="error">
                        {{ Session::get('error') }}
                    </x-alert>
                </div>
            @endif


            @if($errors->any())
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-4">
                    <x-alert type="error">
                        {{ $errors->first() }}
                    </x-alert>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                <div class="pt-6">
                    <div class="mx-auto sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>


<style>
    div.breadcrumbs > * > * {
        background-color: transparent !important;
    }
</style>
