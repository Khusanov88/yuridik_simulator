<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Virtual sud zali — Yuridik Simulyator</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%237c6cff'/%3E%3Cg fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M16 7v18M11 25h10M8 11h16M8 11l-3 7a3.5 3.5 0 0 0 6 0zM24 11l-3 7a3.5 3.5 0 0 0 6 0z'/%3E%3C/g%3E%3C/svg%3E">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/court-hall.css') }}" rel="stylesheet">
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar">
    <a href="{{ route('home') }}" class="brand" title="Bosh sahifaga qaytish">
        <div class="brand-icon">⚖️</div>
        <div>
            <span class="brand-title">YURIDIK SIMULYATOR</span>
            <span class="brand-sub">Oʻzbekiston Interaktiv Sud Zali va AI Maslahatchi</span>
        </div>
    </a>
    <div class="nav-tabs">
        <button class="nav-btn active" id="tabCourtBtn" onclick="switchTab('court')">🏛 Virtual Sud Zali</button>
        <button class="nav-btn" id="tabAdvisorBtn" onclick="switchTab('advisor')">🤖 24/7 AI Huquqiy Maslahatchi</button>
        <a href="https://t.me/YuridikSimulyator_Bot" target="_blank" class="nav-btn">✈️ Telegram Bot</a>
    </div>
</nav>

<!-- ==================== 1-BO'LIM: VIRTUAL SUD ZALI ==================== -->
<div id="courtSection">
    <div class="selector-bar">
        <!-- DARAJALAR -->
        <div class="selector-row">
            <span class="selector-label">🎓 Darajani tanlang:</span>
            <button class="chip" onclick="selectLevel('maktab', this)">🎒 Maktab oʻquvchilari (Boshlangʻich)</button>
            <button class="chip active" onclick="selectLevel('talaba', this)">🎓 Talabalar (Akademik)</button>
            <button class="chip" onclick="selectLevel('yurist', this)">💼 Amaliyotchi Yuristlar (PRO)</button>
            <button class="chip" onclick="selectLevel('ekspert', this)">👑 Sudya va Ekspertlar (Oliy)</button>
        </div>
        <!-- 7 TA SUD YO'NALISHI -->
        <div class="selector-row">
            <span class="selector-label">🏛 Sud instansiyasi:</span>
            <button class="chip active" onclick="selectCourt('Fuqarolik sudi', this)">🏛 Fuqarolik sudi</button>
            <button class="chip" onclick="selectCourt('Jinoyat sudi', this)">🚔 Jinoyat sudi</button>
            <button class="chip" onclick="selectCourt('Ma\'muriy sud', this)">📋 Maʼmuriy sud</button>
            <button class="chip" onclick="selectCourt('Iqtisodiy sud', this)">💼 Iqtisodiy sud</button>
            <button class="chip" onclick="selectCourt('Apellyatsiya shikoyati', this)">📈 Apellyatsiya</button>
            <button class="chip" onclick="selectCourt('Kassatsiya shikoyati', this)">⚖️ Kassatsiya</button>
            <button class="chip" onclick="selectCourt('Hakamlik sudi', this)">🤝 Hakamlik sudi</button>
        </div>
    </div>

    <div class="workspace">
        <!-- CHAP PANEL: ISH HOLATI VA DALILLAR PAPKASI -->
        <aside class="sidebar">
            <div class="panel-box">
                <div class="panel-header">
                    <h3 id="caseTitle">📂 ISH № 201 | Fuqarolik sudi</h3>
                    <button class="action-pill" onclick="generateNewAICase()" title="AI yordamida yangi ish va dalillar tuzish">✨ Yangi kazus</button>
                </div>
                <div class="case-desc" id="caseDesc"></div>
            </div>

            <div class="panel-box">
                <div class="panel-header">
                    <h3>🗂 Ashyoviy Dalillar va Hujjatlar</h3>
                    <span class="evidence-badge">Bosib koʻring</span>
                </div>
                <p style="font-size:12px; color:var(--text-muted); margin-bottom:10px;">Sudda yutish uchun quyidagi dalillarni ochib oʻqing va nutqingizga qoʻshing:</p>
                <div class="evidence-list" id="evidenceList"></div>
            </div>
        </aside>

        <!-- O'NG PANEL: SUD ZALI MINBARI VA CHAT -->
        <main class="court-hall">
            <div class="court-stage">
                <div class="role-podium" id="podiumOpponent">
                    <div class="role-avatar">👔</div>
                    <div class="role-name" id="opponentTitle">Qarshi Tomon Vakili</div>
                    <div class="role-status" id="opponentStatus">Daʼvogar / Prokuror</div>
                </div>
                <div class="role-podium judge-podium" id="podiumJudge">
                    <div class="role-avatar">👩‍⚖️</div>
                    <div class="role-name">Raislik Qiluvchi (Sudya)</div>
                    <div class="role-status" id="judgeStatus">Sud majlisini olib bormoqda</div>
                </div>
                <div class="role-podium" id="podiumAdvocate">
                    <div class="role-avatar">🛡️</div>
                    <div class="role-name">Siz (Advokat)</div>
                    <div class="role-status" id="levelBadgeText">Daraja: Talaba</div>
                </div>
            </div>

            <div class="court-statusbar">
                <div class="meter-wrap">
                    <span style="font-size:12.5px; font-weight:700; color:var(--gold);">⚖️ Sudya ishonchi: <span id="scoreValue">50</span>%</span>
                    <div class="meter-bg"><div class="meter-fill" id="scoreBar"></div></div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button class="action-pill" onclick="playGavelSound()">🔨 Sud bolgʻachasi</button>
                    <button class="action-pill" onclick="requestHint()">💡 Ustoz maslahati</button>
                    <button class="action-pill verdict-btn" onclick="requestVerdict()">🏁 Hukm oʻqish</button>
                </div>
            </div>

            <div class="chat-stream" id="chatStream"></div>

            <div class="court-controls">
                <div class="quick-actions">
                    <button class="action-pill" onclick="insertPhrase('Muhtaram sudya, qarshi tomonning vaji asossiz, chunki ')">✋ Eʼtiroz bildiraman!</button>
                    <button class="action-pill" onclick="insertPhrase('Sudga taqdim etilgan ashyoviy dalilga eʼtibor qaratishingizni soʻrayman: ')">📂 Dalilga tayanaman</button>
                    <button class="action-pill" onclick="insertPhrase('Oʻzbekiston Respublikasi qonunchiligiga binoan, ')">📘 Kodeks moddasi</button>
                    <button class="action-pill" onclick="generateNewAICase()">🔄 Boshqa sud ishi (AI)</button>
                </div>
                <div class="input-row">
                    <button class="mic-btn" id="micBtn" onclick="toggleVoiceRecording('court')" title="Ovozli nutq so'zlash (Mikrofon)">🎙</button>
                    <textarea id="userInput" placeholder="Advokat sifatida nutqingizni yozing yoki 🎙 mikrofonni bosib ovozli gapiring..."></textarea>
                    <button class="send-btn" id="sendBtn" onclick="sendCourtTurn()">Nutqni soʻzlash ➤</button>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- ==================== 2-BO'LIM: 24/7 AI HUQUQIY MASLAHATCHI ==================== -->
<div class="advisor-container" id="advisorSection">
    <div class="panel-box" style="padding: 28px;">
        <div class="panel-header">
            <h3 style="font-size:22px;">🤖 24/7 AI Huquqiy Maslahatchi va Hujjatlar Generatori</h3>
            <span class="evidence-badge">Oʻzbekiston Qonunchiligi Asosida</span>
        </div>
        <p style="color:var(--text-muted); font-size:14.5px; margin-bottom:18px;">
            Har qanday huquqiy muammoingizni yozing (yoki ovozli yuboring) — AI Maslahatchi tegishli Kodeks moddalari bilan yechim beradi hamda Sudga daʼvo arizasi, Shikoyat yoki Shartnoma tayyorlab beradi.
        </p>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px;">
            <button class="chip active" onclick="setAdvisorMode('Huquqiy maslahat va moddalar tahlili', this)">⚖️ Huquqiy savol-javob</button>
            <button class="chip" onclick="setAdvisorMode('Sudga Da\'vo arizasi yoki Shikoyat loyihasini yozish', this)">📝 Daʼvo arizasi / Shikoyat tuzish</button>
            <button class="chip" onclick="setAdvisorMode('Ikki tomonlama Shartnoma loyihasini tuzish', this)">🤝 Shartnoma namunasi</button>
            <button class="chip" onclick="setAdvisorMode('Maktab va OTM talabalari uchun huquqiy masalani yechish', this)">🎓 Huquqiy masala yechimi</button>
        </div>

        <div class="chat-stream" id="advisorStream" style="background:var(--bg-dark); border-radius:14px; border:1px solid var(--border); margin-bottom:16px; min-height:320px;">
            <div class="msg-bubble msg-judge">
                <span class="msg-sender">🤖 AI Yurist-Maslahatchi</span>
                Assalomu alaykum! Sizga qanday huquqiy yordam yoki hujjat loyihasi kerak? Savolingizni quyida yozing yoki 🎙 mikrofon tugmasini bosib ovozli soʻrang.
            </div>
        </div>

        <div class="input-row">
            <button class="mic-btn" id="advisorMicBtn" onclick="toggleVoiceRecording('advisor')" title="Ovozli savol berish">🎙</button>
            <textarea id="advisorInput" placeholder="Masalan: Ish beruvchi meni ogohlantirishsiz ishdan bo'shatdi, qanday huquqlarim bor? Yoki: Qarzni undirish bo'yicha da'vo arizasi yozib ber..."></textarea>
            <button class="send-btn" onclick="sendAdvisorQuery()">Yuborish ➤</button>
        </div>
    </div>
</div>

<!-- DALILNI KO'RISH MODALI -->
<div class="modal-overlay" id="evidenceModal" onclick="if(event.target===this) closeModal()">
    <div class="modal-card">
        <h3 id="modalTitle">📄 Ashyoviy Dalil</h3>
        <p id="modalBody"></p>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button class="action-pill" onclick="closeModal()">Yopish</button>
            <button class="send-btn" style="height:40px; padding:0 18px;" onclick="citeEvidenceInInput()">📌 Nutqqa dalil sifatida qoʻshish</button>
        </div>
    </div>
</div>

@php
    $apiRoutes = [
        'courtTurn' => route('api.court-hall.turn'),
        'courtCase' => route('api.court-hall.cases.store'),
        'courtHint' => route('api.court-hall.hint'),
        'courtVerdict' => route('api.court-hall.verdict'),
        'advisor' => route('api.advisor'),
    ];
@endphp
<script>
    window.API_ROUTES = @json($apiRoutes);
</script>
<script src="{{ asset('js/api.js') }}"></script>
<script src="{{ asset('js/court-hall.js') }}"></script>
</body>
</html>
