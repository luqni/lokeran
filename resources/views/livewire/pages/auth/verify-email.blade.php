<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-col items-center text-center">
        <!-- Envelope/Mail Icon -->
        <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mb-4 border border-red-100 shadow-inner">
            <svg class="w-8 h-8 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 19v-8.93a2 2 0 01.89-1.664l8-4.666a2 2 0 012.22 0l8 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-2.25-1.5a2 2 0 00-2.22 0l-2.25 1.5"></path>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Verifikasi Email Anda</h2>
        <p class="mt-2 text-sm text-gray-600 leading-relaxed px-2">
            Terima kasih telah mendaftar! Sebelum memulai, silakan klik tautan verifikasi yang baru saja kami kirimkan ke alamat email Anda.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="p-4 rounded-2xl bg-green-50 border border-green-200 flex items-start gap-3 text-green-800 shadow-sm animate-pulse">
            <svg class="w-5 h-5 flex-shrink-0 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-xs font-semibold leading-relaxed">
                Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda daftarkan. Silakan periksa folder Inbox atau Spam Anda.
            </div>
        </div>
    @endif

    <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
        <button wire:click="sendVerification" wire:loading.attr="disabled" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-semibold rounded-2xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100 shadow-md shadow-red-600/10 hover:shadow-lg transition-all duration-300 gap-2 disabled:opacity-50">
            <!-- Normal State -->
            <span wire:loading.remove wire:target="sendVerification" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
                Kirim Ulang Email
            </span>
            
            <!-- Loading State -->
            <span wire:loading wire:target="sendVerification" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mengirim...
            </span>
        </button>

        <button wire:click="logout" type="submit" class="text-sm font-semibold text-gray-500 hover:text-red-600 hover:underline transition duration-300 py-2">
            Keluar Akun
        </button>
    </div>
</div>

