<?php
// api/api_organizations.php — Proxy to fetch organization data from VumedHR API
// Falls back to hardcoded data if external API is unavailable
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ─── VumedHR API Config ───
$VUMEDHR_API_URL = 'https://vumedhr.vu.ac.th/vumedhr/public/api/get_organizations.php';
$VUMEDHR_API_KEY = 'MHR-cf335b8cbe671f117fdb1a01e7a2af49';

// ─── Display name normalization (API/DB name → canonical display name) ───
$displayNameMap = [
    'มทส.'                                                    => 'มหาวิทยาลัยเทคโนโลยีสุรนารี',
    'ม.มหิดล'                                                  => 'มหาวิทยาลัยมหิดล',
    'ศูนย์วิชาการสารเสพติด'                                     => 'ศูนย์วิทยาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่',
    'ภาคเหนือ มช.'                                             => 'ศูนย์วิทยาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่',
    'ศูนย์วิชาการสารเสพติด ภาคเหนือ มช.'                       => 'ศูนย์วิทยาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่',
    'ศูนย์วิชาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่'       => 'ศูนย์วิทยาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่',
    'ศูนย์วิชาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม'        => 'ศูนย์วิทยาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่',
    'สถาบันวิจัยสังคม'                                          => 'สถาบันวิจัยสังคม จุฬาฯ',
    'จุฬาฯ'                                                     => 'สถาบันวิจัยสังคม จุฬาฯ',
    'BMC Journal'                                               => 'BMC journal',
    'Suranaree Journal'                                         => 'Suranaree Journal of Science and Technology',
];

// ─── Category mapping (display name → category) ───
$categoryMap = [
    'มหาวิทยาลัยเทคโนโลยีสุรนารี'                           => 'academic',
    'มหาวิทยาลัยมหิดล'                                      => 'academic',
    'ศูนย์วิทยาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่'   => 'academic',
    'สถาบันวิจัยสังคม จุฬาฯ'                                 => 'academic',
    'Karbala International Journal of Modern Science'        => 'academic',
    'สำนักงานศาลยุติธรรม'                                    => 'gov',
    'มูลนิธิสาธารณสุขแห่งชาติ (มสช.)'                        => 'gov',
    'สำนักงาน ป.ป.ส.'                                       => 'gov',
    'BMC journal'                                            => 'journal',
    'Osong PHRP'                                             => 'journal',
    'Suranaree Journal of Science and Technology'            => 'journal',
];

// ─── Node ID mapping (display name → node ID) ───
$idMap = [
    'มหาวิทยาลัยเทคโนโลยีสุรนารี'                           => 'SUT',
    'มหาวิทยาลัยมหิดล'                                      => 'MAHIDOL',
    'ศูนย์วิทยาการสารเสพติดภาคเหนือ มหาวิทยาลัยเชียงใหม่'   => 'CMU',
    'สถาบันวิจัยสังคม จุฬาฯ'                                 => 'CHULA',
    'Karbala International Journal of Modern Science'        => 'KARBALA',
    'สำนักงานศาลยุติธรรม'                                    => 'COJ',
    'มูลนิธิสาธารณสุขแห่งชาติ (มสช.)'                        => 'NHF',
    'สำนักงาน ป.ป.ส.'                                       => 'ONCB',
    'BMC journal'                                            => 'BMC',
    'Osong PHRP'                                             => 'OSONG',
    'Suranaree Journal of Science and Technology'            => 'SURA_J',
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
        CURLOPT_SSL_VERIFYPEER => false,
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
function buildFromApiData($apiData) {
    $nodes = [];
    $links = [];
    $seenIds = [];

    $groups = $apiData['groups'] ?? [];
    foreach ($groups as $groupKey => $group) {
        $orgs = $group['organizations'] ?? [];
        foreach ($orgs as $org) {
            $name = $org['name'];
            $id = $org['org_id'] ?? 'ORG_' . md5($name);
            $category = $org['category'] ?? 'academic';

            if (isset($seenIds[$id])) {
                foreach ($nodes as &$n) {
                    if ($n['id'] === $id) {
                        $n['records_count'] += (int)$org['records_count'];
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
                'category'      => $category,
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
    foreach ($custom as $org) {
        $name = $org['name'];
        $count = $org['records_count'];
        $id = $org['org_id'] ?? 'CUSTOM_' . md5($name);
        $category = $org['category'] ?? 'other';

        if (isset($seenIds[$id])) {
            foreach ($nodes as &$n) {
                if ($n['id'] === $id) {
                    $n['records_count'] += (int)$count;
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
            'category'      => $category,
            'records_count' => (int)$count,
            'group'         => 'custom',
        ];
        $links[] = [
            'source' => 'VU',
            'target' => $id,
        ];
    }

    // Filter out organizations with 0 records
    $filteredNodes = [];
    $filteredLinks = [];
    $validIds = [];

    foreach ($nodes as $n) {
        if ($n['records_count'] > 0) {
            $filteredNodes[] = $n;
            $validIds[$n['id']] = true;
        }
    }

    foreach ($links as $l) {
        if (isset($validIds[$l['target']])) {
            $filteredLinks[] = $l;
        }
    }

    return ['nodes' => $filteredNodes, 'links' => $filteredLinks];
}

// ─── Merging Logic ───
function mergeOrganizationData($apiResult, $manualResult) {
    $mergedNodes = [];
    $mergedLinks = [];
    $seenIds = [];

    // Process API Data
    foreach ($apiResult['nodes'] as $node) {
        $mergedNodes[] = $node;
        $seenIds[$node['id']] = count($mergedNodes) - 1;
    }
    foreach ($apiResult['links'] as $link) {
        $mergedLinks[] = $link;
    }

    // Process Manual Data
    foreach ($manualResult['nodes'] as $node) {
        if (isset($seenIds[$node['id']])) {
            $idx = $seenIds[$node['id']];
            $mergedNodes[$idx]['records_count'] += $node['records_count'];
        } else {
            $mergedNodes[] = $node;
            $seenIds[$node['id']] = count($mergedNodes) - 1;
        }
    }

    foreach ($manualResult['links'] as $link) {
        $isDuplicate = false;
        foreach ($mergedLinks as $mLink) {
            if (($mLink['source'] === $link['source'] && $mLink['target'] === $link['target']) ||
                ($mLink['target'] === $link['source'] && $mLink['source'] === $link['target'])) {
                $isDuplicate = true;
                break;
            }
        }
        if (!$isDuplicate) {
             $sourceExists = $link['source'] === 'VU' || isset($seenIds[$link['source']]);
             $targetExists = $link['target'] === 'VU' || isset($seenIds[$link['target']]);
             if ($sourceExists && $targetExists) {
                $mergedLinks[] = $link;
             }
        }
    }

    return ['nodes' => $mergedNodes, 'links' => $mergedLinks];
}

// ─── Fetch Manual Data from DB ───
function getManualData() {
    $nodes = [];
    $links = [];
    try {
        require 'db.php';
        $stmt = $conn->query("SELECT * FROM organizations_manual");
        while ($row = $stmt->fetch()) {
            $nodes[] = [
                'id'            => $row['node_id'],
                'name'          => $row['name'],
                'category'      => $row['category'],
                'records_count' => (int)$row['records_count'],
                'group'         => $row['group_id'],
            ];
            $links[] = [
                'source' => 'VU',
                'target' => $row['node_id'],
            ];
        }
        if (count($nodes) > 0) {
            $links[] = ['source' => 'CMU', 'target' => 'ONCB'];
        }
    } catch (Exception $e) {
        // Table doesn't exist or DB error, returns empty
    }

    return ['nodes' => $nodes, 'links' => $links];
}

// ─── Normalize final result (merge duplicate IDs) ───
function normalizeResult($result) {
    $normalized = [];
    $links = [];
    $seenIds = [];

    foreach ($result['nodes'] as $node) {
        $id = $node['id'];

        if (isset($seenIds[$id])) {
            $idx = $seenIds[$id];
            $normalized[$idx]['records_count'] += $node['records_count'];
            continue;
        }
        $seenIds[$id] = count($normalized);
        $normalized[] = $node;
    }

    // Rebuild links with only valid node IDs
    $validIds = array_flip(array_column($normalized, 'id'));
    
    $seenLinks = [];
    foreach ($result['links'] as $link) {
        $src = $link['source'];
        $tgt = $link['target'];
        $key = $src . '->' . $tgt;
        if (!isset($seenLinks[$key]) && ($src === 'VU' || isset($validIds[$src])) && isset($validIds[$tgt])) {
            $seenLinks[$key] = true;
            $links[] = ['source' => $src, 'target' => $tgt];
        }
    }

    return ['nodes' => $normalized, 'links' => $links];
}

// ─── Main ───
try {
    $apiData = fetchFromApi($VUMEDHR_API_URL, $VUMEDHR_API_KEY);
    $apiResult = ['nodes' => [], 'links' => []];
    $hasApi = false;
    
    if ($apiData) {
        $apiResult = buildFromApiData($apiData);
        $hasApi = true;
    }

    $manualResult = getManualData();
    $hasManual = count($manualResult['nodes']) > 0;

    $result = mergeOrganizationData($apiResult, $manualResult);
    $result = normalizeResult($result);

    if ($hasApi && $hasManual) {
        $source = 'api_and_manual';
    } else if ($hasApi) {
        $source = 'api';
    } else if ($hasManual) {
        $source = 'manual';
    } else {
        $source = 'none';
    }

    echo json_encode([
        'success' => true,
        'source'  => $source,
        'nodes'   => $result['nodes'],
        'links'   => $result['links'],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    $manualResult = getManualData();
    echo json_encode([
        'success' => true,
        'source'  => 'manual_fallback',
        'nodes'   => $manualResult['nodes'],
        'links'   => $manualResult['links'],
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
