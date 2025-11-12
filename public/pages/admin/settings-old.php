<?php
session_start();

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrator') {
    header("Location: ../signin.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? 'Admin';
$success_message = '';
$error_message = '';

// Get current settings from session or set defaults
$theme = $_SESSION['theme'] ?? 'light';
$notifications_enabled = $_SESSION['notifications_enabled'] ?? true;
$email_notifications = $_SESSION['email_notifications'] ?? true;
$items_per_page = $_SESSION['items_per_page'] ?? 10;
$timezone = $_SESSION['timezone'] ?? 'Asia/Manila';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $_SESSION['theme'] = $_POST['theme'] ?? 'light';
    $_SESSION['notifications_enabled'] = isset($_POST['notifications_enabled']);
    $_SESSION['email_notifications'] = isset($_POST['email_notifications']);
    $_SESSION['items_per_page'] = (int)($_POST['items_per_page'] ?? 10);
    $_SESSION['timezone'] = $_POST['timezone'] ?? 'Asia/Manila';

    $theme = $_SESSION['theme'];
    $notifications_enabled = $_SESSION['notifications_enabled'];
    $email_notifications = $_SESSION['email_notifications'];
    $items_per_page = $_SESSION['items_per_page'];
    $timezone = $_SESSION['timezone'];

    $success_message = "Settings saved successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../assets/images/logo.png">
    <link rel="stylesheet"
        href="../../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../../src/output.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-linear-to-br from-slate-50 via-white to-blue-50 font-['Montserrat'] flex flex-col min-h-screen">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white shadow-md">{
        <div class="container px-4 mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-12 sm:h-16">
                <div class="flex items-center h-full">
                    <a href="../home.php" class="flex items-center group">
                        <img class="w-8 h-8 mr-2 transition-transform sm:w-10 sm:h-10 group-hover:scale-110"
                            src="../../assets/images/logo.png" alt="Gatherly Logo">
                        <span class="text-lg font-bold text-gray-800 sm:text-xl">Gatherly</span>
                    </a>
                </div>
                <div class="items-center hidden gap-6 md:flex">
                    <a href="admin-dashboard.php"
                        class="text-gray-700 transition-colors hover:text-indigo-600">Dashboard</a>
                    <a href="manage-users.php" class="text-gray-700 transition-colors hover:text-indigo-600">Users</a>
                    <a href="manage-venues.php" class="text-gray-700 transition-colors hover:text-indigo-600">Venues</a>
                    <a href="manage-events.php" class="text-gray-700 transition-colors hover:text-indigo-600">Events</a>
                    <a href="reports.php" class="text-gray-700 transition-colors hover:text-indigo-600">Reports</a>
                    <div class="relative">
                        <button id="profile-dropdown-btn"
                            class="flex items-center gap-2 text-gray-700 transition-colors cursor-pointer hover:text-indigo-600">
                            <i class="text-2xl fas fa-user-shield"></i>
                            <span><?php echo htmlspecialchars($first_name); ?></span>
                            <i class="text-xs fas fa-chevron-down"></i>
                        </button>
                        <div id="profile-dropdown"
                            class="absolute right-0 hidden w-48 py-2 mt-2 bg-white rounded-lg shadow-lg">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">Profile</a>
                            <a href="settings.php"
                                class="block px-4 py-2 font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-gray-600">Settings</a>
                            <a href="../../../src/services/signout-handler.php"
                                class="block px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-gray-600">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container px-4 py-8 mx-auto sm:px-6 lg:px-8 grow">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-gray-800 dark:text-white sm:text-4xl transition-colors">
                <i class="mr-2 text-indigo-600 dark:text-indigo-400 fas fa-cog"></i>
                Settings
            </h1>
            <p class="text-gray-600 dark:text-gray-400 transition-colors">Customize your experience and preferences</p>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="p-4 mb-6 text-green-800 bg-green-100 border border-green-200 rounded-lg">
                <i class="mr-2 fas fa-check-circle"></i><?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="p-4 mb-6 text-red-800 bg-red-100 border border-red-200 rounded-lg">
                <i class="mr-2 fas fa-exclamation-circle"></i><?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Appearance Settings -->
            <div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded-xl transition-colors">
                <h2 class="mb-4 text-xl font-bold text-gray-800 dark:text-white transition-colors">
                    <i class="mr-2 text-indigo-600 dark:text-indigo-400 fas fa-palette"></i>
                    Appearance
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300 transition-colors">Theme</label>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all border-gray-200 dark:border-gray-600 dark:bg-gray-700/50">
                                <input type="radio" name="theme" value="light"
                                    class="mr-3">
                                <div class="flex items-center gap-3">
                                    <i class="text-xl text-yellow-500 fas fa-sun"></i>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white">Light</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Bright & clean</p>
                                    </div>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all border-gray-200 dark:border-gray-600 dark:bg-gray-700/50">
                                <input type="radio" name="theme" value="dark"
                                    class="mr-3">
                                <div class="flex items-center gap-3">
                                    <i class="text-xl text-indigo-600 dark:text-indigo-400 fas fa-moon"></i>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white">Dark</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Easy on eyes</p>
                                    </div>
                                </div>
                            </label>
                            <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all border-gray-200 dark:border-gray-600 dark:bg-gray-700/50">
                                <input type="radio" name="theme" value="auto"
                                    class="mr-3">
                                <div class="flex items-center gap-3">
                                    <i class="text-xl text-gray-600 dark:text-gray-400 fas fa-adjust"></i>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white">Auto</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">System default</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded-xl transition-colors">
                <h2 class="mb-4 text-xl font-bold text-gray-800 dark:text-white transition-colors">
                    <i class="mr-2 text-indigo-600 dark:text-indigo-400 fas fa-bell"></i>
                    Notifications
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg dark:bg-gray-700/50 transition-colors">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">Enable Notifications</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Receive in-app notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="notifications_enabled" <?php echo $notifications_enabled ? 'checked' : ''; ?>
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                            </div>
                        </label>
                    </div>
                    <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg dark:bg-gray-700/50 transition-colors">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-white">Email Notifications</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Receive notifications via email</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_notifications" <?php echo $email_notifications ? 'checked' : ''; ?>
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded-xl transition-colors">
                <h2 class="mb-4 text-xl font-bold text-gray-800 dark:text-white transition-colors">
                    <i class="mr-2 text-indigo-600 dark:text-indigo-400 fas fa-desktop"></i>
                    Display Preferences
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Items per Page</label>
                        <select name="items_per_page"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 md:w-1/2 bg-white dark:bg-gray-700 text-gray-800 dark:text-white transition-colors">
                            <option value="10" <?php echo $items_per_page === 10 ? 'selected' : ''; ?>>10 items</option>
                            <option value="25" <?php echo $items_per_page === 25 ? 'selected' : ''; ?>>25 items</option>
                            <option value="50" <?php echo $items_per_page === 50 ? 'selected' : ''; ?>>50 items</option>
                            <option value="100" <?php echo $items_per_page === 100 ? 'selected' : ''; ?>>100 items</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Number of items to display in tables</p>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Timezone</label>
                        <select name="timezone"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 md:w-1/2 bg-white dark:bg-gray-700 text-gray-800 dark:text-white transition-colors">
                            <option value="Asia/Manila" <?php echo $timezone === 'Asia/Manila' ? 'selected' : ''; ?>>Asia/Manila (PHT)</option>
                            <option value="UTC" <?php echo $timezone === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            <option value="America/New_York" <?php echo $timezone === 'America/New_York' ? 'selected' : ''; ?>>America/New York (EST)</option>
                            <option value="America/Los_Angeles" <?php echo $timezone === 'America/Los_Angeles' ? 'selected' : ''; ?>>America/Los Angeles (PST)</option>
                            <option value="Europe/London" <?php echo $timezone === 'Europe/London' ? 'selected' : ''; ?>>Europe/London (GMT)</option>
                            <option value="Asia/Tokyo" <?php echo $timezone === 'Asia/Tokyo' ? 'selected' : ''; ?>>Asia/Tokyo (JST)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose your preferred timezone</p>
                    </div>
                </div>
            </div>

            <!-- Privacy & Security -->
            <div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded-xl transition-colors">
                <h2 class="mb-4 text-xl font-bold text-gray-800 dark:text-white transition-colors">
                    <i class="mr-2 text-indigo-600 dark:text-indigo-400 fas fa-shield-alt"></i>
                    Privacy & Security
                </h2>
                <div class="space-y-3">
                    <a href="profile.php"
                        class="flex items-center justify-between p-4 transition-all border border-gray-200 dark:border-gray-600 rounded-lg hover:border-indigo-200 dark:hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 dark:bg-gray-700/50">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-indigo-600 dark:text-indigo-400 fas fa-key"></i>
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white">Change Password</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Update your account password</p>
                            </div>
                        </div>
                        <i class="text-gray-400 dark:text-gray-500 fas fa-arrow-right"></i>
                    </a>
                    <div
                        class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-600 rounded-lg opacity-50 cursor-not-allowed dark:bg-gray-700/30">
                        <div class="flex items-center gap-3">
                            <i class="text-xl text-gray-600 dark:text-gray-500 fas fa-user-lock"></i>
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white">Two-Factor Authentication</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Coming soon</p>
                            </div>
                        </div>
                        <i class="text-gray-400 dark:text-gray-600 fas fa-lock"></i>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end gap-3">
                <a href="admin-dashboard.php"
                    class="px-6 py-2 text-gray-700 dark:text-gray-300 transition-colors bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="submit" name="update_settings"
                    class="px-6 py-2 text-white transition-colors bg-indigo-600 dark:bg-indigo-500 rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600">
                    <i class="mr-2 fas fa-save"></i>Save Settings
                </button>
            </div>
        </form>
    </div>

    <?php include '../../../src/components/Footer.php'; ?>

    <script>
        // Profile dropdown toggle
        document.getElementById('profile-dropdown-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.getElementById('profile-dropdown').classList.toggle('hidden');
        });

        document.addEventListener('click', function() {
            document.getElementById('profile-dropdown')?.classList.add('hidden');
        });

        // Theme handling
        document.addEventListener('DOMContentLoaded', function() {
            // Get all theme radio buttons
            const themeRadios = document.querySelectorAll('input[name="theme"]');

            // Set current theme on page load
            const currentTheme = window.themeManager.getTheme();
            console.log('Page loaded - Current theme:', currentTheme);

            themeRadios.forEach(radio => {
                if (radio.value === currentTheme) {
                    radio.checked = true;
                }
            });

            // Listen for theme changes
            themeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        console.log('=== USER SELECTED THEME:', this.value, '===');

                        // Apply theme immediately
                        window.themeManager.setTheme(this.value);

                        // Force a page reload after a short delay to ensure clean state
                        setTimeout(function() {
                            console.log('Reloading page to apply theme cleanly...');
                            window.location.reload();
                        }, 300);

                        // Force a small delay to ensure the DOM updates
                        setTimeout(function() {
                            const html = document.documentElement;
                            console.log('Dark class present:', html.classList.contains('dark'));
                        }, 100);
                    }
                });
            });
        });
    </script>
</body>

</html>