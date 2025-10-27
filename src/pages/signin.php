<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Gatherly | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../../public/assets/images/logo.png">
    <link rel="stylesheet" href="../output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="grid grid-rows-[1fr_auto] items-center min-h-screen bg-gray-100 font-['Montserrat']">
        <div class="flex flex-col items-center">
            <a class="flex flex-col items-center text-2xl font-bold" href="home.php">
                <img class="w-16 mb-8" src="../../public/assets/images/logo.png" alt="Logo">
                Sign in to your account
            </a>
            <div class="flex flex-col items-center mt-8">
                <form action="#" method="POST">
                    <div class="flex flex-col p-12 bg-white border border-gray-300 rounded-lg h-max">
                        <label for="email" class="mb-2 font-medium text-gray-700">Email address</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-2 mb-6 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <label for="password" class="mb-2 font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2 mb-6 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" id="remember" name="remember" class="mr-2" />
                                <label for="remember" class="text-sm font-medium text-gray-600">Remember me</label>
                            </div>
                            <a href="#forgotPass">
                                <span class="text-sm font-medium text-indigo-600 hover:text-indigo-700">Forgot
                                    password?</span>
                            </a>
                        </div>
                        <button type="submit"
                            class="px-4 py-2 font-medium text-white transition-all bg-indigo-500 rounded-lg cursor-pointer hover:bg-indigo-600">Sign
                            in</button>
                        <div class="flex items-center mt-10 font-medium">
                            <span class="w-32 h-px bg-gray-300"></span>
                            <span class="mx-4 text-gray-500">Or continue with</span>
                            <span class="w-32 h-px bg-gray-300"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <button
                                class="flex items-center justify-center px-4 py-2 font-medium transition-all bg-gray-100 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-200">
                                <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 256 256">
                                    <path fill="#1877F2"
                                        d="M256 128C256 57.308 198.692 0 128 0C57.308 0 0 57.307 0 128c0 63.888 46.808 116.843 108 126.445V165H75.5v-37H108V99.8c0-32.08 19.11-49.8 48.347-49.8C170.352 50 185 52.5 185 52.5V84h-16.14C152.958 84 148 93.867 148 103.99V128h35.5l-5.675 37H148v89.445c61.192-9.602 108-62.556 108-126.445" />
                                    <path fill="#FFF"
                                        d="m177.825 165l5.675-37H148v-24.01C148 93.866 152.959 84 168.86 84H185V52.5S170.352 50 156.347 50C127.11 50 108 67.72 108 99.8V128H75.5v37H108v89.445A128.959 128.959 0 0 0 128 256a128.9 128.9 0 0 0 20-1.555V165h29.825" />
                                </svg>
                                Facebook
                            </button>
                            <button
                                class="flex items-center justify-center px-4 py-2 font-medium transition-all bg-gray-100 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-200">
                                <svg class="mr-2" width="24" height="24" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 16 16">
                                    <g fill="none" fill-rule="evenodd" clip-rule="evenodd">
                                        <path fill="#F44336"
                                            d="M7.209 1.061c.725-.081 1.154-.081 1.933 0a6.57 6.57 0 0 1 3.65 1.82a100 100 0 0 0-1.986 1.93q-1.876-1.59-4.188-.734q-1.696.78-2.362 2.528a78 78 0 0 1-2.148-1.658a.26.26 0 0 0-.16-.027q1.683-3.245 5.26-3.86"
                                            opacity=".987" />
                                        <path fill="#FFC107"
                                            d="M1.946 4.92q.085-.013.161.027a78 78 0 0 0 2.148 1.658A7.6 7.6 0 0 0 4.04 7.99q.037.678.215 1.331L2 11.116Q.527 8.038 1.946 4.92"
                                            opacity=".997" />
                                        <path fill="#448AFF"
                                            d="M12.685 13.29a26 26 0 0 0-2.202-1.74q1.15-.812 1.396-2.228H8.122V6.713q3.25-.027 6.497.055q.616 3.345-1.423 6.032a7 7 0 0 1-.51.49"
                                            opacity=".999" />
                                        <path fill="#43A047"
                                            d="M4.255 9.322q1.23 3.057 4.51 2.854a3.94 3.94 0 0 0 1.718-.626q1.148.812 2.202 1.74a6.62 6.62 0 0 1-4.027 1.684a6.4 6.4 0 0 1-1.02 0Q3.82 14.524 2 11.116z"
                                            opacity=".993" />
                                    </g>
                                </svg>
                                Google
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="flex items-center">
                <p class="mt-12 text-sm text-gray-600">Don't have an account?
                    <a href="signup.php">
                        <span class="font-medium text-indigo-600 hover:text-indigo-700">Sign up</span>
                    </a>
                </p>
            </div>
        </div>
        <?php include '../components/Footer.php'; ?>
    </div>
</body>

</html>