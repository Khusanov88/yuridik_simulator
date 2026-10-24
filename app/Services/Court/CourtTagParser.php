<?php

namespace App\Services\Court;

class CourtTagParser
{
    /**
     * Split an AI reply into the sections that follow each "[TAG]" marker.
     *
     * Role prefixes the model sometimes repeats (e.g. "Sudya:") are removed.
     *
     * @param  list<string>  $tags
     * @return array<string, string> Sections keyed by lowercase tag name; missing tags map to an empty string.
     */
    public function parse(string $text, array $tags): array
    {
        $sections = array_fill_keys(array_map(strtolower(...), $tags), '');
        $alternation = implode('|', array_map(fn (string $tag): string => preg_quote($tag, '/'), $tags));
        $pattern = '/\[('.$alternation.')\](.*?)(?=\[(?:'.$alternation.')\]|$)/siu';

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $sections[strtolower($match[1])] = $this->cleanRolePrefix($match[2]);
            }
        }

        return $sections;
    }

    private function cleanRolePrefix(string $section): string
    {
        $quotes = " \t\n\r\0\x0B«»\"'";
        $section = trim($section, $quotes);
        $section = preg_replace(
            '/^(👔|👩‍⚖️|📝)?\s*(Javobgar advokati|Qarshi tomon|Raislik qiluvchi|Sudya|Prokuror|Sud majlisi kotibi)\s*(\([^)]*\))?\s*:\s*/iu',
            '',
            $section,
        ) ?? $section;

        return trim($section, $quotes);
    }
}
