<?php

$navbar = <<<EOD
<!-- Navbar -->
<nav class="sticky font-['Roboto'] top-0 z-50 w-full bg-white/50 backdrop-blur-lg shadow">
    <div>
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center h-full">
                    <a href="#" class="flex items-center">
                        <img class="w-8 h-8 mr-2" src="public/assets/images/logo.png" alt="Gatherly Logo">
                        <span class="text-xl font-bold text-gray-800">Gatherly</span>
                    </a>
                    <div class="hidden md:block">
                        <div class="flex items-baseline ml-10 space-x-4">
                            <a href="#home"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 bg-blue-500 rounded-lg">Home</a>
                            <a href="#features"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 transition-all border-b border-transparent hover:border-b-sky-600 hover:border-gray-900">Features</a>
                            <a href="#pricing"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 transition-all border-b border-transparent hover:text-sky-600 hover:border-b-sky-600 hover:border-gray-900">Pricing</a>
                            <a href="#contact"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 transition-all border-b border-transparent hover:text-sky-600 hover:border-b-sky-600 hover:border-gray-900">Contact</a>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
EOD;

echo $navbar;
