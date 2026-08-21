@foreach($blocks as $block)
    @switch($block['type'] ?? 'paragraph')
        @case('paragraph')
            <p class="text-base text-slate-700 leading-relaxed mb-5">{!! $block['text'] !!}</p>
            @break
        @case('bullets')
            @if(!empty($block['title']))
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">{{ $block['title'] }}</p>
            @endif
            <ul class="mb-5 space-y-2 text-sm text-gray-700 leading-relaxed list-disc list-inside marker:text-emerald-500">
                @foreach($block['items'] as $item)
                    <li>{!! $item !!}</li>
                @endforeach
            </ul>
            @break
        @case('steps')
            <ol class="mb-5 space-y-3">
                @foreach($block['items'] as $i => $step)
                    <li class="flex gap-3 rounded-xl border border-gray-200 bg-gray-50/50 p-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-xs font-bold text-white">{{ $i + 1 }}</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $step['title'] }}</p>
                            <p class="mt-0.5 text-sm text-gray-600 leading-relaxed">{{ $step['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            @break
        @case('callout')
            @php
                $styles = [
                    'info' => 'border-emerald-200 bg-emerald-50/60 text-emerald-950',
                    'warning' => 'border-amber-200 bg-amber-50/60 text-amber-950',
                    'success' => 'border-emerald-200 bg-emerald-50/60 text-emerald-950',
                    'law' => 'border-teal-200 bg-teal-50/60 text-teal-950',
                ];
                $cls = $styles[$block['style'] ?? 'info'] ?? $styles['info'];
            @endphp
            <div class="mb-4 rounded-xl border px-4 py-3 {{ $cls }}">
                @if(!empty($block['title']))
                    <p class="text-sm font-semibold mb-1 flex items-center gap-2">
                        @if(($block['style'] ?? '') === 'law')
                            <i class="fas fa-scale-balanced text-teal-600"></i>
                        @else
                            <i class="fas fa-circle-info opacity-70"></i>
                        @endif
                        {{ $block['title'] }}
                    </p>
                @endif
                <p class="text-sm leading-relaxed">{!! $block['text'] !!}</p>
            </div>
            @break
        @case('example')
            <div class="mb-4 flex gap-3 rounded-xl border border-amber-200/80 bg-gradient-to-r from-amber-50 to-orange-50/50 px-4 py-3">
                <i class="fas fa-lightbulb text-amber-500 mt-0.5 shrink-0"></i>
                <p class="text-sm text-amber-950 leading-relaxed"><strong class="font-semibold">Example —</strong> {{ $block['text'] }}</p>
            </div>
            @break
        @case('table')
            <div class="mb-5 overflow-x-auto rounded-xl border border-slate-200/80">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            @foreach($block['headers'] as $h)
                                <th class="px-4 py-2.5 font-semibold">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($block['rows'] as $row)
                            <tr class="hover:bg-emerald-50/40">
                                @foreach($row as $cell)
                                    <td class="px-4 py-2.5 text-slate-700">{!! $cell !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @break
        @case('visual')
            @include('guide.partials.screen', ['screen' => $block['screen'] ?? 'dashboard', 'caption' => $block['caption'] ?? null])
            @break
        @case('link')
            @if(Route::has($block['route']))
                <a href="{{ route($block['route']) }}"
                    class="docs-cta-link inline-flex items-center gap-2 mb-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-500/25 hover:from-emerald-700 hover:to-teal-700 transition">
                    <i class="fas fa-arrow-up-right-from-square text-xs text-white/90"></i>
                    {{ $block['label'] ?? 'Open in app' }}
                </a>
            @endif
            @break
    @endswitch
@endforeach
