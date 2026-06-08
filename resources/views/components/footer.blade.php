<footer class="mt-auto shrink-0 border-t border-gray-200 bg-white">
    <div class="max-w-[1400px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            {{-- Copyright --}}
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="h-3.5 w-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span>&copy; {{ date('Y') }} SIAK-Desa v1.0 &mdash; Pemerintah Desa Kota Baru</span>
            </div>

            {{-- Didukung oleh --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">Didukung oleh</span>
                <a href="https://www.unmaha.ac.id/" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-gray-50 px-2.5 py-1 hover:bg-gray-100 hover:border-gray-300 transition-colors no-underline">
                    <svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                    </svg>
                    <span class="text-xs font-semibold text-gray-600">Universitas Mahakarya Asia</span>
                </a>
            </div>
        </div>
    </div>
</footer>
