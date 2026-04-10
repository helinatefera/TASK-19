<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Campaigns</h1>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('api_user')): ?>
            <a href="<?php echo e(route('campaigns.create')); ?>" wire:navigate
               class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                + New Campaign
            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search campaigns..."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border"
            />
        </div>
        <div class="w-full sm:w-48">
            <select
                wire:model.live="statusFilter"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-2 border"
            >
                <option value="">All Statuses</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($status); ?>"><?php echo e(ucwords(str_replace('_', ' ', $status))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($campaigns)): ?>
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">No campaigns found.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $targetAmount = $campaign['target_amount'] ?? 0;
                    $pledgedAmount = $campaign['pledged_amount'] ?? 0;
                    $progress = $targetAmount > 0
                        ? min(100, round(($pledgedAmount / $targetAmount) * 100, 1))
                        : 0;
                    $endsAt = $campaign['ends_at'] ?? null;
                    $daysRemaining = null;
                    $ended = false;
                    if ($endsAt) {
                        $endsAtDate = \Carbon\Carbon::parse($endsAt);
                        if ($endsAtDate->isFuture()) {
                            $daysRemaining = (int) now()->diffInDays($endsAtDate, false);
                        } else {
                            $ended = true;
                        }
                    }
                    $statusValue = $campaign['status'] ?? 'draft';
                ?>
                <a href="<?php echo e(route('campaigns.detail', ['campaignId' => $campaign['id']])); ?>" wire:navigate
                   class="block bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
                    <div class="p-5">
                        
                        <div class="flex items-start justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 leading-tight line-clamp-2">
                                <?php echo e($campaign['title']); ?>

                            </h3>
                            <span class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php switch($statusValue):
                                    case ('draft'): ?> bg-gray-100 text-gray-800 <?php break; ?>
                                    <?php case ('pending_review'): ?> bg-yellow-100 text-yellow-800 <?php break; ?>
                                    <?php case ('published'): ?> bg-blue-100 text-blue-800 <?php break; ?>
                                    <?php case ('fundraising'): ?> bg-green-100 text-green-800 <?php break; ?>
                                    <?php case ('success'): ?> bg-emerald-100 text-emerald-800 <?php break; ?>
                                    <?php case ('failure'): ?> bg-red-100 text-red-800 <?php break; ?>
                                    <?php case ('closed'): ?> bg-gray-100 text-gray-600 <?php break; ?>
                                <?php endswitch; ?>
                            ">
                                <?php echo e(ucwords(str_replace('_', ' ', $statusValue))); ?>

                            </span>
                        </div>

                        
                        <p class="text-sm text-gray-500 mb-3">
                            by <?php echo e($campaign['creator']['display_name'] ?? $campaign['creator']['username'] ?? 'Unknown'); ?>

                        </p>

                        
                        <div class="mb-2">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>$<?php echo e(number_format($pledgedAmount / 100, 2)); ?></span>
                                <span>$<?php echo e(number_format($targetAmount / 100, 2)); ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300"
                                     style="width: <?php echo e($progress); ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><?php echo e($progress); ?>% funded</p>
                        </div>

                        
                        <div class="text-sm text-gray-500">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($daysRemaining !== null): ?>
                                <span><?php echo e($daysRemaining); ?> <?php echo e(Str::plural('day', $daysRemaining)); ?> remaining</span>
                            <?php elseif($ended): ?>
                                <span class="text-red-600">Ended</span>
                            <?php else: ?>
                                <span>Not yet started</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($meta['last_page'] ?? 1) > 1): ?>
            <div class="mt-8 flex items-center justify-center gap-2">
                <button wire:click="previousPage"
                        <?php if($page <= 1): ?> disabled <?php endif; ?>
                        class="px-3 py-1 text-sm border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Previous
                </button>
                <span class="text-sm text-gray-600">
                    Page <?php echo e($meta['current_page'] ?? $page); ?> of <?php echo e($meta['last_page'] ?? 1); ?>

                </span>
                <button wire:click="nextPage"
                        <?php if($page >= ($meta['last_page'] ?? 1)): ?> disabled <?php endif; ?>
                        class="px-3 py-1 text-sm border rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/campaign/campaign-list.blade.php ENDPATH**/ ?>