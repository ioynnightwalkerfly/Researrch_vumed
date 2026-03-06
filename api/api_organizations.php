<?php
// api/api_organizations.php — Proxy to fetch organization data from VumedHR API
// Falls back to hardcoded data if external API is unavailable
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ─── VumedHR API Config ───
$VUMEDHR_API_URL = 'https://vumedhr.vu.ac.th/vumedhr/public/api/get_organizations.php';
$VUMEDHR_API_KEY = 'MHR-cf335b8cbe671f117fdb1a01e7a2af49';

// ─── Category mapping for org names ───
$categoryMap = [
    'ศูนย์วิชาการสารเสพติด'           => 'academic',
    'ภาคเหนือ มช.'                    => 'academic',
    'มทส.'                            => 'academic',
    'ม.มหิดล'                         => 'academic',
    'สถาบันวิจัยสังคม'                 => 'academic',
    'จุฬาฯ'                            => 'academic',
    'มูลนิธิสาธารณสุขแห่งชาติ (มสช.)' => 'gov',
    'สำนักงาน ป.ป.ส.'                 => 'gov',
    'สำนักงานศาลยุติธรรม'              => 'gov',
    'BMC Journal'                      => 'journal',
    'Suranaree Journal'                => 'journal',
    'Osong PHRP'                       => 'journal',
];

// ─── Node ID mapping ───
$idMap = [
    'ศูนย์วิชาการสารเสพติด'           => 'CMU',
    'ภาคเหนือ มช.'                    => 'CMU_NORTH',
    'มทส.'                            => 'SUT',
    'ม.มหิดล'                         => 'MAHIDOL',
    'สถาบันวิจัยสังคม'                 => 'CHULA',
    'จุฬาฯ'                            => 'CHULA_U',
    'มูลนิธิสาธารณสุขแห่งชาติ (มสช.)' => 'NHF',
    'สำนักงาน ป.ป.ส.'                 => 'ONCB',
    'สำนักงานศาลยุติธรรม'              => 'COJ',
    'BMC Journal'                      => 'BMC',
    'Suranaree Journal'                => 'SURA_J',
    'Osong PHRP'                       => 'OSONG',
];

// ─── Try fetching from VumedHR API ───
function fetchFromApi($url, $apiKey) {
    if (empty($apiKey)) return null;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $apiKey,
            'Accept: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if ($data && isset($data['status']) && $data['status'] === 'success') {
            return $data;
        }
    }
    return null;
}

// ─── Build nodes and links from API response ───
function buildFromApiData($apiData, $categoryMap, $idMap) {
    $nodes = [];
    $links = [];
    $seenIds = [];

    $groups = $apiData['groups'] ?? [];
    foreach ($groups as $groupKey => $group) {
        $orgs = $group['organizations'] ?? [];
        foreach ($orgs as $org) {
            $name = $org['name'];
            $id = $idMap[$name] ?? 'ORG_' . md5($name);

            if (isset($seenIds[$id])) {
                // Accumulate records count if same org appears in multiple groups
                foreach ($nodes as &$n) {
                    if ($n['id'] === $id) {
                        $n['records_count'] += $org['records_count'];
                        break;
                    }
                }
                unset($n);
                continue;
            }
            $seenIds[$id] = true;

            $nodes[] = [
                'id'            => $id,
                'name'          => $name,
                'category'      => $categoryMap[$name] ?? 'academic',
                'records_count' => (int)$org['records_count'],
                'group'         => $groupKey,
            ];
            $links[] = [
                'source' => 'VU',
                'target' => $id,
            ];
        }
    }

    // Include custom organizations (user-entered "อื่นๆ")
    $custom = $apiData['custom_organizations'] ?? [];
    foreach ($custom as $name => $count) {
        $id = 'CUSTOM_' . md5($name);
        if (!isset($seenIds[$id])) {
            $seenIds[$id] = true;
            $nodes[] = [
                'id'            => $id,
                'name'          => $name,
                'category'      => 'academic',
                'records_count' => (int)$count,
                'group'         => 'custom',
            ];
            $links[] = [
                'source' => 'VU',
                'target' => $id,
            ];
        }
    }

    return ['nodes' => $nodes, 'links' => $links];
}

// ─── Fallback hardcoded data ───
function getFallbackData() {
    return [
        'nodes' => [
            ['id' => 'CMU',    'name' => 'ศูนย์วิชาการสารเสพติด ภาคเหนือ มช.', 'category' => 'academic', 'records_count' => 5, 'group' => 'A'],
            ['id' => 'SUT',    'name' => 'มทส.',                               'category' => 'academic', 'records_count' => 2, 'group' => 'A'],
            ['id' => 'MAHIDOL','name' => 'ม.มหิดล',                            'category' => 'academic', 'records_count' => 2, 'group' => 'A'],
            ['id' => 'CHULA',  'name' => 'สถาบันวิจัยสังคม จุฬาฯ',             'category' => 'academic', 'records_count' => 3, 'group' => 'A'],
            ['id' => 'NHF',    'name' => 'มูลนิธิสาธารณสุขแห่งชาติ (มสช.)',    'category' => 'gov',      'records_count' => 3, 'group' => 'A'],
            ['id' => 'ONCB',   'name' => 'สำนักงาน ป.ป.ส.',                    'category' => 'gov',      'records_count' => 2, 'group' => 'A'],
            ['id' => 'COJ',    'name' => 'สำนักงานศาลยุติธรรม',                 'category' => 'gov',      'records_count' => 2, 'group' => 'A'],
            ['id' => 'BMC',    'name' => 'BMC Journal',                         'category' => 'journal',  'records_count' => 1, 'group' => 'B'],
            ['id' => 'SURA_J', 'name' => 'Suranaree Journal',                   'category' => 'journal',  'records_count' => 1, 'group' => 'B'],
            ['id' => 'OSONG',  'name' => 'Osong PHRP',                          'category' => 'journal',  'records_count' => 1, 'group' => 'B'],
        ],
        'links' => [
            ['source' => 'VU', 'target' => 'CMU'],
            ['source' => 'VU', 'target' => 'SUT'],
            ['source' => 'VU', 'target' => 'MAHIDOL'],
            ['source' => 'VU', 'target' => 'CHULA'],
            ['source' => 'VU', 'target' => 'NHF'],
            ['source' => 'VU', 'target' => 'ONCB'],
            ['source' => 'VU', 'target' => 'COJ'],
            ['source' => 'VU', 'target' => 'BMC'],
            ['source' => 'VU', 'target' => 'SURA_J'],
            ['source' => 'VU', 'target' => 'OSONG'],
            ['source' => 'CMU', 'target' => 'ONCB'],
        ],
    ];
}

// ─── Main ───
try {
    $apiData = fetchFromApi($VUMEDHR_API_URL, $VUMEDHR_API_KEY);
    
    if ($apiData) {
        $result = buildFromApiData($apiData, $categoryMap, $idMap);
        $source = 'api';
    } else {
        $result = getFallbackData();
        $source = 'fallback';
    }

    echo json_encode([
        'success' => true,
        'source'  => $source,
        'nodes'   => $result['nodes'],
        'links'   => $result['links'],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // On any error, return fallback
    $result = getFallbackData();
    echo json_encode([
        'success' => true,
        'source'  => 'fallback',
        'nodes'   => $result['nodes'],
        'links'   => $result['links'],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
