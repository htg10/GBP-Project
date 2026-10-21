<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    private array $positive = ['wonderful', 'professional', 'spotless', 'clean', 'best', 'great', 'recommend', 'painless', 'patient', 'smooth', 'excellent', 'love', 'good', 'quality', 'friendly', 'helpful'];
    private array $negative = ['terrible', 'waited', 'wait', 'frustrating', 'disappointed', 'expensive', 'disorganized', 'charged', 'rude', 'dirty', 'late', 'worst', 'bad', 'awful', 'slow'];

    private function key(): ?string
    {
        return config('services.gemini.key') ?: null;
    }

    private function geminiUrl(string $model, string $action = 'generateContent'): string
    {
        $base = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:{$action}";
        return $base . '?key=' . urlencode($this->key());
    }

    /**
     * Core Gemini call — accepts per-call maxTokens and temperature.
     * Thinking is disabled (budget=0) to save output tokens on simple tasks.
     */
    private function callGemini(string $prompt, int $maxTokens = 200, float $temperature = 0.7): ?string
    {
        if (! $this->key()) {
            return null;
        }
        try {
            $model = config('services.gemini.model', 'gemini-3.1-flash-lite');
            $url = $this->geminiUrl($model);

            $res = Http::timeout(20)
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => $maxTokens,
                        'thinkingConfig' => ['thinkingBudget' => 0],
                    ],
                ]);

            if (! $res->successful()) {
                Log::warning('Gemini API returned ' . $res->status() . '; using local fallback. Body: ' . $res->body());
                return null;
            }

            $text = $res->json('candidates.0.content.parts.0.text');
            return is_string($text) ? trim($text) : null;
        } catch (\Throwable $e) {
            Log::warning('Gemini call failed: ' . $e->getMessage() . '; using local fallback.');
            return null;
        }
    }

    /**
     * Sentiment — uses local keyword matching only (no AI call needed).
     * Saves API cost entirely; keyword matching is accurate enough for
     * POSITIVE/NEUTRAL/NEGATIVE classification.
     */
    public function analyzeSentiment(string $text): string
    {
        $clean = trim($text);
        if ($clean === '') {
            return 'NEUTRAL';
        }
        return $this->localSentiment($clean);
    }

    private function localSentiment(string $text): string
    {
        $t = strtolower($text);
        $score = 0;
        foreach ($this->positive as $w) {
            if (str_contains($t, $w)) $score += 1;
        }
        foreach ($this->negative as $w) {
            if (str_contains($t, $w)) $score -= 1.2;
        }
        if ($score > 0.5) return 'POSITIVE';
        if ($score < -0.5) return 'NEGATIVE';
        return 'NEUTRAL';
    }

    public function generateReviewReply(string $reviewerName, string $comment, int $starRating): string
    {
        $name = explode(' ', trim($reviewerName) ?: 'there')[0];

        if ($this->key()) {
            $out = $this->callGemini(
                "Reply to this Google review as the business owner. 2-3 sentences, warm, professional, no emojis.\nReviewer: {$name}\nRating: {$starRating}/5\nReview: \"{$comment}\"",
                150
            );
            if ($out) return $out;
        }

        $sentiment = $this->localSentiment($comment);
        if ($sentiment === 'POSITIVE' || $starRating >= 4) {
            return "Thank you so much for your kind words, {$name}! We're delighted you had a positive experience and truly appreciate you sharing it. We look forward to seeing you again.";
        }
        if ($sentiment === 'NEGATIVE' || $starRating <= 2) {
            return "Hi {$name}, thank you for this feedback, and we're sorry your experience fell short. We'd genuinely like to make it right — please reach out directly so we can look into what happened.";
        }
        return "Thank you for the honest feedback, {$name}. We're glad parts of your visit went well and are always working to improve. We'd love to hear how we can do better.";
    }

    public function generateContent(string $prompt): string
    {
        if ($this->key()) {
            $out = $this->callGemini(
                "Write a short social media post for a small business. {$prompt}. Keep it concise with relevant hashtags.",
                200
            );
            if ($out) return $out;
        }
        return "Draft based on: \"{$prompt}\". (Set GEMINI_API_KEY in your .env for live AI generation.)";
    }

    /**
     * Raw prompt — no persona wrapper. Used by controllers that need
     * structured/JSON output (audit, competitor, rank-checker).
     */
    public function rawPrompt(string $prompt, int $maxTokens = 300): ?string
    {
        return $this->callGemini($prompt, $maxTokens, 0.5);
    }

    public function chat(string $message): array
    {
        if ($this->key()) {
            $out = $this->callGemini(
                "You are ReviewFlow AI, a marketing assistant for local businesses. Help with Google reviews, social media, local SEO, and marketing advice. Be practical and concise.\n\nUser: {$message}",
                400
            );
            if ($out) {
                return ['reply' => $out, 'source' => 'ai'];
            }
        }

        return [
            'reply' => "I couldn't reach the AI right now (check your GEMINI_API_KEY, or you may have hit the free rate limit — wait ~30 seconds and try again).",
            'source' => 'fallback',
        ];
    }

    public function generateImage(string $prompt): array
    {
        if ($this->key()) {
            $result = $this->callGeminiImage($prompt);
            if ($result) {
                return ['success' => true, 'data_uri' => $result, 'source' => 'ai'];
            }
        }

        return ['success' => false, 'data_uri' => $this->placeholderImage($prompt), 'source' => 'fallback'];
    }

    private function callGeminiImage(string $prompt): ?string
    {
        try {
            $model = config('services.gemini.image_model', 'gemini-2.5-flash-image');
            $url = $this->geminiUrl($model);

            $res = Http::timeout(45)
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if (! $res->successful()) {
                Log::warning('Gemini image API returned ' . $res->status() . '; using placeholder. Body: ' . $res->body());
                return null;
            }

            $parts = $res->json('candidates.0.content.parts', []);
            foreach ($parts as $part) {
                $data = $part['inlineData']['data'] ?? $part['inline_data']['data'] ?? null;
                $mime = $part['inlineData']['mimeType'] ?? $part['inline_data']['mime_type'] ?? 'image/png';
                if ($data) {
                    return "data:{$mime};base64,{$data}";
                }
            }
            Log::warning('Gemini image API returned no inline image data.');
            return null;
        } catch (\Throwable $e) {
            Log::warning('Gemini image call failed: ' . $e->getMessage());
            return null;
        }
    }

    private function placeholderImage(string $prompt): string
    {
        $label = trim(mb_substr($prompt, 0, 60)) ?: 'AI image';
        $bg = ['#0f6b5c', '#c2790a', '#b23a48', '#2a5a8a'][crc32($prompt) % 4];
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="512" height="512">'
            . '<rect width="100%" height="100%" fill="' . $bg . '"/>'
            . '<text x="50%" y="46%" font-family="sans-serif" font-size="22" fill="#fff" text-anchor="middle">✦ Preview unavailable</text>'
            . '<text x="50%" y="56%" font-family="sans-serif" font-size="14" fill="#e8e6dd" text-anchor="middle">' . htmlspecialchars($label) . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public function generateKeywords(string $business, string $city, ?string $industry = null): array
    {
        $industryLine = $industry ? " ({$industry})" : '';

        if ($this->key()) {
            $prompt = "Suggest Google search keywords for: {$business}{$industryLine} in {$city}.\n"
                . "Return ONLY valid JSON: {\"groups\":[{\"group\":\"High intent\",\"keywords\":[\"...\"]},{\"group\":\"Local\",\"keywords\":[\"...\"]},{\"group\":\"Long-tail\",\"keywords\":[\"...\"]},{\"group\":\"Services\",\"keywords\":[\"...\"]}]}\n"
                . "5-7 keywords per group, include city name.";

            $raw = $this->callGemini($prompt, 300, 0.6);
            if ($raw) {
                $clean = trim(preg_replace('/```json|```/', '', $raw));
                $data = json_decode($clean, true);
                if (is_array($data) && isset($data['groups']) && is_array($data['groups'])) {
                    return ['source' => 'ai', 'groups' => $data['groups']];
                }
            }
        }

        $b = strtolower($industry ?: $business);
        return ['source' => 'fallback', 'groups' => [
            ['group' => 'High intent', 'keywords' => ["best {$b} in {$city}", "{$b} near me", "top rated {$b} {$city}", "affordable {$b} {$city}"]],
            ['group' => 'Local', 'keywords' => ["{$b} {$city}", "{$city} {$b} services", "{$b} open now {$city}"]],
            ['group' => 'Long-tail', 'keywords' => ["how much does {$b} cost in {$city}", "book {$b} appointment {$city}", "trusted {$b} clinic {$city}"]],
            ['group' => 'Services', 'keywords' => ["{$b} consultation {$city}", "emergency {$b} {$city}", "{$b} reviews {$city}"]],
        ]];
    }

    /**
     * Same as generateKeywords(), but uses extractJson() for a more
     * tolerant parse of Gemini's response. New method — generateKeywords()
     * itself (used by the web app) is untouched, so this only affects
     * callers that explicitly use it.
     */
    public function generateKeywordsRobust(string $business, string $city, ?string $industry = null): array
    {
        $industryLine = $industry ? " in the {$industry} industry" : '';

        if ($this->key()) {
            $prompt = "You are a local SEO expert. Suggest Google search keywords a customer would use to find this business, for local ranking.\n"
                ."Business: {$business}{$industryLine}\nCity: {$city}\n\n"
                ."Return ONLY valid JSON (no markdown, no backticks) in exactly this shape:\n"
                .'{"groups":[{"group":"High intent","keywords":["..."]},{"group":"Local","keywords":["..."]},{"group":"Long-tail","keywords":["..."]},{"group":"Services","keywords":["..."]}]}'."\n"
                ."Give 5-7 keywords per group. Keywords must be realistic search phrases including the city where natural.";

            $raw = $this->callGemini($prompt);
            if ($raw) {
                $data = $this->extractJson($raw);
                if (is_array($data) && isset($data['groups']) && is_array($data['groups'])) {
                    return ['source' => 'ai', 'groups' => $data['groups']];
                }
            }
        }

        // Local fallback (no key or parse failed / rate-limited) — templated keywords.
        $b = strtolower($industry ?: $business);
        return ['source' => 'fallback', 'groups' => [
            ['group' => 'High intent', 'keywords' => ["best {$b} in {$city}", "{$b} near me", "top rated {$b} {$city}", "affordable {$b} {$city}"]],
            ['group' => 'Local', 'keywords' => ["{$b} {$city}", "{$city} {$b} services", "{$b} open now {$city}"]],
            ['group' => 'Long-tail', 'keywords' => ["how much does {$b} cost in {$city}", "book {$b} appointment {$city}", "trusted {$b} clinic {$city}"]],
            ['group' => 'Services', 'keywords' => ["{$b} consultation {$city}", "emergency {$b} {$city}", "{$b} reviews {$city}"]],
        ]];
    }

    /**
     * Gemini is asked to return ONLY JSON, but it sometimes wraps the answer
     * in markdown fences or adds a short sentence before/after the JSON
     * object anyway. Stripping fences and then json_decode-ing the raw
     * string (as some callers used to do) fails whenever that extra text is
     * present, even though the JSON itself is valid.
     *
     * This pulls out the first {...} object in the text and decodes that,
     * which tolerates any surrounding prose. Returns null if nothing
     * decodable is found, so callers can fall back exactly as before.
     *
     * New method — existing callers (web + API controllers using their own
     * parsing) are unaffected unless they are updated to call this.
     */
    public function extractJson(string $raw): ?array
    {
        $clean = trim(preg_replace('/```json|```/', '', $raw));

        $direct = json_decode($clean, true);
        if (is_array($direct)) {
            return $direct;
        }

        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');
        if ($start === false || $end === false || $end < $start) {
            return null;
        }

        $parsed = json_decode(substr($clean, $start, $end - $start + 1), true);
        return is_array($parsed) ? $parsed : null;
    }
}
