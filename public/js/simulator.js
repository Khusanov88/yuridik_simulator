// ==================== SOZLAMALAR ====================
// Server faqat oxirgi 3000 belgini ishlatadi, qolganini yuborish shart emas.
const HISTORY_LIMIT = 3000;
const MAX_FILE_SIZE = 1024 * 1024;
const DOCUMENTS_KEY = "yuridik_documents";
const LEGACY_DOCUMENTS_KEY = "safelaw_files_v1";
const API = window.API_ROUTES;

const MODE_LABELS = {
    doc_create: "📄 Hujjat yaratish",
    doc_analyze: "🔍 Hujjat tahlili",
    news_explain: "📰 Yangilik sharhi"
};

const ICONS = {
    scale: '<svg viewBox="0 0 24 24"><path d="M12 3v18M7 21h10M3 7h18M3 7l-2.5 6a3 3 0 0 0 5 0zM21 7l-2.5 6a3 3 0 0 0 5 0z"/></svg>',
    close: '<svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>'
};

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

let legalNews = [
    { title: "Mehnat kodeksiga masofaviy ish va elektron mehnat shartnomalari boʻyicha yangi tartib kiritildi", category: "Mehnat huquqi", date: "23.10.2026", summary: "Endilikda xodim bilan mehnat shartnomasini Yagona milliy mehnat tizimi orqali masofadan turib ERI yoki Face-ID orqali tasdiqlash tartibi soddalashtirildi." },
    { title: "Tadbirkorlarning xususiy mulkini olib qoʻyishda kompensatsiya oldindan 100% toʻlanishi qatʼiy belgilandi", category: "Tadbirkorlik va mulk", date: "22.10.2026", summary: "Maʼmuriy sudlarda hokimlik qarorlari ustidan nizolarni koʻrishda mutanosiblik va mulkdor roziligi birlamchi shart sifatida mustahkamlandi." },
    { title: "Muddatli toʻlov (rassrochka) shartnomalarida neustoyka (penya) miqdoriga cheklov oʻrnatildi", category: "Fuqarolik huquqi", date: "20.10.2026", summary: "Isteʼmolchi huquqlarini himoya qilish maqsadida kechiktirilgan toʻlovlar uchun hisoblanadigan penya asosiy qarzning 50 foizidan oshmasligi belgilandi." },
    { title: "Sud majlislarida sunʼiy intellekt stenogrammasi va elektron dalillarni qabul qilish tartibi yangilandi", category: "Sud-huquq islohoti", date: "18.10.2026", summary: "Barcha instansiya sudlarida audio va video yozuvlar avtomatik ravishda elektron bayonnomaga aylantirilishi yoʻlga qoʻyildi." }
];

const state = {
    mode: "general",
    attachment: null,
    chatHistory: "",
    courtHistory: "",
    chatBusy: false,
    courtBusy: false,
    recorder: null
};

let documents = [];

// ==================== YORDAMCHILAR ====================
const $ = id => document.getElementById(id);

function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

function button(className, label, onClick) {
    const node = el("button", className, label);
    node.type = "button";
    node.addEventListener("click", onClick);
    return node;
}

function autoResize(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + "px";
}

let toastTimer = null;
function toast(message) {
    const node = $("toast");
    node.textContent = message;
    node.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { node.hidden = true; }, 3500);
}

// ==================== NAVIGATSIYA ====================
function openView(name) {
    const view = $("view-" + name);
    if (!view) return;
    document.querySelectorAll(".view").forEach(v => v.classList.toggle("active", v === view));
    document.querySelectorAll(".nav-item[data-view]").forEach(n => n.classList.toggle("active", n.dataset.view === name));
    $("viewTitle").textContent = view.dataset.title;
    closeSidebar();
}

function openSidebar() { document.body.classList.add("sidebar-open"); }
function closeSidebar() { document.body.classList.remove("sidebar-open"); }

// ==================== AI-YORDAMCHI ====================
function setMode(mode) {
    state.mode = mode in MODE_LABELS ? mode : "general";
    renderChips();
}

function renderChips() {
    const chips = $("composerChips");
    chips.replaceChildren();
    if (state.mode !== "general") {
        chips.append(chip(MODE_LABELS[state.mode], () => setMode("general")));
    }
    if (state.attachment) {
        chips.append(chip("📎 " + state.attachment.name, () => { state.attachment = null; renderChips(); }));
    }
}

function chip(label, onRemove) {
    const node = el("span", "chip", label);
    const remove = button("", "", onRemove);
    remove.innerHTML = ICONS.close;
    remove.setAttribute("aria-label", "Olib tashlash");
    node.append(remove);
    return node;
}

function usePrompt(mode, text) {
    openView("home");
    setMode(mode);
    const input = $("aiInput");
    input.value = text;
    autoResize(input);
    input.focus();
    input.setSelectionRange(text.length, text.length);
}

function startNewChat() {
    state.chatHistory = "";
    state.attachment = null;
    setMode("general");
    $("chatMessages").replaceChildren();
    $("welcome").hidden = false;
    $("aiInput").value = "";
    autoResize($("aiInput"));
    openView("home");
    $("aiInput").focus();
}

function attachFile(file) {
    if (!file) return;
    if (file.size > MAX_FILE_SIZE) {
        toast("Fayl juda katta. 1 MB gacha boʻlgan .txt fayl yuklang.");
        return;
    }
    const reader = new FileReader();
    reader.onload = () => {
        state.attachment = { name: file.name, text: String(reader.result) };
        renderChips();
        $("aiInput").focus();
    };
    reader.onerror = () => toast("Faylni oʻqib boʻlmadi.");
    reader.readAsText(file);
}

function scrollChat() {
    const scroll = $("chatScroll");
    scroll.scrollTop = scroll.scrollHeight;
}

function logo() {
    const node = el("span", "logo");
    node.innerHTML = ICONS.scale;
    return node;
}

function appendUserMessage(text, attachmentName) {
    const msg = el("div", "msg msg-user");
    const body = el("div", "msg-body");
    if (attachmentName) body.append(el("div", "msg-note", "📎 " + attachmentName));
    body.append(document.createTextNode(text));
    msg.append(body);
    $("chatMessages").append(msg);
    scrollChat();
}

function appendAiMessage() {
    const msg = el("div", "msg msg-ai");
    const content = el("div", "msg-content");
    const typing = el("div", "typing");
    typing.append(el("span"), el("span"), el("span"));
    content.append(typing);
    msg.append(logo(), content);
    $("chatMessages").append(msg);
    scrollChat();

    return {
        done(text) {
            typing.remove();
            content.append(el("div", "msg-body", text), messageActions(text));
            scrollChat();
        },
        fail(text) {
            msg.classList.add("msg-error");
            typing.remove();
            content.append(el("div", "msg-body", text));
            scrollChat();
        }
    };
}

function messageActions(text) {
    const actions = el("div", "msg-actions");
    const copy = button("btn btn-ghost btn-sm", "Nusxa olish", () => {
        navigator.clipboard?.writeText(text)
            .then(() => toast("Nusxa olindi"))
            .catch(() => toast("Nusxa olib boʻlmadi"));
    });
    const save = button("btn btn-ghost btn-sm", "Hujjatlarimga saqlash", () => {
        saveDocument(text);
        save.textContent = "✓ Saqlandi";
        save.disabled = true;
    });
    actions.append(copy, save);
    return actions;
}

function setChatBusy(busy) {
    state.chatBusy = busy;
    $("sendBtn").disabled = busy;
    $("homeMicBtn").disabled = busy;
}

async function sendChat(audio = null) {
    if (state.chatBusy) return;
    const input = $("aiInput");
    const question = input.value.trim();
    const attachment = state.attachment;
    if (!question && !audio && !attachment) return;

    $("welcome").hidden = true;
    appendUserMessage(audio ? "🎙 Ovozli savol" : (question || "Biriktirilgan hujjatni koʻrib chiqing."), attachment?.name);
    input.value = "";
    autoResize(input);
    state.attachment = null;
    renderChips();

    const reply = appendAiMessage();
    setChatBusy(true);
    try {
        const data = await apiPost(API.assistantChat, {
            mode: state.mode,
            question: question,
            file_text: attachment?.text ?? "",
            history: state.chatHistory.slice(-HISTORY_LIMIT),
            audio_base64: audio?.data ?? null,
            audio_mime: audio?.mime ?? null
        });
        if (!data.ok) {
            reply.fail(data.answer || "Soʻrovni bajarib boʻlmadi. Qayta urinib koʻring.");
            return;
        }
        reply.done(data.answer);
        state.chatHistory += "Savol: " + (question || "[ovozli savol]") + "\nJavob: " + data.answer + "\n---\n";
    } catch (e) {
        reply.fail("Aloqada xatolik yuz berdi. Internetni tekshirib, qayta urinib koʻring.");
    } finally {
        setChatBusy(false);
    }
}

// ==================== MENING HUJJATLARIM ====================
/** Eski versiya koʻrsatgan namuna hujjatlar (id 1–4, "..." bilan tugaydi) haqiqiy hujjat emas. */
function isRealDocument(doc) {
    return doc && typeof doc.content === "string" && typeof doc.title === "string"
        && !(doc.id <= 4 && doc.content.endsWith("..."));
}

function loadDocuments() {
    try {
        const stored = localStorage.getItem(DOCUMENTS_KEY) ?? localStorage.getItem(LEGACY_DOCUMENTS_KEY);
        const parsed = JSON.parse(stored || "[]");
        return Array.isArray(parsed) ? parsed.filter(isRealDocument) : [];
    } catch (e) {
        return [];
    }
}

function storeDocuments() {
    try {
        localStorage.setItem(DOCUMENTS_KEY, JSON.stringify(documents));
        localStorage.removeItem(LEGACY_DOCUMENTS_KEY);
    } catch (e) {
        toast("Hujjatni brauzerda saqlab boʻlmadi.");
    }
}

function saveDocument(content) {
    const firstLine = content.split("\n").map(line => line.trim()).find(Boolean) || "Yuridik hujjat";
    documents.unshift({
        id: Date.now(),
        title: firstLine.slice(0, 80),
        date: new Date().toLocaleDateString("ru-RU"),
        size: Math.max(1, Math.ceil(new Blob([content]).size / 1024)) + " KB",
        content: content
    });
    storeDocuments();
    renderDocuments();
    toast("«Mening hujjatlarim» boʻlimiga saqlandi");
}

function renderDocuments() {
    const query = $("filesSearch").value.trim().toLowerCase();
    const visible = documents.filter(doc => doc.title.toLowerCase().includes(query));

    $("filesCount").textContent = documents.length;
    $("filesCount").hidden = documents.length === 0;
    $("filesGrid").replaceChildren(...visible.map(documentCard));

    const empty = $("filesEmpty");
    empty.hidden = visible.length > 0;
    empty.querySelector("strong").textContent = documents.length ? "Hech narsa topilmadi" : "Hozircha hujjat yoʻq";
    empty.querySelector("span").hidden = documents.length > 0;
}

function documentCard(doc) {
    const card = el("article", "card");
    const meta = el("div", "card-meta");
    meta.append(el("span", "badge", "DOC"), el("span", "", doc.date + " · " + doc.size));
    const preview = doc.content.length > 160 ? doc.content.slice(0, 160) + "…" : doc.content;

    const actions = el("div", "card-actions");
    actions.append(
        button("btn btn-primary btn-sm", "Yuklab olish", () => downloadDocument(doc)),
        button("btn btn-ghost btn-sm", "Chatda ochish", () => openDocumentInChat(doc)),
        button("btn btn-danger btn-sm", "Oʻchirish", () => deleteDocument(doc))
    );

    card.append(meta, el("h3", "file-title", doc.title), el("p", "", preview), actions);
    return card;
}

function downloadDocument(doc) {
    const blob = new Blob(["﻿" + doc.content], { type: "application/msword;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = (doc.title.replace(/[\\/:*?"<>|]+/g, "").trim() || "hujjat") + ".doc";
    link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function openDocumentInChat(doc) {
    state.attachment = { name: doc.title, text: doc.content };
    usePrompt("doc_analyze", "Ushbu hujjatni Oʻzbekiston qonunchiligi boʻyicha tekshirib, kamchiliklarini aytib bering.");
}

function deleteDocument(doc) {
    if (!confirm("«" + doc.title + "» hujjatini oʻchirasizmi?")) return;
    documents = documents.filter(item => item.id !== doc.id);
    storeDocuments();
    renderDocuments();
}

// ==================== HUQUQIY YANGILIKLAR ====================
function renderNews() {
    $("newsGrid").replaceChildren(...legalNews.map((news, index) => {
        const card = el("article", "card");
        const meta = el("div", "card-meta");
        meta.append(el("span", "badge", news.category || "Qonunchilik"), el("span", "", news.date || ""));
        const actions = el("div", "card-actions");
        actions.append(button("btn btn-ghost btn-sm", "🤖 AI sharhi", () => explainNews(index)));
        card.append(meta, el("h3", "", news.title || ""), el("p", "", news.summary || ""), actions);
        return card;
    }));
}

function explainNews(index) {
    const news = legalNews[index];
    usePrompt("news_explain", "Quyidagi qonunchilik yangiligini batafsil sharhlab bering: " + news.title + " — " + news.summary);
    sendChat();
}

async function refreshNews(trigger) {
    const label = trigger.textContent;
    trigger.disabled = true;
    trigger.textContent = "Yangilanmoqda…";
    try {
        const data = await apiPost(API.newsRefresh, {});
        const fresh = Array.isArray(data.news) ? data.news.filter(item => item && item.title) : [];
        if (data.ok && fresh.length) {
            legalNews = fresh;
            renderNews();
        } else {
            toast(data.result || "Yangiliklarni yangilab boʻlmadi. Keyinroq urinib koʻring.");
        }
    } catch (e) {
        toast("Aloqada xatolik yuz berdi.");
    } finally {
        trigger.disabled = false;
        trigger.textContent = label;
    }
}

// ==================== SUD SIMULYATORI ====================
function currentLevel() {
    return LEVELS[$("levelSelect").value] || LEVELS.talaba;
}

function loadCourtCase() {
    const data = COURT_CASES[$("courtSelect").value] || COURT_CASES["Fuqarolik sudi"];
    showCase(data.title, data.desc, data.evidences, data.opp, data.judge);
}

function showCase(title, desc, evidences, opponent, judge) {
    $("caseTitle").textContent = title;
    $("caseDesc").textContent = desc;
    renderEvidences(evidences);
    $("courtMessages").replaceChildren();
    appendCourtMessage("opponent", "Qarshi tomon", opponent);
    appendCourtMessage("judge", "Raislik qiluvchi (sudya)", judge);
    state.courtHistory = "Qarshi tomon: " + opponent + "\nSudya: " + judge + "\n---\n";
}

function renderEvidences(evidences) {
    $("evidenceList").replaceChildren(...evidences.map(evidence => {
        const [name, ...rest] = evidence.split("|");
        const title = name.trim();
        const item = button("evidence", "", () => {
            const input = $("courtInput");
            const separator = input.value && !input.value.endsWith(" ") ? " " : "";
            input.value += separator + "«" + title + "» daliliga koʻra, ";
            autoResize(input);
            input.focus();
        });
        item.append(el("strong", "", "📄 " + title), el("span", "", rest.join("|").trim()));
        return item;
    }));
}

function appendCourtMessage(role, name, text) {
    const box = $("courtMessages");
    const msg = el("div", "role-msg role-" + role);
    msg.append(el("span", "role-name", name), el("div", "role-text", text));
    box.append(msg);
    box.scrollTop = box.scrollHeight;
    return msg;
}

function courtContext() {
    const level = currentLevel();
    return {
        court_type: $("courtSelect").value,
        level_name: level.name,
        level_rule: level.rule,
        case_text: $("caseDesc").textContent,
        evidence_list: Array.from(document.querySelectorAll(".evidence")).map(item => item.innerText).join("\n"),
        history: state.courtHistory.slice(-HISTORY_LIMIT)
    };
}

function setCourtBusy(busy) {
    state.courtBusy = busy;
    document.querySelectorAll('#view-court [data-action]').forEach(control => { control.disabled = busy; });
}

/** Soʻrov davomida kutish xabarini koʻrsatadi; xatoda null qaytaradi. */
async function courtRequest(pendingText, request) {
    if (state.courtBusy) return null;
    setCourtBusy(true);
    const pending = appendCourtMessage("system", "Sud kotibi", pendingText);
    try {
        return await request();
    } catch (e) {
        appendCourtMessage("system", "Sud kotibi", "⚠️ Aloqada xatolik yuz berdi. Qayta urinib koʻring.");
        return null;
    } finally {
        pending.remove();
        setCourtBusy(false);
    }
}

async function sendCourtTurn(audio = null) {
    const input = $("courtInput");
    const text = input.value.trim();
    if (state.courtBusy || (!text && !audio)) return;

    appendCourtMessage("advocate", "Siz (advokat)", audio ? "🎙 Ovozli nutq" : text);
    input.value = "";
    autoResize(input);

    const data = await courtRequest("Sud nutqingizni tinglamoqda…", () => apiPost(API.courtTurn, {
        ...courtContext(),
        user_text: text,
        audio_base64: audio?.data ?? null,
        audio_mime: audio?.mime ?? null
    }));
    if (!data) return;
    if (!data.ok) {
        appendCourtMessage("system", "Sud kotibi", "⚠️ " + (data.error || "Aloqada uzilish boʻldi. Qayta urinib koʻring."));
        return;
    }

    if (audio && data.transcript) {
        appendCourtMessage("advocate", "Stenogramma", data.transcript);
    }
    appendCourtMessage("opponent", "Qarshi tomon eʼtirozi", data.opponent);
    appendCourtMessage("judge", "Raislik qiluvchi (sudya)", data.judge);
    state.courtHistory += "Advokat: " + (data.transcript || text) + "\nQarshi tomon: " + data.opponent + "\nSudya: " + data.judge + "\n---\n";
}

async function generateCourtCase() {
    const level = currentLevel();
    const data = await courtRequest("Yangi sud ishi va dalillar tayyorlanmoqda…", () => apiPost(API.courtCase, {
        court_type: $("courtSelect").value,
        level_name: level.name,
        level_rule: level.rule
    }));
    if (!data) return;
    if (!data.ok) {
        appendCourtMessage("system", "Sud kotibi", "⚠️ " + (data.error || "Yangi ish yaratib boʻlmadi. Qayta urinib koʻring."));
        return;
    }
    showCase(data.title, data.desc, data.evidences || [], data.opponent, data.judge);
}

async function requestHint() {
    const data = await courtRequest("Ustoz yurist ish materiallarini koʻrib chiqmoqda…", () => apiPost(API.courtHint, courtContext()));
    if (data) {
        appendCourtMessage(data.ok ? "hint" : "system", data.ok ? "💡 Ustoz maslahati" : "Sud kotibi", data.ok ? data.result : "⚠️ " + data.result);
    }
}

async function requestVerdict() {
    const data = await courtRequest("Sud hal qiluv qarorini tayyorlamoqda…", () => apiPost(API.courtVerdict, courtContext()));
    if (data) {
        appendCourtMessage(data.ok ? "verdict" : "system", data.ok ? "📜 Yakuniy sud hujjati" : "Sud kotibi", data.ok ? data.result : "⚠️ " + data.result);
    }
}

// ==================== OVOZLI XABAR ====================
async function toggleVoice(target, trigger) {
    if (state.recorder) {
        state.recorder.stop();
        return;
    }
    if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === "undefined") {
        toast("Brauzeringiz ovoz yozishni qoʻllab-quvvatlamaydi.");
        return;
    }

    let stream;
    try {
        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch (e) {
        toast("Mikrofondan foydalanish uchun brauzerda ruxsat bering.");
        return;
    }

    const chunks = [];
    const recorder = new MediaRecorder(stream);
    recorder.ondataavailable = event => { if (event.data.size > 0) chunks.push(event.data); };
    recorder.onstop = () => {
        stream.getTracks().forEach(track => track.stop());
        state.recorder = null;
        trigger.classList.remove("recording");
        const mime = recorder.mimeType || "audio/webm";
        const reader = new FileReader();
        reader.onloadend = () => {
            const data = String(reader.result).split(",")[1];
            if (!data) return;
            const audio = { data, mime };
            if (target === "home") sendChat(audio);
            else sendCourtTurn(audio);
        };
        reader.readAsDataURL(new Blob(chunks, { type: mime }));
    };

    recorder.start();
    state.recorder = recorder;
    trigger.classList.add("recording");
    toast("Yozib olinmoqda… Tugatish uchun tugmani yana bosing.");
}

// ==================== HODISALAR ====================
document.addEventListener("click", event => {
    const target = event.target.closest("[data-action], [data-view], [data-prompt]");
    if (!target || target.disabled) return;

    if (target.dataset.prompt !== undefined) {
        usePrompt(target.dataset.mode || "general", target.dataset.prompt);
        return;
    }
    if (target.dataset.view) {
        openView(target.dataset.view);
        return;
    }

    switch (target.dataset.action) {
        case "new-chat": startNewChat(); break;
        case "send-chat": sendChat(); break;
        case "send-court": sendCourtTurn(); break;
        case "voice": toggleVoice(target.dataset.target, target); break;
        case "new-case": generateCourtCase(); break;
        case "hint": requestHint(); break;
        case "verdict": requestVerdict(); break;
        case "refresh-news": refreshNews(target); break;
        case "open-sidebar": openSidebar(); break;
        case "close-sidebar": closeSidebar(); break;
    }
});

function sendOnEnter(textarea, send) {
    textarea.addEventListener("keydown", event => {
        if (event.key === "Enter" && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            send();
        }
    });
    textarea.addEventListener("input", () => autoResize(textarea));
}

sendOnEnter($("aiInput"), () => sendChat());
sendOnEnter($("courtInput"), () => sendCourtTurn());

$("fileInput").addEventListener("change", event => {
    attachFile(event.target.files[0]);
    event.target.value = "";
});
$("filesSearch").addEventListener("input", renderDocuments);
$("courtSelect").addEventListener("change", loadCourtCase);
$("levelSelect").addEventListener("change", () => {
    appendCourtMessage("system", "Sud kotibi", "Daraja: " + currentLevel().name + ". Shu daraja uchun yangi ish olish uchun «✨ Yangi ish» tugmasini bosing.");
});
document.addEventListener("keydown", event => {
    if (event.key === "Escape") closeSidebar();
});

documents = loadDocuments();
renderDocuments();
renderNews();
loadCourtCase();
renderChips();
