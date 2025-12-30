<x-filament-panels::page.simple>
    <div class="text-center">
        <div class="mb-4">
            <p class="text-lg">تم إرسال رسالة تأكيد إلى بريدك الإلكتروني <strong>{{ auth()->user()->email }}</strong></p>
            <p class="mt-2">يرجى التحقق من صندوق الوارد الخاص بك والنقر على رابط التأكيد.</p>
        </div>

        <div class="mt-6">
            <x-filament::button wire:click="resendEmailVerification" color="primary">
                إعادة إرسال رسالة التأكيد
            </x-filament::button>
        </div>

        <div class="mt-4">
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <x-filament::button type="submit" color="gray">
                    تسجيل الخروج
                </x-filament::button>
            </form>
        </div>
    </div>
</x-filament-panels::page.simple>
