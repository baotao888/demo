<?php
    if(!function_exists('parse_padding')){
        function parse_padding($source)
        {
            $length  = strlen(strval(count($source['source']) + $source['first']));
            return 40 + ($length - 1) * 8;
        }
    }

    if(!function_exists('parse_class')){
        function parse_class($name)
        {
            $names = explode('\\', $name);
            return '<abbr title="'.$name.'">'.end($names).'</abbr>';
        }
    }

    if(!function_exists('parse_file')){
        function parse_file($file, $line)
        {
            return '<a class="toggle" title="'."{$file} line {$line}".'">'.basename($file)." line {$line}".'</a>';
        }
    }

    if(!function_exists('parse_args')){
        function parse_args($args)
        {
            $result = [];

            foreach ($args as $key => $item) {
                switch (true) {
                    case is_object($item):
                        $value = sprintf('<em>object</em>(%s)', parse_class(get_class($item)));
                        break;
                    case is_array($item):
                        if(count($item) > 3){
                            $value = sprintf('[%s, ...]', parse_args(array_slice($item, 0, 3)));
                        } else {
                            $value = sprintf('[%s]', parse_args($item));
                        }
                        break;
                    case is_string($item):
                        if(strlen($item) > 20){
                            $value = sprintf(
                                '\'<a class="toggle" title="%s">%s...</a>\'',
                                htmlentities($item),
                                htmlentities(substr($item, 0, 20))
                            );
                        } else {
                            $value = sprintf("'%s'", htmlentities($item));
                        }
                        break;
                    case is_int($item):
                    case is_float($item):
                        $value = $item;
                        break;
                    case is_null($item):
                        $value = '<em>null</em>';
                        break;
                    case is_bool($item):
                        $value = '<em>' . ($item ? 'true' : 'false') . '</em>';
                        break;
                    case is_resource($item):
                        $value = '<em>resource</em>';
                        break;
                    default:
                        $value = htmlentities(str_replace("\n", '', var_export(strval($item), true)));
                        break;
                }

                $result[] = is_int($key) ? $value : "'{$key}' => {$value}";
            }

            return implode(', ', $result);
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo \think\Lang::get('System Error'); ?></title>
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
</head>
<body>
    <div class="echo">
        <?php echo $echo;?>
    </div>
    <?php if(\think\App::$debug) { ?>
    <div class="exception">
        <h2>[<?php echo $code; ?>] <?php echo sprintf('%s in %s', parse_class($name), parse_file($file, $line)); ?></h2>
        <h1><?php echo nl2br(htmlentities($message)); ?></h1>
        <div class="trace">
            <h2>Call Stack</h2>
            <ol>
                <li><?php echo sprintf('in %s', parse_file($file, $line)); ?></li>
                <?php foreach ((array) $trace as $value) { ?>
                <li>
                <?php 
                    // Show Function
                    if($value['function']){
                        echo sprintf(
                            'at %s%s%s(%s)', 
                            isset($value['class']) ? parse_class($value['class']) : '',
                            isset($value['type'])  ? $value['type'] : '', 
                            $value['function'], 
                            isset($value['args'])?parse_args($value['args']):''
                        );
                    }

                    // Show line
                    if (isset($value['file']) && isset($value['line'])) {
                        echo sprintf(' in %s', parse_file($value['file'], $value['line']));
                    }
                ?>
                </li>
                <?php } ?>
            </ol>
        </div>
    </div>
    <?php } else { ?>
    <div class="exception">
        <div class="info"><h1><?php echo htmlentities($message); ?></h1></div> 
    </div>
    <?php } ?>
</body>
</html>
