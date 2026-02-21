<?php
/** Aumentar memoria para descargas STL grandes */
@ini_set('memory_limit', '512M');

/**
 * Script para importar modelos de Printables.com (imagenes + STL).
 *
 * Usa el GraphQL API no documentado de Printables para buscar modelos
 * y descarga imagenes y archivos STL.
 *
 * Uso desde CLI:
 *   php scrape_printables.php --ids=12345,67890
 *   php scrape_printables.php --search="vase" --max=10
 *
 * Uso desde navegador:
 *   ?ids=12345,67890
 *   ?search=vase&max=10
 */

require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/includes/functions.php';

class PrintablesScraper
{
    private $graphqlUrl = 'https://api.printables.com/graphql/';
    private $mediaBaseUrl = 'https://media.printables.com/';
    private $filesBaseUrl = 'https://files.printables.com/';
    private $siteBaseUrl = 'https://www.printables.com';
    private $stlDir;
    private $imgDir;
    private $stlErrorLog;
    private $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    /** Número de reintentos para la descarga del ZIP de STL */
    const STL_DOWNLOAD_RETRIES = 2;

    public function __construct($stlErrorLogPath = null)
    {
        $this->stlDir = __DIR__ . '/public/stl';
        $this->imgDir = __DIR__ . '/public/images';
        $this->stlErrorLog = $stlErrorLogPath !== null ? $stlErrorLogPath : (__DIR__ . '/printables_stl_errors.log');

        if (!is_dir($this->stlDir)) {
            mkdir($this->stlDir, 0755, true);
        }
        if (!is_dir($this->imgDir)) {
            mkdir($this->imgDir, 0755, true);
        }
    }

    /**
     * Registra un fallo de descarga STL para poder reintentar después
     */
    private function logStlError($modelId, $reason)
    {
        $line = date('Y-m-d H:i:s') . "\t" . $modelId . "\t" . $reason . "\n";
        @file_put_contents($this->stlErrorLog, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Busca modelos en Printables via GraphQL
     */
    public function searchModels($query, $limit = 10)
    {
        $graphqlQuery = [
            'operationName' => 'SearchModels',
            'query' => 'query SearchModels($query: String!, $limit: Int) {
                result: searchPrints2(query: $query, limit: $limit) {
                    items {
                        id
                        slug
                        name
                        summary
                        image {
                            filePath
                        }
                        images {
                            filePath
                        }
                        downloadCount
                        likesCount
                        stls {
                            id
                            name
                            fileSize
                        }
                        category {
                            name
                        }
                    }
                }
            }',
            'variables' => [
                'query' => $query,
                'limit' => (int) $limit,
            ],
        ];

        $response = $this->curlRequest($this->graphqlUrl, 'POST', json_encode($graphqlQuery), [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: https://www.printables.com',
            'Referer: https://www.printables.com/',
        ]);

        if (!$response || !isset($response['data']['result']['items'])) {
            return $this->searchModelsFallback($query, $limit);
        }

        return $response['data']['result']['items'];
    }

    private function searchModelsFallback($query, $limit)
    {
        $searchUrl = $this->siteBaseUrl . '/search/models?q=' . urlencode($query);
        $html = $this->curlRequestRaw($searchUrl);

        if (!$html) {
            return [];
        }

        $models = [];
        if (preg_match_all('/\/model\/(\d+)-([a-z0-9\-]+)/i', $html, $matches, PREG_SET_ORDER)) {
            $seen = [];
            foreach ($matches as $match) {
                $id = (int) $match[1];
                if (isset($seen[$id]) || count($models) >= $limit) {
                    continue;
                }
                $seen[$id] = true;
                $slug = $match[2];
                $name = ucwords(str_replace('-', ' ', $slug));
                $models[] = [
                    'id' => $id,
                    'slug' => $slug,
                    'name' => $name,
                    'images' => [],
                    'stls' => [],
                ];
            }
        }

        foreach ($models as &$model) {
            try {
                $detail = $this->getModelDetail($model['id']);
                if ($detail) {
                    $model = array_merge($model, $detail);
                }
            } catch (Exception $e) {
            }
            sleep(1);
        }

        return $models;
    }

    public function getModelDetail($modelId)
    {
        $graphqlQuery = [
            'operationName' => 'PrintDetail',
            'query' => 'query PrintDetail($id: ID!) {
                print(id: $id) {
                    id
                    slug
                    name
                    summary
                    image { filePath }
                    images { filePath }
                    stls { id name fileSize }
                    downloadCount
                    likesCount
                    category { name }
                }
            }',
            'variables' => ['id' => (string) $modelId],
        ];

        $response = $this->curlRequest($this->graphqlUrl, 'POST', json_encode($graphqlQuery), [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: https://www.printables.com',
            'Referer: https://www.printables.com/',
        ]);

        if (!$response || !isset($response['data']['print'])) {
            return $this->getModelDetailFallback($modelId);
        }

        return $response['data']['print'];
    }

    private function getModelDetailFallback($modelId)
    {
        $url = $this->siteBaseUrl . '/model/' . $modelId;
        $html = $this->curlRequestRaw($url);

        if (!$html) {
            return null;
        }

        $model = [
            'id' => $modelId,
            'name' => "Printables Model $modelId",
            'images' => [],
            'stls' => [],
        ];

        if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $html, $m)) {
            $model['name'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (preg_match('/<h1[^>]*>([^<]+)</i', $html, $m)) {
            $model['name'] = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $html, $m)) {
            $model['images'][] = ['filePath' => $m[1]];
        }

        if (preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]+)"/i', $html, $m)) {
            $model['summary'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $model;
    }

    public function downloadImage($model)
    {
        $images = $model['images'] ?? [];
        if (empty($images) && !empty($model['image']['filePath'])) {
            $images = [$model['image']];
        }
        if (empty($images)) {
            return null;
        }

        $filePath = $images[0]['filePath'] ?? null;
        if (!$filePath) {
            return null;
        }

        if (strpos($filePath, 'http') !== 0) {
            $imageUrl = $this->mediaBaseUrl . ltrim($filePath, '/');
        } else {
            $imageUrl = $filePath;
        }

        $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $ext = 'jpg';
        }

        $modelId = $model['id'] ?? 'unknown';
        $filename = 'printables_' . $modelId . '_' . time() . '.' . $ext;
        $savePath = $this->imgDir . '/' . $filename;

        $imageData = $this->curlRequestRaw($imageUrl);
        if ($imageData && strlen($imageData) > 100) {
            file_put_contents($savePath, $imageData);
            return $filename;
        }

        return null;
    }

    private function getDownloadLink($printId, $source, array $files)
    {
        $graphqlQuery = [
            'operationName' => 'GetDownload',
            'query' => 'mutation GetDownload($printId: ID!, $source: DownloadSourceEnum!, $files: [DownloadFileInput!]) {
                getDownloadLink(printId: $printId, source: $source, files: $files) {
                    ok
                    output {
                        link
                        ttl
                        files { id fileType link ttl }
                    }
                }
            }',
            'variables' => [
                'printId' => (string) $printId,
                'source' => (string) $source,
                'files' => $files,
            ],
        ];

        $response = $this->curlRequest($this->graphqlUrl, 'POST', json_encode($graphqlQuery), [
            'Content-Type: application/json',
            'Accept: application/json',
            'Origin: https://www.printables.com',
            'Referer: https://www.printables.com/',
        ]);

        $link = $response['data']['getDownloadLink']['output']['files'][0]['link']
            ?? $response['data']['getDownloadLink']['output']['link']
            ?? null;

        if (!is_string($link) || $link === '') {
            return null;
        }
        if (strpos($link, 'http') !== 0) {
            $link = $this->filesBaseUrl . ltrim($link, '/');
        }
        return $link;
    }

    private function sanitizeFilename($name)
    {
        $name = (string) $name;
        $name = str_replace(["\0", "\r", "\n", "\t"], '', $name);
        $name = basename($name);
        $name = preg_replace('/[\\\\\\/\\:\\*\\?\\"\\<\\>\\|]+/', '_', $name);
        $name = trim($name);
        return $name !== '' ? $name : 'file.stl';
    }

    public function downloadStl($model)
    {
        $modelId = $model['id'] ?? null;
        if (!$modelId) {
            return null;
        }

        $stls = $model['stls'] ?? [];
        if (!empty($stls) && is_array($stls)) {
            foreach ($stls as $stl) {
                $stlId = $stl['id'] ?? null;
                if (!$stlId) {
                    continue;
                }
                $stlName = $stl['name'] ?? ('printables_' . $modelId . '.stl');
                $downloadUrl = $this->getDownloadLink($modelId, 'model_detail', [
                    ['fileType' => 'stl', 'ids' => [(string) $stlId]],
                ]);

                if (!$downloadUrl) {
                    continue;
                }

                $stlData = $this->curlRequestRaw($downloadUrl);
                if (!$stlData || strlen($stlData) <= 100) {
                    continue;
                }

                $safeName = $this->sanitizeFilename($stlName);
                if (strtolower(substr($safeName, -4)) !== '.stl') {
                    $safeName .= '.stl';
                }

                $basename = 'printables_' . $modelId . '_' . $safeName;
                $targetPath = $this->stlDir . '/' . $basename;

                $counter = 1;
                $originalBasename = $basename;
                while (file_exists($targetPath)) {
                    $pathInfo = pathinfo($originalBasename);
                    $basename = $pathInfo['filename'] . '_' . $counter . '.' . ($pathInfo['extension'] ?? 'stl');
                    $targetPath = $this->stlDir . '/' . $basename;
                    $counter++;
                }

                file_put_contents($targetPath, $stlData);
                return $basename;
            }
        }

        $zipUrl = $this->siteBaseUrl . '/model/' . $modelId . '/download';
        $tempDir = sys_get_temp_dir();
        $maxAttempts = 1 + self::STL_DOWNLOAD_RETRIES;
        $lastError = '';

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $zipFile = $tempDir . '/printables_' . $modelId . '_' . time() . '_' . $attempt . '.zip';

            $ch = curl_init($zipUrl);
            $fp = fopen($zipFile, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: ' . $this->userAgent,
                'Accept: application/zip,application/octet-stream,*/*;q=0.8',
                'Referer: ' . $this->siteBaseUrl . '/model/' . $modelId,
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            fclose($fp);

            if ($httpCode !== 200) {
                $lastError = "HTTP $httpCode" . ($curlError ? ": $curlError" : '');
                @unlink($zipFile);
                if ($attempt < $maxAttempts) {
                    sleep(2);
                }
                continue;
            }

            if (!file_exists($zipFile) || filesize($zipFile) <= 0) {
                $lastError = 'ZIP vacío o no descargado';
                @unlink($zipFile);
                if ($attempt < $maxAttempts) {
                    sleep(2);
                }
                continue;
            }

            $fileContent = @file_get_contents($zipFile, false, null, 0, 4);
            if ($fileContent !== 'PK' . chr(0x03) . chr(0x04)) {
                $lastError = 'Respuesta no es un ZIP válido';
                @unlink($zipFile);
                if ($attempt < $maxAttempts) {
                    sleep(2);
                }
                continue;
            }

            $stlFilename = $this->extractFirstStl($zipFile, $modelId);
            @unlink($zipFile);
            if ($stlFilename) {
                return $stlFilename;
            }
            $lastError = 'ZIP sin archivos .stl';
            if ($attempt < $maxAttempts) {
                sleep(2);
            }
        }

        $this->logStlError($modelId, $lastError ?: 'ZIP fallido sin detalle');
        return null;
    }

    private function extractFirstStl($zipFile, $modelId)
    {
        if (!class_exists('ZipArchive')) {
            return null;
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFile) !== TRUE) {
            return null;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (substr($filename, -1) === '/' || strtolower(substr($filename, -4)) !== '.stl') {
                continue;
            }

            $basename = 'printables_' . $modelId . '_' . basename($filename);
            $targetPath = $this->stlDir . '/' . $basename;

            $counter = 1;
            $originalBasename = $basename;
            while (file_exists($targetPath)) {
                $pathInfo = pathinfo($originalBasename);
                $basename = $pathInfo['filename'] . '_' . $counter . '.' . $pathInfo['extension'];
                $targetPath = $this->stlDir . '/' . $basename;
                $counter++;
            }

            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                file_put_contents($targetPath, $content);
                $zip->close();
                return $basename;
            }
        }

        $zip->close();
        return null;
    }

    private function curlRequest($url, $method = 'GET', $body = null, $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['User-Agent: ' . $this->userAgent], $headers));

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        return json_decode($response, true);
    }

    private function curlRequestRaw($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: ' . $this->userAgent,
            'Accept: */*',
            'Referer: https://www.printables.com/',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return null;
        }

        return $response;
    }
}

function readPrintablesImportLog($logPath)
{
    $imported = [];
    if (!is_file($logPath) || !is_readable($logPath)) {
        return $imported;
    }
    $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode("\t", $line, 2);
        if (isset($parts[0]) && is_numeric(trim($parts[0]))) {
            $imported[(string) (int) $parts[0]] = true;
        }
    }
    return $imported;
}

function appendPrintablesImportLog($logPath, $modelId, $productId)
{
    if (!$logPath || !$modelId || !$productId) {
        return;
    }
    $dir = dirname($logPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $line = $modelId . "\t" . $productId . "\t" . date('Y-m-d H:i:s') . "\n";
    @file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
}

function improveDescriptionWithAI($text)
{
    if (trim($text) === '') {
        return null;
    }
    $baseUrl = getenv('AI_3D_SERVICE_URL') ?: 'http://localhost:8000';
    $url = rtrim($baseUrl, '/') . '/api/v1/description/improve';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $text]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || !$response) {
        return null;
    }
    $data = json_decode($response, true);
    return isset($data['text']) && is_string($data['text']) ? trim($data['text']) : null;
}

function processPrintablesModels(array $models, PrintablesScraper $scraper, $importLogPath = null, array $options = [])
{
    $productModel = new Product();
    $created = 0;
    $results = [];
    $alreadyImported = $importLogPath ? readPrintablesImportLog($importLogPath) : [];

    foreach ($models as $model) {
        $modelId = $model['id'] ?? 'unknown';
        $modelIdKey = is_numeric($modelId) ? (string) (int) $modelId : (string) $modelId;
        $name = $model['name'] ?? "Printables Model $modelId";
        $summary = $model['summary'] ?? $model['description'] ?? '';

        $entry = [
            'model_id' => $modelId,
            'name' => $name,
            'image' => null,
            'stl' => null,
            'product_id' => null,
            'status' => 'pending',
        ];

        if (isset($alreadyImported[$modelIdKey])) {
            $entry['status'] = 'skipped';
            $entry['product_id'] = '(ya importado)';
            $results[] = $entry;
            continue;
        }

        $imageFilename = $scraper->downloadImage($model);
        $entry['image'] = $imageFilename;

        $stlFilename = $scraper->downloadStl($model);
        $entry['stl'] = $stlFilename;

        if (!$stlFilename && !$imageFilename) {
            $entry['status'] = 'failed';
            $results[] = $entry;
            continue;
        }

        $rawDescription = $summary
            ? 'Modelo importado desde Printables.com. ' . $summary
            : 'Modelo 3D importado desde Printables.com: "' . $name . '"';
        $description = improveDescriptionWithAI($rawDescription);
        if ($description === null) {
            $description = $rawDescription;
        }

        $priceMin = isset($options['price_min']) ? (float) $options['price_min'] : null;
        $priceMax = isset($options['price_max']) ? (float) $options['price_max'] : null;
        if ($priceMin !== null && $priceMax !== null && $priceMin <= $priceMax) {
            $price = round($priceMin + mt_rand() / mt_getrandmax() * ($priceMax - $priceMin), 2);
        } else {
            $price = 19.99;
        }
        $imageUrl = $imageFilename ? asset('images/' . $imageFilename) : '';

        $newId = $productModel->create($name, $description, $price, $imageUrl);
        if ($newId) {
            if ($stlFilename) {
                $productModel->updateDimensions($newId, $stlFilename);
            }
            $entry['product_id'] = $newId;
            $entry['status'] = 'ok';
            $created++;
            if ($importLogPath) {
                appendPrintablesImportLog($importLogPath, $modelIdKey, $newId);
                $alreadyImported[$modelIdKey] = true;
            }
        } else {
            $entry['status'] = 'db_error';
        }

        $results[] = $entry;
        sleep(2);
    }

    return [
        'total_models' => count($models),
        'products_created' => $created,
        'details' => $results,
    ];
}

if (realpath(__FILE__) !== realpath($_SERVER['SCRIPT_FILENAME'] ?? __FILE__)) {
    return;
}

if (php_sapi_name() === 'cli') {
    $options = getopt('', ['ids:', 'search:', 'max:', 'log:', 'category:', 'price-min:', 'price-max:']);
    $ids = $options['ids'] ?? '';
    $search = $options['search'] ?? '';
    $max = (int) ($options['max'] ?? 10);
    $importLog = isset($options['log']) ? $options['log'] : (__DIR__ . '/printables_import.log');
    $importOptions = [];
    if (isset($options['category']) && $options['category'] !== '') {
        $importOptions['category'] = $options['category'];
    }
    if (isset($options['price-min']) && $options['price-min'] !== '') {
        $importOptions['price_min'] = $options['price-min'];
    }
    if (isset($options['price-max']) && $options['price-max'] !== '') {
        $importOptions['price_max'] = $options['price-max'];
    }

    if (!$ids && !$search) {
        fwrite(STDERR, "Uso:\n  php scrape_printables.php --search=\"vase\" --max=20\n");
        exit(1);
    }

    $scraper = new PrintablesScraper();

    try {
        if ($ids) {
            $idList = array_filter(array_map('intval', explode(',', $ids)));
            $models = [];
            foreach ($idList as $id) {
                echo "Obteniendo modelo #$id...\n";
                $detail = $scraper->getModelDetail($id);
                if ($detail) {
                    $models[] = $detail;
                } else {
                    echo "  No se pudo obtener modelo #$id\n";
                }
                sleep(1);
            }
        } else {
            echo "Buscando \"$search\" (max: $max)...\n";
            $models = $scraper->searchModels($search, $max);
        }

        echo "Encontrados: " . count($models) . " modelos\n";

        if (empty($models)) {
            echo "No se encontraron modelos.\n";
            exit(0);
        }

        echo "Descargando imagenes y STL...\n";
        $result = processPrintablesModels($models, $scraper, $importLog, $importOptions);

        echo "\nResultado:\n";
        echo "  Modelos procesados: {$result['total_models']}\n";
        echo "  Productos creados: {$result['products_created']}\n";

        foreach ($result['details'] as $d) {
            $status = $d['status'] === 'ok' ? 'OK' : ($d['status'] === 'skipped' ? 'OMIT' : 'FALLO');
            echo "  [{$status}] {$d['name']} - Imagen: " . ($d['image'] ?: 'ninguna') . " - STL: " . ($d['stl'] ?: 'ninguno') . "\n";
        }
    } catch (Exception $e) {
        fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
        exit(1);
    }
} else {
    header('Content-Type: application/json; charset=utf-8');

    $ids = $_GET['ids'] ?? '';
    $search = $_GET['search'] ?? '';
    $max = (int) ($_GET['max'] ?? 10);
    $importLog = __DIR__ . '/printables_import.log';
    $importOptions = [];
    if (!empty($_GET['category'])) {
        $importOptions['category'] = $_GET['category'];
    }
    if (isset($_GET['price_min']) && $_GET['price_min'] !== '') {
        $importOptions['price_min'] = $_GET['price_min'];
    }
    if (isset($_GET['price_max']) && $_GET['price_max'] !== '') {
        $importOptions['price_max'] = $_GET['price_max'];
    }

    if (!$ids && !$search) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Usa ?ids=12345,67890 o ?search=vase&max=10'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $scraper = new PrintablesScraper();

    try {
        if ($ids) {
            $idList = array_filter(array_map('intval', explode(',', $ids)));
            $models = [];
            foreach ($idList as $id) {
                $detail = $scraper->getModelDetail($id);
                if ($detail) {
                    $models[] = $detail;
                }
                sleep(1);
            }
        } else {
            $models = $scraper->searchModels($search, $max);
        }

        if (empty($models)) {
            echo json_encode(['status' => 'ok', 'message' => 'No se encontraron modelos', 'data' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }

        $result = processPrintablesModels($models, $scraper, $importLog, $importOptions);

        echo json_encode(['status' => 'ok', 'data' => $result], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
