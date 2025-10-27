<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Gatherly | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="public/assets/images/logo.png">
    <link rel="stylesheet" href="../output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="bg-gray-100 font-['Montserrat']">
        <div class="flex flex-col items-center justify-center min-h-screen px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-center">
                <img src="../../public/assets/images/logo.png" alt="Logo" class="h-16">
                <h1 class="mt-8 text-2xl font-bold">Sign in to your account</h1>
            </div>
            <div class="shadow-lg rounded-xl">
                <form action="#" method="POST"
                    class="w-full max-w-md p-8 mt-8 space-y-6 bg-white rounded-lg shadow-md sm:p-10">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email
                            address</label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full px-4 py-2 font-medium text-white transition-all bg-indigo-600 rounded-lg hover:bg-indigo-700">Sign
                            In</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>