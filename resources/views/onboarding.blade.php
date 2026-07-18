@extends('layouts.guest')

@section('title', 'พิธีเข้าสู่โลก — Vaelthorn')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=EB+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
<style>
/* ── Stage card transitions ──────────────────────────────────── */
.ob-body {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.55s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.35s ease;
}
.ob-body.is-open {
    max-height: 900px;
    opacity: 1;
}
/* ── Progress track connector line fill ──────────────────────── */
.ob-connector-fill {
    width: 0%;
    transition: width 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}
.ob-connector-fill.filled {
    width: 100%;
}
/* ── Corner ornament base (position only — border set inline) ─── */
.ob-corner { position: absolute; }
/* ── Completion seal slide-in ────────────────────────────────── */
.ob-seal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s ease, transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.ob-seal.is-visible {
    opacity: 1;
    transform: translateY(0);
}
/* ── Textarea override — chronicle font ──────────────────────── */
.ob-textarea {
    font-family: 'EB Garamond', Georgia, ui-serif, serif;
    font-size: 1rem;
    line-height: 1.75;
}
.ob-textarea::placeholder {
    font-style: italic;
    color: #746a5a;
}
</style>
@endpush

@section('content')
{{-- ── Noise + radial bg overlay ─────────────────────────────── --}}
<div class="pointer-events-none fixed inset-0 z-0"
     style="background:radial-gradient(ellipse at 50% 0%,rgba(200,168,75,0.05) 0%,transparent 55%),radial-gradient(ellipse at 80% 100%,rgba(120,70,10,0.07) 0%,transparent 50%)"></div>

<div class="relative z-10 w-full max-w-2xl">

    @php
        $stages = [
            1 => [
                'thaiNum'     => '๑',
                'title'       => 'ตัวตน',
                'subtitle'    => 'เจ้าเป็นใคร มาจากแผ่นดินใด',
                'prompt'      => 'ก่อนที่ประตูจะเปิด เจ้าต้องเปิดเผยตัวตนของเจ้าให้แก่ผู้พิทักษ์ทราบ บอกเล่าถึงชื่อที่คนรู้จัก ดินแดนที่เจ้าจากมา และเชื้อสายที่หล่อหลอมเจ้าขึ้นมา ไม่ว่าจะเป็นนักรบจากทุ่งกว้าง พ่อมดจากหอคอยสูง หรือผู้พเนจรไร้รากเหง้า',
                'placeholder' => 'เขียนเรื่องราวของเจ้าที่นี่...',
            ],
            2 => [
                'thaiNum'     => '๒',
                'title'       => 'เหตุ',
                'subtitle'    => 'อะไรนำพาเจ้ามาสู่ดินแดนนี้',
                'prompt'      => 'ทุกก้าวย่างมีเหตุ ทุกการเดินทางมีจุดกำเนิด ผู้พิทักษ์ต้องการรู้ว่าสิ่งใดผลักดันให้เจ้าออกเดินทาง ไม่ว่าจะเป็นคำสาป พันธสัญญา ความสูญเสีย หรือเสียงเรียกที่เจ้าเองก็ยังไม่อาจอธิบายได้ บอกเล่าถึงเหตุนั้น',
                'placeholder' => 'เล่าถึงสิ่งที่นำพาเจ้ามาที่นี่...',
            ],
            3 => [
                'thaiNum'     => '๓',
                'title'       => 'ปณิธาน',
                'subtitle'    => 'เจ้าหวังจะตามหาสิ่งใด',
                'prompt'      => 'บทสุดท้าย — หัวใจของการเดินทาง ผู้พิทักษ์จำเป็นต้องรู้ถึงสิ่งที่เจ้าแสวงหา ไม่ว่าจะเป็นสมบัติ คำตอบ ความแค้น ความรัก หรือสิ่งที่ยิ่งใหญ่กว่านั้น เพราะดินแดนนี้จะมอบให้เฉพาะผู้ที่รู้ว่าตนเองต้องการอะไร',
                'placeholder' => 'สิ่งที่เจ้าแสวงหานั้นคืออะไร...',
            ],
        ];
    @endphp

    {{-- ── Header ─────────────────────────────────────────────── --}}
    <header class="mb-12 text-center">
        {{-- Diamond sigil --}}
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center" style="position:relative">
            <div class="absolute inset-0 rotate-45 border border-gold/30"></div>
            <div class="absolute inset-2 rotate-45 border border-gold/15"></div>
            <svg class="relative z-10 h-6 w-6 text-gold/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>

        <p class="font-display mb-3 text-xs tracking-[0.4em] text-gold/60 uppercase">
            พิธีกรรมแห่งการก้าวผ่าน
        </p>

        <h1 class="font-display mb-4 text-3xl font-bold text-text sm:text-4xl">
            พิธีเข้าสู่โลก
        </h1>

        {{-- Decorative divider --}}
        <div class="mb-5 flex items-center justify-center gap-4">
            <div class="h-px w-16 bg-gradient-to-r from-transparent to-gold/50"></div>
            <span class="text-sm text-gold/50">✦ ✦ ✦</span>
            <div class="h-px w-16 bg-gradient-to-l from-transparent to-gold/50"></div>
        </div>

        <p class="font-chronicle mx-auto max-w-md text-sm italic leading-relaxed text-text-muted sm:text-base">
            ผู้จะก้าวผ่านประตูมิติต้องสักขีพยานตัวเองต่อหน้าผู้พิทักษ์
            บันทึกเรื่องราวของเจ้าทีละบท เพื่อที่ดินแดนนี้จะจดจำเจ้าได้
        </p>
    </header>

    {{-- ── Progress track ─────────────────────────────────────── --}}
    <div class="mb-12 px-4">
        {{-- items-start so connector mt-4 aligns with circle center --}}
        <div class="flex items-start gap-0">
            @foreach($stages as $num => $stage)
                @php
                    $entry     = $entries->get($num);
                    $isDone    = $entry !== null;
                    $isCurrent = !$isDone && $nextStage === $num;
                    $pIsLocked = !$isDone && !$isCurrent;
                @endphp

                {{-- Circle + label (in flow, no absolute) --}}
                <div class="flex shrink-0 flex-col items-center gap-2">
                    <div @class([
                        'font-display flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all duration-500 ob-progress-circle',
                        'border-gold bg-gold text-bg shadow-[0_0_12px_rgba(200,168,75,0.4)]' => $isDone,
                        'border-gold/70 bg-gold/10 text-gold' => $isCurrent,
                        'border-border bg-bg-elevated text-text-subtle' => $pIsLocked,
                    ])>
                        @if($isDone)
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $stage['thaiNum'] }}
                        @endif
                    </div>
                    <span @class([
                        'ob-progress-label font-display whitespace-nowrap text-[10px] tracking-widest transition-colors duration-500',
                        'text-gold/80' => $isDone,
                        'text-gold/60' => $isCurrent,
                        'text-text-subtle/40' => $pIsLocked,
                    ])>
                        บทที่ {{ $stage['thaiNum'] }}
                    </span>
                </div>

                {{-- Connector mt-4 = half of h-8 circle, aligns with circle center --}}
                @if($num < count($stages))
                    <div class="mx-2 mt-4 h-px flex-1 overflow-hidden bg-border">
                        <div class="ob-connector-fill h-full bg-gold/60 {{ $isDone ? 'filled' : '' }}"
                             data-connector="{{ $num }}"></div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Stage cards ─────────────────────────────────────────── --}}
    <div class="space-y-4">
        @foreach($stages as $num => $stage)
            @php
                $entry     = $entries->get($num);
                $isDone    = $entry !== null;
                $isCurrent = !$isDone && $nextStage === $num;
                $isLocked  = !$isDone && !$isCurrent;
            @endphp

            <div @class([
                     'relative overflow-hidden border transition-all duration-500',
                     'border-border/30 opacity-40' => $isLocked,
                     'border-gold/25 bg-bg-elevated' => $isDone,
                     'border-gold/45 bg-bg-elevated shadow-[0_0_30px_rgba(200,168,75,0.07)]' => $isCurrent,
                 ])
                 style="border-radius:0.25rem"
                 data-stage-card="{{ $num }}"
                 data-status="{{ $isDone ? 'done' : ($isCurrent ? 'active' : 'locked') }}">

                {{-- Corner ornaments (active only) --}}
                @if($isCurrent)
                    <div style="position:absolute;top:0;left:0;width:1.25rem;height:1.25rem;border-top:1.5px solid rgba(200,168,75,0.55);border-left:1.5px solid rgba(200,168,75,0.55)" class="ob-corner"></div>
                    <div style="position:absolute;top:0;right:0;width:1.25rem;height:1.25rem;border-top:1.5px solid rgba(200,168,75,0.55);border-right:1.5px solid rgba(200,168,75,0.55)" class="ob-corner"></div>
                    <div style="position:absolute;bottom:0;left:0;width:1.25rem;height:1.25rem;border-bottom:1.5px solid rgba(200,168,75,0.55);border-left:1.5px solid rgba(200,168,75,0.55)" class="ob-corner"></div>
                    <div style="position:absolute;bottom:0;right:0;width:1.25rem;height:1.25rem;border-bottom:1.5px solid rgba(200,168,75,0.55);border-right:1.5px solid rgba(200,168,75,0.55)" class="ob-corner"></div>
                @endif

                {{-- ── Card header ─────────────────────────────── --}}
                <div class="flex items-center gap-4 px-6 py-4">
                    {{-- Stage badge --}}
                    <div @class([
                             'font-display flex h-10 w-10 shrink-0 items-center justify-center rounded-full border text-lg font-bold transition-all duration-500',
                             'border-border/40 text-text-subtle/50' => $isLocked,
                             'border-gold/60 text-gold' => $isDone,
                             'border-gold text-gold shadow-[0_0_10px_rgba(200,168,75,0.25)]' => $isCurrent,
                         ])
                         data-badge>
                        @if($isLocked)
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        @elseif($isDone)
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $stage['thaiNum'] }}
                        @endif
                    </div>

                    {{-- Title / subtitle --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-baseline gap-3">
                            <span @class([
                                'font-display text-xs tracking-[0.25em] uppercase transition-colors duration-500',
                                'text-text-subtle/30' => $isLocked,
                                'text-gold/70' => !$isLocked,
                            ])>
                                บทที่ {{ $stage['thaiNum'] }}
                            </span>
                            <h3 @class([
                                'font-display text-lg font-semibold transition-colors duration-500',
                                'text-text-subtle/30' => $isLocked,
                                'text-text/90' => $isDone,
                                'text-text' => $isCurrent,
                            ])
                                data-title
                                data-title-value="{{ $stage['title'] }}">
                                {{ $isLocked ? '· · · · · · ·' : $stage['title'] }}
                            </h3>
                        </div>
                        <p class="font-chronicle mt-0.5 text-sm italic text-text-muted transition-colors duration-500
                            @if($isLocked) opacity-20 @endif">
                            {{ $isLocked ? 'ยังไม่ถึงเวลา...' : $stage['subtitle'] }}
                        </p>
                    </div>

                    {{-- Right icon --}}
                    <div class="shrink-0">
                        @if($isDone)
                            <svg class="h-5 w-5 text-gold/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @elseif($isLocked)
                            <svg class="h-4 w-4 text-text-subtle/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        @endif
                    </div>
                </div>

                {{-- ── Done snippet (collapsed) ─────────────────── --}}
                <div class="ob-body {{ $isDone ? 'is-open' : '' }}" data-done-body>
                    <div class="px-6 pb-4">
                        <div class="my-2 flex items-center gap-3">
                            <div class="h-px flex-1 bg-gradient-to-r from-transparent to-gold/35"></div>
                            <span class="text-xs text-gold/50">✦</span>
                            <div class="h-px flex-1 bg-gradient-to-l from-transparent to-gold/35"></div>
                        </div>
                        <p class="font-chronicle mt-3 border-l border-gold/20 pl-4 text-sm italic leading-relaxed text-text-muted/80 line-clamp-2"
                           data-snippet>
                            {{ $entry?->content ?? '' }}
                        </p>
                    </div>
                </div>

                {{-- ── Active body ──────────────────────────────── --}}
                <div class="ob-body {{ $isCurrent ? 'is-open' : '' }}" data-active-body>
                    <div class="px-6 pb-6">
                        {{-- Ornamental divider --}}
                        <div class="my-2 flex items-center gap-3">
                            <div class="h-px flex-1 bg-gradient-to-r from-transparent to-gold/35"></div>
                            <span class="text-xs text-gold/50">✦</span>
                            <div class="h-px flex-1 bg-gradient-to-l from-transparent to-gold/35"></div>
                        </div>

                        {{-- Prompt --}}
                        <p class="font-chronicle mt-4 mb-5 text-sm italic leading-relaxed text-text/75">
                            {{ $stage['prompt'] }}
                        </p>

                        {{-- Textarea --}}
                        <div class="relative">
                            <textarea
                                class="ob-textarea input-field min-h-[7rem] resize-none"
                                placeholder="{{ $stage['placeholder'] }}"
                                rows="5"
                                maxlength="5000"
                                data-textarea
                            ></textarea>
                            <span class="absolute bottom-3 right-3 text-xs text-text-subtle/40" data-charcount></span>
                        </div>

                        {{-- Error message --}}
                        <p class="mt-2 hidden text-xs text-red-400" data-error>
                            กรุณาเขียนอย่างน้อย 30 ตัวอักษร
                        </p>

                        {{-- Submit button --}}
                        <div class="mt-4 flex justify-end">
                            <button
                                type="button"
                                class="font-display flex items-center gap-2 border border-border/40 px-6 py-2.5 text-sm tracking-wider text-text-subtle/40 transition-all duration-300"
                                style="border-radius:0.25rem"
                                data-submit-btn
                                data-stage="{{ $num }}"
                                disabled
                            >
                                <span data-btn-label>ยืนยันบทนี้</span>
                                <span class="text-gold/50">✦</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Completion seal ─────────────────────────────────────── --}}
    <div class="ob-seal mt-10 {{ $allComplete ? '' : 'hidden' }}" id="ob-seal">
        <div class="relative overflow-hidden border border-gold/35 bg-bg-elevated px-8 py-8 text-center"
             style="border-radius:0.25rem">
            {{-- Corner ornaments — inline styles to avoid Tailwind preflight border-style conflict --}}
            <div style="position:absolute;top:0;left:0;width:2rem;height:2rem;border-top:1.5px solid rgba(200,168,75,0.45);border-left:1.5px solid rgba(200,168,75,0.45)"></div>
            <div style="position:absolute;top:0;right:0;width:2rem;height:2rem;border-top:1.5px solid rgba(200,168,75,0.45);border-right:1.5px solid rgba(200,168,75,0.45)"></div>
            <div style="position:absolute;bottom:0;left:0;width:2rem;height:2rem;border-bottom:1.5px solid rgba(200,168,75,0.45);border-left:1.5px solid rgba(200,168,75,0.45)"></div>
            <div style="position:absolute;bottom:0;right:0;width:2rem;height:2rem;border-bottom:1.5px solid rgba(200,168,75,0.45);border-right:1.5px solid rgba(200,168,75,0.45)"></div>

            <div class="absolute inset-0 opacity-[0.04]"
                 style="background:radial-gradient(ellipse at center,rgba(200,168,75,1) 0%,transparent 70%)"></div>

            <div class="relative z-10">
                {{-- Rotating diamond --}}
                <div class="mx-auto mb-5 flex h-14 w-14 rotate-45 items-center justify-center border-2 border-gold/55"
                     style="background:rgba(200,168,75,0.05)">
                    <svg class="h-6 w-6 -rotate-45 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <p class="font-display mb-3 text-xs tracking-[0.35em] uppercase text-gold/60">
                    บันทึกสมบูรณ์
                </p>

                <h2 class="font-display mb-5 text-xl font-semibold text-text sm:text-2xl">
                    เรื่องราวของเจ้าถูกบันทึกแล้ว
                </h2>

                <div class="my-4 flex items-center gap-3">
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent to-gold/35"></div>
                    <span class="text-xs text-gold/50">✦</span>
                    <div class="h-px flex-1 bg-gradient-to-l from-transparent to-gold/35"></div>
                </div>

                <p class="font-chronicle mx-auto mt-5 max-w-md text-sm italic leading-relaxed text-text-muted sm:text-base">
                    ผู้พิทักษ์แห่งดินแดนได้รับม้วนหนังสือของเจ้าแล้ว
                    พวกเขาจะพิจารณาเรื่องราวและตัดสินใจก่อนที่ประตูจะเปิดรับเจ้า
                    จงรอคอยด้วยความอดทน เพราะการตัดสินใจของผู้พิทักษ์นั้นใช้เวลา...
                </p>

                <p class="font-display mt-5 text-xs tracking-wider text-text-subtle/50">
                    · รอการพิจารณาจากแอดมิน ·
                </p>
            </div>
        </div>
    </div>

    {{-- ── Footer sigil ────────────────────────────────────────── --}}
    <div class="mt-14 mb-4 text-center">
        <div class="flex items-center justify-center gap-3 opacity-25">
            <div class="h-px w-12 bg-gold"></div>
            <span class="font-display text-xs text-gold">✦</span>
            <div class="h-px w-12 bg-gold"></div>
        </div>
    </div>

    <div class="mt-4 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="font-display text-xs tracking-widest text-text-subtle/40 transition hover:text-text-subtle uppercase">
                ออกจากระบบ
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const STAGE_COUNT = {{ count($stages) }};
    const SUBMIT_URL  = '{{ route("onboarding.submit") }}';

    function openBody(el)  { if (el) el.classList.add('is-open'); }
    function closeBody(el) { if (el) el.classList.remove('is-open'); }

    // ── Wire ALL cards once (locked cards have disabled buttons) ─
    document.querySelectorAll('[data-stage-card]').forEach(card => {
        const textarea  = card.querySelector('[data-textarea]');
        const submitBtn = card.querySelector('[data-submit-btn]');
        const charCount = card.querySelector('[data-charcount]');
        const errorEl   = card.querySelector('[data-error]');
        const btnLabel  = card.querySelector('[data-btn-label]');

        if (!textarea || !submitBtn) return;

        textarea.addEventListener('input', () => {
            const len   = textarea.value.length;
            if (charCount) charCount.textContent = len > 0 ? len : '';
            const ready = len >= 30;
            submitBtn.disabled = !ready;
            setBtnStyle(submitBtn, ready);
        });

        submitBtn.addEventListener('click', async () => {
            if (submitBtn.disabled) return;
            const content = textarea.value.trim();
            if (content.length < 30) {
                if (errorEl) { errorEl.textContent = 'กรุณาเขียนอย่างน้อย 30 ตัวอักษร'; errorEl.classList.remove('hidden'); }
                return;
            }
            if (errorEl) errorEl.classList.add('hidden');

            const stage = parseInt(submitBtn.dataset.stage, 10);
            submitBtn.disabled = true;
            if (btnLabel) btnLabel.textContent = 'กำลังบันทึก...';

            try {
                const res = await fetch(SUBMIT_URL, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body:    JSON.stringify({ stage, content }),
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);

                if (btnLabel) btnLabel.textContent = 'บันทึกแล้ว ✓';
                await new Promise(r => setTimeout(r, 380));
                animateToDone(card, stage, content);

            } catch (err) {
                submitBtn.disabled = false;
                setBtnStyle(submitBtn, true);
                if (btnLabel) btnLabel.textContent = 'ยืนยันบทนี้';
                if (errorEl) { errorEl.textContent = 'เกิดข้อผิดพลาด กรุณาลองใหม่'; errorEl.classList.remove('hidden'); }
            }
        });
    });

    function setBtnStyle(btn, ready) {
        btn.style.borderColor = ready ? 'rgba(200,168,75,0.65)' : '';
        btn.style.color       = ready ? '#c8a84b' : '';
        btn.style.cursor      = ready ? 'pointer' : '';
    }

    // ── Card done animation ──────────────────────────────────────
    function animateToDone(card, stageNum, content) {
        const activeBody = card.querySelector('[data-active-body]');
        const doneBody   = card.querySelector('[data-done-body]');
        const snippet    = card.querySelector('[data-snippet]');

        closeBody(activeBody);

        setTimeout(() => {
            card.classList.remove('border-gold/45', 'shadow-[0_0_30px_rgba(200,168,75,0.07)]');
            card.classList.add('border-gold/25');
            card.querySelectorAll('.ob-corner').forEach(c => c.remove());

            const badge = card.querySelector('[data-badge]');
            if (badge) {
                badge.style.borderColor = 'rgba(200,168,75,0.6)';
                badge.style.color       = '#c8a84b';
                badge.innerHTML = '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
            }

            if (snippet) snippet.textContent = content;
            openBody(doneBody);

            const connector = document.querySelector(`[data-connector="${stageNum}"]`);
            if (connector) connector.classList.add('filled');

            updateProgressCircle(stageNum, 'done');
            setTimeout(() => unlockNextCard(stageNum + 1), 450);
        }, 480);
    }

    // ── Unlock next card (visual only — click already wired) ────
    function unlockNextCard(nextNum) {
        if (nextNum > STAGE_COUNT) {
            const seal = document.getElementById('ob-seal');
            if (seal) {
                seal.classList.remove('hidden');
                requestAnimationFrame(() => requestAnimationFrame(() => seal.classList.add('is-visible')));
            }
            return;
        }

        const nextCard = document.querySelector(`[data-stage-card="${nextNum}"]`);
        if (!nextCard) return;

        nextCard.classList.remove('border-border/30', 'opacity-40');
        nextCard.classList.add('border-gold/45', 'shadow-[0_0_30px_rgba(200,168,75,0.07)]');

        // Reveal title from data attribute (no PHP→JS data passing needed)
        const titleEl = nextCard.querySelector('[data-title]');
        if (titleEl && titleEl.dataset.titleValue) {
            titleEl.textContent = titleEl.dataset.titleValue;
        }

        const activeBody = nextCard.querySelector('[data-active-body]');
        requestAnimationFrame(() => requestAnimationFrame(() => openBody(activeBody)));

        // Sync button state with current textarea value (textarea input listener already wired)
        const textarea = nextCard.querySelector('[data-textarea]');
        if (textarea) {
            textarea.dispatchEvent(new Event('input'));
            setTimeout(() => textarea.focus(), 550);
        }

        updateProgressCircle(nextNum, 'active');
    }

    function updateProgressCircle(stageNum, status) {
        const circles = document.querySelectorAll('.ob-progress-circle');
        const labels  = document.querySelectorAll('.ob-progress-label');
        const circle  = circles[stageNum - 1];
        const label   = labels[stageNum - 1];

        if (circle) {
            if (status === 'done') {
                circle.style.borderColor  = '#c8a84b';
                circle.style.background   = '#c8a84b';
                circle.style.color        = '#090807';
                circle.style.boxShadow    = '0 0 12px rgba(200,168,75,0.4)';
                circle.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
            } else if (status === 'active') {
                circle.style.borderColor = 'rgba(200,168,75,0.7)';
                circle.style.background  = 'rgba(200,168,75,0.1)';
                circle.style.color       = '#c8a84b';
            }
        }

        if (label) {
            label.style.color = status === 'done' ? 'rgba(200,168,75,0.8)' : 'rgba(200,168,75,0.6)';
        }
    }

    @if($allComplete)
    (function () {
        const seal = document.getElementById('ob-seal');
        if (seal) {
            seal.classList.remove('hidden');
            requestAnimationFrame(() => requestAnimationFrame(() => seal.classList.add('is-visible')));
        }
    })();
    @endif

})();
</script>
@endpush
