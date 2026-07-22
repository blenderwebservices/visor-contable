<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Visor Contable</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Inter', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 min-h-screen flex flex-col justify-center items-center">
        
        <div class="max-w-4xl w-full px-6">
            <div class="text-center mb-12">
                <div class="inline-block p-4 rounded-full bg-blue-100 dark:bg-blue-900/30 mb-6">
                    <svg class="w-16 h-16 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h1 class="text-5xl font-bold mb-4 text-gray-900 dark:text-white tracking-tight">Visor Contable</h1>
                <p class="text-xl text-gray-600 dark:text-gray-400">Sistema seguro de gestión documental y visor de archivos jerárquico.</p>
            </div>

            <div class="max-w-2xl mx-auto mt-12">
                <!-- Portal de Documentos -->
                <a href="/portal" class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-10 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 block text-center">
                    <div class="mb-6 flex justify-center">
                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-full group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-3xl font-semibold mb-4 text-gray-900 dark:text-white">Portal de Documentos</h2>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                        Accede de forma rápida y segura a todos los documentos relevantes que han sido asignados para ti. Explora tus carpetas y visualiza tus archivos importantes en un solo lugar.
                    </p>
                    <span class="inline-flex items-center text-blue-600 dark:text-blue-400 font-medium group-hover:text-blue-700 dark:group-hover:text-blue-300">
                        Iniciar Sesión
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </span>
                </a>
            </div>
            
            <div class="mt-16 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} Visor Contable. Todos los derechos reservados.
            </div>
        </div>

    </body>
</html>
