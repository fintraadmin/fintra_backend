<?php
/**
 * cc_tracking.php
 * Credit Card Application Tracking Page
 *
 * URL: /english/cc_tracking?cid=findibankit&aid=<agent_id>
 */

require_once 'vendor/autoload.php';
require_once 'apis/dao/CCApplicationDAO.php';
require_once 'apis/dao/CreditCardDAO.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig   = new Twig_Environment($loader /*, ['cache' => '/tmp/twig_cache'] */);

$aid = trim($_REQUEST['aid'] ?? '');
$cid = trim($_REQUEST['cid'] ?? 'default');

// ── Brand config (mirrors cc_landing.php) ─────────────────────────
// Add a new entry here to support a new partner CID.
$CID_CONFIG = [

    'findibankit' => [
        'brand_name'      => 'FindiBankit',
        'brand_short'     => 'FB',
        'brand_color'     => '#4f46e5',
        'brand_color2'    => '#7c3aed',
        'brand_color_rgb' => '79,70,229',
        'logo_url'        => '',
        'footer_text'     => '© 2025 FindiBankit. All rights reserved.',
        'landing_url'     => "cc_landing?cid=findibankit&aid={$aid}&product=cc",
    ],

    'cardkart' => [
        'brand_name'      => 'CardKart',
        'brand_short'     => 'CK',
        'brand_color'     => '#0ea5e9',
        'brand_color2'    => '#0284c7',
        'brand_color_rgb' => '14,165,233',
        'logo_url'        => '',
        'footer_text'     => '© 2025 CardKart. All rights reserved.',
        'landing_url'     => "cc_landing?cid=cardkart&aid={$aid}&product=cc",
    ],

    'rupeerank' => [
        'brand_name'      => 'RupeeRank',
        'brand_short'     => 'RR',
        'brand_color'     => '#059669',
        'brand_color2'    => '#047857',
        'brand_color_rgb' => '5,150,105',
        'logo_url'        => '',
        'footer_text'     => '© 2025 RupeeRank. All rights reserved.',
        'landing_url'     => "cc_landing?cid=rupeerank&aid={$aid}&product=cc",
    ],

    'default' => [
        'brand_name'      => 'FindiBankit',
        'brand_short'     => 'FB',
        'brand_color'     => '#4f46e5',
        'brand_color2'    => '#7c3aed',
        'brand_color_rgb' => '79,70,229',
        'logo_url'        => '',
        'footer_text'     => '© 2025 FindiBankit.',
        'landing_url'     => "cc_landing?cid=default&aid={$aid}&product=cc",
    ],
];

$brand = $CID_CONFIG[$cid] ?? $CID_CONFIG['default'];

// ── Status → timeline step index ──────────────────────────────────
// Maps lead_status values to how far along the 4-step journey they are.
// step 0 = Submitted, 1 = Under Review, 2 = Approved/Rejected, 3 = Dispatched
$STATUS_STEP = [
    'new'        => 0,
    'progress'   => 1,
    'approved'   => 2,
    'dispatched' => 3,
    'rejected'   => 2,
];

// ── Fetch applications ─────────────────────────────────────────────
try {
    $appDao = new CCApplicationDAO();
    $cDao   = new CreditCardDAO();
    $result = $appDao->getApplicationByAgent($aid);

    $counts = ['total' => 0, 'new' => 0, 'progress' => 0, 'approved' => 0, 'rejected' => 0];

    foreach ($result as &$r) {
        // Enrich with card details
        $card            = $cDao->getByID($r['productid']);
        $r['card_name']  = $card['title'];
        $r['card_image'] = $card['image'];

        // Normalise status
        if (empty($r['lead_status'])) {
            $r['lead_status'] = 'New';
        }
        $r['class']        = strtolower(str_replace(' ', '', $r['lead_status']));
        $r['timeline_step'] = $STATUS_STEP[$r['class']] ?? 0;
        $r['is_rejected']  = ($r['class'] === 'rejected');

        // Tally counts
        $counts['total']++;
        $key = $r['class'];
        if (isset($counts[$key])) {
            $counts[$key]++;
        }
    }
    unset($r);

    $tpl_data = [
        'brand'        => $brand,
        'cid'          => $cid,
        'aid'          => $aid,
        'applications' => $result,
        'counts'       => $counts,
    ];

    $template = $twig->load('cc_tracking.twig.html');
    echo $template->render($tpl_data);

} catch (Exception $e) {
    error_log('[cc_tracking] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to fetch applications']);
}
