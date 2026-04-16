<div wire:poll.2s="refreshAvailability" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($timeSlot['programable']['title'])): ?>
                        <?php echo e($timeSlot['programable']['title']); ?>

                    <?php elseif(!empty($timeSlot['programable_title'])): ?>
                        <?php echo e($timeSlot['programable_title']); ?>

                    <?php else: ?>
                        Event Booking
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($timeSlot['starts_at'])): ?>
                    <p class="mt-1 text-sm text-gray-500">
                        <?php echo e(\Carbon\Carbon::parse($timeSlot['starts_at'])->format('l, F j, Y')); ?>

                        &mdash;
                        <?php echo e(\Carbon\Carbon::parse($timeSlot['starts_at'])->format('g:i A')); ?> to <?php echo e(\Carbon\Carbon::parse($timeSlot['ends_at'] ?? $timeSlot['starts_at'])->format('g:i A')); ?>

                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="text-right">
                <?php
                    $programableType = $timeSlot['programable_type'] ?? '';
                    $isCampaign = str_contains($programableType, 'Campaign');
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    <?php echo e($isCampaign ? 'bg-indigo-100 text-indigo-800' : 'bg-teal-100 text-teal-800'); ?>

                ">
                    <?php echo e($isCampaign ? 'Campaign' : 'Venue Program'); ?>

                </span>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
        <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800"><?php echo e($errorMessage); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confirmationNumber): ?>
        <div class="mb-6 rounded-md bg-green-50 border border-green-200 p-6 text-center">
            <svg class="mx-auto h-10 w-10 text-green-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <h2 class="text-lg font-semibold text-green-800">Booking Confirmed</h2>
            <p class="mt-2 text-3xl font-mono font-bold text-green-900"><?php echo e($confirmationNumber); ?></p>
            <p class="mt-1 text-sm text-green-700">Save this confirmation number for your records.</p>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Seat Availability</h2>

        <?php
            $pct = $this->availabilityPercent;
            if ($pct > 50) {
                $colorClass = 'text-green-600';
                $bgClass = 'bg-green-500';
            } elseif ($pct > 20) {
                $colorClass = 'text-yellow-600';
                $bgClass = 'bg-yellow-500';
            } else {
                $colorClass = 'text-red-600';
                $bgClass = 'bg-red-500';
            }
        ?>

        <div class="text-center mb-6">
            <p class="text-5xl font-bold <?php echo e($colorClass); ?>"><?php echo e($availableSeats); ?></p>
            <p class="text-lg text-gray-500 mt-1">of <?php echo e($totalCapacity); ?> seats available</p>
        </div>

        
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
            <?php
                $bookedPct = $totalCapacity > 0 ? (($seatsBooked / $totalCapacity) * 100) : 0;
                $lockedOthersPct = $totalCapacity > 0 ? (($lockedByOthers / $totalCapacity) * 100) : 0;
                $myLockedPct = $totalCapacity > 0 && $myLock ? (($myLock['quantity'] / $totalCapacity) * 100) : 0;
            ?>
            <div class="flex h-4 rounded-full overflow-hidden">
                <div class="bg-gray-400 transition-all duration-300" style="width: <?php echo e($bookedPct); ?>%"></div>
                <div class="bg-blue-400 transition-all duration-300" style="width: <?php echo e($myLockedPct); ?>%"></div>
                <div class="bg-yellow-400 transition-all duration-300" style="width: <?php echo e($lockedOthersPct); ?>%"></div>
                <div class="bg-green-400 flex-1"></div>
            </div>
        </div>

        
        <div class="flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="inline-block w-4 h-4 rounded bg-green-400"></span>
                <span class="text-gray-600">Available</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-4 h-4 rounded bg-blue-400"></span>
                <span class="text-gray-600">Held by you</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-4 h-4 rounded bg-yellow-400"></span>
                <span class="text-gray-600">Held by others</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-4 h-4 rounded bg-gray-400"></span>
                <span class="text-gray-600">Booked</span>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lockedByOthers > 0): ?>
            <p class="mt-3 text-sm text-yellow-700 bg-yellow-50 rounded px-3 py-2">
                <?php echo e($lockedByOthers); ?> <?php echo e($lockedByOthers === 1 ? 'seat' : 'seats'); ?> held by other users.
            </p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="bg-white rounded-lg shadow p-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$myLock): ?>
            
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Reserve Your Seats</h2>

            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Number of Seats</label>
                    <input
                        type="number"
                        id="quantity"
                        wire:model="quantity"
                        min="1"
                        max="<?php echo e($availableSeats); ?>"
                        class="block w-full sm:w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                    >
                </div>

                <button
                    wire:click="lockSeats"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    <?php if($availableSeats < 1): ?> disabled <?php endif; ?>
                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="lockSeats">Lock Seats</span>
                    <span wire:loading wire:target="lockSeats" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($availableSeats < 1): ?>
                <p class="mt-3 text-sm text-red-600">No seats currently available. Please wait or try again later.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php else: ?>
            
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Your Seats Are Locked</h2>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="text-blue-800 font-medium">
                            <?php echo e($myLock['quantity']); ?> <?php echo e($myLock['quantity'] === 1 ? 'seat' : 'seats'); ?> reserved
                        </p>
                        <p class="text-sm text-blue-600 mt-1">
                            Time remaining:
                            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('booking.seat-lock-timer', [
                                'lockId' => $myLock['id'],
                                'expiresAt' => $myLock['locked_until'],
                            ]);

$__key = 'timer-' . $myLock['id'];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1088744111-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                        </p>
                    </div>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                Your seats are temporarily held. Please confirm your booking before the timer expires, or release the lock.
            </p>

            <div class="flex flex-col sm:flex-row gap-3">
                <button
                    wire:click="confirmBooking"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    <span wire:loading.remove wire:target="confirmBooking">Confirm Booking</span>
                    <span wire:loading wire:target="confirmBooking" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>

                <button
                    wire:click="releaseLock"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="inline-flex items-center justify-center px-6 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    <span wire:loading.remove wire:target="releaseLock">Release Lock</span>
                    <span wire:loading wire:target="releaseLock" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Processing...
                    </span>
                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

</div>
<?php /**PATH /var/www/html/resources/views/livewire/booking/seat-map.blade.php ENDPATH**/ ?>