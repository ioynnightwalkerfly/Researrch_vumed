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
    foreach ($custom as $org) {
        $name = $org['name'];
        $count = $org['records_count'];
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

// ─── Main ───
try {
    $apiData = fetchFromApi($VUMEDHR_API_URL, $VUMEDHR_API_KEY);
    $apiResult = ['nodes' => [], 'links' => []];
    $hasApi = false;
    
    if ($apiData) {
        $apiResult = buildFromApiData($apiData, $categoryMap, $idMap);
        $hasApi = true;
    }

    $manualResult = getManualData();
    $hasManual = count($manualResult['nodes']) > 0;

    $result = mergeOrganizationData($apiResult, $manualResult);

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
