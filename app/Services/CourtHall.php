<?php

namespace App\Services;

use App\Services\Court\CourtTagParser;
use Illuminate\Support\Str;

/**
 * Builds the prompts of the "Virtual Sud Zali" page, which scores every speech
 * and also hosts the 24/7 legal advisor.
 *
 * @phpstan-import-type CourtContext from LegalAssistant
 * @phpstan-import-type Audio from LegalAssistant
 */
class CourtHall
{
    /**
     * @var list<string>
     */
    public const array ADVISOR_MODES = [
        'Huquqiy maslahat va moddalar tahlili',
        "Sudga Da'vo arizasi yoki Shikoyat loyihasini yozish",
        'Ikki tomonlama Shartnoma loyihasini tuzish',
        'Maktab va OTM talabalari uchun huquqiy masalani yechish',
    ];

    private const int HISTORY_LENGTH = 3500;

    private const int HINT_HISTORY_LENGTH = 1800;

    private const float TEMPERATURE = 0.6;

    private const int MAX_OUTPUT_TOKENS = 1400;

    /**
     * The judge's trust in the advocate changes by this many points per speech.
     */
    private const int MIN_SCORE_DELTA = -10;

    private const int MAX_SCORE_DELTA = 15;

    private const int DEFAULT_SCORE_DELTA = 5;

    public function __construct(private GeminiClient $gemini, private CourtTagParser $parser) {}

    /**
     * The opponent's and the judge's reaction to a speech, and how it changed the judge's trust.
     *
     * @param  CourtContext  $context
     * @param  Audio|null  $audio
     * @return array{transcript: string, opponent: string, judge: string, delta: int}|null
     */
    public function turn(array $context, string $speech, ?array $audio): ?array
    {
        $levelName = $context['level_name'] ?? null ?: 'Talaba';

        $reply = $this->generate(
            "Siz O'zbekiston Respublikasining '".($context['court_type'] ?? null ?: 'Fuqarolik sudi')."' zalida sud majlisini olib boryapsiz.\n"
                .'FOYDALANUVCHI DARAJASI: '.$levelName.' ('.($context['level_rule'] ?? '').").\n\n"
                ."KO'RILAYOTGAN ISH:\n".($context['case_text'] ?? '')."\n\n"
                ."MAVJUD DALILLAR BAZASI:\n".($context['evidence_list'] ?? '')."\n\n"
                ."OLDINGI MUZOKARA TARIXI:\n".$this->history($context, self::HISTORY_LENGTH)."\n\n"
                .($audio !== null ? "Foydalanuvchi (Advokat) o'z nutqini OVOZLI XABAR shaklida yubordi.\n" : "ADVOKATNING YANGI NUTQI:\n".$speech."\n\n")
                ."QAT'IY TOPSHIRIQ — Javobingizni faqat quyidagi teglarga bo'lib yozing (yulduzcha ** ishlatmang, rol nomini teg ichida qayta yozmang):\n\n"
                ."[STENOGRAMMA]\n".($audio !== null ? "Advokat ovozli xabarda nima deganini o'zbek tilida so'zma-so'z yozing." : $speech)."\n\n"
                ."[QARSHI_TOMON]\nQarshi taraf (Prokuror yoki Javobgar yuristi) nomidan advokatning fikri va dalillariga qarshi 2-3 gapdan iborat QISQA, JONLI va KESKIN e'tiroz yozing.\n\n"
                ."[SUDYA]\nRaislik qiluvchi (Sudya) nomidan foydalanuvchi darajasiga (".$levelName.") mos tilda 2-3 gap bilan munosabat bildiring (dalil yoki modda to'g'ri aytilgan bo'lsa e'tirof eting, xato bo'lsa tanbeh bering) va ish bo'yicha BITTADAN ortiq bo'lmagan aniq savol bering.\n\n"
                ."[REYTING]\nAdvokatning ushbu javobi qanchalik asosli ekaniga qarab -10 dan +15 gacha faqat bitta son yozing (masalan: +10 yoki -5).",
            $audio,
        );

        if ($reply === null) {
            return null;
        }

        $sections = $this->parser->parse($reply, ['STENOGRAMMA', 'QARSHI_TOMON', 'SUDYA', 'REYTING']);

        return [
            'transcript' => $sections['stenogramma'] ?: $speech,
            'opponent' => $sections['qarshi_tomon'] ?: "E'tirozimiz bor! Da'vogar vakili dalillarni noto'g'ri talqin qilmoqda.",
            'judge' => $sections['sudya'] ?: $reply,
            'delta' => $this->scoreDelta($sections['reyting']),
        ];
    }

    /**
     * Generate a fresh case with four pieces of evidence for the given court and level.
     *
     * @param  CourtContext  $context
     * @return array{title: string, desc: string, evidences: list<string>, opponent: string, judge: string}|null
     */
    public function newCase(array $context): ?array
    {
        $courtType = $context['court_type'] ?? null ?: 'Fuqarolik sudi';

        $reply = $this->generate(
            "O'zbekiston Respublikasi qonunchiligi asosida '".$courtType."' yo'nalishi bo'yicha '".($context['level_name'] ?? null ?: 'Talaba')."' (".($context['level_rule'] ?? '').") darajasiga mos yangi, qiziqarli sud ishi (kazus) va 4 ta ashyoviy dalil tuzing.\n"
                ."Javobingizni qat'iy ravishda quyidagi teglarga bo'lib yozing (yulduzcha ** ishlatmang):\n\n"
                ."[ISH_NOMI]\nIsh № va qisqa nomi\n\n"
                ."[ISH_HOLATI]\nIshning aniq fabulasi (sanalar, ishtirokchilar, summalar) va foydalanuvchining roli (qaysi taraf advokati ekani).\n\n"
                ."[DALIL_1]\nSarlavha | Hujjatning to'liq matni (masalan: Shartnoma, Ekspertiza xulosasi, Chek yoki Guvoh ko'rsatmasi)\n\n"
                ."[DALIL_2]\nSarlavha | Hujjatning to'liq matni\n\n"
                ."[DALIL_3]\nSarlavha | Hujjatning to'liq matni\n\n"
                ."[DALIL_4]\nSarlavha | Tegishli Kodeks/Qonun moddasidan ko'chirma\n\n"
                ."[QARSHI_TOMON]\nQarshi tarafning 2 gaplik dastlabki da'vosi yoki ayblovi.\n\n"
                ."[SUDYA]\nSudyaning advokatga beradigan dastlabki savoli.",
        );

        if ($reply === null) {
            return null;
        }

        $sections = $this->parser->parse($reply, ['ISH_NOMI', 'ISH_HOLATI', 'DALIL_1', 'DALIL_2', 'DALIL_3', 'DALIL_4', 'QARSHI_TOMON', 'SUDYA']);

        return [
            'title' => $sections['ish_nomi'] ?: '📂 Yangi Sud Ishi ('.$courtType.')',
            'desc' => $sections['ish_holati'] ?: $reply,
            'evidences' => array_values(array_filter([$sections['dalil_1'], $sections['dalil_2'], $sections['dalil_3'], $sections['dalil_4']])),
            'opponent' => $sections['qarshi_tomon'] ?: "Talablarimiz qonuniy, qanoatlantirilishini so'raymiz!",
            'judge' => $sections['sudya'] ?: "Advokat, ish materiallari va dalillar bilan tanishib, o'z pozitsiyangizni bildiring.",
        ];
    }

    /**
     * @param  CourtContext  $context
     */
    public function hint(array $context): ?string
    {
        return $this->generate(
            'Siz ustoz advokatsiz. Daraja: '.($context['level_name'] ?? '').'. Sud: '.($context['court_type'] ?? '').".\n"
                .'Ish: '.($context['case_text'] ?? '')."\nDalillar: ".($context['evidence_list'] ?? '')."\nOxirgi bahs:\n".$this->history($context, self::HINT_HISTORY_LENGTH)."\n\n"
                ."TOPSHIRIQ: Foydalanuvchiga hozir qaysi dalilni ko'rsatish va O'zbekistonning qaysi qonun/kodeks moddasini aytish kerakligi bo'yicha 3-4 gaplik aniq yordam (shpargalka) yozing. Yulduzcha ishlatmang.",
        );
    }

    /**
     * @param  CourtContext  $context
     */
    public function verdict(array $context): ?string
    {
        $levelName = $context['level_name'] ?? '';

        return $this->generate(
            "Siz O'zbekiston Respublikasining '".($context['court_type'] ?? '')."' raisi (Sudya)siz. Sana: ".now()->format('d.m.Y')."-yil.\n"
                .'Ishtirokchi darajasi: '.$levelName.".\n"
                ."KO'RILGAN ISH:\n".($context['case_text'] ?? '')."\n\n"
                ."SUD MUZOKARASI TARIXI:\n".$this->history($context, self::HISTORY_LENGTH)."\n\n"
                ."QAT'IY TOPSHIRIQ:\n"
                ."Sud muhokamasi yakunlandi! O'zbekiston Respublikasi nomidan YAKUNIY SUD HUJJATINI (Hal qiluv qarori / Hukm / Ajrim) e'lon qiling:\n"
                ."1. ANIQLANGAN HOLATLAR VA QONUNIY ASOSLAR (qo'llanilgan moddalar va dalillar tahlili);\n"
                ."2. SUD QARORI (HUKM): Da'vo/shikoyat qanoatlantirildimi, qismanmi yoki rad etildimi;\n"
                ."3. ADVOKAT MAHORATIGA BAHO: '".$levelName."' darajasi bo'yicha 100 ballik tizimda ball, yutuqlar va yo'l qo'yilgan xatolar tahlili.\n"
                .'Yulduzcha (**) ishlatmang, rasmiy va tushunarli yozing.',
        );
    }

    /**
     * Answer a legal question or draft the requested document.
     *
     * @param  Audio|null  $audio
     */
    public function advise(string $mode, string $question, ?array $audio): ?string
    {
        return $this->generate(
            "Siz O'zbekiston Respublikasi qonunchiligi bo'yicha oliy toifali AI Huquqiy Maslahatchi va Advokatsiz.\n"
                .'Murojaat turi: '.$mode.".\n"
                .($audio !== null ? "Foydalanuvchi savolini OVOZLI XABAR orqali yubordi.\n" : 'Foydalanuvchi savoli: '.$question."\n\n")
                ."TOPSHIRIQ:\n"
                ."1. O'zbekiston Respublikasining amaldagi Kodekslari va Qonunlari (aniq moddalari) asosida batafsil, professional va tushunarli huquqiy yechim bering.\n"
                ."2. Fuqaro amalda nima qilishi kerakligini qadamma-qadam (1, 2, 3...) tushuntiring.\n"
                ."3. Agar foydalanuvchi ariza, da'vo arizasi yoki shartnoma so'ragan bo'lsa, to'liq rasmiy hujjat namunasini yozib bering.\n"
                .'Matnda ** yulduzchalar ishlatmang.',
            $audio,
        );
    }

    private function scoreDelta(string $rating): int
    {
        if (! preg_match('/[-+]?\d+/', $rating, $match)) {
            return self::DEFAULT_SCORE_DELTA;
        }

        return max(self::MIN_SCORE_DELTA, min(self::MAX_SCORE_DELTA, (int) $match[0]));
    }

    /**
     * @param  CourtContext  $context
     */
    private function history(array $context, int $length): string
    {
        return Str::substr($context['history'] ?? '', -$length);
    }

    /**
     * @param  Audio|null  $audio
     */
    private function generate(string $prompt, ?array $audio = null): ?string
    {
        return $this->gemini->generate($prompt, $audio, self::TEMPERATURE, self::MAX_OUTPUT_TOKENS);
    }
}
