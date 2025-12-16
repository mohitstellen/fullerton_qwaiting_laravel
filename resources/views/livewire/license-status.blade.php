<div wire:poll.10s="checkLicense" class="text-sm" x-data="{
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
        <div class="flex items-center space-x-2">
            <span>License for: <strong>{{ $client }}</strong></span>

            <span :class="{
                    'text-red-600 font-bold': remaining <= 10,
                    'text-green-600': remaining > 10
                }" x-text="remaining <= 0 ? '(Expired)' : '(' + formatTime(remaining) + ')'">
            </span>
        </div>
    @else
        <div class="text-red-600 font-bold">License Invalid or Expired</div>
    @endif
</div>