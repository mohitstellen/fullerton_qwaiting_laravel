<div wire:poll.10s="checkLicense" class="flex items-center" x-data="{
         remaining: {{ $remainingSeconds }},
         init() {
             // Reset timer if it exists to avoid duplicates on re-render
             if (this.timer) clearInterval(this.timer);
             
             this.timer = setInterval(() => {
                 if (this.remaining > 0) {
                     this.remaining--;
                 }
                 
                 // Only trigger check if we effectively hit zero
                 if (this.remaining <= 0) {
                     this.remaining = 0;
                     // Trigger immediate check to logout
                     // We add a small delay to ensure server side is also expired
                     setTimeout(() => { $wire.checkLicense(); }, 1000);
                     clearInterval(this.timer);
                 }
             }, 1000);
         },
         formatTime(seconds) {
             if (seconds <= 0) return '00:00:00';
             const d = Math.floor(seconds / (3600*24));
             const h = Math.floor((seconds % (3600*24)) / 3600);
             const m = Math.floor((seconds % 3600) / 60);
             const s = Math.floor(seconds % 60);
             
             if (d > 1) return d + ' days remaining';
             if (d === 1) return '1 day ' + h + 'h remaining';
             
             const pad = (num) => num.toString().padStart(2, '0');
             return pad(h) + ':' + pad(m) + ':' + pad(s);
         }
     }" x-init="init()">
    @if($isValid)
        <div x-show="remaining <= 2592000" 
            :class="{
                'bg-red-50 border-red-300 dark:bg-red-900/20 dark:border-red-600': remaining <= 86400,
                'bg-orange-50 border-orange-300 dark:bg-orange-900/20 dark:border-orange-600': remaining > 86400 && remaining <= 259200,
                'bg-yellow-50 border-yellow-300 dark:bg-yellow-900/20 dark:border-yellow-600': remaining > 259200 && remaining <= 2592000
            }"
            class="flex items-center gap-2 px-3 py-2 border rounded-lg">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">License:</span>
            <span 
                :class="{
                    'text-red-600 dark:text-red-400 font-bold': remaining <= 86400,
                    'text-orange-600 dark:text-orange-400 font-semibold': remaining > 86400 && remaining <= 259200,
                    'text-yellow-700 dark:text-yellow-400 font-semibold': remaining > 259200 && remaining <= 2592000
                }" 
                class="text-sm font-semibold whitespace-nowrap"
                x-text="remaining <= 0 ? 'Expired' : formatTime(remaining)">
            </span>
        </div>
    @else
        <div class="flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-300 rounded-lg dark:bg-red-900/20 dark:border-red-600">
            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <span class="text-sm font-bold text-red-600 dark:text-red-400 whitespace-nowrap">License Invalid or Expired</span>
        </div>
    @endif
</div>