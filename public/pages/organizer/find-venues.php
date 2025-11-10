<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Discover Venues - GEMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow px-6 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <span class="text-2xl font-bold flex items-center space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                <!-- Ground line -->
                <line x1="3" y1="21" x2="21" y2="21" stroke="#0011ffff" stroke-width="1.2" stroke-linecap="round"/>
                
                <!-- Left building (with horizontal floors) -->
                <rect x="5" y="8" width="6" height="12" fill="none" stroke="#0011ffff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="5" y1="11" x2="11" y2="11" stroke="#0011ffff" stroke-width="0.8"/>
                <line x1="5" y1="14" x2="11" y2="14" stroke="#0011ffff" stroke-width="0.8"/>
                <line x1="5" y1="17" x2="11" y2="17" stroke="#0011ffff" stroke-width="0.8"/>
                
                <!-- Right building (taller, vertical windows) -->
                <rect x="12" y="6" width="5" height="14" fill="none" stroke="#0011ffff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="13.5" y1="9" x2="13.5" y2="18" stroke="#0011ffff" stroke-width="0.6"/>
                </svg>
                <span>GEMS</span>
            </span>

            <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                <!-- Ground line -->
                <line x1="2" y1="14" x2="14" y2="14" stroke="#000000ff" stroke-width="0.8" stroke-linecap="round"/>
                
                <!-- Left building (with horizontal floors) -->
                <rect x="3.5" y="6" width="4" height="8" fill="none" stroke="#000000ff" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="3.5" y1="8" x2="7.5" y2="8" stroke="#000000ff" stroke-width="0.5"/>
                <line x1="3.5" y1="10" x2="7.5" y2="10" stroke="#000000ff" stroke-width="0.5"/>
                <line x1="3.5" y1="12" x2="7.5" y2="12" stroke="#000000ff" stroke-width="0.5"/>
                
                <!-- Right building (taller, vertical windows) -->
                <rect x="8" y="4" width="3.5" height="9.5" fill="none" stroke="#000000ff" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="9.25" y1="6" x2="9.25" y2="12.5" stroke="#000000ff" stroke-width="0.4"/>
                </svg>
            <a href="#">
                <span>Home</span>
            </a>
            </button>
            <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                <!-- Circle (magnifying glass lens) -->
                <circle cx="6.5" cy="6.5" r="4.5" fill="none" stroke="#000" stroke-width="1.2" stroke-linecap="round"/>
                <!-- Handle (diagonal line) -->
                <line x1="10" y1="10" x2="13" y2="13" stroke="#000" stroke-width="1.2" stroke-linecap="round"/>
                </svg>
            <a href="#">
                <span>Venues</span>
            </a>
            </button>
            <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
            <rect x="2" y="4" width="12" height="8" rx="1" fill="none" stroke="#000000ff" stroke-width="1" stroke-linecap="round"/>
            <line x1="6" y1="12" x2="10" y2="12" stroke="#000000ff" stroke-width="1" stroke-linecap="round"/>
            <line x1="8" y1="12" x2="8" y2="14" stroke="#000000ff" stroke-width="1" stroke-linecap="round"/>
            <line x1="5" y1="9" x2="5" y2="10" stroke="#000000ff" stroke-width="1.2"/>
            <line x1="8" y1="7" x2="8" y2="10" stroke="#000000ff" stroke-width="1.2"/>
            </svg>
            <a href="#">
                <span>Analytics</span>
            </a>
            </button>
            <button class="ml-2 px-4 py-2 font-semi-bold text-gray-700 hover:bg-gray-300 rounded-md flex items-center space-x-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
            <!-- Speech bubble outline -->
            <path d="M13 4H5C4.45 4 4 4.45 4 5v6c0 0.55 0.45 1 1 1h2l1 2 1-2h3c0.55 0 1-0.45 1-1V5c0-0.55-0.45-1-1-1z"
                    fill="none" stroke="#000" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <a href="#">
                <span>Messages</span>
            </a>
            </button>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Light/Dark mode toggle placeholder -->
            <button aria-label="Toggle Dark Mode" class="text-gray-600 hover:text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                <!-- Sun circle -->
                <circle cx="8" cy="8" r="3" fill="none" stroke="#000" stroke-width="1" stroke-linecap="round"/>
                <!-- Rays (top, right, bottom, left) -->
                <line x1="8" y1="2" x2="8" y2="4" stroke="#000" stroke-width="1" stroke-linecap="round"/>
                <line x1="12" y1="8" x2="14" y2="8" stroke="#000" stroke-width="1" stroke-linecap="round"/>
                <line x1="8" y1="12" x2="8" y2="14" stroke="#000" stroke-width="1" stroke-linecap="round"/>
                <line x1="2" y1="8" x2="4" y2="8" stroke="#000" stroke-width="1" stroke-linecap="round"/>
                </svg>
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700">
                <a href="#">Sign In</a>
            </button>
        </div>
    </nav>

    <!--Main-->
        <main class="container mx-auto px-6 py-8">

            <h1 class="text-3xl font-extrabold text-gray-900 mb-1">Discover Venues</h1>
            <p class="text-gray-600 mb-6">6 Venues available</p>

            <!--Search and Filter-->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0 mb-6">
                <div class="flex items-center flex-grow max-w-xl sm:max-w-none">
                    <label for="search" class="sr-only">Search Venues</label>
                        <input type="search" id="search" name="search" placeholder="⌕ Search venues by name, location, and features..."
                         class="w-full rounded-md border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                         <button class="ml-2 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-300 flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <line x1="2" y1="4" x2="14" y2="4" stroke="#000" stroke-width="1.2" stroke-linecap="round"/>
                            <line x1="2" y1="8" x2="14" y2="8" stroke="#000" stroke-width="1.2" stroke-linecap="round"/>
                            <line x1="2" y1="12" x2="14" y2="12" stroke="#000" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                            <span>Filters</span>
                        </button>
                </div>
            </div>

            <!--Category Filter tabs-->
            <div class="flex flex-wrap gap-2 mb-6">
            <?php
            // Define venue categories (could be dynamic from database)
            $categories = ['All', 'Ballroom', 'Conference', 'Outdoor', 'Rooftop'];
            
            foreach ($categories as $index => $category) {
                $isActive = ($index === 0); // First category is active by default
                $buttonClass = $isActive ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200';
            ?>
                <button class="<?php echo $buttonClass; ?> px-3 py-1 rounded-md text-sm font-medium transition">
                    <?php echo htmlspecialchars($category); ?>
                </button>
            <?php } ?>
        </div>

        <!-- Venue Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Sample venue data (in a real app, this would come from a database)
            $venues = [
                [
                    'name' => 'Grand Ballroom',
                    'location' => 'Downtown Manhattan, NY',
                    'guests' => 'Up to 200 guests',
                    'price' => '$10,000',
                    'image' => 'https://placehold.co/400x200?text=Grand+Ballroom'
                ],
                [
                    'name' => 'Garden Paradise',
                    'location' => 'Brooklyn Heights, NY',
                    'guests' => 'Up to 150 guests',
                    'price' => '$7,500',
                    'image' => 'https://placehold.co/400x200?text=Garden+Paradise'
                ],
                [
                    'name' => 'Modern Conference Center',
                    'location' => 'Midtown Manhattan, NY',
                    'guests' => 'Up to 300 guests',
                    'price' => '$15,000',
                    'image' => 'https://placehold.co/400x200?text=Modern+Conference+Center'
                ],
                [
                    'name' => 'Elegant Banquet Hall',
                    'location' => 'Upper East Side, NY',
                    'guests' => 'Up to 250 guests',
                    'price' => '$12,000',
                    'image' => 'https://placehold.co/400x200?text=Elegant+Banquet+Hall'
                ],
                [
                    'name' => 'Skyline Rooftop Venue',
                    'location' => 'Chelsea, NY',
                    'guests' => 'Up to 120 guests',
                    'price' => '$9,000',
                    'image' => 'https://placehold.co/400x200?text=Skyline+Rooftop+Venue'
                ],
                [
                    'name' => 'Rustic Barn Estate',
                    'location' => 'Hudson Valley, NY',
                    'guests' => 'Up to 180 guests',
                    'price' => '$6,500',
                    'image' => 'https://placehold.co/400x200?text=Rustic+Barn+Estate'
                ]
            ];
            
            // Display each venue card
            foreach ($venues as $venue) {
            ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:shadow-lg hover:-translate-y-1">
                    <!-- Venue Image with Gradient Overlay -->
                    <div class="relative h-48">
                        <img src="<?php echo htmlspecialchars($venue['image']); ?>" 
                             alt="<?php echo htmlspecialchars($venue['name']); ?>"
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        
                        <!-- Venue Title and Location -->
                        <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($venue['name']); ?></h3>
                            <div class="flex items-center mt-1 text-sm">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                <span><?php echo htmlspecialchars($venue['location']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Venue Details -->
                    <div class="p-4">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-users mr-1"></i>
                                <span><?php echo htmlspecialchars($venue['guests']); ?></span>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($venue['price']); ?></div>
                                <div class="text-xs text-gray-500">per day</div>
                            </div>
                        </div>
                        
                        <!-- View Details Button -->
                        <button class="w-full bg-white border border-gray-300 text-gray-700 py-2 rounded-md hover:bg-gray-50 transition">
                            View Details
                        </button>
                    </div>
                </div>
            <?php } ?>
        </div>
        </main>

        <!-- Footer -->
    <footer class="bg-white border-t mt-10 py-6">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            © 2025 GEMS. All rights reserved.
        </div>
    </footer>
</body>
</html>
