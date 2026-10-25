<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Oʻzbekiston qonunchiligi boʻyicha AI-yordamchi, hujjat tayyorlash va interaktiv sud simulyatori.">
    <meta name="theme-color" content="#0b0d12">
    <title>Yuridik Simulyator — Oʻzbekiston qonunchiligi boʻyicha AI-yordamchi</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%237c6cff'/%3E%3Cg fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M16 7v18M11 25h10M8 11h16M8 11l-3 7a3.5 3.5 0 0 0 6 0zM24 11l-3 7a3.5 3.5 0 0 0 6 0z'/%3E%3C/g%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/simulator.css') }}" rel="stylesheet">
</head>
<body>
<div class="app">

    <aside class="sidebar" id="sidebar">
        <a href="{{ route('home') }}" class="brand">
            <span class="logo">
                <svg viewBox="0 0 24 24"><path d="M12 3v18M7 21h10M3 7h18M3 7l-2.5 6a3 3 0 0 0 5 0zM21 7l-2.5 6a3 3 0 0 0 5 0z"/></svg>
            </span>
            <span>
                <span class="brand-name">Yuridik Simulyator</span>
                <span class="brand-sub">AI huquqiy yordamchi</span>
            </span>
        </a>

        <button class="btn btn-primary btn-block" data-action="new-chat">
            <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Yangi suhbat
        </button>

        <nav class="nav">
            <button class="nav-item active" data-view="home">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                AI-yordamchi
            </button>
            <button class="nav-item" data-view="court">
                <svg viewBox="0 0 24 24"><path d="m14.5 12.5-8 8a2.12 2.12 0 0 1-3-3l8-8M16 16l6-6M8 8l6-6M9 7l8 8M21 11l-8-8"/></svg>
                Sud simulyatori
            </button>
            <a class="nav-item" href="{{ route('court-hall') }}">
                <svg viewBox="0 0 24 24"><path d="M3 22h18M6 18v-7M10 18v-7M14 18v-7M18 18v-7M12 2l8 5H4z"/></svg>
                Virtual sud zali
                <span class="nav-tag">Ball</span>
            </a>
            <button class="nav-item" data-view="news">
                <svg viewBox="0 0 24 24"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2M18 14h-8M15 18h-5M10 6h8v4h-8z"/></svg>
                Huquqiy yangiliklar
            </button>
            <button class="nav-item" data-view="files">
                <svg viewBox="0 0 24 24"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2z"/></svg>
                Mening hujjatlarim
                <span class="nav-count" id="filesCount" hidden></span>
            </button>
        </nav>

        <div class="sidebar-section">
            <div class="section-label">Tezkor boshlash</div>
            <button class="shortcut" data-mode="doc_create" data-prompt="Muddatli toʻlov sharti bilan oldi-sotdi shartnomasi loyihasini tuzib bering.">📄 Shartnoma tuzish</button>
            <button class="shortcut" data-mode="doc_create" data-prompt="Ish haqi oʻz vaqtida toʻlanmagani uchun sudga daʼvo arizasi namunasini tayyorlab bering.">⚖️ Daʼvo arizasi yozish</button>
            <button class="shortcut" data-mode="doc_analyze" data-prompt="Quyidagi shartnoma matnidagi huquqiy xatarlarni tahlil qilib bering:">🔍 Hujjatni tahlil qilish</button>
            <button class="shortcut" data-mode="general" data-prompt="Ish beruvchi meni ogohlantirmasdan ishdan boʻshatdi. Qanday huquqlarim bor?">💼 Mehnat huquqlari</button>
        </div>

        <a class="tg-card" href="https://t.me/YuridikSimulyator_Bot" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4zM22 2 11 13"/></svg>
            <span>
                <strong>Telegram bot</strong>
                <small>Sud simulyatori telefoningizda</small>
            </span>
        </a>
    </aside>
    <div class="backdrop" data-action="close-sidebar"></div>

    <main class="main">
        <header class="topbar">
            <button class="icon-btn menu-btn" data-action="open-sidebar" aria-label="Menyu">
                <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="topbar-title" id="viewTitle">AI-yordamchi</h1>
            <a class="btn btn-ghost btn-sm" href="https://t.me/YuridikSimulyator_Bot" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24"><path d="m22 2-7 20-4-9-9-4zM22 2 11 13"/></svg>
                <span class="hide-sm">Telegram bot</span>
            </a>
        </header>

        {{-- AI-YORDAMCHI --}}
        <section class="view active" id="view-home" data-title="AI-yordamchi">
            <div class="chat-scroll" id="chatScroll">
                <div class="welcome" id="welcome">
                    <span class="logo logo-lg">
                        <svg viewBox="0 0 24 24"><path d="M12 3v18M7 21h10M3 7h18M3 7l-2.5 6a3 3 0 0 0 5 0zM21 7l-2.5 6a3 3 0 0 0 5 0z"/></svg>
                    </span>
                    <h2>Qanday huquqiy yordam kerak?</h2>
                    <p>Savolingizni yozing yoki ovozli yuboring — AI Oʻzbekiston qonunlarining tegishli moddalariga tayanib javob beradi, hujjat tayyorlaydi yoki shartnomangizni tekshiradi.</p>
                    <div class="suggestions">
                        <button class="suggestion" data-mode="general" data-prompt="Muddatli toʻlov (rassrochka) doʻkoni kechikkan toʻlov uchun penya undirishi qonuniymi?">
                            <strong>Huquqiy savol</strong>
                            <span>Rassrochka uchun penya undirish qonuniymi?</span>
                        </button>
                        <button class="suggestion" data-mode="doc_create" data-prompt="Talab qilish huquqini oʻtkazish (sessiya) shartnomasini tuzib bering.">
                            <strong>Hujjat tayyorlash</strong>
                            <span>Sessiya shartnomasini tuzib bering</span>
                        </button>
                        <button class="suggestion" data-mode="doc_analyze" data-prompt="Quyidagi shartnoma matnidagi huquqiy xatarlarni tahlil qilib bering:">
                            <strong>Hujjat tahlili</strong>
                            <span>Shartnomadagi xatarlarni toping</span>
                        </button>
                        <button class="suggestion" data-view="court">
                            <strong>Sud simulyatori</strong>
                            <span>7 xil sudda advokat boʻlib koʻring</span>
                        </button>
                    </div>
                </div>
                <div class="messages" id="chatMessages"></div>
            </div>

            <div class="composer-wrap">
                <div class="composer">
                    <div class="composer-chips" id="composerChips"></div>
                    <textarea id="aiInput" rows="1" placeholder="Savolingizni yozing…" aria-label="Savolingiz"></textarea>
                    <div class="composer-bar">
                        <label class="btn btn-ghost btn-sm" title="Matnli faylni biriktirish (.txt)">
                            <input type="file" id="fileInput" accept=".txt,text/plain" hidden>
                            <svg viewBox="0 0 24 24"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                            <span class="hide-sm">Fayl (.txt)</span>
                        </label>
                        <div class="composer-actions">
                            <button class="icon-btn" id="homeMicBtn" data-action="voice" data-target="home" title="Ovozli savol" aria-label="Ovozli savol">
                                <svg viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2M12 19v3"/></svg>
                            </button>
                            <button class="icon-btn icon-btn-primary" id="sendBtn" data-action="send-chat" title="Yuborish (Enter)" aria-label="Yuborish">
                                <svg viewBox="0 0 24 24"><path d="m5 12 7-7 7 7M12 19V5"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="disclaimer">AI javoblari maʼlumot uchun. Muhim masalalarda advokat bilan maslahatlashing.</p>
            </div>
        </section>

        {{-- SUD SIMULYATORI --}}
        <section class="view" id="view-court" data-title="Sud simulyatori">
            <div class="court-toolbar">
                <div class="court-selects">
                    <select class="select" id="courtSelect" aria-label="Sud turi">
                        <option value="Fuqarolik sudi">Fuqarolik sudi</option>
                        <option value="Jinoyat sudi">Jinoyat sudi</option>
                        <option value="Ma'muriy sud">Maʼmuriy sud</option>
                        <option value="Iqtisodiy sud">Iqtisodiy sud</option>
                        <option value="Apellyatsiya shikoyati">Apellyatsiya instansiyasi</option>
                        <option value="Kassatsiya shikoyati">Kassatsiya instansiyasi</option>
                        <option value="Hakamlik sudi">Hakamlik sudi</option>
                    </select>
                    <select class="select" id="levelSelect" aria-label="Daraja">
                        <option value="maktab">Maktab oʻquvchisi</option>
                        <option value="talaba" selected>Talaba</option>
                        <option value="yurist">Amaliyotchi yurist</option>
                        <option value="ekspert">Sudya va ekspert</option>
                    </select>
                </div>
                <div class="court-actions">
                    <button class="btn btn-ghost btn-sm" data-action="new-case">✨ Yangi ish</button>
                    <button class="btn btn-ghost btn-sm" data-action="hint">💡 Maslahat</button>
                    <button class="btn btn-primary btn-sm" data-action="verdict">🏁 Hukm</button>
                </div>
            </div>

            <div class="court-layout">
                <aside class="case-panel">
                    <div class="section-label">Koʻrilayotgan ish</div>
                    <h3 class="case-title" id="caseTitle"></h3>
                    <p class="case-desc" id="caseDesc"></p>
                    <div class="section-label">Dalillar <span class="label-hint">— bosib nutqqa qoʻshing</span></div>
                    <div class="evidence-list" id="evidenceList"></div>
                </aside>

                <div class="court-chat">
                    <div class="court-messages" id="courtMessages"></div>
                    <div class="court-input">
                        <button class="icon-btn" id="courtMicBtn" data-action="voice" data-target="court" title="Ovozli nutq" aria-label="Ovozli nutq">
                            <svg viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3zM19 10v2a7 7 0 0 1-14 0v-2M12 19v3"/></svg>
                        </button>
                        <textarea id="courtInput" rows="1" placeholder="Advokat sifatida nutqingizni yozing…" aria-label="Nutqingiz"></textarea>
                        <button class="icon-btn icon-btn-primary" data-action="send-court" title="Yuborish (Enter)" aria-label="Yuborish">
                            <svg viewBox="0 0 24 24"><path d="m5 12 7-7 7 7M12 19V5"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- HUQUQIY YANGILIKLAR --}}
        <section class="view" id="view-news" data-title="Huquqiy yangiliklar">
            <div class="page">
                <div class="page-head">
                    <div>
                        <h2>Huquqiy yangiliklar</h2>
                        <p>Qonunchilikdagi oʻzgarishlar haqida AI tayyorlagan qisqa sharhlar. Rasmiy matnni <a href="https://lex.uz" target="_blank" rel="noopener">lex.uz</a> da tekshiring.</p>
                    </div>
                    <button class="btn btn-ghost btn-sm" data-action="refresh-news">🔄 Yangilash</button>
                </div>
                <div class="card-grid" id="newsGrid"></div>
            </div>
        </section>

        {{-- MENING HUJJATLARIM --}}
        <section class="view" id="view-files" data-title="Mening hujjatlarim">
            <div class="page">
                <div class="page-head">
                    <div>
                        <h2>Mening hujjatlarim</h2>
                        <p>AI javoblaridan saqlagan hujjatlaringiz. Ular faqat shu brauzerda saqlanadi.</p>
                    </div>
                    <button class="btn btn-primary btn-sm" data-mode="doc_create" data-prompt="Quyidagi maʼlumotlar asosida hujjat tuzib bering: ">+ Yangi hujjat</button>
                </div>
                <input type="search" class="input" id="filesSearch" placeholder="Hujjatlarni qidirish…" aria-label="Hujjatlarni qidirish">
                <div class="card-grid" id="filesGrid"></div>
                <div class="empty" id="filesEmpty" hidden>
                    <strong>Hozircha hujjat yoʻq</strong>
                    <span>AI javobi ostidagi «Saqlash» tugmasini bosing — hujjat shu yerda paydo boʻladi.</span>
                </div>
            </div>
        </section>
    </main>
</div>

<div class="toast" id="toast" role="status" hidden></div>

@php
    $apiRoutes = [
        'assistantChat' => route('api.assistant.chat'),
        'courtTurn' => route('api.court.turn'),
        'courtCase' => route('api.court.cases.store'),
        'courtHint' => route('api.court.hint'),
        'courtVerdict' => route('api.court.verdict'),
        'newsRefresh' => route('api.news.refresh'),
    ];
@endphp
<script>
    window.API_ROUTES = @json($apiRoutes);
</script>
<script src="{{ asset('js/api.js') }}"></script>
<script src="{{ asset('js/simulator.js') }}"></script>
</body>
</html>
