<?php

$GLOBALS['cwd'] = getcwd();
$GLOBALS['time'] = strtotime('now');
$GLOBALS['filetype'] = $_POST['filetype'];

function filter_parameter($input) {
    return preg_replace('/\s+/', '', $input);
}

function shutdown() {
    $html_file = $GLOBALS['cwd'].'/request-'.$GLOBALS['time'].'.html';
    $pdf_file = $GLOBALS['cwd'].'/report-'.$GLOBALS['time'].'.'.$GLOBALS['filetype'];
    if(file_exists($html_file))
        unlink($html_file);
    if(file_exists($pdf_file))
        unlink($pdf_file);
}

register_shutdown_function('shutdown');

if(isset($_POST['html']) && isset($_POST['filetype'])) {
    $req_dump = '<html>
    <head>
        <title>Sample Report</title>
        <META name="generator" content="CrowdGooseDocGen v1.0.0">
        <META NAME="COPYRIGHT" CONTENT="CrowdGooseDocGen v1.0.0">
        <meta charset="utf-8">
    </head><body>';
    $req_dump .= print_r($_POST['html'], TRUE);
    $req_dump .= "</body></html>";
    $fp = fopen('request-'.$time.'.html', 'w');
    fwrite($fp, $req_dump);
    fclose($fp);
    $allow = array('epub','html','pdf');
    if(in_array($_POST['filetype'], $allow)) {
        $additional = '';
        if(isset($_POST['extra_size'])) $additional = ' --size '.filter_parameter($_POST['extra_size']);
        if(isset($_POST['extra_font_size'])) $additional .= ' --fontsize '.filter_parameter($_POST['extra_font_size']);
        if(isset($_POST['extra_compression'])) $additional .= ' --compression '.filter_parameter($_POST['extra_compression']);
        $format = ' --format '.$_POST['filetype'];
        shell_exec('htmldoc --charset utf-8 --footer 1td -f report-'.$time.'.'.$_POST['filetype'].' request-'.$time.'.html'.$format.$additional);
        $pdf_file = 'report-'.$time.'.'.$_POST['filetype'];
        if(file_exists($pdf_file))
        {
            if ($_POST['filetype'] === 'pdf') header("Content-Type: application/pdf");
            if ($_POST['filetype'] === 'html') header("Content-Type: text/html");
            if ($_POST['filetype'] === 'epub') { 
                header('Content-Disposition: attachment; filename="report-'.$time.'.epub"');
                header("Content-Type: application/epub+zip");
            }
            echo file_get_contents($pdf_file);
        }
        else {
            http_response_code(500);
            header('Content-Type: text/plain');
            echo "Something went wrong.";
        }
    }
    else {
        http_response_code(400);
        header('Content-Type: text/plain');
        echo "Invalid file type.";
    }
}
else {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo "Missing parameter.";
}

?>