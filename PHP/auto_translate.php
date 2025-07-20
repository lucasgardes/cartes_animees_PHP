<?php
require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;

// 🌐 Langue courante
$lang = $_GET['lang'] ?? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
$lang = in_array($lang, ['en', 'fr']) ? $lang : 'fr';

// 📁 Fichier de cache
$cacheFile = __DIR__ . "/translations/cache_$lang.json";
$translations = file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];

function t($text) {
    global $lang, $translations, $cacheFile;

    if ($lang === 'fr') return $text;
    if (isset($translations[$text])) return $translations[$text];

    // 🛰️ Traduction automatique via LibreTranslate
    try {
        $client = new Client(['base_uri' => 'https://libretranslate.de/']);
        $res = $client->post('/translate', [
            'form_params' => [
                'q' => $text,
                'source' => 'fr',
                'target' => $lang,
                'format' => 'text'
            ]
        ]);
        $data = json_decode($res->getBody(), true);
        $translated = $data['translatedText'] ?? $text;
    } catch (Exception $e) {
        $translated = $text; // fallback
    }

    $translations[$text] = $translated;
    file_put_contents($cacheFile, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return $translated;
}
