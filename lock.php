<?php
set_time_limit(0);
define('ROOT_DIR', strtr(substr(__FILE__, 0, -9), '\\', '/'));

//测试目录
$dirs = array(
    'share-datadir'
);
$j = 10000;
$w = str_repeat('-', 10240);

//单文件锁
echo "<pre>\n";
foreach ($dirs as &$dir) {
    $a = microtime(true);
    for ($i = 0; $i < $j * 4; ++$i) {
        $lock = fopen(ROOT_DIR . "/{$dir}/lock.txt", 'w');
        flock($lock, 2);
        fclose($lock);
    }
    echo "lock single {$dir}: ", round(microtime(true) - $a, 2), "s\n";
}

//随机锁
echo "\n";
foreach ($dirs as &$dir) {
    $a = microtime(true);
    for ($i = 0; $i < $j * 4; ++$i) {
        $temp = uniqid();
        $$temp = fopen(ROOT_DIR . "/{$dir}/{$temp}", 'w');
        flock($$temp, 2);
    }
    echo "lock rand {$dir}: ", round(microtime(true) - $a, 2), "s\n";
}

//单文件写
echo "\n";
foreach ($dirs as &$dir) {
    $a = microtime(true);
    for ($i = 0; $i < $j; ++$i) {
        $lock = fopen(ROOT_DIR . "/{$dir}/lock.txt", 'w');
        fwrite($lock, $w);
        fclose($lock);
    }
    echo "write single {$dir}: ", round(microtime(true) - $a, 2), "s\n";
}

//单文件读
echo "\n";
foreach ($dirs as &$dir) {
    $a = microtime(true);
    for ($i = 0; $i < $j * 10; ++$i) {
        $lock = fopen(ROOT_DIR . "/{$dir}/lock.txt", 'r');
        fread($lock, strlen($w));
        fclose($lock);
    }
    echo "read single {$dir}: ", round(microtime(true) - $a, 2), "s\n";
}

//批量删除
echo "\n";
foreach ($dirs as &$dir) {
    $a = microtime(true);
    $handle = opendir(ROOT_DIR . "/{$dir}");
    while (is_string($v = readdir($handle))) {
        $v[0] === '.' || is_file($temp = ROOT_DIR . "/{$dir}/{$v}") && unlink($temp);
    }
    closedir($handle);
    echo "delete batch {$dir}: ", round(microtime(true) - $a, 2), "s\n";
}

exit;