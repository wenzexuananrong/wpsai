
<!DOCTYPE html>
<html>
<head>
    <title>Track Package</title>
    <style>
    .collapsed {
        display: none;
    }
    .radio-div{
        display: inline-block;
        margin-right: 20px; 
    }
    </style>
    <script>
    function toggleTable(tableId) {
        var table = document.getElementById(tableId);
        var rows = table.getElementsByTagName('tr');

        for (var i = 1; i < rows.length; i++) {
        if (rows[i].style.display === 'none' || rows[i].style.display === '') {
            rows[i].style.display = 'table-row';
        } else {
            rows[i].style.display = 'none';
        }
        }
    }
    </script>
</head>
<body>
    <h1>Track Package</h1>
    <form method="post" action="" class="wp-block-search__button-outside wp-block-search__text-button wp-block-search">
        <!-- 添加单选框 -->
        <div class="radio-div">
            <input style="display: inline-block;vertical-align: middle;" type="radio" name="search_type" value="order_num" id="order_num" checked>
            <label style="display: inline-block;vertical-align: middle;" for="order_num">Order Number</label>
        </div>
        <div class="radio-div">
            <input style="display: inline-block;vertical-align: middle;" type="radio" name="search_type" value="way_bill_num" id="way_bill_num" >
            <label style="display: inline-block;vertical-align: middle;" for="way_bill_num">Package Tracking Number</label>
        </div>
        
        <div class="wp-block-search__inside-wrapper ">
        <input type="text" name="keyword" placeholder="" value="<?php echo isset($_POST['keyword']) ? $_POST['keyword'] : ''; ?>">
        <button aria-label="Search" class="wp-block-search__button wp-element-button" type="submit">Search</button>
        </div>
        
    
    <!-- 在这里显示搜索结果 -->
    <?php

    function get_token() {
        $user_id = 'C72110';
        $api_secret = 'Ym3s4lVAqxM=';
        $token = base64_encode($user_id . '&' . $api_secret);
        return $token;
    }
    
    function get_sign($method, $json_data, $v,$AppKey,$AppSecret,$timestamp) {
        $format = "json";
        $input_str = "app_key" . $AppKey . "format" . $format . "method" . $method . "timestamp" . $timestamp . "v" . $v . $json_data . $AppSecret;
        // 使用md5生成32位小写签名sign
        $sign = md5($input_str);
        return $sign;
    }

    function get_tracking_info($way_bill_num,$Package_int){
        try {
            echo '<h2>Package '.$Package_int.' Tracking Number: '.$way_bill_num.'</h2>';
            if (strpos($way_bill_num, '4PX') !== false){
                $AppKey = "cebfffe7-c28b-4be4-921b-0de7bf823cea";
                $AppSecret = "4ff81339-49b9-488a-8732-54f8e2c6fa7e";
                $v = "1.0";
                $order_json = '{"deliveryOrderNo":"'.$way_bill_num.'"}'; // 请替换成实际的JSON数据
                $timestamp = round(microtime(true) * 1000);
                $sign = get_sign("tr.order.tracking.get", $order_json, $v,$AppKey,$AppSecret,$timestamp);
                $url = "https://open.4px.com/router/api/service?method=tr.order.tracking.get&app_key=" . $AppKey . "&format=json&v=1.0&sign=" . $sign . "&timestamp=" . $timestamp;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $order_json);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);

                $res_json = json_decode($response, true);
                $trackingList = $res_json['data']['trackingList'];
                $tbody = '';
                $tr_i = 1;
                while ($tracking = current($trackingList)) {
                    if ($tr_i == 1){
                        if (count($trackingList) > 1){          
                            $tr = '<tr><a href="javascript:#" class="wp-block-search__button wp-element-button"  onclick="toggleTable(\'table'.$Package_int.'\')" >show more</a> ';
                        }
                        else{
                            $tr = '<tr>';
                        }
                    }else{
                        $tr = '<tr class="collapsed" >';
                    }
                        
                    $tbody .= $tr.'<td> <h5>'.$tracking['occurDatetime'].'&nbsp;'.$tracking['occurLocation'].'</h5>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$tracking['trackingContent'].'</td></tr>';
                    $tr_i++;
                    next($trackingList);
                    
                }
                echo '<table><tbody id="table'.$Package_int.'">'.$tbody.'</tbody></table>';
                #echo json_encode($trackingList, JSON_PRETTY_PRINT);
            } 
            else if (strpos($way_bill_num, 'YT') !== false){
                $token = get_token();
                $url = 'http://oms.api.yunexpress.com/api/Tracking/GetTrackInfo?OrderNumber='. $way_bill_num;
                $headers = [
                    'Authorization: Basic ' . $token,
                    'Content-Type: application/json'
                ];
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                curl_close($ch);
                // 尝试将响应数据解析为JSON格式
                $jsonResponse = json_decode($response, true);
                $OrderTrackingDetails = $jsonResponse['Item']['OrderTrackingDetails'];
                $tbody = '';
                $tr_i = 1;
                end($OrderTrackingDetails); // 将数组指针移动到最后一个元素
                while ($OrderTracking = current($OrderTrackingDetails)) {
                    if ($tr_i == 1){
                        if (count($OrderTrackingDetails) > 1){          
                            $tr = '<tr><a href="javascript:#" class="wp-block-search__button wp-element-button" onclick="toggleTable(\'table'.$Package_int.'\')" >show more</a> ';
                        }
                        else{
                            $tr = '<tr>';
                        }
                    }else{
                        $tr = '<tr class="collapsed" >';
                    }
                    $tbody .= $tr.'<td> <h5>'.$OrderTracking['ProcessDate'].'&nbsp;'.$OrderTracking['ProcessLocation'].'</h5>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$OrderTracking['ProcessContent'].'</td></tr>';
                    $tr_i++;
                    prev($OrderTrackingDetails); // 倒退一个元素
                }
                echo '<table><tbody id="table'.$Package_int.'">'.$tbody.'</tbody></table>';

                //echo json_encode($OrderTrackingDetails, JSON_PRETTY_PRINT);
                
            }
            
        } 
        catch (Exception $e) {
            die('None to be found.');
        }
    }
    // 在这里使用 PHP 来显示搜索结果
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $keyword = $_POST["keyword"];
        //清楚左右空格
        $keyword = trim($keyword);
        // 这里可以输出你的搜索结果
        if ( $keyword == ''){
                exit();
        }

        $search_type = $_POST["search_type"]; // 获取用户选择的搜索类型
        if ($search_type == "order_num") {
            
            //判断$keyword是否为纯数字
            if (!preg_match('/^\d+$/', $keyword)) {
                //不是"纯数字";
                die('Incorrect order number format.');
            }
            $jsonFilePath = '/home/dbkey/key.json'; //dbkey sbssb
            // Check if the file exists
            if (!file_exists($jsonFilePath)) {
                die('None to be found1.');
            }
            $jsonString = file_get_contents($jsonFilePath);
            // Parse the JSON data into a PHP array or object
            $jsonData = json_decode($jsonString);
            if ($jsonData == null) {
                die('None to be found2.');
            }
            //数据库连接
            $db = new mysqli($jsonData->host, $jsonData->username, $jsonData->password, $jsonData->database);
            //执行查询
            $query = "SELECT `package`.way_bill_num FROM `order` INNER JOIN `package` ON `order` .wc_order_id = `package`.wc_order_id WHERE `order`.wc_order_num = $keyword";
            $result = $db->query($query);
            $way_bill_num = '';
            if (!$result) {      
                die('None to be found3.');
            } 
            $way_bill_num_list = array();
            while ($row = $result->fetch_assoc()) {    
                if ($row['way_bill_num']!== ""){
                    $way_bill_num_list[] = $row['way_bill_num'];
                }                                        
            }
            //echo json_encode($way_bill_num_list, JSON_PRETTY_PRINT);
            // $row = $result->fetch_assoc();
            // $way_bill_num = $row['way_bill_num'];
            //关闭数据库连接
            $db->close();

            
        }else{
            //单选框选中way_bill_num
            echo "<script>document.getElementById('way_bill_num').checked=true;</script>";
            $way_bill_num_list = [$keyword];
        }
        $Package_int = 1;
        while ($way_bill_num = current($way_bill_num_list)) {
            get_tracking_info($way_bill_num, $Package_int);
            next($way_bill_num_list); // 倒退一个元素
            $Package_int++;
        }

        

       
        
    
}
    ?>
    </form>
</body>
</html>
