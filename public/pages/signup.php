<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up to Gatherly | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 font-['Montserrat']">
        <div class="flex flex-col min-h-screen lg:flex-row">
            <!-- Form Section -->
            <div class="flex flex-col items-center justify-center w-full p-4 lg:w-5/12 sm:p-8">
                <div class="flex flex-col w-full max-w-md">
                    <div class="flex flex-col items-start justify-center w-full">
                        <a class="flex flex-col items-start text-xl font-bold transition-transform sm:text-2xl hover:scale-105" href="home.php">
                            <img class="w-12 mb-6 sm:w-16 sm:mb-10 drop-shadow-lg" src="../assets/images/logo.png" alt="Logo">
                            <span class="text-gray-800">Create your new account</span>
                        </a>
                        <div class="flex items-center mt-3 sm:mt-4">
                            <p class="text-xs text-gray-600 sm:text-sm">Already have an account?
                                <a href="signin.php">
                                    <span class="font-semibold text-indigo-600 transition-colors hover:text-indigo-700 hover:underline">Sign in</span>
                                </a>
                            </p>
                        </div>
                    </div>
                    <form action="../../src/services/signup-handler.php" method="POST" class="w-full mt-6 sm:mt-10">
                        <div class="flex flex-col w-full">
                            <div class="grid w-full grid-cols-1 gap-4 mb-5 sm:grid-cols-2">
                                <div class="flex flex-col">
                                    <label for="first_name" class="mb-2 text-sm font-semibold text-gray-700">First Name</label>
                                    <input type="text" id="first_name" name="first_name" required
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Juan">
                                </div>
                                <div class="flex flex-col">
                                    <label for="last_name" class="mb-2 text-sm font-semibold text-gray-700">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" required
                                        class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Dela Cruz">
                                </div>
                            </div>
                            <label for="email" class="mb-2 text-sm font-semibold text-gray-700">Email address</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-2.5 mb-5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                placeholder="juandelacruz@gmail.com">
                            <div class="flex flex-col mb-5">
                                <label for="password" class="mb-2 text-sm font-semibold text-gray-700">Password</label>
                                <input type="password" id="password" name="password" required
                                    class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                <p class="mt-2 text-xs leading-relaxed text-gray-500 sm:text-sm">Password should be at least 15 characters OR at least 8 characters including a number and a lowercase letter.</p>
                            </div>
                            <label for="password2" class="mb-2 text-sm font-semibold text-gray-700">Re-enter Password</label>
                            <input type="password" id="password2" name="password2" required
                                class="w-full px-4 py-2.5 mb-6 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <button type="submit"
                                class="w-full px-4 py-3 font-semibold text-white transition-all bg-indigo-600 rounded-lg shadow-md hover:bg-indigo-700 hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0">Sign up</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Image Section -->
            <div class="relative hidden overflow-hidden lg:block lg:w-7/12">
                <img src="../assets/images/signup.png" alt="Side Image" class="absolute inset-0 object-cover w-full h-full">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-purple-600/20"></div>
            </div>
        </div>
    </div>
</body>

</html>