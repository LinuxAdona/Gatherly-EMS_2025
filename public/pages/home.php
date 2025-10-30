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
        <nav class="sticky top-0 z-50 w-full shadow-md bg-white/90 backdrop-blur-lg border-b border-gray-200">
            <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 md:h-20">
                    <div class="flex items-center h-full">
                        <a href="home.php" class="flex items-center group">
                            <img class="w-8 h-8 sm:w-10 sm:h-10 mr-2 transition-transform group-hover:scale-110" src="../assets/images/logo.png" alt="Gatherly Logo">
                            <span class="text-lg sm:text-xl font-bold text-gray-800">Gatherly</span>
                        </a>
                    </div>
                    <div class="hidden md:block">
                        <div class="flex items-center space-x-1 lg:space-x-2">
                            <a href="#home"
                                class="px-3 py-2 text-sm lg:text-base font-semibold text-gray-700 transition-all duration-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Home</a>
                            <a href="#features"
                                class="px-3 py-2 text-sm lg:text-base font-semibold text-gray-700 transition-all duration-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Features</a>
                            <a href="#pricing"
                                class="px-3 py-2 text-sm lg:text-base font-semibold text-gray-700 transition-all duration-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Pricing</a>
                            <a href="#contact"
                                class="px-3 py-2 text-sm lg:text-base font-semibold text-gray-700 transition-all duration-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Contact</a>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="signin.php"
                            class="px-3 sm:px-4 py-2 text-sm sm:text-base font-semibold text-white transition-all bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5">Sign in</a>
                        <button id="mobile-menu-button" class="md:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Mobile Menu -->
                <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-2">
                    <a href="#home" class="block px-3 py-2 text-sm font-semibold text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Home</a>
                    <a href="#features" class="block px-3 py-2 text-sm font-semibold text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Features</a>
                    <a href="#pricing" class="block px-3 py-2 text-sm font-semibold text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Pricing</a>
                    <a href="#contact" class="block px-3 py-2 text-sm font-semibold text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600">Contact</a>
                </div>
            </div>
        </nav>
        <div class="grid grid-rows-[auto_1fr_auto]">
            <!-- Hero Section -->
            <div id="home" class="relative pt-8 sm:pt-12 md:pt-16 overflow-hidden bg-center bg-no-repeat bg-cover hero-section"
                style="background-image: url('../assets/images/hero-bg.png'); will-change: background-position; min-height: 500px; height: calc(100vh - 4rem);"
                data-pan-speed="0.45">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/50 via-purple-900/30 to-transparent"></div>
                <div class="flex flex-col justify-center h-full mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:max-w-3xl lg:w-full lg:pb-28 xl:pb-32">
                        <main class="mt-6 sm:mt-10 md:mt-16 lg:mt-20 xl:mt-28">
                            <div class="text-center lg:text-left">
                                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                                    <span class="block">Organize Events</span>
                                    <span class="block text-indigo-300">Seamlessly</span>
                                </h1>
                                <p class="mt-4 sm:mt-5 md:mt-6 text-sm sm:text-base md:text-lg lg:text-xl text-gray-100 max-w-md mx-auto lg:mx-0 leading-relaxed">
                                    Gatherly simplifies event management with powerful tools for planning, promotion, and attendee engagement.
                                </p>
                                <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row gap-3 sm:gap-4 items-center justify-center lg:justify-start">
                                    <a href="signup.php"
                                        class="w-full sm:w-auto px-6 py-3 sm:px-8 sm:py-4 text-sm sm:text-base lg:text-lg font-semibold text-white transition-all bg-indigo-600 rounded-xl shadow-xl hover:bg-indigo-700 hover:shadow-2xl hover:-translate-y-1">
                                        Get Started Free
                                    </a>
                                    <a href="#features"
                                        class="w-full sm:w-auto px-6 py-3 sm:px-8 sm:py-4 text-sm sm:text-base lg:text-lg font-semibold text-white transition-all bg-white/10 backdrop-blur-sm border-2 border-white/30 rounded-xl hover:bg-white/20">
                                        Learn More
                                    </a>
                                </div>
                            </div>
                        </main>
                    </div>
                </div>
            </div>
            <!-- Features -->
            <div id="features" class="py-12 sm:py-16 md:py-20 bg-gray-50">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col items-center text-center mb-8 sm:mb-12">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900">Why Choose Gatherly?</h2>
                        <p class="mt-2 sm:mt-3 text-base sm:text-lg md:text-xl text-gray-600">The smartest way to manage your events</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="flex flex-col items-center justify-start gap-3 p-6 sm:p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 group">
                            <div class="p-4 bg-indigo-100 rounded-xl group-hover:bg-indigo-200 transition-colors">
                                <i class="text-2xl sm:text-3xl text-indigo-600 fa-solid fa-comment-nodes" aria-hidden="true"></i>
                            </div>
                            <h3 class="mt-2 text-base sm:text-lg font-bold text-gray-800">AI-Powered Matching</h3>
                            <p class="text-xs sm:text-sm text-center text-gray-600 leading-relaxed">Smart recommendations based on your event requirements and preferences</p>
                        </div>
                        <div class="flex flex-col items-center justify-start gap-3 p-6 sm:p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 group">
                            <div class="p-4 bg-indigo-100 rounded-xl group-hover:bg-indigo-200 transition-colors">
                                <i class="text-2xl sm:text-3xl text-indigo-600 fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
                            </div>
                            <h3 class="mt-2 text-base sm:text-lg font-bold text-gray-800">Dynamic Pricing</h3>
                            <p class="text-xs sm:text-sm text-center text-gray-600 leading-relaxed">Get the best rates with real-time pricing based on demand and seasonality</p>
                        </div>
                        <div class="flex flex-col items-center justify-start gap-3 p-6 sm:p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 group">
                            <div class="p-4 bg-indigo-100 rounded-xl group-hover:bg-indigo-200 transition-colors">
                                <i class="text-2xl sm:text-3xl text-indigo-600 fa-solid fa-shield-halved" aria-hidden="true"></i>
                            </div>
                            <h3 class="mt-2 text-base sm:text-lg font-bold text-gray-800">Secure Contracts</h3>
                            <p class="text-xs sm:text-sm text-center text-gray-600 leading-relaxed">Auto-generated agreements with transparent terms and conditions</p>
                        </div>
                        <div class="flex flex-col items-center justify-start gap-3 p-6 sm:p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 group">
                            <div class="p-4 bg-indigo-100 rounded-xl group-hover:bg-indigo-200 transition-colors">
                                <i class="text-2xl sm:text-3xl text-indigo-600 fa-solid fa-bolt" aria-hidden="true"></i>
                            </div>
                            <h3 class="mt-2 text-base sm:text-lg font-bold text-gray-800">Lightning Fast Setup</h3>
                            <p class="text-xs sm:text-sm text-center text-gray-600 leading-relaxed">Get your event up and running in no time with our streamlined process</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Call to Action -->
            <div class="relative py-12 sm:py-16 md:py-20 lg:py-24 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <!-- Soft colorful blobs -->
                    <div class="absolute w-96 h-96 rounded-full filter blur-[72px] opacity-60 pointer-events-none left-10 sm:left-80 top-20 sm:top-40 bg-[radial-gradient(circle_at_30%_30%,#7c3aed_0%,#5b21b6_40%,transparent_60%)] animate-[bounce_8s_ease-in-out_infinite]">
                    </div>
                    <div class="absolute w-96 h-96 rounded-full filter blur-[72px] opacity-60 pointer-events-none right-10 sm:right-72 bottom-20 sm:bottom-48 bg-[radial-gradient(circle_at_70%_70%,#06b6d4_0%,#0891b2_40%,transparent_60%)] animate-[bounce_10s_ease-in-out_infinite]">
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
                    <div class="absolute inset-0 bg-linear-to-r from-indigo-800 via-indigo-600 to-pink-600 opacity-60 mix-blend-multiply">
                    </div>
                </div>

                <div class="relative z-10 mx-auto text-center max-w-4xl px-4 sm:px-6">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight">Ready to Elevate Your Event Management?</h2>
                    <p class="mt-3 sm:mt-4 text-base sm:text-lg md:text-xl font-medium text-indigo-100">Join Gatherly today and experience the future of event planning!</p>
                    <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row gap-3 sm:gap-4 items-center justify-center">
                        <a href="signup.php"
                            class="w-full sm:w-auto inline-block px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-indigo-600 bg-white rounded-xl shadow-xl transition-all hover:bg-gray-50 hover:shadow-2xl hover:-translate-y-1">
                            Get Started Now
                        </a>
                        <a href="#contact"
                            class="w-full sm:w-auto inline-block px-6 sm:px-8 py-3 sm:py-4 text-base sm:text-lg font-semibold text-white bg-white/10 backdrop-blur-sm border-2 border-white/30 rounded-xl transition-all hover:bg-white/20">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
            <?php include '../../src/components/Footer.php'; ?>
        </div>
    </div>

    <script src="../assets/js/home.js"></script>
</body>

</html>