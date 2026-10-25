// Server sud muzokarasining faqat oxirgi 3500 belgisini ishlatadi.
const HISTORY_LIMIT = 3500;

// ==================== DARAJALAR VA BAZAVIY KAZUSLAR ====================
const LEVELS = {
    maktab: {
        name: "Maktab o'quvchisi (Boshlang'ich)",
        rule: "8-11-sinf maktab o'quvchisi uchun tushunarli tilda, O'zbekiston Konstitutsiyasi, Iste'molchilar huquqlari va bola huquqlari asosida savol-javob qiling, juda murakkab protsessual atamalarni sodda tushuntiring."
    },
    talaba: {
        name: "Yuridik fakultet talabasi (Akademik)",
        rule: "Yuridik universitet talabasi darajasida, O'zbekiston Kodekslari (FK, JK, MK, MJTK) aniq moddalarini so'rab, nazariya va amaliyot uyg'unligida baholang."
    },
    yurist: {
        name: "Amaliyotchi Yurist / Advokat (PRO)",
        rule: "Tajribali advokat darajasida, protsessual kodekslar (FPK, JPK, IPK), dalillarning maqbulligi va isbotlash yuklamasi bo'yicha qat'iy talab qo'ying."
    },
    ekspert: {
        name: "Sudya va Ekspert (Oliy daraja)",
        rule: "Eng yuqori murakkablikda: Oliy sud Plenumi tushuntirishlari, normalar kolliziyasi va kassatsiya/taftish amaliyoti bo'yicha chuqur tahlil talab qiling."
    }
};

const DEFAULT_CASES = {
    "Fuqarolik sudi": {
        title: "📂 ISH № 201 | Fuqarolik sudi (Meros va majburiy ulush)",
        opponentTitle: "👔 Javobgar (katta oʻgʻil) advokati",
        desc: "Fuqaro A. vafotidan oldin Toshkentdagi 120 000 dollarlik uyini vasiyatnoma orqali faqat katta oʻgʻli B.ga qoldirdi. Marhumning 60 yoshli (II guruh nogironi) xotini va uyni oʻz puliga taʼmirlab, ota-onasi bilan yashagan kenja oʻgʻli V. uydan koʻchirilmoqda. Siz kenja oʻgʻil va nogiron onaning advokatisiz.",
        evidences: [
            "📄 Dalil #1: Notarial vasiyatnoma | Marhum A. 2025-yil 12-mayda barcha uy-joyini katta o'g'li B.ga vasiyat qilgan. Vasiyatnomada marhumning xotini tilga olinmagan.",
            "🏥 Dalil #2: Tibbiy-mehnat ekspert komissiyasi (TMEK) ma'lumotnomasi | Marhumning turmush o'rtog'i 60 yoshda va II guruh nogironi hisoblanadi (mehnatga qobiliyatsiz).",
            "🧾 Dalil #3: Qurilish mollari xaridi cheklari va Usta shartnomasi | Kenja o'g'il V. o'z bank kartasidan uyni kapital ta'mirlash uchun 180 mln so'm sarflagani haqida kvitansiyalar.",
            "📘 Dalil #4: FK 1142-modda va Oila kodeksi 23-modda | FK 1142-modda: Mehnatga qobiliyatsiz xotin vasiyatnomadan qat'i nazar qonun bo'yicha tegishli ulushning kamida yarmini (majburiy ulush) oladi. OK 23-modda: Nikoh davomida orttirilgan mulkning 1/2 qismi er-xotinning umumiy mulkidir."
        ],
        opponentFirst: "Muhtaram sud! FK 1120-moddasiga ko'ra vasiyat erkinligi bor, marhum ota uyni faqat mening mijozimga qoldirgan. Ularni uydan majburiy chiqarishni so'raymiz!",
        judgeFirst: "Da'vogar advokati, javobgar vakilining e'tirozini eshitdingiz. Chap tarafdagi Ashyoviy Dalillarni o'rganib chiqib, nogiron ona va kenja o'g'ilning huquqlarini qanday himoya qilasiz?"
    },
    "Jinoyat sudi": {
        title: "📂 ISH № 305 | Jinoyat sudi (JK 167 va 209-moddalar)",
        opponentTitle: "👮‍♂️ Davlat ayblovchisi (Prokuror)",
        desc: "Davlat korxonasi omboridan 450 mln soʻmlik qurilish mollari kamomadi chiqqan. Bosh hisobchi S.ga JK 167-moddasi (oʻzlashtirish) va 209-moddasi (mansab soxtakorligi) bilan ayblov eʼlon qilingan. Siz bosh hisobchining advokatisiz.",
        evidences: [
            "🔐 Dalil #1: Xizmat tekshiruvi dalolatnomasi | Bosh hisobchining ERI (elektron raqamli imzo) fleshkasi korxona direktorining shaxsiy seyfida saqlangan va paroli 4 nafar xodimga ma'lum bo'lgan.",
            "✈️ Dalil #2: Xizmat safari buyrug'i va mehmonxona cheki | Soxta nakladnoylar imzolangan 14-18-mart kunlari bosh hisobchi S. Samarqand viloyatida rasmiy xizmat safarida bo'lgan.",
            "📋 Dalil #3: Tergov bayonnomasi | Tergovchi ERI kalitini aynan kim kompyuterga ulagani bo'yicha kompyuter-texnik ekspertizasi o'tkazmasdan ayblov xulosasini tuzgan.",
            "📘 Dalil #4: JPK 23-modda va 95-1-modda | JPK 23-modda: Aybdorlikka oid barcha shubhalar sudlanuvchi foydasiga hal qilinishi lozim. JPK 95-1-modda: Qonunni buzgan holda olingan dalillar yuridik kuchga ega emas."
        ],
        opponentFirst: "Hurmatli sud! Elektron hisob-fakturalarda bosh hisobchining elektron imzosi turibdi. Ombor mudiri ham unga qarshi ko'rsatma berdi. 8 yil ozodlikdan mahrum qilishni so'rayman!",
        judgeFirst: "Himoyachi, sizga so'z beriladi. Ish materiallaridagi dalillarga va JPK moddalariga asoslanib himoya pozitsiyangizni bayon qiling!"
    },
    "Ma'muriy sud": {
        title: "📂 ISH № 402 | Maʼmuriy sud (Hokim qarori nizosi)",
        opponentTitle: "👔 Tuman hokimligi bosh yuristi",
        desc: "Tuman hokimi ogohlantirishsiz «Baraka-Savdo» MChJning 2 qavatli doʻkonini buzish va yerini olib qoʻyish haqida qaror chiqardi. Siz tadbirkorning advokatisiz.",
        evidences: [
            "🏛 Dalil #1: Kadastr hujjati va Mulk guvohnomasi | 2 qavatli do'kon binosi 2019-yilda davlat ro'yxatidan o'tgan qonuniy xususiy mulk hisoblanadi.",
            "📬 Dalil #2: Pochta ma'lumotnomasi | Hokimlik qaror qabul qilishdan oldin tadbirkorga hech qanday xabarnoma yubormagan va uni ma'muriy ishni ko'rishga chaqirmagan.",
            "💰 Dalil #3: Baholash hisoboti | Do'konning bozor qiymati 1,8 mlrd so'm bo'lib, hokimlik kompensatsiya to'lamasdan buzishni boshlagan.",
            "📘 Dalil #4: 'Ma'muriy tartib-taomillar to'g'risida'gi Qonun | Manfaatdor shaxsga o'z fikrini bildirish (tinglash) imkoniyati berilishi shart hamda chora mutanosib bo'lishi kerak."
        ],
        opponentFirst: "Hurmatli sudya! Do'kon yonidagi 3 metrlik naves kadastr chizmasidan tashqariga chiqqan. Hokim obodonlashtirish uchun binoni buzishga haqli!",
        judgeFirst: "Arizachi advokati, marhamat. 3 metrlik ayvon uchun butun 2 qavatli do'konni kompensatsiyasiz buzish qaroriga qanday huquqiy e'tirozingiz bor?"
    },
    "Iqtisodiy sud": {
        title: "📂 ISH № 104 | Iqtisodiy sud (Shartnoma va zarar)",
        opponentTitle: "👔 «Tezkor-Yetkazish» MChJ yuristi",
        desc: "Kuryerlik firmasi kuryeri YTH sodir etib, «Shirin-Taom» restoranining 15 mln soʻmlik banket taomlarini yaroqsiz holga keltirdi. Siz restoran advokatisiz.",
        evidences: [
            "📑 Dalil #1: Yetkazib berish shartnomasi №18 | «Tezkor-Yetkazish» MChJ buyurtmani but va belgilangan vaqtda yetkazish majburiyatini olgan.",
            "🚔 Dalil #2: YHXBB ma'lumotnomasi va Dalolatnoma | YTH kuryerning tezlikni oshirishi oqibatida yuz bergan, 15 mln so'mlik tayyor banket taomlari to'liq yaroqsiz bo'lgan.",
            "🧾 Dalil #3: Restoran kalkulyatsiyasi va Mijoz rad xati | Banket buyurtmachisi taom kelmagani uchun 15 mln so'm to'lashdan bosh tortgan.",
            "📘 Dalil #4: FK 14 va 334-moddalar | FK 14-modda: Haqiqiy zarar to'liq qoplanadi. FK 334-modda: Qarzdor majburiyatni bajarishni uchinchi shaxsga (frilanserga) yuklagan bo'lsa ham, o'zi javobgar bo'ladi."
        ],
        opponentFirst: "Hurmatli sud! Biz faqat xomashyo narxini (5 mln so'm) to'laymiz. Qolaversa, kuryer frilanser bo'lib ishlaydi, qolgan pulni kuryerdan undirishsin!",
        judgeFirst: "Da'vogar vakili, javobgar yuristining e'tiroziga dalillar va Fuqarolik kodeksi moddalari asosida javob bering."
    },
    "Apellyatsiya shikoyati": {
        title: "📂 ISH № 508 | Apellyatsiya instansiyasi",
        opponentTitle: "👔 Daʼvogar kompaniya yuristi",
        desc: "Birinchi instansiya sudi mijozingiz («Agro-Tex» MChJ)ni xabardor qilmasdan 200 mln soʻm asosiy qarz va 180 mln soʻm penya undirish haqida qaror chiqargan.",
        evidences: [
            "📮 Dalil #1: Elektron sud tizimi ko'chirmasi | Sud chaqiruv qog'ozi «Agro-Tex» MChJning 3 yil oldin o'zgargan eski manziliga yuborilgan.",
            "📊 Dalil #2: Penya hisob-kitobi | Asosiy qarz 200 mln so'm bo'lgani holda, penya 180 mln so'm (90%) qilib hisoblangan.",
            "🏥 Dalil #3: Fors-major ma'lumotnomasi | Ehtiyot qismlar chegaradagi bojxona tekshiruvi tufayli 15 kunga kechikkan.",
            "📘 Dalil #4: IPK va FK 326-modda | Sud majlisi haqida xabardor qilinmaganlik hal qiluv qarorini bekor qilishga asos bo'ladi. FK 326-modda: Sud neustoykani kamaytirishga haqli."
        ],
        opponentFirst: "Muhtaram hay'at! Shartnomada kuniga 1% penya yozilgan, birinchi instansiya qarori o'z kuchida qolishi shart!",
        judgeFirst: "Apellyatsiya shikoyati beruvchi advokat, marhamat. Quyi sud qarorini o'zgartirish yoki bekor qilish uchun dalillaringizni keltiring."
    },
    "Kassatsiya shikoyati": {
        title: "📂 ISH № 612 | Kassatsiya instansiyasi (Daʼvo muddati)",
        opponentTitle: "👔 Qarshi taraf advokati",
        desc: "2021-yildagi shartnoma boʻyicha 2026-yilda berilgan daʼvoda 3 yillik daʼvo muddati oʻtgan boʻlsa ham quyi sudlar 500 mln soʻm undirgan.",
        evidences: [
            "📅 Dalil #1: Qarz shartnomasi (2021-yil 10-yanvar) | Qarzni qaytarish muddati 2021-yil 1-iyul etib belgilangan. Da'vo muddati 2024-yil 1-iyulda tugagan.",
            "📝 Dalil #2: Da'vo muddatini qo'llash haqida ariza | Siz birinchi instansiya sudida yozma ravishda da'vo muddati o'tgani haqida ariza kiritgansiz.",
            "📱 Dalil #3: Telegram yozishma ekspertizasi | Qarshi taraf ko'rsatgan 'Sabr qiling' degan xabar korxona direktoridan emas, oddiy qorovul raqamidan yozilgan.",
            "📘 Dalil #4: FK 150, 153, 157-moddalar | Umumiy da'vo muddati 3 yil. Da'vo muddati o'tishi da'voni rad etish uchun mustaqil asos hisoblanadi."
        ],
        opponentFirst: "Hurmatli Kassatsiya hay'ati! Telegramda 'Sabr qiling' deb yozishgani da'vo muddatini uzaytiradi. Qaror qonuniy!",
        judgeFirst: "Kassatsiya shikoyati muallifi, quyi sudlar moddiy huquq normasini qanday buzganini dalillar bilan asoslab bering."
    },
    "Hakamlik sudi": {
        title: "📂 ISH № 704 | Hakamlik sudi (Vakolat va xolislik)",
        opponentTitle: "👔 Tijorat banki bosh yuristi",
        desc: "Bank Hakamlik sudiga daʼvo kiritib, davlat ulushi boʻlgan binoni undirish va tuman kadastr boʻlimiga majburiyat yuklashni soʻradi. Hakamlik sudyasi esa bankning sobiq xodimi.",
        evidences: [
            "🏛 Dalil #1: Davlat aktivlari agentligi ma'lumotnomasi | Garovdagi binoning 51% ulushi davlat mulki hisoblanadi.",
            "📋 Dalil #2: Mehnat daftarchasi ko'chirmasi | Hakamlik sudyasi N. 8 oy oldin aynan shu da'vogar Bank boshqaruvchisining o'rinbosari bo'lib ishlagan.",
            "📑 Dalil #3: Da'vo arizasi talablari | Bank davlat organi bo'lgan Tuman kadastr bo'limiga binoni ro'yxatdan o'tkazish majburiyatini yuklashni so'ragan.",
            "📘 Dalil #4: 'Hakamlik sudlari to'g'risida'gi Qonun | Ma'muriy-huquqiy munosabatlar va davlat organlari vakolatiga oid nizolar hakamlik sudida ko'rilmaydi. Manfaatdor sudya rad etilishi shart."
        ],
        opponentFirst: "Hurmatli Hakamlik sudyasi! Kredit shartnomasida hakamlik bitimi bor, demak Hakamlik sudi barcha talablarni ko'rishga haqli!",
        judgeFirst: "Javobgar vakili, bank yuristining talabiga va Hakamlik sudi vakolatiga doir qanday huquqiy e'tirozingiz bor?"
    }
};

// ==================== GLOBAL HOLAT ====================
let currentLevel = "talaba";
let currentCourt = "Fuqarolik sudi";
let currentCaseText = "";
let currentEvidences = [];
let chatHistory = "";
let trustScore = 50;
let selectedModalEvidence = "";
let advisorMode = "Huquqiy maslahat va moddalar tahlili";

// Ovoz yozib olish o'zgaruvchilari
let mediaRecorder = null;
let audioChunks = [];
let isRecording = false;
let activeRecordingTarget = "court";

window.onload = () => {
    loadCaseData(currentCourt);
};

function switchTab(tab) {
    document.getElementById("courtSection").style.display = (tab === "court") ? "block" : "none";
    document.getElementById("advisorSection").style.display = (tab === "advisor") ? "block" : "none";
    document.getElementById("tabCourtBtn").classList.toggle("active", tab === "court");
    document.getElementById("tabAdvisorBtn").classList.toggle("active", tab === "advisor");
}

function selectLevel(lvlKey, btn) {
    currentLevel = lvlKey;
    btn.parentElement.querySelectorAll(".chip").forEach(c => c.classList.remove("active"));
    btn.classList.add("active");
    document.getElementById("levelBadgeText").innerText = "Daraja: " + LEVELS[lvlKey].name.split("(")[0];
    addSystemNotice("🎓 Daraja o'zgartirildi: " + LEVELS[lvlKey].name + ". «✨ Yangi kazus» tugmasini bosib aynan shu daraja uchun yangi ish boshlashingiz mumkin!");
}

function selectCourt(courtName, btn) {
    currentCourt = courtName;
    btn.parentElement.querySelectorAll(".chip").forEach(c => c.classList.remove("active"));
    btn.classList.add("active");
    loadCaseData(courtName);
}

function loadCaseData(courtName) {
    const data = DEFAULT_CASES[courtName] || DEFAULT_CASES["Fuqarolik sudi"];
    currentCaseText = data.title + "\n" + data.desc;
    currentEvidences = data.evidences;
    chatHistory = "Qarshi tomon: " + data.opponentFirst + "\nSudya: " + data.judgeFirst + "\n---\n";
    updateScore(50, true);

    document.getElementById("caseTitle").innerText = data.title;
    document.getElementById("caseDesc").innerText = data.desc;
    document.getElementById("opponentTitle").innerText = data.opponentTitle;

    renderEvidences();

    const stream = document.getElementById("chatStream");
    stream.innerHTML = "";
    appendBubble("opponent", data.opponentTitle, data.opponentFirst);
    appendBubble("judge", "👩‍⚖️ Raislik qiluvchi (Sudya) — [" + LEVELS[currentLevel].name + "]", data.judgeFirst);
    playGavelSound();
}

function renderEvidences() {
    const container = document.getElementById("evidenceList");
    container.innerHTML = "";
    currentEvidences.forEach((ev, idx) => {
        const parts = ev.split("|");
        const title = parts[0] ? parts[0].trim() : ("📄 Dalil #" + (idx + 1));
        const card = document.createElement("div");
        card.className = "evidence-card";
        card.innerHTML = `<span>${escapeHtml(title)}</span><span class="evidence-badge">Koʻrish 🔍</span>`;
        card.onclick = () => openEvidenceModal(title, parts.slice(1).join("|").trim() || ev);
        container.appendChild(card);
    });
}

function openEvidenceModal(title, body) {
    selectedModalEvidence = title + " (" + body + ")";
    document.getElementById("modalTitle").innerText = title;
    document.getElementById("modalBody").innerText = body;
    document.getElementById("evidenceModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("evidenceModal").style.display = "none";
}

function citeEvidenceInInput() {
    const input = document.getElementById("userInput");
    input.value = (input.value ? input.value + " " : "") + "Hurmatli sud, ish materiallaridagi «" + selectedModalEvidence + "»ga e'tiboringizni qarataman. ";
    closeModal();
    input.focus();
}

function insertPhrase(text) {
    const input = document.getElementById("userInput");
    input.value += text;
    input.focus();
}

function appendBubble(role, sender, text) {
    const stream = document.getElementById("chatStream");
    const div = document.createElement("div");
    const cls = role === "judge" ? "msg-judge" : (role === "opponent" ? "msg-opponent" : (role === "clerk" ? "msg-clerk" : "msg-advocate"));
    div.className = "msg-bubble " + cls;
    div.innerHTML = `<span class="msg-sender">${escapeHtml(sender)}</span>${escapeHtml(text).replace(/\n/g, "<br>")}`;
    stream.appendChild(div);
    stream.scrollTop = stream.scrollHeight;
}

function addSystemNotice(text) {
    appendBubble("clerk", "📝 Sud majlisi kotibi", text);
}

function updateScore(deltaOrVal, reset = false) {
    if (reset) trustScore = deltaOrVal;
    else trustScore = Math.max(5, Math.min(100, trustScore + deltaOrVal));
    document.getElementById("scoreValue").innerText = trustScore;
    document.getElementById("scoreBar").style.width = trustScore + "%";
}

function highlightPodium(id) {
    ["podiumOpponent", "podiumJudge", "podiumAdvocate"].forEach(p => {
        document.getElementById(p).classList.toggle("speaking", p === id);
    });
}

// ==================== SUD NUTQINI YUBORISH ====================
async function sendCourtTurn(audioB64 = null, audioMime = null) {
    const input = document.getElementById("userInput");
    const text = input.value.trim();
    if (!text && !audioB64) return;

    if (!audioB64) {
        appendBubble("advocate", "🛡️ Siz (Advokat)", text);
        input.value = "";
    } else {
        addSystemNotice("🎙 Ovozli nutqingiz qabul qilindi, Sud kotibi bayonnomaga kiritmoqda...");
    }

    highlightPodium("podiumAdvocate");
    document.getElementById("judgeStatus").innerText = "Tinglamoqda va tahlil qilmoqda...";

    try {
        const data = await apiPost(window.API_ROUTES.courtTurn, {
            court_type: currentCourt,
            level_name: LEVELS[currentLevel].name,
            level_rule: LEVELS[currentLevel].rule,
            case_text: currentCaseText,
            evidence_list: currentEvidences.join("\n"),
            history: chatHistory.slice(-HISTORY_LIMIT),
            user_text: text,
            audio_base64: audioB64,
            audio_mime: audioMime
        });
        if (!data.ok) {
            addSystemNotice("⚠️ " + (data.error || "Xatolik yuz berdi"));
            return;
        }

        if (audioB64 && data.transcript) {
            appendBubble("advocate", "📝 Ovozli nutqingiz stenogrammasi", "«" + data.transcript + "»");
        }

        highlightPodium("podiumOpponent");
        appendBubble("opponent", document.getElementById("opponentTitle").innerText, data.opponent);

        setTimeout(() => {
            highlightPodium("podiumJudge");
            appendBubble("judge", "👩‍⚖️ Raislik qiluvchi (Sudya)", data.judge);
            updateScore(data.delta || 5);
            document.getElementById("judgeStatus").innerText = "Advokat javobini kutmoqda";
        }, 500);

        chatHistory += "Advokat: " + (data.transcript || text) + "\nQarshi tomon: " + data.opponent + "\nSudya: " + data.judge + "\n---\n";
    } catch (e) {
        addSystemNotice("⚠️ Aloqada xatolik yuz berdi. Qaytadan yuboring.");
    }
}

// ==================== YANGI KAZUS VA DALILLAR GENERATSIYASI ====================
async function generateNewAICase() {
    addSystemNotice("⏳ Sunʼiy intellekt «" + LEVELS[currentLevel].name + "» darajasi uchun yangi sud ishi va ashyoviy dalillarni tayyorlamoqda...");
    try {
        const data = await apiPost(window.API_ROUTES.courtCase, {
            court_type: currentCourt,
            level_name: LEVELS[currentLevel].name,
            level_rule: LEVELS[currentLevel].rule
        });
        if (!data.ok) {
            addSystemNotice("⚠️ " + (data.error || "Yangi kazus tuzishda uzilish bo'ldi."));
            return;
        }
        currentCaseText = data.title + "\n" + data.desc;
        currentEvidences = data.evidences.length ? data.evidences : DEFAULT_CASES[currentCourt].evidences;
        chatHistory = "Qarshi tomon: " + data.opponent + "\nSudya: " + data.judge + "\n---\n";
        updateScore(50, true);

        document.getElementById("caseTitle").innerText = data.title;
        document.getElementById("caseDesc").innerText = data.desc;
        renderEvidences();

        document.getElementById("chatStream").innerHTML = "";
        appendBubble("opponent", "👔 Qarshi tomon vakili", data.opponent);
        appendBubble("judge", "👩‍⚖️ Raislik qiluvchi (Sudya)", data.judge);
        playGavelSound();
    } catch (e) {
        addSystemNotice("⚠️ Yangi kazus tuzishda uzilish bo'ldi.");
    }
}

// ==================== USTOZ MASLAHATI ====================
async function requestHint() {
    addSystemNotice("💡 Ustoz advokat ish materiallarini koʻrib chiqmoqda...");
    try {
        const data = await apiPost(window.API_ROUTES.courtHint, {
            court_type: currentCourt,
            level_name: LEVELS[currentLevel].name,
            case_text: currentCaseText,
            evidence_list: currentEvidences.join("\n"),
            history: chatHistory.slice(-HISTORY_LIMIT)
        });
        if (data.ok) {
            appendBubble("clerk", "💡 Ustoz Advokat Maslahati (Shpargalka)", data.hint);
        } else {
            addSystemNotice("⚠️ " + data.hint);
        }
    } catch (e) {}
}

// ==================== YAKUNIY HUKM ====================
async function requestVerdict() {
    playGavelSound();
    addSystemNotice("🏛 Sud Hal qiluv qarori (Hukm) chiqarish uchun maslahatxonaga kirdi...");
    try {
        const data = await apiPost(window.API_ROUTES.courtVerdict, {
            court_type: currentCourt,
            level_name: LEVELS[currentLevel].name,
            case_text: currentCaseText,
            history: chatHistory.slice(-HISTORY_LIMIT)
        });
        if (data.ok) {
            playGavelSound();
            appendBubble("judge", "📜 OʻZBEKISTON RESPUBLIKASI NOMIDAN — YAKUNIY SUD HUJJATI", data.verdict);
        } else {
            addSystemNotice("⚠️ " + data.verdict);
        }
    } catch (e) {}
}

// ==================== 24/7 AI HUQUQIY MASLAHATCHI ====================
function setAdvisorMode(mode, btn) {
    advisorMode = mode;
    btn.parentElement.querySelectorAll(".chip").forEach(c => c.classList.remove("active"));
    btn.classList.add("active");
}

async function sendAdvisorQuery(audioB64 = null, audioMime = null) {
    const input = document.getElementById("advisorInput");
    const q = input.value.trim();
    if (!q && !audioB64) return;

    const stream = document.getElementById("advisorStream");
    const userDiv = document.createElement("div");
    userDiv.className = "msg-bubble msg-advocate";
    userDiv.innerHTML = `<span class="msg-sender">👤 Sizning murojaatingiz (${escapeHtml(advisorMode)})</span>${audioB64 ? "🎙 [Ovozli savol yuborildi]" : escapeHtml(q)}`;
    stream.appendChild(userDiv);
    stream.scrollTop = stream.scrollHeight;
    input.value = "";

    const waitDiv = document.createElement("div");
    waitDiv.className = "msg-bubble msg-clerk";
    waitDiv.innerText = "⏳ AI Huquqiy Maslahatchi qonunchilik bazasini tahlil qilmoqda...";
    stream.appendChild(waitDiv);
    stream.scrollTop = stream.scrollHeight;

    try {
        const data = await apiPost(window.API_ROUTES.advisor, {
            mode: advisorMode,
            question: q,
            audio_base64: audioB64,
            audio_mime: audioMime
        });
        waitDiv.remove();
        const botDiv = document.createElement("div");
        botDiv.className = "msg-bubble msg-judge";
        botDiv.innerHTML = `<span class="msg-sender">🤖 AI Huquqiy Maslahatchi</span>${escapeHtml(data.answer || "Xatolik yuz berdi").replace(/\n/g, "<br>")}`;
        stream.appendChild(botDiv);
        stream.scrollTop = stream.scrollHeight;
    } catch (e) {
        waitDiv.innerText = "⚠️ Aloqada xatolik yuz berdi.";
    }
}

// ==================== OVOZLI XABAR YOZISH (MIKROFON) ====================
async function toggleVoiceRecording(target) {
    const btn = document.getElementById(target === "court" ? "micBtn" : "advisorMicBtn");

    if (isRecording) {
        mediaRecorder.stop();
        isRecording = false;
        btn.classList.remove("recording");
        btn.innerText = "🎙";
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        activeRecordingTarget = target;
        audioChunks = [];
        mediaRecorder = new MediaRecorder(stream);

        mediaRecorder.ondataavailable = e => {
            if (e.data.size > 0) audioChunks.push(e.data);
        };

        mediaRecorder.onstop = () => {
            const mimeType = mediaRecorder.mimeType || "audio/webm";
            const blob = new Blob(audioChunks, { type: mimeType });
            stream.getTracks().forEach(t => t.stop());

            const reader = new FileReader();
            reader.onloadend = () => {
                const base64Data = reader.result.split(",")[1];
                if (activeRecordingTarget === "court") {
                    sendCourtTurn(base64Data, mimeType);
                } else {
                    sendAdvisorQuery(base64Data, mimeType);
                }
            };
            reader.readAsDataURL(blob);
        };

        mediaRecorder.start();
        isRecording = true;
        btn.classList.add("recording");
        btn.innerText = "⏹";
    } catch (err) {
        alert("Mikrofondan foydalanish uchun brauzeringizda ruxsat bering!");
    }
}

// ==================== SUD BOLG'ACHASI OVOZ EFFEKTI ====================
function playGavelSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        [0, 0.22].forEach(delay => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = "triangle";
            osc.frequency.setValueAtTime(140, ctx.currentTime + delay);
            osc.frequency.exponentialRampToValueAtTime(38, ctx.currentTime + delay + 0.14);
            gain.gain.setValueAtTime(0.6, ctx.currentTime + delay);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + delay + 0.15);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + 0.16);
        });
    } catch (e) {}
}
