<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatherly | An Event Management Platform</title>
    <link rel="icon" type="image/x-icon" href="public/assets/images/logo.png">
    <link rel="stylesheet" href="src/output.css">
    <script src="https://kit.fontawesome.com/2a99de0fa5.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="min-h-screen font-['Montserrat']">
        <?php include 'src/components/Navbar.php'; ?>
        <div class="grid grid-rows-[auto_1fr_auto]">
            <?php include 'src/components/Hero.php'; ?>
            <?php include 'src/components/Features.php'; ?>
            <?php include 'src/components/CallToAction.php'; ?>
            <?php include 'src/components/Footer.php'; ?>
        </div>
    </div>

    <script src="public/assets/js/hero.js"></script>
</body>

</html>