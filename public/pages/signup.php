<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up to Gatherly | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css?v=<?php echo filemtime(__DIR__ . '/../../src/output.css'); ?>">
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

<body>
    <?php
    session_start();

    // Redirect to appropriate dashboard if already logged in
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'administrator':
                header("Location: admin/admin-dashboard.php");
                exit();
            case 'organizer':
                header("Location: organizer/organizer-dashboard.php");
                exit();
            case 'manager':
                header("Location: manager/manager-dashboard.php");
                exit();
            case 'supplier':
                header("Location: supplier/supplier-dashboard.php");
                exit();
            default:
                header("Location: ../../index.php");
                exit();
        }
    }

    // Get success or error messages from URL parameters
    $success = $_GET['success'] ?? '';
    $error = $_GET['error'] ?? '';
    ?>
    <div class="min-h-screen bg-linear-to-br from-indigo-50 via-white to-purple-50 font-['Montserrat']">
        <div class="flex flex-col min-h-screen lg:flex-row">
            <!-- Form Section -->
            <div class="flex flex-col items-center justify-center w-full p-4 lg:w-5/12 sm:p-8">
                <div class="flex flex-col w-full max-w-xl">
                    <!-- Header -->
                    <div class="flex flex-col items-start justify-center w-full">
                        <div class="flex flex-col items-start text-xl font-bold sm:text-2xl">
                            <a class="flex flex-col group" href="index.php">
                                <img class="w-12 mb-6 transition-transform sm:w-16 sm:mb-10 drop-shadow-lg group-hover:scale-110"
                                    src="../assets/images/logo.png" alt="Logo">
                                <span class="text-gray-800">Create your new account</span>
                            </a>
                        </div>
                        <div class="flex items-center mt-3 sm:mt-4">
                            <p class="text-xs text-gray-600 sm:text-sm">Already have an account?
                                <a href="signin.php">
                                    <span
                                        class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700 hover:underline">Sign
                                        in</span>
                                </a>
                            </p>
                        </div>
                    </div>
                    <!-- Signup Form -->
                    <form action="../../src/services/signup-handler.php" method="POST" class="w-full mt-6 sm:mt-10">
                        <div class="flex flex-col w-full">
                            <!-- Name Fields -->
                            <div class="grid w-full grid-cols-1 gap-4 mb-5 sm:grid-cols-2">
                                <div class="flex flex-col">
                                    <label for="first_name" class="mb-2 text-sm font-semibold text-gray-700">First
                                        Name</label>
                                    <input type="text" id="first_name" name="first_name" required
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Juan">
                                </div>
                                <div class="flex flex-col">
                                    <label for="last_name" class="mb-2 text-sm font-semibold text-gray-700">Last
                                        Name</label>
                                    <input type="text" id="last_name" name="last_name" required
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Dela Cruz">
                                </div>
                            </div>
                            <!-- Username Field -->
                            <label for="username" class="mb-2 text-sm font-semibold text-gray-700">Username</label>
                            <input type="text" id="username" name="username" required
                                class="w-full px-4 py-2.5 mb-5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                placeholder="juandelacruz123">
                            <!-- Role Field -->
                            <label for="role" class="mb-2 text-sm font-semibold text-gray-700">Role</label>
                            <select id="role" name="role" required
                                class="w-full px-4 py-2.5 mb-5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <option value="" disabled selected>Select your role</option>
                                <option value="manager">Manager</option>
                                <option value="organizer">Organizer</option>
                            </select>
                            <!-- Email Field -->
                            <div class="">
                                <div class="flex flex-col">
                                    <label for="email" class="mb-2 text-sm font-semibold text-gray-700">Email
                                        address</label>
                                    <input type="email" id="email" name="email" required
                                        class="w-full px-4 py-2.5 mb-5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="juandelacruz@gmail.com">
                                </div>
                            </div>
                            <!-- Phone Field -->
                            <label for="phone" class="mb-2 text-sm font-semibold text-gray-700">Contact Number</label>
                            <input type="tel" id="phone" name="phone" required
                                class="w-full px-4 py-2.5 mb-5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                placeholder="09171234567" pattern="[0-9]{11}"
                                title="Please enter a valid 11-digit phone number">
                            <!-- Password Fields -->
                            <div class="grid w-full grid-cols-1 gap-4 mb-4 sm:grid-cols-2">
                                <div class="flex flex-col">
                                    <label for="password"
                                        class="mb-2 text-sm font-semibold text-gray-700">Password</label>
                                    <input type="password" id="password" name="password" required
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                </div>
                                <div class="flex flex-col">
                                    <label for="password2" class="mb-2 text-sm font-semibold text-gray-700">Re-enter
                                        Password</label>
                                    <input type="password" id="password2" name="password2" required
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                </div>
                            </div>
                            <p class="mb-6 text-xs leading-relaxed text-gray-500 sm:text-sm">Password should be at
                                least 15 characters OR at least 8 characters including a number and a lowercase
                                letter.</p>

                            <button type="submit"
                                class="cursor-pointer w-full px-4 py-3 font-semibold text-white transition-all bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">Sign
                                up</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Image Section -->
            <div class="relative hidden overflow-hidden lg:block lg:w-7/12">
                <img src="../assets/images/signup.png" alt="Side Image"
                    class="absolute inset-0 object-cover w-full h-full">
                <div class="absolute inset-0 bg-linear-to-br from-indigo-600/20 to-purple-600/20"></div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 hidden" onclick="if(event.target === this) closeSuccessModal()">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
                <div class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 bg-green-100 rounded-full">
                        <i class="text-3xl text-green-600 fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold text-gray-800">Success!</h3>
                    <p id="successMessage" class="mb-6 text-gray-600">Your account has been created successfully.</p>
                    <div class="flex gap-3">
                        <button onclick="closeSuccessModal()"
                            class="px-6 py-2 text-gray-700 transition-colors bg-gray-200 rounded-lg hover:bg-gray-300">
                            Close
                        </button>
                        <a href="signin.php"
                            class="px-6 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                            <i class="mr-2 fas fa-sign-in-alt"></i>Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 z-50 hidden" onclick="if(event.target === this) closeErrorModal()">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
                <div class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-16 h-16 mb-4 bg-red-100 rounded-full">
                        <i class="text-3xl text-red-600 fas fa-exclamation-circle"></i>
                    </div>
                    <h3 class="mb-2 text-2xl font-bold text-gray-800">Error</h3>
                    <p id="errorMessage" class="mb-6 text-gray-600">Something went wrong. Please try again.</p>
                    <button onclick="closeErrorModal()"
                        class="px-6 py-2 text-white transition-colors bg-red-600 rounded-lg hover:bg-red-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Success modal functions
        function openSuccessModal(message) {
            document.getElementById('successMessage').textContent = message;
            const modal = document.getElementById('successModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSuccessModal() {
            const modal = document.getElementById('successModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Error modal functions
        function openErrorModal(message) {
            document.getElementById('errorMessage').textContent = message;
            const modal = document.getElementById('errorModal');
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeErrorModal() {
            const modal = document.getElementById('errorModal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Close modals on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSuccessModal();
                closeErrorModal();
            }
        });

        // Check for messages on page load
        window.addEventListener('DOMContentLoaded', function() {
            <?php if ($success): ?>
                openSuccessModal(<?php echo json_encode($success); ?>);
            <?php endif; ?>

            <?php if ($error): ?>
                openErrorModal(<?php echo json_encode($error); ?>);
            <?php endif; ?>
        });
    </script>
</body>

</html>