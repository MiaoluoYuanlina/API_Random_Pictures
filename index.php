<?php
// 读取本地 JSON 文件
$jsonData = file_get_contents('images.json');
$data = json_decode($jsonData, true);

// 获取 URL 参数
$tags = isset($_GET['tags']) ? explode(',', $_GET['tags']) : [];
$ratio = isset($_GET['ratio']) ? $_GET['ratio'] : null;
$r18 = isset($_GET['r18']) ? $_GET['r18'] : null;
$matchAll = isset($_GET['match_all']) ? filter_var($_GET['match_all'], FILTER_VALIDATE_BOOLEAN) : false;
$returnType = isset($_GET['returnType']) ? (int)$_GET['returnType'] : 1;  // 新增 returnType 参数

// 函数：检查标签是否匹配（根据 match_all 控制匹配逻辑）
function checkTags($tags, $itemTags, $matchAll) {
    if ($matchAll) {
        // 如果 match_all 为 true，必须所有标签都匹配
        foreach ($tags as $tag) {
            $found = false;
            foreach ($itemTags as $itemTag) {
                // 检查标签是否包含在 itemTag 中
                if (stripos($itemTag, $tag) !== false) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;  // 如果有标签不匹配，返回 false
            }
        }
        return true;  // 所有标签都匹配
    } else {
        // 如果 match_all 为 false，任何标签匹配一个即可
        foreach ($tags as $tag) {
            foreach ($itemTags as $itemTag) {
                // 检查标签是否包含在 itemTag 中
                if (stripos($itemTag, $tag) !== false) {
                    return true;
                }
            }
        }
        return false;  // 如果没有任何标签匹配，返回 false
    }
}

// 筛选数据
$filteredData = array_filter($data, function($item) use ($tags, $ratio, $r18, $matchAll) {
    // 检查 tags 是否匹配
    if (!empty($tags) && !checkTags($tags, $item['tags'], $matchAll) && !in_array($item['author_name'], $tags)) {
        return false;
    }
    
    // 检查 r18
    if ($r18 !== null) {
        $isR18 = filter_var($r18, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($isR18 !== null && $item['r18'] !== $isR18) {
            return false;
        }
    }
    
    // 检查 ratio
    if ($ratio !== null) {
        $ratioValues = explode('*', $item['ratio']);
        $width = (int)$ratioValues[0];
        $height = (int)$ratioValues[1];
        
        if ($ratio == 1 && $width > $height) {
            return true; // 横排
        } elseif ($ratio == 2 && $width < $height) {
            return true; // 竖排
        } elseif ($ratio == 3 && $width == $height) {
            return true; // 方正
        }
        return false;
    }
    
    return true;
});

// 随机选择一张图片
$randomImage = null;
if (count($filteredData) > 0) {
    $randomImage = $filteredData[array_rand($filteredData)];
}

// 返回内容
if ($randomImage) {
    if ($returnType == 1) {
        // 返回图片内容
        $imageUrl = $randomImage['image_url'];

        // 使用 cURL 获取图片内容
        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // 跟随重定向
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Referer: https://www.pixiv.net/'  // 设置 Referer 头部
        ]);
        $imageData = curl_exec($ch);

        // 获取 cURL 请求的响应信息
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        // 检查 cURL 请求是否成功
        if(curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
            exit;
        }

        curl_close($ch);

        // 设置正确的 Content-Type 头部
        if (strpos($contentType, 'image/jpeg') !== false) {
            header('Content-Type: image/jpeg');
        } elseif (strpos($contentType, 'image/png') !== false) {
            header('Content-Type: image/png');
        } elseif (strpos($contentType, 'image/gif') !== false) {
            header('Content-Type: image/gif');
        } else {
            echo json_encode(['error' => 'Unsupported image type']);
            exit;
        }

        // 输出图片内容
        echo $imageData;
    } elseif ($returnType == 2) {
        // 返回图片的 URL
        echo $randomImage['image_url'];
    } elseif ($returnType == 3) {
        // 返回 JSON 格式的数据
        echo json_encode($randomImage);
    }
} else {
    echo json_encode(['error' => 'No image found matching the criteria']);
}
?>
