<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to Gatherly | Gatherly</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../../src/output.css">
    <script src=" https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous">
    </script>
</head>

<body>
    <?php
    session_start();
    $error = null;
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
    }
    ?>

    <div class="grid grid-rows-[1fr_auto] items-center min-h-screen bg-gray-100 font-['Montserrat']">
        <div class="flex flex-col items-center w-full">
            <a class="flex flex-col items-center text-2xl font-bold" href="home.php">
                <img class="w-16 mb-8" src="../assets/images/logo.png" alt="Logo">
                Sign in to your account
            </a>
            <div class="flex flex-col items-center w-full mt-8">
                <form action="../../src/services/signin-handler.php" method="POST"
                    class="w-full max-w-md px-4 md:max-w-lg">
                    <div class="flex flex-col w-full p-12 bg-white border border-gray-300 rounded-lg h-max">
                        <?php if (!empty($error)): ?>
                        <div class="p-3 mb-4 text-sm text-red-600 border border-red-100 rounded bg-red-50">
                            <?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
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
        <?php include '../../src/components/Footer.php'; ?>
    </div>
</body>

</html>