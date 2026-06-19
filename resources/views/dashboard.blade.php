<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">

        @php
            $totalInvoices   = \App\Models\Invoice::count();
            $approvedCount   = \App\Models\Invoice::where('status', 'approve')->count();
            $pendingCount    = \App\Models\Invoice::where('status', 'pending')->count();
            $approvedAmount  = \App\Models\Invoice::where('status', 'approve')->sum('total_amount');
            $pendingAmount   = \App\Models\Invoice::where('status', 'pending')->sum('total_amount');
            $approvedPercent = $totalInvoices > 0 ? round(($approvedCount / $totalInvoices) * 100) : 0;
            $pendingPercent  = $totalInvoices > 0 ? round(($pendingCount / $totalInvoices) * 100) : 0;
            $totalMitras     = \App\Models\Mitra::count();

            $recentInvoices  = \App\Models\Invoice::with('mitra')->latest()->limit(5)->get();
            $recentDocuments = \App\Models\DraftDocument::latest()->limit(5)->get();
            $recentMitras    = \App\Models\Mitra::latest()->limit(5)->get();
        @endphp

        {{-- Welcome Section --}}
        <!-- <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-900 shadow-sm">
            <div class="relative z-10">
                <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mb-1">Koperasi Sehat Mulia</p>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">
                    Selamat Datang, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="mt-2 max-w-2xl text-zinc-500 dark:text-zinc-400">
                    Panel kendali terpusat — kelola invoice, mitra bisnis, dan dokumen draft Anda dengan cepat dan aman.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-zinc-900 rounded-lg hover:bg-zinc-800 transition dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Invoice
                    </a>
                    <a href="{{ route('invoice') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-zinc-700 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                        Lihat Invoice
                    </a>
                    <a href="{{ route('draft-documents.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-zinc-700 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        Dokumen Draft
                    </a>
                    <a href="{{ route('mitras.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-zinc-700 bg-zinc-100 rounded-lg hover:bg-zinc-200 transition dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                        Data Mitra
                    </a>
                </div>
            </div>
            <div class="absolute -right-10 -top-10 size-48 rounded-full bg-green-500/10 blur-3xl"></div>
            <div class="absolute -bottom-10 right-20 size-36 rounded-full bg-blue-500/10 blur-3xl"></div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="relative overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Invoice</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">{{ number_format($totalInvoices) }}</p>
                        <p class="mt-1 text-xs text-zinc-400">Semua invoice tercatat</p>
                    </div>
                    <div class="p-2.5 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-zinc-600 dark:text-zinc-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-green-100 dark:border-green-900/40 bg-white dark:bg-zinc-900 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Approved</p>
                        <p class="mt-2 text-xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($approvedAmount, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-zinc-400">{{ $approvedCount }} invoice disetujui</p>
                    </div>
                    <div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-green-600 dark:text-green-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-green-500/20 rounded-b-xl">
                    <div class="h-full bg-green-500 rounded-b-xl" style="width: {{ $approvedPercent }}%"></div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-amber-100 dark:border-amber-900/40 bg-white dark:bg-zinc-900 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Pending</p>
                        <p class="mt-2 text-xl font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($pendingAmount, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-zinc-400">{{ $pendingCount }} invoice menunggu</p>
                    </div>
                    <div class="p-2.5 bg-amber-50 dark:bg-amber-900/30 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-amber-600 dark:text-amber-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-500/20 rounded-b-xl">
                    <div class="h-full bg-amber-500 rounded-b-xl" style="width: {{ $pendingPercent }}%"></div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl border border-blue-100 dark:border-blue-900/40 bg-white dark:bg-zinc-900 shadow-sm p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Mitra</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalMitras) }}</p>
                        <p class="mt-1 text-xs text-zinc-400">Mitra bisnis terdaftar</p>
                    </div>
                    <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-blue-600 dark:text-blue-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Recent Invoices --}}
            <div class="lg:col-span-2 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 shadow-sm">
                <div class="flex items-center justify-between p-5 border-b border-neutral-200 dark:border-neutral-700">
                    <div>
                        <h2 class="text-base font-bold text-zinc-900 dark:text-white">Invoice Terbaru</h2>
                        <p class="text-xs text-zinc-400 mt-0.5">5 invoice paling baru</p>
                    </div>
                    <a href="{{ route('invoice') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition flex items-center gap-1">
                        Lihat Semua
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
                <div class="overflow-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-neutral-200 dark:border-neutral-700">
                            <tr>
                                <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Invoice No</th>
                                <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Mitra</th>
                                <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Status</th>
                                <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($recentInvoices as $invoice)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</p>
                                        <p class="text-[10px] text-zinc-400">{{ $invoice->invoice_date->format('d M Y') }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">{{ $invoice->mitra->company_name }}</td>
                                    <td class="py-3 px-4">
                                        @if($invoice->status === 'approve')
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-[10px] font-bold text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-400/10 dark:text-green-400">APPROVE</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 ring-1 ring-inset ring-amber-600/10 dark:bg-amber-400/10 dark:text-amber-400">PENDING</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-medium text-right text-zinc-900 dark:text-zinc-100">
                                        Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="flex flex-col gap-6">

                {{-- Status Breakdown --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 shadow-sm p-5">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-white mb-1">Status Invoice</h2>
                    <p class="text-xs text-zinc-400 mb-4">Distribusi status keseluruhan</p>
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 flex items-center gap-1.5">
                                    <span class="size-2 rounded-full bg-green-500 inline-block"></span>
                                    Approved
                                </span>
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">{{ $approvedCount }}</span>
                            </div>
                            <div class="h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" style="width: {{ $approvedPercent }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 flex items-center gap-1.5">
                                    <span class="size-2 rounded-full bg-amber-500 inline-block"></span>
                                    Pending
                                </span>
                                <span class="text-xs font-bold text-zinc-900 dark:text-white">{{ $pendingCount }}</span>
                            </div>
                            <div class="h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-500 rounded-full" style="width: {{ $pendingPercent }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                        <span class="text-xs text-zinc-400">Total semua invoice</span>
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">{{ number_format($totalInvoices) }}</span>
                    </div>
                </div>

                {{-- Recent Documents --}}
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 shadow-sm p-5 flex-1">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-bold text-zinc-900 dark:text-white">Draft Dokumen</h2>
                            <p class="text-xs text-zinc-400 mt-0.5">Dokumen terbaru</p>
                        </div>
                        <a href="{{ route('draft-documents.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition flex items-center gap-1">
                            Semua
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                    <div class="space-y-1">
                        @foreach ($recentDocuments as $doc)
                            @php $isPdf = str_contains($doc->mime_type, 'pdf'); @endphp
                            <div class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                <div class="p-2 rounded-lg {{ $isPdf ? 'bg-red-50 dark:bg-red-900/20' : 'bg-blue-50 dark:bg-blue-900/20' }} shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 {{ $isPdf ? 'text-red-500' : 'text-blue-500' }}">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $doc->name }}</p>
                                    <p class="text-[10px] text-zinc-400">{{ $doc->date->format('d M Y') }}</p>
                                </div>
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded shrink-0 {{ $isPdf ? 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400' : 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' }}">
                                    {{ $isPdf ? 'PDF' : 'DOCX' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Mitras --}}
        <div class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 shadow-sm">
            <div class="flex items-center justify-between p-5 border-b border-neutral-200 dark:border-neutral-700">
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-white">Mitra Terdaftar</h2>
                    <p class="text-xs text-zinc-400 mt-0.5">5 mitra paling baru</p>
                </div>
                <a href="{{ route('mitras.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:hover:text-white transition flex items-center gap-1">
                    Lihat Semua
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
            <div class="overflow-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800/50 border-b border-neutral-200 dark:border-neutral-700">
                        <tr>
                            <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Perusahaan</th>
                            <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Kontak</th>
                            <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Email</th>
                            <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider">Telepon</th>
                            <th class="py-2.5 px-4 text-[10px] font-medium text-zinc-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($recentMitras as $mitra)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="size-8 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-bold text-zinc-500 uppercase border border-zinc-200 dark:border-zinc-700 shrink-0">
                                            {{ substr($mitra->company_name, 0, 1) }}
                                        </div>
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $mitra->company_name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">{{ $mitra->contact_person }}</td>
                                <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">{{ $mitra->email }}</td>
                                <td class="py-3 px-4 text-zinc-600 dark:text-zinc-400">{{ $mitra->phone }}</td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('mitras.show', $mitra->id) }}" class="p-1 text-zinc-400 hover:text-green-600 dark:hover:text-green-400 transition" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div> -->

    </div>
</x-layouts::app>