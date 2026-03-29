<?php
/**
 * leadgenpage.php
 * Lead Capture Form — shown before redirecting user to bank's application page.
 *
 * URL: /leadgen?cid=<partner>&aid=<agent_id>&pid=<product_id>
 */

require_once 'vendor/autoload.php';
require_once 'apis/dao/CreditCardDAO.php';
require_once 'config/brand_config.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig   = new Twig_Environment($loader);

$aid = trim($_REQUEST['aid'] ?? '');
$cid = trim($_REQUEST['cid'] ?? 'default');
$pid = trim($_REQUEST['pid'] ?? '');

$brand = getBrand($cid, $aid);

// Fetch card details so the template can show the card preview panel
$card = [];
if (!empty($pid)) {
    try {
        $cDao = new CreditCardDAO();
        $card = $cDao->getByID($pid) ?? [];
    } catch (Exception $e) {
        error_log('[leadgen] card fetch failed: ' . $e->getMessage());
    }
}

// The redirect URL — name/phone/pincode/email are appended by JS after validation
$redirectURL = 'https://fintra.co.in/redir?aid=' . urlencode($aid)
             . '&cid=' . urlencode($cid)
             . '&pid=' . urlencode($pid);

echo $twig->load('leadgen.twig.html')->render([
    'brand'       => $brand,
    'cid'         => $cid,
    'aid'         => $aid,
    'pid'         => $pid,
    'card'        => $card,
    'redirectURL' => $redirectURL,
]);
