<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatherly | An Event Management Platform</title>
    <link rel="icon" type="image/x-icon" href="public/assets/images/logo.png">
    <link rel="stylesheet" href="src/output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="min-h-screen">
        <!-- Navbar -->
        <nav class="sticky font-['Montserrat'] top-0 z-50 w-full bg-white/60 backdrop-blur-lg shadow">
            <div>
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center h-full">
                            <a href="#" class="flex items-center">
                                <img class="w-8 h-8 mr-2" src="public/assets/images/logo.png" alt="Gatherly Logo">
                                <span class="text-xl font-bold text-gray-800">Gatherly</span>
                            </a>
                            <div class="hidden md:block">
                                <div class="flex items-baseline ml-10 space-x-2">
                                    <a href="#home"
                                        class="px-3 py-2 text-sm font-semibold text-white bg-indigo-500 rounded-lg">Home</a>
                                    <a href="#features"
                                        class="px-3 py-2 text-sm font-semibold text-gray-700 transition-all rounded-lg hover:bg-gray-200">Features</a>
                                    <a href="#pricing"
                                        class="px-3 py-2 text-sm font-semibold text-gray-700 transition-all rounded-lg hover:bg-gray-200">Pricing</a>
                                    <a href="#contact"
                                        class="px-3 py-2 text-sm font-semibold text-gray-700 transition-all rounded-lg hover:bg-gray-200">Contact</a>
                                </div>
                            </div>
                        </div>
                        <button
                            class="px-4 py-2 text-white font-medium transition-all bg-indigo-500 rounded-lg cursor-pointer hover:bg-indigo-600">Sign
                            in</button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="font-['Montserrat'] relative overflow-hidden pt-16 bg-cover bg-center bg-no-repeat"
            style="background-image: linear-gradient(to right, rgba(0,0,0,0.75), rgba(0,0,0,0.45)), url('public/assets/images/hero.png'); background-size:cover; background-position:center; background-repeat:no-repeat; padding-top:4rem;">
            <div class="flex flex-col justify-center h-full mx-auto max-w-7xl">
                <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                    <main class="px-4 mx-auto mt-10 max-w-7xl sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="sm:text-center lg:text-left">
                            <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl">
                                <span class="block xl:inline">Organize Events Seamlessly</span>
                            </h1>
                            <p
                                class="mt-3 text-base text-white sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                Gatherly simplifies event management with tools for planning, promotion, and attendee
                                engagement.</p>
                            <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                <div class="rounded-md shadow">
                                    <a href="#"
                                        class="flex items-center justify-center w-full px-4 py-2 text-base font-medium text-white transition-all bg-indigo-600 border border-transparent rounded-xl hover:bg-indigo-700 md:py-3 md:text-lg md:px-6">Get
                                        Started</a>
                                </div>
                            </div>
                        </div>
                    </main>

                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col items-center">
                    <h2 class="text-4xl font-bold text-center text-gray-900">Why Choose Gatherly?</h2>
                    <p class="mt-2 text-xl text-gray-600">The smartest way to manage your events</p>
                </div>
                <div class="mt-8 grid grid-cols-4 gap-4 mx-8">
                    <div class="flex flex-col items-center justify-center gap-3 p-6 shadow-md bg-white rounded-lg">
                        <div class="p-3 rounded-lg bg-indigo-100">
                            <i class="fa-solid fa-comment-nodes text-indigo-600 text-2xl" aria-hidden="true"></i>
                        </div>
                        <h1 class="mt-2 text-lg font-semibold text-gray-800">AI-Powered Matching</h1>
                        <p class="text-gray-600 text-center text-sm">Smart recommendations based on your event
                            requirements and
                            preferences
                        </p>
                    </div>
                </div>
            </div>
        </div>
</body>

</html>