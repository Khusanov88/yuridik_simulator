<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yuridik Simulyator — Oʻzbekiston Huquqiy AI Platformasi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/simulator.css') }}" rel="stylesheet">
</head>
<body>

<!-- ==================== LEFT SIDEBAR ==================== -->
<aside class="sidebar">
    <a href="#" class="brand" onclick="openView('home')">
        <div class="brand-box">S</div>
        <span class="brand-name">Yuridik AI</span>
    </a>

    <button class="btn-new-chat" onclick="resetHomeChat()">
        <span>+</span> Yangi chat
    </button>

    <div class="nav-group">
        <button class="nav-item active" id="nav-home" onclick="openView('home')">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            AI-Yordamchi (Chat)
        </button>
        <button class="nav-item" id="nav-news" onclick="openView('news')">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Huquqiy yangiliklar
        </button>
        <button class="nav-item" id="nav-files" onclick="openView('files')">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            Mening fayllarim
        </button>
    </div>

    <div class="nav-group">
        <button class="nav-item" id="nav-court" onclick="openView('court')">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Sud simulyatori (7 sud)
        </button>
        <button class="nav-item" onclick="startQuickAction('doc_create', 'Mengaa muddatli to\'lov sharti bilan oldi-sotdi shartnomasi loyihasini tuzib bering')">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Hujjat yaratish
        </button>
        <button class="nav-item" onclick="startQuickAction('doc_analyze', 'Quyidagi shartnoma matnidagi huquqiy xatarlarni tahlil qilib bering:')">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Hujjat tahlili
        </button>
    </div>

    <input type="text" class="sidebar-search" placeholder="Chatlarni qidirish..." oninput="filterHistory(this.value)">
    <div class="sidebar-label">CHATLAR</div>
    <div class="history-list" id="sidebarHistory">
        <div class="history-item" onclick="startQuickAction('general', 'O\'zbekiston mehnat qonunchiligidagi so\'nggi o\'zgarishlar haqida ma\'lumot bering')">
            Qonunlar monitoringi
            <span class="history-date">Hozirgina</span>
        </div>
        <div class="history-item" onclick="startQuickAction('general', 'Muddatli to\'lov (rassrochka) do\'konlari mijozdan penya undirishi qonuniymi?')">
            Kredit va muddatli toʻlov nizosi
            <span class="history-date">23.10.2026</span>
        </div>
        <div class="history-item" onclick="openView('court')">
            Iqtisodiy sud: ISH № 104
            <span class="history-date">23.10.2026</span>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="user-avatar">Y</div>
            <div>
                <div style="font-size:12.5px;">Yuridik Profil</div>
                <div style="font-size:10.5px; color:var(--text-muted);">PRO / Oʻzbekcha</div>
            </div>
        </div>
    </div>
</aside>

<!-- ==================== MAIN CONTENT AREA ==================== -->
<div class="main-area">
    <!-- TOP BAR -->
    <header class="top-bar">
        <div class="top-links">
            <a onclick="openView('home')">AI-Yordamchi</a>
            <a onclick="openView('court')">Sud Simulyatori</a>
            <a onclick="openView('news')">Yangiliklar</a>
            <a onclick="openView('files')">Fayllarim</a>
        </div>
        <div class="top-links">
            <span>UZ Oʻzbekcha</span>
            <a href="https://t.me/YuridikSimulyator_Bot" target="_blank" class="btn-tg">Telegram Bot</a>
        </div>
    </header>

    <!-- 1. VIEW: AI-YORDAMCHI (SAFELAW LANDING + CHAT) -->
    <section class="view-section active" id="view-home">
        <div class="hero-center" id="heroBlock">
            <div class="hero-logo">
                <div class="brand-box">S</div>
                <span>Yuridik AI</span>
            </div>
            <h1 class="hero-title">Oʻzbekiston huquqiy maʼlumotlari boʻyicha AI-yordamchi</h1>
            <p class="hero-subtitle">Savol bering — AI Oʻzbekiston qonunlaridan kerakli moddalarni topib, 10–30 soniyada javob beradi. Kecha-kunduz ishlaydi.</p>
        </div>

        <div class="chat-Log" id="mainChatLog"></div>

        <div class="hero-center" style="margin-top:0;">
            <div class="prompt-box">
                <div id="attachedFileBadge" style="display:none; font-size:12px; color:var(--accent-light); margin-bottom:8px; text-align:left;"></div>
                <textarea id="aiInput" placeholder="Muammoingizni tasvirlab bering yoki huquqiy hujjat nomini yozing..."></textarea>
                <div class="prompt-toolbar">
                    <label class="file-upload-lbl">
                        <input type="file" id="fileInput" accept=".txt,.doc,.docx,.html" style="display:none;" onchange="handleFileAttach(this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Fayl yuklang
                    </label>
                    <div class="toolbar-right">
                        <button class="icon-btn" id="homeMicBtn" onclick="toggleVoice('home')" title="Ovozli xabar yuborish">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/></svg>
                        </button>
                        <button class="send-pill-btn" onclick="sendAIChat()">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="quick-pills">
                <button class="q-pill" onclick="startQuickAction('doc_analyze', 'Ushbu shartnoma matnini huquqiy tahlil qilib, xatarlarini ko\'rsating:')">🔍 Hujjat tahlili</button>
                <button class="q-pill" onclick="startQuickAction('doc_create', 'Mengaa talab qilish huquqini o\'tkazish (sessiya) shartnomasini tuzib bering')">📄 Hujjat tayyorlash</button>
                <button class="q-pill" onclick="openView('court')">⚖️ Sud simulyatori (7 sud)</button>
                <button class="q-pill" onclick="openView('news')">📋 Qonunlar monitoringi</button>
            </div>
        </div>
    </section>

    <!-- 2. VIEW: MENING FAYLLARIM -->
    <section class="view-section" id="view-files">
        <h2 class="page-title">Mening fayllarim</h2>
        <div class="page-count" id="filesCountText">Jami: 4</div>

        <div class="search-row">
            <input type="text" class="main-search" placeholder="Fayllarni qidirish..." oninput="renderFilesGrid(this.value)">
        </div>

        <div class="filter-tabs">
            <button class="f-tab active">Barchasi</button>
            <button class="f-tab" onclick="startQuickAction('doc_create', 'Yangi ikki tomonlama shartnoma tuzib bering')">+ Yangi hujjat yaratish (AI)</button>
        </div>

        <div class="files-grid" id="filesGrid"></div>
    </section>

    <!-- 3. VIEW: HUQUQIY YANGILIKLAR -->
    <section class="view-section" id="view-news">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <div>
                <h2 class="page-title">Huquqiy yangiliklar va Qonunchilik monitoringi</h2>
                <div class="page-count" style="margin-bottom:0;">Oʻzbekiston Respublikasi qonunchiligidagi soʻnggi oʻzgarishlar va AI sharhlari</div>
            </div>
            <button class="btn-sm primary" onclick="refreshLegalNews()">🔄 AI orqali yangilash</button>
        </div>
        <div class="news-grid" id="newsGrid"></div>
    </section>

    <!-- 4. VIEW: SUD SIMULYATORI (TARTIBLI VA PROFESSIONAL) -->
    <section class="view-section" id="view-court" style="padding: 20px 28px;">
        <div class="sim-header">
            <div class="sim-selects">
                <select class="clean-select" id="levelSelect" onchange="changeSimConfig()">
                    <option value="maktab">Daraja: Maktab oʻquvchilari (Boshlangʻich)</option>
                    <option value="talaba" selected>Daraja: Talabalar (Akademik)</option>
                    <option value="yurist">Daraja: Amaliyotchi yuristlar (PRO)</option>
                    <option value="ekspert">Daraja: Sudya va Ekspertlar (Oliy)</option>
                </select>
                <select class="clean-select" id="courtSelect" onchange="changeSimConfig()">
                    <option value="Fuqarolik sudi">Sud: Fuqarolik ishlari boʻyicha sud</option>
                    <option value="Jinoyat sudi">Sud: Jinoyat ishlari boʻyicha sud</option>
                    <option value="Ma'muriy sud">Sud: Maʼmuriy sud</option>
                    <option value="Iqtisodiy sud">Sud: Iqtisodiy sud</option>
                    <option value="Apellyatsiya shikoyati">Sud: Apellyatsiya instansiyasi</option>
                    <option value="Kassatsiya shikoyati">Sud: Kassatsiya instansiyasi</option>
                    <option value="Hakamlik sudi">Sud: Hakamlik sudi</option>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button class="btn-sm" onclick="generateNewSimCase()">✨ Yangi ish (AI)</button>
                <button class="btn-sm" onclick="getSimHint()">💡 Huquqiy maslahat</button>
                <button class="btn-sm primary" onclick="getSimVerdict()">🏁 Yakuniy Hukm</button>
            </div>
        </div>

        <div class="sim-layout">
            <!-- CHAPDA ISH FABULASI VA DALILLAR -->
            <div class="sim-case-panel">
                <div>
                    <div class="sidebar-label" style="padding:0; margin-bottom:6px;">KOʻRILAYOTGAN ISH</div>
                    <h4 id="simCaseTitle" style="font-size:14.5px; color:#fff; margin-bottom:8px;"></h4>
                    <p id="simCaseDesc" style="font-size:13px; color:var(--text-secondary); line-height:1.55;"></p>
                </div>
                <div>
                    <div class="sidebar-label" style="padding:0; margin-bottom:8px;">ASHYOVIY DALILLAR (Bosib nutqqa qoʻshing)</div>
                    <div id="simEvidences" style="display:flex; flex-direction:column; gap:8px;"></div>
                </div>
            </div>

            <!-- O'NGDA SUD MUZOKARASI -->
            <div class="sim-chat-box">
                <div class="sim-messages" id="simMessages"></div>
                <div style="padding:12px 16px; border-top:1px solid var(--border); display:flex; gap:10px; align-items:center; background:var(--bg-input);">
                    <button class="icon-btn" id="courtMicBtn" onclick="toggleVoice('court')" title="Ovozli nutq so'zlash">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg>
                    </button>
                    <input type="text" id="simInput" class="main-search" style="margin:0;" placeholder="Advokat sifatida huquqiy pozitsiyangizni yozing yoki mikrofonda gapiring..." onkeydown="if(event.key==='Enter') sendSimTurn()">
                    <button class="btn-sm primary" style="height:40px; padding:0 18px;" onclick="sendSimTurn()">Yuborish</button>
                </div>
            </div>
        </div>
    </section>
</div>

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
