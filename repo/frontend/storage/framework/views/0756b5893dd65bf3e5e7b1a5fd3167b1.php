<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <button
            wire:click="markAllAsRead"
            wire:loading.attr="disabled"
            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            Mark all as read
        </button>
    </div>

    
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
            <button
                wire:click="$set('filter', 'all')"
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors <?php echo e($filter === 'all' ? 'bg-white text-gray-900 shadow' : 'text-gray-600 hover:text-gray-900'); ?>"
            >
                All
            </button>
            <button
                wire:click="$set('filter', 'unread')"
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors <?php echo e($filter === 'unread' ? 'bg-white text-gray-900 shadow' : 'text-gray-600 hover:text-gray-900'); ?>"
            >
                Unread
            </button>
            <button
                wire:click="$set('filter', 'read')"
                class="px-4 py-2 text-sm font-medium rounded-md transition-colors <?php echo e($filter === 'read' ? 'bg-white text-gray-900 shadow' : 'text-gray-600 hover:text-gray-900'); ?>"
            >
                Read
            </button>
        </div>

        
        <div>
            <select
                wire:model.live="typeFilter"
                class="block w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm border py-2 px-3"
            >
                <option value="all">All Types</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notificationTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(is_array($type) ? $type['value'] ?? $type : $type); ?>"><?php echo e(ucfirst(is_array($type) ? $type['value'] ?? $type : $type)); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </select>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($notifications)): ?>
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            No notifications found.
        </div>
    <?php else: ?>
        <?php
            $grouped = collect($notifications)->groupBy(fn($n) => $n['type'] ?? 'general');
        ?>

        <div class="space-y-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-1"><?php echo e(ucfirst($type)); ?></h2>
                    <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $group; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-start px-4 py-4 <?php echo e(empty($notification['read_at']) ? 'bg-indigo-50' : ''); ?>">
                                
                                <div class="flex-shrink-0 mr-3 mt-0.5">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($notification['type'] ?? '') === 'alert'): ?>
                                        <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    <?php else: ?>
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm <?php echo e(empty($notification['read_at']) ? 'font-bold text-gray-900' : 'font-medium text-gray-700'); ?>">
                                        <?php echo e($notification['title'] ?? ''); ?>

                                    </p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($notification['body'])): ?>
                                        <p class="text-sm text-gray-600 mt-0.5"><?php echo e($notification['body']); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?php echo e(\Carbon\Carbon::parse($notification['created_at'])->diffForHumans()); ?>

                                        &middot;
                                        <?php echo e(\Carbon\Carbon::parse($notification['created_at'])->format('M d, Y H:i')); ?>

                                    </p>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($notification['read_at'])): ?>
                                    <div class="flex-shrink-0 ml-3">
                                        <button
                                            wire:click="markAsRead('<?php echo e($notification['id']); ?>')"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                                            title="Mark as read"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($meta)): ?>
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Page <?php echo e($meta['current_page'] ?? $page); ?> of <?php echo e($meta['last_page'] ?? 1); ?>

                </div>
                <div class="flex space-x-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($meta['current_page'] ?? 1) > 1): ?>
                        <button wire:click="previousPage" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Previous</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($meta['current_page'] ?? 1) < ($meta['last_page'] ?? 1)): ?>
                        <button wire:click="nextPage" class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Next</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /var/www/html/resources/views/livewire/notification/notification-inbox.blade.php ENDPATH**/ ?>