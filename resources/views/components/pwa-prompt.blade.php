<div id="pwa-prompt" class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 p-5 transform transition-transform translate-y-full opacity-0 z-50 duration-500">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-bold text-gray-900" id="prompt-title">Dapatkan Notifikasi Loker</h3>
            <p class="text-xs text-gray-500 mt-1" id="prompt-desc">Install aplikasi ini dan aktifkan notifikasi untuk mendapat info loker terbaru sesuai skill Anda.</p>
            <div class="mt-4 flex gap-2">
                <button id="pwa-action-btn" class="px-4 py-2 bg-red-600 text-white text-xs font-bold rounded-xl hover:bg-red-700 transition-all shadow-md hover:shadow-lg">Aktifkan</button>
                <button id="pwa-close-btn" class="px-4 py-2 bg-gray-50 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-100 transition-colors border border-gray-200">Nanti Saja</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const prompt = document.getElementById('pwa-prompt');
        const actionBtn = document.getElementById('pwa-action-btn');
        const closeBtn = document.getElementById('pwa-close-btn');
        const title = document.getElementById('prompt-title');
        const desc = document.getElementById('prompt-desc');
        
        let deferredPrompt;
        let isPwaSupported = false;
        
        // Listen to install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            isPwaSupported = true;
            checkAndShowPrompt();
        });

        // Check dismiss status
        const promptDismissed = localStorage.getItem('pwa_prompt_dismissed');

        function checkAndShowPrompt() {
            if (promptDismissed) return;
            
            if (isPwaSupported && deferredPrompt) {
                title.innerText = "Install Aplikasi Loker";
                desc.innerText = "Install Loker Merah Putih di perangkat Anda untuk akses lebih cepat dan ringan.";
                actionBtn.innerText = "Install Sekarang";
                showPrompt();
                
                actionBtn.onclick = async () => {
                    hidePrompt();
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        deferredPrompt = null;
                        setTimeout(checkNotifPrompt, 2000);
                    }
                };
            } else {
                checkNotifPrompt();
            }
        }
        
        function checkNotifPrompt() {
            if (promptDismissed) return;
            // Show only if subscribe func is available (authenticated layout)
            if (typeof subscribeToPushNotifications === 'function') {
                if ('Notification' in window && Notification.permission === 'default') {
                    title.innerText = "Jangan Ketinggalan Info!";
                    desc.innerText = "Aktifkan notifikasi untuk langsung mendapat loker terbaru sesuai keahlian Anda.";
                    actionBtn.innerText = "Aktifkan Notif";
                    
                    actionBtn.onclick = () => {
                        hidePrompt();
                        subscribeToPushNotifications();
                    };
                    
                    showPrompt();
                }
            }
        }
        
        function showPrompt() {
            setTimeout(() => {
                prompt.classList.remove('translate-y-full', 'opacity-0');
            }, 100);
        }
        
        function hidePrompt() {
            prompt.classList.add('translate-y-full', 'opacity-0');
        }
        
        closeBtn.onclick = () => {
            hidePrompt();
            localStorage.setItem('pwa_prompt_dismissed', 'true');
        };

        // Delay checking to avoid intrusive popup right on load
        setTimeout(() => {
            if (!isPwaSupported) {
                checkNotifPrompt();
            }
        }, 3000);
    });
</script>
