<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'CivicCrowd - Login'); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">
        
        <div class="text-center mb-8">
            <a href="<?php echo e(url('/')); ?>" class="text-3xl font-bold text-indigo-600">CivicCrowd</a>
            <p class="mt-2 text-sm text-gray-600">Community Campaigns &amp; Events</p>
        </div>

        
        <div class="bg-white shadow-lg rounded-lg px-8 py-8">
            <?php echo e($slot); ?>

        </div>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH /Users/macbookpro/Projects/eaglepoint/TASK-19/repo/frontend/resources/views/components/layouts/guest.blade.php ENDPATH**/ ?>