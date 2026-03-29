<?php
/**
 * cc_landing.php
 * Credit Card Landing Page Controller
 *
 * URL format:
 *   /english/cc_landing?aid=<affiliate_id>&cid=<partner_id>&product=cc
 *
 * To add a new partner: add an entry to $CID_CONFIG below.
 * To recategorise a card: edit $CARD_CATEGORIES below.
 */

require_once 'vendor/autoload.php';
require_once 'apis/dao/CreditCardDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig   = new Twig_Environment($loader /*, ['cache' => '/tmp/twig_cache'] */);

// ── Request params ─────────────────────────────────────────────────────────
$aid     = trim($_REQUEST['aid']   ?? '');
$cid     = trim($_REQUEST['cid']   ?? 'default');
$qa      = $_REQUEST['qa']          ?? null;
$product = trim($_REQUEST['product'] ?? 'cc');

if (empty($aid)) {
    echo $twig->load('error.twig.html')->render(['error' => 'Missing affiliate ID.']);
    return;
}

// ── Partner / brand configuration ─────────────────────────────────────────
// Each key = the ?cid= value passed in the URL.
// brand_color  : primary accent (buttons, links, badges)
// brand_color2 : gradient end colour
// affiliate    : slug passed to CreditCardDAO::getByAffliate()
// logo_url     : optional hosted logo image; leave '' to use text initials
$CID_CONFIG = [

    'findibankit' => [
        'brand_name'   => 'FindiBankit',
        'brand_short'  => 'FB',
        'brand_color'  => '#4f46e5',
        'brand_color2' => '#7c3aed',
        'tagline'      => 'Find Your Perfect Credit Card',
        'sub_tagline'  => 'Compare top cards & apply in 2 minutes.',
        'logo_url'     => '',
        'favicon_url'  => '',
        'stats'        => [
            ['num' => '50+',   'lbl' => 'Cards Available'],
            ['num' => '2 min', 'lbl' => 'Apply Time'],
            ['num' => '100%',  'lbl' => 'Secure'],
        ],
        'footer_text'  => '© 2025 FindiBankit. All rights reserved.',
        'affiliate'    => 'findipay',
    ],

    'cardkart' => [
        'brand_name'   => 'CardKart',
        'brand_short'  => 'CK',
        'brand_color'  => '#0ea5e9',
        'brand_color2' => '#0284c7',
        'tagline'      => 'Smart Cards for Smart People',
        'sub_tagline'  => "India's fastest growing card comparison platform.",
        'logo_url'     => '',
        'favicon_url'  => '',
        'stats'        => [
            ['num' => '80+',   'lbl' => 'Cards Listed'],
            ['num' => '5 min', 'lbl' => 'Apply Time'],
            ['num' => '4.8★',  'lbl' => 'Rated'],
        ],
        'footer_text'  => '© 2025 CardKart. All rights reserved.',
        'affiliate'    => 'cardkart',
    ],

    'rupeerank' => [
        'brand_name'   => 'RupeeRank',
        'brand_short'  => 'RR',
        'brand_color'  => '#059669',
        'brand_color2' => '#047857',
        'tagline'      => 'Rank Your Savings Not Your Debt',
        'sub_tagline'  => 'Compare rewards & cashback across top Indian cards.',
        'logo_url'     => '',
        'favicon_url'  => '',
        'stats'        => [
            ['num' => '60+',   'lbl' => 'Cards'],
            ['num' => '₹5Cr+', 'lbl' => 'Cashback Tracked'],
            ['num' => '1M+',   'lbl' => 'Users'],
        ],
        'footer_text'  => '© 2025 RupeeRank. All rights reserved.',
        'affiliate'    => 'rupeerank',
    ],

    // ── Fallback used when ?cid= is unrecognised ──
    'default' => [
        'brand_name'   => 'FindiBankit',
        'brand_short'  => 'FB',
        'brand_color'  => '#4f46e5',
        'brand_color2' => '#7c3aed',
        'tagline'      => 'Find Your Perfect Credit Card',
        'sub_tagline'  => 'Compare top cards & apply instantly.',
        'logo_url'     => '',
        'favicon_url'  => '',
        'stats'        => [
            ['num' => '50+',   'lbl' => 'Cards Available'],
            ['num' => '2 min', 'lbl' => 'Apply Time'],
            ['num' => '100%',  'lbl' => 'Secure'],
        ],
        'footer_text'  => '© 2025 FindiBankit.',
        'affiliate'    => 'findipay',
    ],
];

// ── Card category map ──────────────────────────────────────────────────────
// Key   = card id as stored in the database
// Value = 'premium' | 'popular' | 'trending'
// Cards whose id is NOT listed here default to 'popular'.
$CARD_CATEGORIES = [
    'hdfc-infinia'       => 'premium',
    'amex-platinum'      => 'premium',
    'axis-magnus'        => 'premium',
    'hdfc-regalia-gold'  => 'popular',
    'sbi-simplyclick'    => 'popular',
    'icici-amazon-pay'   => 'popular',
    'axis-ace'           => 'trending',
    'flipkart-axis'      => 'trending',
    'onecard'            => 'trending',
    'idfc-first-wealth'  => 'trending',
];

// ── Resolve brand ──────────────────────────────────────────────────────────
$brand = $CID_CONFIG[$cid] ?? $CID_CONFIG['default'];

// ── Fetch & bucket cards ───────────────────────────────────────────────────
$dao   = new CreditCardDAO();
$cards = $dao->getByAffliate($brand['affiliate']);
error_log("[cc_landing] cid=$cid affiliate={$brand['affiliate']} cards=" . count($cards));

$premium  = [];
$popular  = [];
$trending = [];

foreach ($cards as &$card) {
    $card['a_link'] = "https://fintra.co.in/leadgen?cid={$cid}&aid={$aid}&pid={$card['id']}";
    $cat = $CARD_CATEGORIES[$card['id']] ?? 'popular';
    $card['category'] = $cat;
    match ($cat) {
        'premium'  => $premium[]  = $card,
        'trending' => $trending[] = $card,
        default    => $popular[]  = $card,
    };
}
unset($card);

// ── Render ─────────────────────────────────────────────────────────────────
$tpl_data = [
    'brand'        => $brand,
    'cid'          => $cid,
    'aid'          => $aid,
    'premium'      => $premium,
    'popular'      => $popular,
    'trending'     => $trending,
    'tracking_url' => "cc_tracking?cid={$cid}&aid={$aid}",
];

if ($product === 'cc') {
    echo $twig->load('cc_landing.twig.html')->render($tpl_data);
}

if ($product === 'saving') {
    $tpl_data['a_link'] = "https://fintra.co.in/leadgen?cid={$cid}&aid={$aid}&pid=indusind-saving";
    echo $twig->load('landing.saving.twig.html')->render($tpl_data);
}

