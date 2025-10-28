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
    <div class="min-h-screen bg-gray-100 font-['Montserrat']">
        <div class="flex">
            <div class="flex flex-col items-center justify-center lg:w-1/3">
                <div class="flex flex-col w-full p-16">
                    <div class="flex flex-col items-start justify-center w-full">
                        <a class="flex flex-col items-start text-2xl font-bold" href="home.php">
                            <img class="w-16 mb-10" src="../assets/images/logo.png" alt="Logo">
                            Create your new account
                        </a>
                        <div class="flex items-center mt-4">
                            <p class="text-sm text-gray-600">Already have an account?
                                <a href="signin.php">
                                    <span class="font-medium text-indigo-600 hover:text-indigo-700">Sign in</span>
                                </a>
                            </p>
                        </div>
                    </div>
                    <form action="#" method="POST" class="w-full mt-10">
                        <div class="flex flex-col w-full h-max ">
                            <div class="grid w-full grid-cols-2 gap-4 mb-6">
                                <div class="flex flex-col">
                                    <label for="first_name" class="mb-2 font-medium text-gray-700">First Name</label>
                                    <input type="text" id="first_name" name="first_name" required
                                        class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Juan">
                                </div>
                                <div class="flex flex-col">
                                    <label for="last_name" class="mb-2 font-medium text-gray-700">Last Name</label>
                                    <input type="text" id="last_name" name="last_name" required
                                        class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="Dela Cruz">
                                </div>
                            </div>
                            <label for="email" class="mb-2 font-medium text-gray-700">Email address</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-2 mb-6 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="juandelacruz@gmail.com">
                            <div class="flex flex-col mb-6">
                                <label for="password" class="mb-2 font-medium text-gray-700">Password</label>
                                <input type="password" id="password" name="password" required
                                    class="w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <p class="mt-2 text-sm text-gray-500">Password should be at least 15 characters OR at
                                    least
                                    8
                                    characters
                                    including a number and
                                    a lowercase letter. </p>
                            </div>
                            <label for="name" class="mb-2 font-medium text-gray-700">Re-enter Password</label>
                            <input type="password2" id="password2" name="password2" required
                                class="w-full px-4 py-2 mb-6 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <button type="submit"
                                class="px-4 py-2 font-medium text-white transition-all bg-indigo-500 rounded-lg cursor-pointer hover:bg-indigo-600">Sign
                                up</button>
                        </div>
                    </form>
                </div>
            </div>
            <img src="../assets/images/signup.png" alt="Side Image" class="hidden object-cover w-2/3 h-screen lg:block">
        </div>
    </div>
</body>

</html>