<span
    x-data="{
        expiresAt: new Date('<?php echo e($expiresAt); ?>').getTime(),
        remaining: 0,
        interval: null,
        init() {
            this.tick();
            this.interval = setInterval(() => this.tick(), 1000);
        },
        tick() {
            const now = Date.now();
            this.remaining = Math.max(0, Math.floor((this.expiresAt - now) / 1000));
            if (this.remaining <= 0) {
                clearInterval(this.interval);
                this.$dispatch('lock-expired');
            }
        },
        get minutes() {
            return String(Math.floor(this.remaining / 60)).padStart(2, '0');
        },
        get seconds() {
            return String(this.remaining % 60).padStart(2, '0');
        }
    }"
    x-init="init()"
    class="inline-flex items-center font-mono"
>
    <span
        x-text="minutes + ':' + seconds"
        :class="remaining <= 30 ? 'text-red-600 font-bold' : 'text-blue-700 font-semibold'"
        class="text-lg"
    ></span>
    <span x-show="remaining <= 30" class="ml-2 text-xs text-red-500 font-medium animate-pulse">Expiring soon</span>
</span>
<?php /**PATH /var/www/html/resources/views/livewire/booking/seat-lock-timer.blade.php ENDPATH**/ ?>