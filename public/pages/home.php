<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatherly | An Event Management Platform</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="min-h-screen font-['Montserrat']">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 w-full shadow bg-gray-100/80 backdrop-blur-lg">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center h-full">
                        <a href="home.php" class="flex items-center">
                            <img class="w-8 h-8 mr-2" src="../assets/images/logo.png" alt="Gatherly Logo">
                            <span class="text-xl font-bold text-gray-800">Gatherly</span>
                        </a>
                    </div>
                    <div class="hidden md:block">
                        <div class="flex items-baseline ml-10 space-x-2">
                            <a href="#home"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 transition transform duration-200 ease-out rounded-lg hover:shadow-sm hover:-translate-y-0.5">Home</a>
                            <a href="#features"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 transition transform duration-200 ease-out rounded-lg hover:shadow-sm hover:-translate-y-0.5">Features</a>
                            <a href="#pricing"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 transition transform duration-200 ease-out rounded-lg hover:shadow-sm hover:-translate-y-0.5">Pricing</a>
                            <a href="#contact"
                                class="px-3 py-2 text-sm font-semibold text-gray-700 transition transform duration-200 ease-out rounded-lg hover:shadow-sm hover:-translate-y-0.5">Contact</a>
                        </div>
                    </div>
                    <a href="signin.php"
                        class="px-4 py-2 font-medium text-white transition-all bg-indigo-500 rounded-lg cursor-pointer hover:bg-indigo-600 hover:-translate-y-0.5 hover:shadow-sm">Sign
                        in</a>
                </div>
            </div>
        </nav>
        <div class="grid grid-rows-[auto_1fr_auto]">
            <!-- Hero Section -->
            <div id="home" class="relative pt-16 overflow-hidden bg-center bg-no-repeat bg-cover hero-section"
                style="background-image: url('../assets/images/hero-bg.png'); will-change: background-position; height: calc(100vh - var(--nav-height, 4rem));"
                data-pan-speed="0.45">
                <div class="flex flex-col justify-center h-full mx-auto max-w-7xl">
                    <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                        <main class="px-4 mx-auto mt-10 max-w-7xl sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                            <div class="sm:text-center lg:text-left">
                                <h1 class="text-4xl font-extrabold text-white sm:text-5xl md:text-6xl">
                                    <span class="block xl:inline">Organize Events Seamlessly</span>
                                </h1>
                                <p
                                    class="mt-3 text-base text-white sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                                    Gatherly simplifies event management with tools for planning, promotion, and
                                    attendee
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
            <div id="features" class="py-16 bg-gray-50">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-col items-center">
                        <h2 class="text-4xl font-bold text-center text-gray-900">Why Choose Gatherly?</h2>
                        <p class="mt-2 text-xl text-gray-600">The smartest way to manage your events</p>
                    </div>
                    <div class="grid grid-cols-4 gap-4 mt-8">
                        <div class="flex flex-col items-center justify-center gap-3 p-6 bg-white rounded-lg shadow-md">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <i class="text-2xl text-indigo-600 fa-solid fa-comment-nodes" aria-hidden="true"></i>
                            </div>
                            <h1 class="mt-2 text-lg font-semibold text-gray-800">AI-Powered Matching</h1>
                            <p class="text-sm text-center text-gray-600">Smart recommendations based on your event
                                requirements and
                                preferences
                            </p>
                        </div>
                        <div class="flex flex-col items-center justify-center gap-3 p-6 bg-white rounded-lg shadow-md">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <i class="text-2xl text-indigo-600 fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
                            </div>
                            <h1 class="mt-2 text-lg font-semibold text-gray-800">Dynamic Pricing</h1>
                            <p class="text-sm text-center text-gray-600">Get the best rates with real-time pricing based
                                on
                                demand and seasonality
                            </p>
                        </div>
                        <div class="flex flex-col items-center justify-center gap-3 p-6 bg-white rounded-lg shadow-md">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <i class="text-2xl text-indigo-600 fa-solid fa-shield-halved" aria-hidden="true"></i>
                            </div>
                            <h1 class="mt-2 text-lg font-semibold text-gray-800">Secure Contracts</h1>
                            <p class="text-sm text-center text-gray-600">Auto-generated agreements with transparent
                                terms
                                and conditions
                            </p>
                        </div>
                        <div class="flex flex-col items-center justify-center gap-3 p-6 bg-white rounded-lg shadow-md">
                            <div class="p-3 bg-indigo-100 rounded-lg">
                                <i class="text-2xl text-indigo-600 fa-solid fa-bolt" aria-hidden="true"></i>
                            </div>
                            <h1 class="mt-2 text-lg font-semibold text-gray-800">Lightning Fast Setup</h1>
                            <p class="text-sm text-center text-gray-600">Get your event up and running in no time with
                                our
                                streamlined process
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Call to Action -->
            <div class="relative py-16 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <!-- Soft colorful blobs using Tailwind arbitrary backgrounds and built-in animations -->
                    <div
                        class="absolute w-3xl h-3xl rounded-full filter blur-[72px] opacity-60 pointer-events-none left-80 top-40 bg-[radial-gradient(circle_at_30%_30%,#7c3aed_0%,#5b21b6_40%,transparent_60%)] animate-[bounce_8s_ease-in-out_infinite]">
                    </div>
                    <div
                        class="absolute w-3xl h-3xl rounded-full filter blur-[72px] opacity-60 pointer-events-none right-72 bottom-48 bg-[radial-gradient(circle_at_70%_70%,#06b6d4_0%,#0891b2_40%,transparent_60%)] animate-[bounce_10s_ease-in-out_infinite]">
                    </div>

                    <!-- Subtle diagonal SVG pattern -->
                    <svg class="absolute inset-0 w-full h-full" preserveAspectRatio="none"
                        xmlns="http://www.w3.org/2000/svg" fill="none" aria-hidden="true">
                        <defs>
                            <pattern id="diagonal" width="40" height="40" patternUnits="userSpaceOnUse"
                                patternTransform="rotate(30)">
                                <path d="M0 0 L0 40" stroke="rgba(255,255,255,0.03)" stroke-width="2" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#diagonal)"></rect>
                    </svg>

                    <!-- Color overlay for depth -->
                    <div
                        class="absolute inset-0 bg-linear-to-r from-indigo-800 via-indigo-600 to-pink-600 opacity-60 mix-blend-multiply">
                    </div>
                </div>

                <div class="relative z-10 mx-auto text-center max-w-7xl px-4">
                    <h2 class="text-4xl font-bold text-white">Ready to Elevate Your Event Management?</h2>
                    <p class="mt-2 text-xl font-medium text-indigo-100">Join Gatherly today and experience the future of
                        event planning!</p>
                    <div class="mt-6">
                        <a href="#"
                            class="inline-block px-6 py-3 text-lg font-semibold text-indigo-600 bg-white rounded-lg transition-all hover:bg-gray-100">Get
                            Started Now</a>
                    </div>
                </div>
            </div>
            <?php include '../../src/components/Footer.php'; ?>
        </div>
    </div>

    <script src="../assets/js/home.js"></script>
</body>

</html>