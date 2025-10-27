<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Discover Venues - GEMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow px-6 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <span class="text-2xl font-bold flex items-center space-x-1">
                <!-- Icon placeholder -->
                <span>GEMS</span>
            </span>

            <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
            <a href="#">
                <!--Img src for Icon-->
                <span>Home</span>
            </a>
            </button>
           <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
            <a href="#">
                <!--Img src for Icon-->
                <span>Venues</span>
            </a>
            </button>
            <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
            <a href="#">
                <!--Img src for Icon-->
                <span>Analytics</span>
            </a>
            </button>
            <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
            <a href="#">
                <!--Img src for Icon-->
                <span>Messages</span>
            </a>
            </button>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Light/Dark mode toggle placeholder -->
            <button aria-label="Toggle Dark Mode" class="text-gray-600 hover:text-gray-900">
                <!--Img src for Icon--> Mode
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700">
                <a href="#">Sign In</a>
            </button>
        </div>
    </nav>

    <!--Main-->
        <main class="max-w-7xl mx-auto px-6 py-8">

            <h1 class="text-3xl font-extrabold text-gray-900 mb-1">Discover Venues</h1>
            <p class="text-gray-600 mb-6">6 Venues available</p>

            <!--Search and Filter-->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 mb-6">
                <div class="flex items-center flex-grow max-w-xl sm:max-w-none">
                    <label for="search" class="sr-only">Search Venues</label>
                        <input type="search" id="search" name="search" placeholder="Search venues by name, location, and features..."
                         class="w-full rounded-md border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                         <button class="ml-2 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-300 flex items-center space-x-1">
                            <!--Img src for Icon-->
                            <span>Filters</span>
                        </button>
                </div>
            </div> 
        </main>
</body>
</html>
