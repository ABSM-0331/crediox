<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión</title>
    <?php
    $baseUrl = '/panel/public';
    ?>
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('gp_theme');
                if (savedTheme === 'light' || savedTheme === 'dark') {
                    document.documentElement.setAttribute('data-theme', savedTheme);
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/styles.css">
</head>

<?php
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['success'], $_SESSION['error'], $_SESSION['toast']);
?>

<body data-toast-type="<?= htmlspecialchars($toast['type'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-toast-message="<?= htmlspecialchars($toast['message'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <div class="app-container">
        <?php $currentPath = $_SERVER['REQUEST_URI']; ?>
        <?php require  __DIR__ . '/sidebar.php' ?>
        <main class="main-content">
            <?php require __DIR__ . '/header.php' ?>

            <!-- Content Sections -->
            <div class="content-wrapper">
                <?php require $view; ?>
            </div>
        </main>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/toast.js"></script>
    <script src="<?= $baseUrl ?>/assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if ($flashSuccess): ?>
        <script>
            window.showToast(<?= json_encode($flashSuccess) ?>, 'success');
        </script>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <script>
            window.showToast(<?= json_encode($flashError) ?>, 'error');
        </script>
    <?php endif; ?>
    <script>
        if (document.referrer.includes('')) {
            window.history.replaceState(null, null, window.location.href);
        }
        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
            window.history.pushState(null, null, window.location.href);
        });
    </script>
</body>

</html>