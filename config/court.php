<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Court Simulator Cases
    |--------------------------------------------------------------------------
    |
    | The ready-made cases offered by the Telegram bot's main menu. Each key
    | is the menu button label, so the order here is the order of buttons.
    |
    */

    'cases' => [
        '🏛 Fuqarolik sudi' => [
            'type' => "Fuqarolik ishlari bo'yicha sud (FK, OK va FPK)",
            'opponent_title' => "👔 Javobgar (katta o'g'il) advokati",
            'title' => '📂 ISH № 201 | FUQAROLIK SUDI (Meros va majburiy ulush nizosi)',
            'desc' => "📋 Ish holati:\nFuqaro A. vafotidan oldin Toshkentdagi 120 000 AQSh dollarlik uyini notarial vasiyatnoma orqali faqat katta o'g'li B.ga qoldirdi. Marhumning 60 yoshli (II guruh nogironi) xotini va uyni o'z puliga ta'mirlab, ota-onasi bilan birga yashagan kenja o'g'li V. uydan ko'chirilmoqda.\n\n"
                ."🎯 Sizning rolingiz: Kenja o'g'il va nogiron onaning advokatisiz.",
            'opponent_first' => "Muhtaram sud! FK 1120-moddasiga ko'ra vasiyat erkinligi bor, marhum ota uyni faqat mening mijozimga vasiyat qilgan. Ukasi uyni ta'mirlagani haqida cheklari yo'q. Ularni uydan majburiy chiqarishni so'raymiz!",
            'judge_first' => "Da'vogar advokati, javobgar vakilining e'tirozini eshitdingiz. Vasiyatnoma mavjud bo'lgan holatda onaning va kenja o'g'ilning uydagi huquqlarini qaysi qonun moddalari bilan himoya qilasiz? (Yozma yoki 🎙 ovozli xabar yuboring)",
        ],
        '🚔 Jinoyat sudi' => [
            'type' => "Jinoyat ishlari bo'yicha sud (JK va JPK)",
            'opponent_title' => '👮‍♂️ Davlat ayblovchisi (Prokuror)',
            'title' => '📂 ISH № 305 | JINOYAT SUDI (JK 167 va 209-moddalar)',
            'desc' => "📋 Ish holati:\nDavlat korxonasi omboridan 450 mln so'mlik qurilish mollari kamomadi chiqqan. Bosh hisobchi S.ga JK 167-moddasi 3-qismi 'a' bandi (juda ko'p miqdorda o'zlashtirish) va 209-moddasi (mansab soxtakorligi) bilan ayblov e'lon qilingan.\n\n"
                .'🎯 Sizning rolingiz: Sudlanuvchi (bosh hisobchi) advokatisiz.',
            'opponent_first' => "Hurmatli sud! Elektron hisob-fakturalarda bosh hisobchining elektron raqamli imzosi (ERI) turibdi. Ombor mudiri ham tergovda 'Bosh hisobchi menga og'zaki buyruq bergan' deb ko'rsatma berdi. Sudlanuvchiga 8 yil ozodlikdan mahrum qilish jazosini so'rayman!",
            'judge_first' => "Himoyachi, sizga so'z beriladi. ERI kaliti korxona direktorining seyfida turgani va ekspertiza o'tkazilmagani bo'yicha JPK normalariga tayanib himoya pozitsiyangizni bayon qiling! (Yozma yoki 🎙 ovozli xabar yuboring)",
        ],
        "📋 Ma'muriy sud" => [
            'type' => "Ma'muriy sud (MSIYuK va 'Ma'muriy tartib-taomillar to'g'risida'gi Qonun)",
            'opponent_title' => '👔 Tuman hokimligi yuristi',
            'title' => "📂 ISH № 402 | MA'MURIY SUD (Hokim qarorini haqiqiy emas deb topish)",
            'desc' => "📋 Ish holati:\nTuman hokimi 'Shaharsozlik qoidalarini buzganlik' vaji bilan 'Baraka-Savdo' MChJning 2 qavatli do'konini buzish va yer uchastkasini olib qo'yish haqida qaror chiqardi. Tadbirkor yig'ilishga chaqirilmadi va kompensatsiya to'lanmadi.\n\n"
                ."🎯 Sizning rolingiz: 'Baraka-Savdo' MChJ advokatisiz.",
            'opponent_first' => "Hurmatli sudya! Do'kon yonidagi 3 metrlik ayvon kadastr chizmasidan tashqariga chiqqan. Hokim tuman hududini obodonlashtirish vakolatiga ega, qaror qonuniy!",
            'judge_first' => "Arizachi advokati, marhamat. 3 metrlik ayvon uchun butun boshli 2 qavatli do'konni buzish va tadbirkorni eshitmasdan qaror chiqarishga qonunchilik nima deydi? (Yozma yoki 🎙 ovozli xabar yuboring)",
        ],
        '💼 Iqtisodiy sud' => [
            'type' => 'Iqtisodiy sud (FK va IPK)',
            'opponent_title' => '👔 Javobgar («Tezkor-Yetkazish» MChJ) yuristi',
            'title' => '📂 ISH № 104 | IQTISODIY SUD (Shartnoma va zarar nizosi)',
            'desc' => "📋 Ish holati:\nKuryerlik firmasi («Tezkor-Yetkazish» MChJ) kuryeri YTH sodir etib, «Shirin-Taom» restoranining 15 mln so'mlik banket taomlarini yaroqsiz holga keltirdi. Restoran 15 mln so'm moddiy zarar, 20 mln so'm boy berilgan foyda va 30 mln so'm ma'naviy zarar talab qildi.\n\n"
                ."🎯 Sizning rolingiz: Da'vogar («Shirin-Taom» restorani) advokatisiz.",
            'opponent_first' => "Hurmatli sud! Biz faqat masalliq tannarxini (5 mln so'm) to'laymiz, 15 mln bu restoranning ustamasi. Qolaversa, kuryer bizda frilanser bo'lib ishlaydi, zararni kuryerdan undirishsin. MChJ ma'naviy azob chekmaydi!",
            'judge_first' => "Da'vogar vakili, javobgar yuristining uchta e'tiroziga O'zbekiston Respublikasi FK va IPK moddalari asosida javob bering. (Yozma yoki 🎙 ovozli xabar yuboring)",
        ],
        '📈 Apellyatsiya shikoyati' => [
            'type' => 'Apellyatsiya instansiyasi sudi (IPK va FK)',
            'opponent_title' => "👔 Da'vogar kompaniya yuristi",
            'title' => '📂 ISH № 508 | APELLYATSIYA INSTANSIYASI',
            'desc' => "📋 Ish holati:\nBirinchi instansiya sudi sizning mijozingiz ('Agro-Tex' MChJ)dan 200 mln so'm asosiy qarz va 180 mln so'm penya (neustoyka) undirish haqida qaror chiqardi. Sud majlisi haqida mijozingiz xabardor qilinmagan.\n\n"
                .'🎯 Sizning rolingiz: Apellyatsiya shikoyati kiritgan taraf advokatisiz.',
            'opponent_first' => "Muhtaram hay'at! Shartnomada har bir kechikkan kun uchun 1% penya yozilgan, shartnoma qonuniy. Sud xabarnomani korxonaning eski manziliga yuborgan, qaror o'z kuchida qolishi kerak!",
            'judge_first' => "Shikoyat beruvchi advokat, so'z sizga. Birinchi instansiya qarorini bekor qilish va penyani kamaytirish uchun qanday protsessual hamda moddiy asoslaringiz bor? (Yozma yoki 🎙 ovozli xabar yuboring)",
        ],
        '⚖️ Kassatsiya shikoyati' => [
            'type' => 'Kassatsiya instansiyasi sudi (FK va IPK)',
            'opponent_title' => '👔 Qarshi taraf advokati',
            'title' => "📂 ISH № 612 | KASSATSIYA INSTANSIYASI (Da'vo muddati nizosi)",
            'desc' => "📋 Ish holati:\nQuyi va apellyatsiya sudlari 2021-yildagi qarz shartnomasi bo'yicha 2026-yilda kiritilgan da'voni qanoatlantirib, mijozingizdan 500 mln so'm undirdi. Siz birinchi instansiyada 3 yillik da'vo muddati o'tgani haqida ariza bergansiz, lekin sud buni inobatga olmagan.\n\n"
                .'🎯 Sizning rolingiz: Kassatsiya shikoyati beruvchi advokatsiz.',
            'opponent_first' => "Hurmatli Kassatsiya hay'ati! Qarz oluvchi 2024-yilda Telegram orqali 'Sabr qiling, to'layman' deb yozgan, bu da'vo muddatini uzaytiradi. Ikki instansiya qarori qonuniy!",
            'judge_first' => "Kassatsiya shikoyati muallifi, marhamat. Telegramdagi yozishma va da'vo muddati o'tishi oqibatlari bo'yicha FK moddalari asosida pozitsiyangizni tushuntiring. (Yozma yoki 🎙 ovozli xabar yuboring)",
        ],
        '🤝 Hakamlik sudi' => [
            'type' => "Hakamlik sudi ('Hakamlik sudlari to'g'risida'gi Qonun va FK)",
            'opponent_title' => '👔 Tijorat banki bosh yuristi',
            'title' => '📂 ISH № 704 | HAKAMLIK SUDI (Vakolat va xolislik nizosi)',
            'desc' => "📋 Ish holati:\nBank kredit qarzi (1 mlrd so'm) bilan birga garovdagi davlat ulushi bo'lgan binoni undirish va tuman kadastr bo'limiga binoni qayta ro'yxatdan o'tkazish majburiyatini yuklash haqida Hakamlik sudiga da'vo kiritdi. Hakamlik sudyasi esa bank boshqaruvchisining sobiq o'rinbosari ekani aniqlandi.\n\n"
                ."🎯 Sizning rolingiz: Javobgar ('Qurilish-Invest' MChJ) advokatisiz.",
            'opponent_first' => 'Hurmatli Hakamlik sudyasi! Kredit shartnomasida hakamlik bitimi bor, demak Hakamlik sudi barcha talablarni ko\'rib chiqishga to\'liq haqli!',
            'judge_first' => "Javobgar vakili, bank yuristining talabiga 'Hakamlik sudlari to'g'risida'gi Qonun moddalari asosida qanday e'tiroz bildirasiz? (Yozma yoki 🎙 ovozli xabar yuboring)",
        ],
    ],

];
