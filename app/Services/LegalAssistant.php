<?php

namespace App\Services;

use App\Services\Court\CourtTagParser;
use Illuminate\Support\Str;

/**
 * Builds the web platform's AI prompts and shapes Gemini's replies.
 *
 * @phpstan-type CourtContext array{court_type?: string|null, level_name?: string|null, level_rule?: string|null, case_text?: string|null, evidence_list?: string|null, history?: string|null}
 * @phpstan-type Audio array{mime_type: string, data: string}
 */
class LegalAssistant
{
    private const int HISTORY_LENGTH = 3000;

    private const int ATTACHMENT_LENGTH = 6000;

    private const float TEMPERATURE = 0.55;

    private const int MAX_OUTPUT_TOKENS = 1500;

    /**
     * @var array<string, string>
     */
    private const array MODE_INSTRUCTIONS = [
        'general' => "Foydalanuvchining savoliga O'zbekiston Respublikasi kodekslari va qonunlarining aniq moddalari asosida qisqa, tushunarli va professional javob bering.",
        'doc_create' => "Foydalanuvchi so'ragan huquqiy hujjatni (Shartnoma, Da'vo arizasi, Shikoyat yoki Ariza) O'zbekiston rasmiy ish yuritish standartlariga to'liq mos holda, barcha bandlari bilan mukammal tuzib bering.",
        'doc_analyze' => "Taqdim etilgan hujjat yoki shartnoma matnini O'zbekiston qonunchiligi bo'yicha tahlil qiling: 1) Huquqiy xatarlar (risklar); 2) Qonunga zid yoki noaniq bandlar; 3) Tuzatish bo'yicha aniq tavsiyalar.",
        'news_explain' => "Berilgan qonunchilik yangiligining mazmun-mohiyatini, u fuqarolar va tadbirkorlar uchun amalda nimani o'zgartirishini sodda va aniq huquqiy tilda tushuntirib bering.",
    ];

    public function __construct(private GeminiClient $gemini, private CourtTagParser $parser) {}

    /**
     * @return list<string>
     */
    public static function modes(): array
    {
        return array_keys(self::MODE_INSTRUCTIONS);
    }

    /**
     * Answer a legal question, draft a document or analyse an attached one.
     *
     * @param  Audio|null  $audio
     */
    public function answer(string $mode, string $question, string $attachmentText, string $history, ?array $audio): ?string
    {
        $history = Str::substr($history, -self::HISTORY_LENGTH);

        $prompt = "Siz «Yuridik Simulyator» platformasining O'zbekiston Respublikasi qonunchiligi bo'yicha AI-yordamchisisiz. "
            ."O'zingizni faqat shu nom bilan tanishtiring, boshqa brend yoki xizmat nomlarini ishlatmang.\n"
            .'VAZIFA: '.(self::MODE_INSTRUCTIONS[$mode] ?? self::MODE_INSTRUCTIONS['general'])."\n\n"
            .($history !== '' ? "OLDINGI SUHBAT:\n".$history."\n\n" : '')
            .($attachmentText !== '' ? "BIRIKTIRILGAN HUJJAT MATNI:\n".Str::substr($attachmentText, 0, self::ATTACHMENT_LENGTH)."\n\n" : '')
            .($audio !== null
                ? "Foydalanuvchi OVOZLI XABAR yubordi. Avval uning savolini tushunib, so'ng huquqiy javob bering.\n"
                : "FOYDALANUVCHI MUROJAATI:\n".$question."\n\n")
            ."QAT'IY QOIDA: Matnda ** kabi yulduzchalar ishlatmang. Bo'limlarni raqamlar va chiroyli sarlavhalar bilan ajrating.";

        return $this->generate($prompt, $audio);
    }

    /**
     * The opponent's and the judge's reaction to the advocate's speech.
     *
     * @param  CourtContext  $context
     * @param  Audio|null  $audio
     * @return array{transcript: string, opponent: string, judge: string}|null
     */
    public function courtTurn(array $context, string $speech, ?array $audio): ?array
    {
        $prompt = "Siz O'zbekiston Respublikasining '".($context['court_type'] ?? null ?: 'Fuqarolik sudi')."' majlisini olib boryapsiz.\n"
            .'ISHTIROKCHI DARAJASI: '.($context['level_name'] ?? null ?: 'Talaba').' ('.($context['level_rule'] ?? '').").\n"
            ."ISH FABULASI:\n".($context['case_text'] ?? '')."\n"
            ."DALILLAR:\n".($context['evidence_list'] ?? '')."\n"
            ."TARIX:\n".$this->history($context)."\n"
            .($audio !== null ? "Advokat nutqini OVOZLI yubordi.\n" : "ADVOKAT NUTQI:\n".$speech."\n\n")
            ."Javobingizni faqat quyidagi teglarga bo'lib, yulduzchalarsiz (**) yozing:\n"
            ."[STENOGRAMMA]\n".($audio !== null ? 'Advokatning ovozli nutqi matni.' : $speech)."\n"
            ."[QARSHI_TOMON]\nProkuror yoki javobgar vakilining 2 gaplik qisqa, jiddiy e'tirozi.\n"
            ."[SUDYA]\nSudyaning darajaga mos 2 gaplik xulosasi va BITTADAN ortiq bo'lmagan aniq savoli.";

        $reply = $this->generate($prompt, $audio);

        if ($reply === null) {
            return null;
        }

        $sections = $this->parser->parse($reply, ['STENOGRAMMA', 'QARSHI_TOMON', 'SUDYA']);

        return [
            'transcript' => $sections['stenogramma'] ?: $speech,
            'opponent' => $sections['qarshi_tomon'] ?: "Da'vogar vakilining vajlariga e'tiroz bildiramiz.",
            'judge' => $sections['sudya'] ?: $reply,
        ];
    }

    /**
     * Generate a fresh case with three pieces of evidence for the given court and level.
     *
     * @param  CourtContext  $context
     * @return array{ok: bool, title: string, desc: string, evidences: list<string>, opponent: string, judge: string}
     */
    public function newCase(array $context): array
    {
        $courtType = $context['court_type'] ?? null ?: 'Fuqarolik sudi';

        $reply = $this->generate(
            "O'zbekiston qonunchiligi bo'yicha '".$courtType."' uchun '".($context['level_name'] ?? null ?: 'Talaba')."' (".($context['level_rule'] ?? '').") darajasiga mos yangi kazus va 3 ta dalil tuzing.\n"
                ."Faqat quyidagi teglarda, yulduzchalarsiz yozing:\n"
                ."[ISH_NOMI]\nIsh № va nomi\n"
                ."[ISH_HOLATI]\nAniq fabula va foydalanuvchi roli\n"
                ."[DALIL_1]\nHujjat nomi | Mazmuni\n"
                ."[DALIL_2]\nHujjat nomi | Mazmuni\n"
                ."[DALIL_3]\nQonun moddasi | Mazmuni\n"
                ."[QARSHI_TOMON]\nQarshi tomonning dastlabki e'tirozi (2 gap)\n"
                ."[SUDYA]\nSudyaning dastlabki savoli (1-2 gap)",
        );

        $sections = $this->parser->parse($reply ?? '', ['ISH_NOMI', 'ISH_HOLATI', 'DALIL_1', 'DALIL_2', 'DALIL_3', 'QARSHI_TOMON', 'SUDYA']);

        return [
            'ok' => $reply !== null,
            'title' => $sections['ish_nomi'] ?: 'Yangi sud ishi ('.$courtType.')',
            'desc' => $sections['ish_holati'] ?: (string) $reply,
            'evidences' => array_values(array_filter([$sections['dalil_1'], $sections['dalil_2'], $sections['dalil_3']])),
            'opponent' => $sections['qarshi_tomon'] ?: "Talablarimiz qanoatlantirilishini so'raymiz.",
            'judge' => $sections['sudya'] ?: 'Advokat, pozitsiyangizni bayon qiling.',
        ];
    }

    /**
     * @param  CourtContext  $context
     */
    public function verdict(array $context): ?string
    {
        return $this->generate(
            "Siz O'zbekiston Respublikasi '".($context['court_type'] ?? '')."' raisisiz. Ish: ".($context['case_text'] ?? '')."\nMuzokara: ".$this->history($context)."\n"
                ."Yakuniy Hal qiluv qarorini (Hukmni) e'lon qiling: 1) Huquqiy asoslar; 2) Qaror; 3) Ishtirokchiga (".($context['level_name'] ?? '').') 100 ballik tizimda baho va tahlil. Yulduzcha ishlatmang.',
        );
    }

    /**
     * @param  CourtContext  $context
     */
    public function hint(array $context): ?string
    {
        return $this->generate(
            'Siz ustoz yuristsiz ('.($context['level_name'] ?? '').' uchun). Ish: '.($context['case_text'] ?? '')."\nTarix: ".$this->history($context)."\nQaysi dalil va qaysi kodeks moddasini aytish kerakligini 3 gapda aniq ko'rsating. Yulduzcha ishlatmang.",
        );
    }

    /**
     * The four most relevant recent changes in Uzbek legislation.
     *
     * @return list<array{title?: string, category?: string, date?: string, summary?: string}>
     */
    public function latestNews(): array
    {
        $reply = $this->generate(
            "O'zbekiston Respublikasi qonunchiligidagi eng dolzarb 4 ta huquqiy o'zgarish va yangilikni (Mehnat, Fuqarolik, Soliq/Tadbirkorlik va Ta'lim/Sud sohalarida) JSON massiv formatida yozib bering. Format: [{\"title\":\"...\",\"category\":\"...\",\"date\":\"2026\",\"summary\":\"...\"}]. Faqat toza JSON qaytaring.",
        ) ?? '';

        $start = strpos($reply, '[');
        $end = strrpos($reply, ']');

        if ($start === false || $end === false || $end < $start) {
            return [];
        }

        $news = json_decode(substr($reply, $start, $end - $start + 1), true);

        return is_array($news) ? array_values(array_filter($news, is_array(...))) : [];
    }

    /**
     * @param  CourtContext  $context
     */
    private function history(array $context): string
    {
        return Str::substr($context['history'] ?? '', -self::HISTORY_LENGTH);
    }

    /**
     * @param  Audio|null  $audio
     */
    private function generate(string $prompt, ?array $audio = null): ?string
    {
        return $this->gemini->generate($prompt, $audio, self::TEMPERATURE, self::MAX_OUTPUT_TOKENS);
    }
}
