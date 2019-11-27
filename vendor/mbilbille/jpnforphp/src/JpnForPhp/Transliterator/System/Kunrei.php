<?php

/*
 * This file is part of the JpnForPhp package.
 *
 * (c) Matthieu Bilbille
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JpnForPhp\Transliterator\System;

/**
 * Transliteration system class to support Kunrei romanization system.
 *
 * @author Matthieu Bilbille (@mbibille)
 */
class Kunrei extends Romaji
{
    // Based on the following rules:
    // - http://www.age.ne.jp/x/nrs/iso3602/iso3602_unicode.html#kutoten
    private $mapping = array(
        'あ' => 'a', 'い' => 'i', 'う' => 'u', 'え' => 'e', 'お' => 'o',
        'か' => 'ka', 'き' => 'ki', 'く' => 'ku', 'け' => 'ke', 'こ' => 'ko',
        'さ' => 'sa', 'し' => 'si', 'す' => 'su', 'せ' => 'se', 'そ' => 'so',
        'た' => 'ta', 'ち' => 'ti', 'つ' => 'tu', 'て' => 'te', 'と' => 'to',
        'な' => 'na', 'に' => 'ni', 'ぬ' => 'nu', 'ね' => 'ne', 'の' => 'no',
        'は' => 'ha', 'ひ' => 'hi', 'ふ' => 'hu', 'へ' => 'he', 'ほ' => 'ho',
        'ま' => 'ma', 'み' => 'mi', 'む' => 'mu', 'め' => 'me', 'も' => 'mo',
        'や' => 'ya', 'ゆ' => 'yu', 'よ' => 'yo',
        'ら' => 'ra', 'り' => 'ri', 'る' => 'ru', 'れ' => 're', 'ろ' => 'ro',
        'わ' => 'wa', 'ゐ' => 'i', 'ゑ' => 'e', 'を' => 'wo',
        'ん' => 'n', 'が' => 'ga', 'ぎ' => 'gi', 'ぐ' => 'gu', 'げ' => 'ge', 'ご' => 'go',
        'ざ' => 'za', 'じ' => 'zi', 'ず' => 'zu', 'ぜ' => 'ze', 'ぞ' => 'zo',
        'だ' => 'da', 'ぢ' => 'zi', 'づ' => 'zu', 'で' => 'de', 'ど' => 'do',
        'ば' => 'ba', 'び' => 'bi', 'ぶ' => 'bu', 'べ' => 'be', 'ぼ' => 'bo',
        'ぱ' => 'pa', 'ぴ' => 'pi', 'ぷ' => 'pu', 'ぺ' => 'pe', 'ぽ' => 'po',
        'ゔ' => 'vu',
        'きゃ' => 'kya', 'きゅ' => 'kyu', 'きょ' => 'kyo',
        'しゃ' => 'sya', 'しゅ' => 'syu', 'しょ' => 'syo',
        'ちゃ' => 'tya', 'ちゅ' => 'tyu', 'ちょ' => 'tyo',
        'にゃ' => 'nya', 'にゅ' => 'nyu', 'にょ' => 'nyo',
        'ひゃ' => 'hya', 'ひゅ' => 'hyu', 'ひょ' => 'hyo',
        'みゃ' => 'mya', 'みゅ' => 'myu', 'みょ' => 'myo',
        'りゃ' => 'rya', 'りゅ' => 'ryu', 'りょ' => 'ryo',
        'ぎゃ' => 'gya', 'ぎゅ' => 'gyu', 'ぎょ' => 'gyo',
        'じゃ' => 'zya', 'じゅ' => 'zyu', 'じょ' => 'zyo',
        'ぢゃ' => 'zya', 'ぢゅ' => 'zyu', 'ぢょ' => 'zyo',
        'びゃ' => 'bya', 'びゅ' => 'byu', 'びょ' => 'byo',
        'ぴゃ' => 'pya', 'ぴゅ' => 'pyu', 'ぴょ' => 'pyo',
        'くゎ' => 'ka', 'ぐゎ' => 'ga',
        'んあ' => 'n\'a', 'んい' => 'n\'i', 'んう' => 'n\'u', 'んえ' => 'n\'e', 'んお' => 'n\'o',
        'んや' => 'n\'ya', 'んゆ' => 'n\'yu', 'んよ' => 'n\'yo',
        'いぃ' => 'yi', 'いぇ' => 'ye',
        'うぃ' => 'wi', 'うぅ' => 'wu', 'うぇ' => 'we', 'うぉ' => 'wo',
        'うゃ' => 'wya', 'ぶぁ' => 'va', 'ぶぃ' => 'vi', 'ぶぇ' => 've', 'ぶぉ' => 'vo',
        'ぶゃ' => 'vya', 'ぶゅ' => 'vyu', 'ぶぃぇ' => 'vye', 'ぶょ' => 'vyo',
        'きぇ' => 'kye', 'ぎぇ' => 'gye',
        'くぁ' => 'kwa', 'くぃ' => 'kwi', 'くぇ' => 'kwe', 'くぉ' => 'kwo',
        'ぐぁ' => 'gwa', 'ぐぃ' => 'gwi', 'ぐぇ' => 'gwe', 'ぐぉ' => 'gwo',
        'しぇ' => 'she', 'じぇ' => 'je',
        'すぃ' => 'si', 'ずぃ' => 'zi',
        'ちぇ' => 'che',
        'つぁ' => 'tsa', 'つぃ' => 'tsi', 'つぇ' => 'tse', 'つぉ' => 'tso', 'つゅ' => 'tsyu',
        'てぃ' => 'ti', 'てぅ' => 'tu', 'てゅ' => 'tyu',
        'でぃ' => 'di', 'でぅ' => 'du', 'でゅ' => 'dyu',
        'にぇ' => 'nye', 'ひぇ' => 'hye', 'びぇ' => 'bye', 'ぴぇ' => 'pye', 'みぇ' => 'mye', 'りぇ' => 'rye',
        'ふぁ' => 'fa', 'ふぃ' => 'fi', 'ふぇ' => 'fe', 'ふぉ' => 'fo',
        'ふゃ' => 'fya', 'ふゅ' => 'fyu', 'ふぃぇ' => 'fye', 'ふょ' => 'fyo',
        'ほぅ' => 'hu',
        '　' => ' ',
        '、' => ', ',
        '，' => ', ',
        '：' => ':',
        '・' => '-',
        '。' => '.',
        '！' => '!',
        '？' => '?',
        '‥' => '…',
        '「' => '\'',
        '」' => '\'',
        '『' => '"',
        '』' => '"',
        '（' => '(',
        '）' => ')',
        '｛' => '{',
        '｝' => '}',
        '［' => '[',
        '］' => ']',
        '【' => '[',
        '】' => ']',
        '〜' => '~',
        '〽' => '\'',
    );

    private $macrons = array(
        'a' => 'â',
        'i' => 'î',
        'u' => 'û',
        'e' => 'ê',
        'o' => 'ô'
    );

    private $longVowels = array(
        'aa' => 'â',
        'uu' => 'û',
        'ee' => 'ê',
        'oo' => 'ô',
        'ou' => 'ô'
    );

    private $particles = array(
      ' ha ' => ' wa ',
      ' he ' => ' e ',
      ' wo ' => ' o '
    );

    /**
     * Override __toString().
     *
     * @see System
     */
    public function __toString()
    {
        return 'Kunrei romanization system (訓令式ローマ字)';
    }

    /**
     * Override transliterate().
     *
     * @see System
     */
    public function transliterate($str) {

      $str = self::preTransliterate($str);

      // Workflow:
      //  1/ Default characters
      //  2/ Sokuon
      //  3/ Choonpu
      //  4/ Long vowels
      //  5/ Particles

      $str = self::transliterateDefaultCharacters($str, $this->mapping);

      $str = self::transliterateSokuon($str);

      $str = self::transliterateChoonpu($str, $this->macrons);

      $str = self::transliterateLongVowels($str, $this->longVowels);

      $str = self::transliterateParticles($str, $this->particles);

      return self::postTransliterate($str);
    }
}
