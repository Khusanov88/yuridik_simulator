// ==================== SERVER BILAN ALOQA ====================
// Server faqat oxirgi 3000 belgini ishlatadi, qolganini yuborish shart emas.
const HISTORY_LIMIT = 3000;
const FILE_TEXT_LIMIT = 6000;

// ==================== DASTLABKI MA'LUMOTLAR ====================
const LEVELS = {
    maktab: { name: "Maktab o'quvchisi", rule: "Maktab o'quvchisiga tushunarli, Konstitutsiya va bazaviy qonunlar asosida sodda savol-javob qiling." },
    talaba: { name: "Yuridik talaba", rule: "Yuridik OTM talabasi darajasida Kodeks moddalari va nazariy asoslarni so'rang." },
    yurist: { name: "Amaliyotchi yurist", rule: "Protsessual normalar, isbotlash majburiyati va dalillar maqbulligi bo'yicha professional talab qo'ying." },
    ekspert: { name: "Sudya va Ekspert", rule: "Oliy sud Plenumi qarorlari va murakkab huquqiy kolliziyalar darajasida tahlil qiling." }
};

const COURT_CASES = {
    "Fuqarolik sudi": {
        title: "ISH № 201 — Meros va majburiy ulush nizosi",
        desc: "Fuqaro A. Toshkentdagi uyini vasiyatnoma orqali faqat katta o'g'liga qoldirdi. Marhumning 60 yoshli (II guruh nogironi) xotini va uyni o'z puliga ta'mirlagan kenja o'g'li uydan ko'chirilmoqda. Siz nogiron ona va kenja o'g'il advokatisiz.",
        evidences: [
            "TMEK ma'lumotnomasi | Marhumning xotini 60 yoshda va II guruh nogironi (mehnatga qobiliyatsiz).",
            "Ta'mirlash cheklari | Kenja o'g'il uyni kapital ta'mirlash uchun 180 mln so'm sarflagan.",
            "FK 1142-modda va OK 23-modda | Mehnatga qobiliyatsiz xotin majburiy ulush (kamida 1/2) oladi."
        ],
        opp: "FK 1120-moddasiga ko'ra vasiyat erkinligi bor, ota uyni faqat katta o'g'ilga vasiyat qilgan. Da'voni rad etishni so'raymiz!",
        judge: "Da'vogar advokati, vasiyatnoma mavjud bo'lgan holatda onaning va kenja o'g'ilning huquqlarini qaysi moddalar va dalillar bilan asoslaysiz?"
    },
    "Jinoyat sudi": {
        title: "ISH № 305 — JK 167 va 209-moddalar (Kamomad)",
        desc: "Davlat korxonasi omboridan 450 mln so'mlik kamomad chiqqan. Bosh hisobchiga elektron imzo (ERI) orqali soxta hujjat tasdiqlash aybi qo'yilgan. Siz bosh hisobchi advokatisiz.",
        evidences: [
            "Xizmat tekshiruvi | ERI kaliti direktor seyfida turgan va paroli 4 kishiga ma'lum bo'lgan.",
            "Xizmat safari buyrug'i | Hujjat imzolangan kunlari bosh hisobchi Samarqandda xizmat safarida bo'lgan.",
            "JPK 23 va 95-1-moddalar | Aybdorlikka oid barcha shubhalar sudlanuvchi foydasiga hal qilinadi."
        ],
        opp: "Hujjatlarda bosh hisobchining ERI imzosi turibdi, ombor mudiri ham unga qarshi ko'rsatma berdi. 8 yil jazo so'rayman!",
        judge: "Himoyachi, ERI imzosi va ombor mudiri ko'rsatmasiga qarshi qanday himoya dalillaringiz bor?"
    },
    "Ma'muriy sud": {
        title: "ISH № 402 — Hokim qarorini haqiqiy emas deb topish",
        desc: "Tuman hokimi tadbirkorni ogohlantirmasdan va kompensatsiya to'lamasdan 2 qavatli do'konni buzish haqida qaror chiqardi.",
        evidences: [
            "Kadastr guvohnomasi | 2 qavatli bino qonuniy xususiy mulk sifatida ro'yxatdan o'tgan.",
            "Pochta ma'lumotnomasi | Tadbirkor qaror qabul qilinishidan oldin tinglash uchun chaqirilmagan.",
            "Ma'muriy tartib-taomillar qonuni | Manfaatdor shaxsni tinglash majburiyati va mutanosiblik prinsipi."
        ],
        opp: "Do'kon oldidagi 3 metrlik naves chizmadan chiqqan, hokim obodonlashtirish uchun buzishga haqli!",
        judge: "Arizachi advokati, hokim qarorini haqiqiy emas deb topish uchun qonuniy asoslaringizni keltiring."
    },
    "Iqtisodiy sud": {
        title: "ISH № 104 — Yetkazib berish va zarar nizosi",
        desc: "Kuryerlik firmasi kuryeri YTH sodir etib, restoranning 15 mln so'mlik banket taomlarini yaroqsiz qildi. Siz restoran advokatisiz.",
        evidences: [
            "Shartnoma №18 | Kuryerlik firmasi buyurtmani but yetkazish majburiyatini olgan.",
            "YHXBB ma'lumotnomasi | YTH kuryer aybi bilan sodir bo'lgan, zarar 15 mln so'm.",
            "FK 14 va 334-moddalar | Qarzdor uchinchi shaxs (frilanser) harakati uchun ham bevosita javobgar."
        ],
        opp: "Biz faqat masalliq narxini (5 mln) to'laymiz, qolganini frilanser kuryerdan undiringlar!",
        judge: "Da'vogar vakili, javobgar yuristining e'tiroziga FK moddalari asosida javob bering."
    },
    "Apellyatsiya shikoyati": {
        title: "ISH № 508 — Apellyatsiya instansiyasi",
        desc: "Birinchi instansiya sudi mijozingizni xabardor qilmasdan 200 mln qarz va 180 mln penya undirgan.",
        evidences: [
            "Pochta reestri | Chaqiruv qog'ozi korxonaning eski manziliga yuborilgan.",
            "FK 326-modda va IPK | Xabardor qilmaslik qarorni bekor qilishga asos, penya kamaytirilishi lozim."
        ],
        opp: "Shartnomada kuniga 1% penya yozilgan, qaror qonuniy!",
        judge: "Apellyatsiya shikoyati muallifi, quyi sud qarorini bekor qilish asoslarini bayon qiling."
    },
    "Kassatsiya shikoyati": {
        title: "ISH № 612 — Kassatsiya (Da'vo muddati)",
        desc: "2021-yilgi shartnoma bo'yicha 2026-yilda da'vo kiritilgan. 3 yillik da'vo muddati o'tgan bo'lsa ham quyi sudlar qarz undirgan.",
        evidences: [
            "Ariza nusxasi | Birinchi instansiyada da'vo muddatini qo'llash haqida ariza berilgan.",
            "FK 150 va 153-moddalar | Da'vo muddati o'tishi da'voni rad etish uchun mustaqil asosdir."
        ],
        opp: "Ikki instansiya qaror chiqargan, kassatsiya buni o'zgartirmasligi kerak!",
        judge: "Kassatsiya shikoyati beruvchi advokat, quyi sudlar qaysi moddiy huquq normasini buzgan?"
    },
    "Hakamlik sudi": {
        title: "ISH № 704 — Hakamlik sudi vakolati",
        desc: "Bank davlat ulushi bo'lgan binoni undirish va kadastr bo'limiga majburiyat yuklash haqida Hakamlik sudiga murojaat qilgan.",
        evidences: [
            "Davlat aktivlari ma'lumotnomasi | Binoning 51% qismi davlat ulushi hisoblanadi.",
            "Hakamlik sudlari to'g'risidagi Qonun | Ma'muriy organlar bilan bog'liq nizolar hakamlik sudiga taalluqli emas."
        ],
        opp: "Shartnomada hakamlik bitimi bor, demak ish shu yerda ko'riladi!",
        judge: "Javobgar vakili, Hakamlik sudi vakolati bo'yicha e'tirozingizni bildiring."
    }
};

let savedFiles = JSON.parse(localStorage.getItem("safelaw_files_v1") || "null") || [
    { id: 1, title: "TALAB QILISH HUQUQINI O'TKAZISH TO'G'RISIDA SHARTNOMA", date: "20 okt. 2026 y.", size: "12 KB", content: "TALAB QILISH HUQUQINI O'TKAZISH SHARTNOMASI (SESSIYA)\n\nO'zbekiston Respublikasi Fuqarolik kodeksining 313-320-moddalariga asosan tuzildi..." },
    { id: 2, title: "MUDDATLI TO'LOV SHARTI BILAN OLDI-SOTDI SHARTNOMASI", date: "20 okt. 2026 y.", size: "24 KB", content: "MUDDATLI TO'LOV SHARTI BILAN OLDI-SOTDI SHARTNOMASI\n\nFuqarolik kodeksining 421-moddasiga muvofiq tovarni bo'lib to'lash sharti bilan yetkazib berish..." },
    { id: 3, title: "IQTISODIY SUDGA DA'VO ARIZASI (ZARARNI UNDIRISH)", date: "15 okt. 2026 y.", size: "14 KB", content: "Toshkent tumanlararo iqtisodiy sudiga\nDA'VO ARIZASI\nO'zbekiston Respublikasi FK 14 va 334-moddalariga asosan yetkazilgan 15 000 000 so'm zararni undirish to'g'risida..." },
    { id: 4, title: "MEHNAT SHARTNOMASINI BEKOR QILISH USTIDAN SHIKOYAT", date: "15 okt. 2026 y.", size: "27 KB", content: "Fuqarolik ishlari bo'yicha sudga\nMehnat kodeksining 161-moddasi talablari buzilgan holda ishdan bo'shatilganlik sababli ishga tiklash to'g'risida..." }
];

let legalNews = [
    { title: "Mehnat kodeksiga masofaviy ish va elektron mehnat shartnomalari boʻyicha yangi tartib kiritildi", category: "Mehnat huquqi", date: "23.10.2026", summary: "Endilikda xodim bilan mehnat shartnomasini Yagona milliy mehnat tizimi orqali masofadan turib ERI yoki Face-ID orqali tasdiqlash tartibi soddalashtirildi." },
    { title: "Tadbirkorlarning xususiy mulkini olib qoʻyishda kompensatsiya oldindan 100% toʻlanishi qatʼiy belgilandi", category: "Tadbirkorlik va Mulk", date: "22.10.2026", summary: "Maʼmuriy sudlarda hokimlik qarorlari ustidan nizolarni koʻrishda mutanosiblik va mulkdor roziligi birlamchi shart sifatida mustahkamlandi." },
    { title: "Muddatli toʻlov (rassrochka) shartnomalarida neustoyka (penya) miqdoriga cheklov oʻrnatildi", category: "Fuqarolik huquqi", date: "20.10.2026", summary: "Isteʼmolchi huquqlarini himoya qilish maqsadida kechiktirilgan toʻlovlar uchun hisoblanadigan penya asosiy qarzning 50 foizidan oshmasligi belgilandi." },
    { title: "Sud majlislarida sunʼiy intellekt stenogrammasi va elektron dalillarni qabul qilish tartibi yangilandi", category: "Sud-huquq islohoti", date: "18.10.2026", summary: "Barcha instansiya sudlarida audio va video yozuvlar avtomatik ravishda elektron bayonnomaga aylantirilishi yoʻlga qoʻyildi." }
];

let currentMode = "general";
let attachedFileContent = "";
let mainChatHistory = "";
let simHistory = "";
let mediaRecorder = null;
let audioChunks = [];
let isRecording = false;
let voiceTarget = "home";

window.onload = () => {
    renderFilesGrid();
    renderNewsGrid();
    changeSimConfig();
};

function openView(name) {
    document.querySelectorAll(".view-section").forEach(s => s.classList.remove("active"));
    document.querySelectorAll(".nav-item").forEach(n => n.classList.remove("active"));
    document.getElementById("view-" + name).classList.add("active");
    const navBtn = document.getElementById("nav-" + name);
    if (navBtn) navBtn.classList.add("active");
}

function resetHomeChat() {
    openView("home");
    mainChatHistory = "";
    currentMode = "general";
    document.getElementById("mainChatLog").style.display = "none";
    document.getElementById("mainChatLog").innerHTML = "";
    document.getElementById("heroBlock").style.display = "flex";
    document.getElementById("aiInput").value = "";
}

function startQuickAction(mode, promptText) {
    openView("home");
    currentMode = mode;
    const input = document.getElementById("aiInput");
    input.value = promptText;
    input.focus();
}

function handleFileAttach(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        attachedFileContent = e.target.result;
        const badge = document.getElementById("attachedFileBadge");
        badge.style.display = "block";
        badge.innerText = "📎 Biriktirildi: " + file.name;
    };
    reader.readAsText(file);
}

// ==================== 1. AI YORDAMCHI MULOQOTI ====================
async function sendAIChat(audioB64 = null, audioMime = null) {
    const input = document.getElementById("aiInput");
    const q = input.value.trim();
    if (!q && !audioB64) return;

    document.getElementById("heroBlock").style.display = "none";
    const log = document.getElementById("mainChatLog");
    log.style.display = "flex";

    const userBubble = document.createElement("div");
    userBubble.className = "chat-msg user";
    userBubble.innerText = audioB64 ? "🎙 [Ovozli murojaat yuborildi]" : q;
    log.appendChild(userBubble);
    input.value = "";
    document.getElementById("attachedFileBadge").style.display = "none";

    const aiBubble = document.createElement("div");
    aiBubble.className = "chat-msg ai";
    aiBubble.innerHTML = `<div class="msg-head"><span>Yuridik AI</span></div><div class="msg-body" style="color:var(--text-secondary);">Qonunchilik bazasi tahlil qilinmoqda...</div>`;
    log.appendChild(aiBubble);
    log.scrollTop = log.scrollHeight;

    try {
        const data = await apiPost(window.API_ROUTES.assistantChat, {
            mode: currentMode,
            question: q,
            file_text: attachedFileContent.slice(0, FILE_TEXT_LIMIT),
            history: mainChatHistory.slice(-HISTORY_LIMIT),
            audio_base64: audioB64,
            audio_mime: audioMime
        });
        attachedFileContent = "";
        const answer = data.answer || "Xatolik yuz berdi.";

        aiBubble.innerHTML = `
            <div class="msg-head">
                <span>Yuridik AI — Huquqiy xulosa</span>
                <button class="btn-sm">💾 Fayllarimga saqlash (.DOCX)</button>
            </div>
            <div>${escapeHtml(answer).replace(/\n/g, "<br>")}</div>
        `;
        aiBubble.querySelector("button").addEventListener("click", () => saveAnswerToFiles(answer));
        mainChatHistory += "Savol: " + q + "\nJavob: " + answer + "\n---\n";
    } catch (e) {
        aiBubble.innerText = "Aloqada xatolik yuz berdi.";
    }
}

function saveAnswerToFiles(content) {
    const firstLine = content.split("\n")[0].substring(0, 50).toUpperCase() || "YURIDIK HUJJAT";
    const newDoc = {
        id: Date.now(),
        title: firstLine,
        date: new Date().toLocaleDateString("uz-UZ"),
        size: Math.max(8, Math.round(content.length / 120)) + " KB",
        content: content
    };
    savedFiles.unshift(newDoc);
    localStorage.setItem("safelaw_files_v1", JSON.stringify(savedFiles));
    renderFilesGrid();
    openView("files");
}

// ==================== 2. MENING FAYLLARIM ====================
function renderFilesGrid(query = "") {
    const grid = document.getElementById("filesGrid");
    const q = query.toLowerCase();
    const filtered = savedFiles.filter(f => f.title.toLowerCase().includes(q));
    document.getElementById("filesCountText").innerText = "Jami: " + filtered.length;
    grid.innerHTML = "";

    filtered.forEach(f => {
        const card = document.createElement("div");
        card.className = "file-card";
        card.innerHTML = `
            <div class="file-top">
                <div class="file-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <div class="file-name" title="${escapeHtml(f.title)}">${escapeHtml(f.title)}</div>
                    <div class="file-meta"><span>DOCX</span> ${escapeHtml(f.date)} · ${escapeHtml(f.size)}</div>
                </div>
            </div>
            <div class="file-actions">
                <div style="display:flex; gap:6px;">
                    <button class="btn-sm primary" onclick="downloadDocFile(${f.id})">⬇ Yuklab olish</button>
                    <button class="btn-sm" onclick="openFileInChat(${f.id})">💬 Chatga</button>
                </div>
                <button class="btn-sm danger" onclick="deleteDocFile(${f.id})">🗑 Oʻchirish</button>
            </div>
        `;
        grid.appendChild(card);
    });
}

function downloadDocFile(id) {
    const f = savedFiles.find(x => x.id === id);
    if (!f) return;
    const blob = new Blob(["\ufeff" + f.content], { type: "application/msword;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = f.title.replace(/[^a-zA-Z0-9А-Яа-яЎўҚқҒғҲҳ_\- ]/g, "") + ".doc";
    a.click();
}

function openFileInChat(id) {
    const f = savedFiles.find(x => x.id === id);
    if (!f) return;
    attachedFileContent = f.content;
    openView("home");
    const badge = document.getElementById("attachedFileBadge");
    badge.style.display = "block";
    badge.innerText = "📎 Tanlangan hujjat: " + f.title;
    document.getElementById("aiInput").value = "Ushbu hujjatni O'zbekiston qonunchiligi bo'yicha tekshirib, kamchiliklarini aytib bering.";
}

function deleteDocFile(id) {
    savedFiles = savedFiles.filter(x => x.id !== id);
    localStorage.setItem("safelaw_files_v1", JSON.stringify(savedFiles));
    renderFilesGrid();
}

// ==================== 3. HUQUQIY YANGILIKLAR ====================
function renderNewsGrid() {
    const grid = document.getElementById("newsGrid");
    grid.innerHTML = "";
    legalNews.forEach((n, i) => {
        const card = document.createElement("div");
        card.className = "news-card";
        card.innerHTML = `
            <div>
                <span class="news-badge">${escapeHtml(n.category)} · ${escapeHtml(n.date)}</span>
                <h3 style="font-size:16px; color:#fff; margin-bottom:8px; line-height:1.4;">${escapeHtml(n.title)}</h3>
                <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.55;">${escapeHtml(n.summary)}</p>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button class="btn-sm" onclick="explainNews(${i})">🤖 AI sharhini oʻqish</button>
            </div>
        `;
        grid.appendChild(card);
    });
}

function explainNews(idx) {
    const n = legalNews[idx];
    startQuickAction("news_explain", "Quyidagi qonunchilik yangiligini batafsil huquqiy sharhlab bering: " + n.title + " — " + n.summary);
    sendAIChat();
}

async function refreshLegalNews() {
    try {
        const data = await apiPost(window.API_ROUTES.newsRefresh, {});
        if (data.ok && Array.isArray(data.news)) {
            legalNews = data.news;
            renderNewsGrid();
        }
    } catch (e) {}
}

// ==================== 4. SUD SIMULYATORI ====================
function changeSimConfig() {
    const court = document.getElementById("courtSelect").value;
    const data = COURT_CASES[court] || COURT_CASES["Fuqarolik sudi"];
    document.getElementById("simCaseTitle").innerText = data.title;
    document.getElementById("simCaseDesc").innerText = data.desc;

    const evBox = document.getElementById("simEvidences");
    evBox.innerHTML = "";
    data.evidences.forEach(e => {
        const parts = e.split("|");
        const div = document.createElement("div");
        div.className = "ev-item";
        div.innerHTML = `<strong>📄 ${escapeHtml(parts[0].trim())}</strong><span>${parts[1] ? escapeHtml(parts[1].trim()) : ""}</span>`;
        div.onclick = () => {
            const inp = document.getElementById("simInput");
            inp.value += `Ish materiallaridagi «${parts[0].trim()}» daliliga asosan, `;
            inp.focus();
        };
        evBox.appendChild(div);
    });

    const msgBox = document.getElementById("simMessages");
    msgBox.innerHTML = "";
    appendSimMsg("opponent", "Qarshi tomon vakili", data.opp);
    appendSimMsg("judge", "Raislik qiluvchi (Sudya)", data.judge);
    simHistory = "Qarshi tomon: " + data.opp + "\nSudya: " + data.judge + "\n---\n";
}

function appendSimMsg(type, roleTitle, text) {
    const box = document.getElementById("simMessages");
    const div = document.createElement("div");
    div.className = "role-card " + type;
    div.innerHTML = `<span class="role-tag">${escapeHtml(roleTitle)}</span><div>${escapeHtml(text).replace(/\n/g, "<br>")}</div>`;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}

async function sendSimTurn(audioB64 = null, audioMime = null) {
    const inp = document.getElementById("simInput");
    const text = inp.value.trim();
    if (!text && !audioB64) return;

    const lvlKey = document.getElementById("levelSelect").value;
    const court = document.getElementById("courtSelect").value;

    if (!audioB64) {
        appendSimMsg("advocate", "Siz (Advokat)", text);
        inp.value = "";
    } else {
        appendSimMsg("advocate", "Ovozli nutq", "🎙 Ovozli nutq tahlil qilinmoqda...");
    }

    try {
        const data = await apiPost(window.API_ROUTES.courtTurn, {
            court_type: court,
            level_name: LEVELS[lvlKey].name,
            level_rule: LEVELS[lvlKey].rule,
            case_text: document.getElementById("simCaseDesc").innerText,
            evidence_list: Array.from(document.querySelectorAll(".ev-item")).map(el => el.innerText).join("\n"),
            history: simHistory.slice(-HISTORY_LIMIT),
            user_text: text,
            audio_base64: audioB64,
            audio_mime: audioMime
        });
        if (!data.ok) {
            appendSimMsg("judge", "Tizim", data.error || "Aloqada uzilish bo'ldi. Qayta urinib ko'ring.");
            return;
        }
        if (audioB64 && data.transcript) {
            appendSimMsg("advocate", "Sud kotibi (Stenogramma)", data.transcript);
        }
        appendSimMsg("opponent", "Qarshi tomon eʼtirozi", data.opponent);
        appendSimMsg("judge", "Raislik qiluvchi (Sudya)", data.judge);
        simHistory += "Advokat: " + (data.transcript || text) + "\nQarshi tomon: " + data.opponent + "\nSudya: " + data.judge + "\n---\n";
    } catch (e) {}
}

async function generateNewSimCase() {
    const lvlKey = document.getElementById("levelSelect").value;
    const court = document.getElementById("courtSelect").value;
    appendSimMsg("judge", "Tizim", "Yangi sud ishi va dalillar shakllantirilmoqda...");
    try {
        const data = await apiPost(window.API_ROUTES.courtCase, {
            court_type: court,
            level_name: LEVELS[lvlKey].name,
            level_rule: LEVELS[lvlKey].rule
        });
        if (!data.ok) {
            appendSimMsg("judge", "Tizim", data.error || "Yangi ish yaratishda uzilish bo'ldi. Qayta urinib ko'ring.");
            return;
        }
        document.getElementById("simCaseTitle").innerText = data.title;
        document.getElementById("simCaseDesc").innerText = data.desc;

        const evBox = document.getElementById("simEvidences");
        evBox.innerHTML = "";
        (data.evidences || []).forEach(e => {
            const parts = e.split("|");
            const div = document.createElement("div");
            div.className = "ev-item";
            div.innerHTML = `<strong>📄 ${escapeHtml(parts[0].trim())}</strong><span>${parts[1] ? escapeHtml(parts[1].trim()) : ""}</span>`;
            div.onclick = () => { document.getElementById("simInput").value += `«${parts[0].trim()}»ga ko'ra, `; };
            evBox.appendChild(div);
        });

        document.getElementById("simMessages").innerHTML = "";
        appendSimMsg("opponent", "Qarshi tomon vakili", data.opponent);
        appendSimMsg("judge", "Raislik qiluvchi (Sudya)", data.judge);
        simHistory = "Qarshi tomon: " + data.opponent + "\nSudya: " + data.judge + "\n---\n";
    } catch (e) {}
}

async function getSimHint() {
    const lvlKey = document.getElementById("levelSelect").value;
    const court = document.getElementById("courtSelect").value;
    const data = await apiPost(window.API_ROUTES.courtHint, {
        court_type: court,
        level_name: LEVELS[lvlKey].name,
        case_text: document.getElementById("simCaseDesc").innerText,
        history: simHistory.slice(-HISTORY_LIMIT)
    });
    if (data.ok) appendSimMsg("advocate", "Ustoz yurist maslahati", data.result);
}

async function getSimVerdict() {
    const lvlKey = document.getElementById("levelSelect").value;
    const court = document.getElementById("courtSelect").value;
    appendSimMsg("judge", "Sud maslahatxonasi", "Hal qiluv qarori tayyorlanmoqda...");
    const data = await apiPost(window.API_ROUTES.courtVerdict, {
        court_type: court,
        level_name: LEVELS[lvlKey].name,
        case_text: document.getElementById("simCaseDesc").innerText,
        history: simHistory.slice(-HISTORY_LIMIT)
    });
    if (data.ok) {
        appendSimMsg("judge", "OʻZBEKISTON RESPUBLIKASI NOMIDAN — YAKUNIY SUD HUJJATI", data.result);
    } else {
        appendSimMsg("judge", "Tizim", data.result || "Hukm chiqarishda uzilish bo'ldi.");
    }
}

// ==================== OVOZLI XABAR (MIKROFON) ====================
async function toggleVoice(target) {
    const btn = document.getElementById(target === "home" ? "homeMicBtn" : "courtMicBtn");
    if (isRecording) {
        mediaRecorder.stop();
        isRecording = false;
        btn.classList.remove("recording");
        return;
    }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        voiceTarget = target;
        audioChunks = [];
        mediaRecorder = new MediaRecorder(stream);
        mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
        mediaRecorder.onstop = () => {
            const mime = mediaRecorder.mimeType || "audio/webm";
            const blob = new Blob(audioChunks, { type: mime });
            stream.getTracks().forEach(t => t.stop());
            const reader = new FileReader();
            reader.onloadend = () => {
                const b64 = reader.result.split(",")[1];
                if (voiceTarget === "home") sendAIChat(b64, mime);
                else sendSimTurn(b64, mime);
            };
            reader.readAsDataURL(blob);
        };
        mediaRecorder.start();
        isRecording = true;
        btn.classList.add("recording");
    } catch (e) {
        alert("Mikrofondan foydalanish uchun brauzerda ruxsat bering.");
    }
}

function filterHistory(q) {
    const val = q.toLowerCase();
    document.querySelectorAll(".history-item").forEach(el => {
        el.style.display = el.innerText.toLowerCase().includes(val) ? "block" : "none";
    });
}
