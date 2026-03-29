<?php
/**
 * config/brand_config.php
 *
 * Single source of truth for all CID-based brand configuration.
 * Included by: cc_landing.php, cc_tracking.php, leadgenpage.php
 *
 * To add a new partner:
 *   1. Add an entry to $BRAND_CONFIG keyed by the ?cid= value.
 *   2. No other files need changing.
 *
 * brand_color_rgb  : RGB components of brand_color as "R,G,B"
 *                    Used by CSS rgba() tints in all stylesheets.
 * affiliate        : Slug passed to CreditCardDAO::getByAffliate()
 *                    (only relevant for cc_landing.php)
 * logo_url         : Hosted image URL. Leave '' to use text+initials logo.
 * landing_path     : Relative path to the card listing page for this CID.
 *                    Used as the "← Apply Cards" back-link on tracking/leadgen pages.
 */

$BRAND_CONFIG = [

    // ── FindiBankit ───────────────────────────────────────────────
    'findibankit' => [
        'brand_name'      => 'FindiBankit',
        'brand_short'     => 'FB',
        'brand_color'     => '#4f46e5',
        'brand_color2'    => '#7c3aed',
        'brand_color_rgb' => '79,70,229',
        'tagline'         => 'Find Your Perfect Credit Card',
        'sub_tagline'     => 'Compare top cards & apply in 2 minutes.',
        'logo_url'        => '',
        'favicon_url'     => '',
        'stats'           => [
            ['num' => '50+',   'lbl' => 'Cards Available'],
            ['num' => '2 min', 'lbl' => 'Apply Time'],
            ['num' => '100%',  'lbl' => 'Secure'],
        ],
        'footer_text'     => '© 2025 FindiBankit. All rights reserved.',
        'affiliate'       => 'findipay',
        'landing_path'    => 'cc_landing',
    ],

    // ── CardKart ──────────────────────────────────────────────────
    'cardkart' => [
        'brand_name'      => 'CardKart',
        'brand_short'     => 'CK',
        'brand_color'     => '#0ea5e9',
        'brand_color2'    => '#0284c7',
        'brand_color_rgb' => '14,165,233',
        'tagline'         => 'Smart Cards for Smart People',
        'sub_tagline'     => "India's fastest growing card comparison platform.",
        'logo_url'        => '',
        'favicon_url'     => '',
        'stats'           => [
            ['num' => '80+',   'lbl' => 'Cards Listed'],
            ['num' => '5 min', 'lbl' => 'Apply Time'],
            ['num' => '4.8★',  'lbl' => 'Rated'],
        ],
        'footer_text'     => '© 2025 CardKart. All rights reserved.',
        'affiliate'       => 'cardkart',
        'landing_path'    => 'cc_landing',
    ],

    // ── RupeeRank ─────────────────────────────────────────────────
    'rupeerank' => [
        'brand_name'      => 'RupeeRank',
        'brand_short'     => 'RR',
        'brand_color'     => '#059669',
        'brand_color2'    => '#047857',
        'brand_color_rgb' => '5,150,105',
        'tagline'         => 'Rank Your Savings Not Your Debt',
        'sub_tagline'     => 'Compare rewards & cashback across top Indian cards.',
        'logo_url'        => '',
        'favicon_url'     => '',
        'stats'           => [
            ['num' => '60+',   'lbl' => 'Cards'],
            ['num' => '₹5Cr+', 'lbl' => 'Cashback Tracked'],
            ['num' => '1M+',   'lbl' => 'Users'],
        ],
        'footer_text'     => '© 2025 RupeeRank. All rights reserved.',
        'affiliate'       => 'rupeerank',
        'landing_path'    => 'cc_landing',
    ],

    // ── ZXPay ─────────────────────────────────────────────────────
    'zxpay' => [
        'brand_name'      => 'ZXPay',
        'brand_short'     => 'ZX',
        'brand_color'     => '#7c3aed',
        'brand_color2'    => '#6d28d9',
        'brand_color_rgb' => '124,58,237',
        'tagline'         => 'Pay Smarter Earn Better',
        'sub_tagline'     => 'Top credit cards curated for you.',
        'logo_url'        => '',
        'favicon_url'     => '',
        'stats'           => [
            ['num' => '40+',   'lbl' => 'Cards'],
            ['num' => '3 min', 'lbl' => 'Apply Time'],
            ['num' => '100%',  'lbl' => 'Secure'],
        ],
        'footer_text'     => '© 2025 ZXPay. All rights reserved.',
        'affiliate'       => 'zxpay',
        'landing_path'    => 'cc_landing',
    ],

    // ── Default fallback (used when ?cid= is unrecognised) ────────
    'default' => [
        'brand_name'      => 'FindiBankit',
        'brand_short'     => 'FB',
        'brand_color'     => '#4f46e5',
        'brand_color2'    => '#7c3aed',
        'brand_color_rgb' => '79,70,229',
        'tagline'         => 'Find Your Perfect Credit Card',
        'sub_tagline'     => 'Compare top cards & apply instantly.',
        'logo_url'        => '',
        'favicon_url'     => '',
        'stats'           => [
            ['num' => '50+',   'lbl' => 'Cards Available'],
            ['num' => '2 min', 'lbl' => 'Apply Time'],
            ['num' => '100%',  'lbl' => 'Secure'],
        ],
        'footer_text'     => '© 2025 FindiBankit.',
        'affiliate'       => 'findipay',
        'landing_path'    => 'cc_landing',
    ],
];

/**
 * Helper: resolve a brand config from a CID string.
 * Falls back to 'default' if the CID is unknown.
 *
 * @param  string $cid
 * @param  string $aid   Agent ID — used to build navigation URLs.
 * @return array
 */
function getBrand(string $cid, string $aid = ''): array
{
    global $BRAND_CONFIG;
    $brand = $BRAND_CONFIG[$cid] ?? $BRAND_CONFIG['default'];

    // Attach computed nav URLs so templates don't build them manually.
    $brand['landing_url']  = $brand['landing_path'] . "?cid={$cid}&aid={$aid}&product=cc";
    $brand['tracking_url'] = "cc_tracking?cid={$cid}&aid={$aid}";

    return $brand;
}
