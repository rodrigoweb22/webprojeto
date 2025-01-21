<?php
$action = $_POST['action'];
$action($_POST);

function slug($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        return $text;

    }

function servidorOrigemRequest()
{
    $origin = $_SERVER['REMOTE_ADDR'];

    if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
        $origin = $_SERVER['HTTP_ORIGIN'];
    } else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
        $origin = $_SERVER['HTTP_REFERER'];
    }

    $origin = explode("//", $origin);
    $origin = $origin[1];

    return $origin;
}

function listaCidades($post = null)
{
    $url = URL_AMBIENTE . "/api/cidade";
    $header = [
        "token_app: A4fol741q9u8g5l6nxl65",
        "codigo_uf: {$post['search']}",
    ];
    $retorno = solicitaApi($url, [], $header, 'GET');
    return retorno(200, 'Lista de cidades', json_decode($retorno, true));
}

function validacaoGoogle($post = null)
{
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $dados = [
        "secret" => "6LfYltQhAAAAAHpmOaiLgAMsEsh9ZC-jWXi2In25",
        "response" => $post['search'],
        "remoteip" => $_SERVER['REMOTE_ADDR']
    ];

    $ret = solicitaApi($url, $dados, [], 'POST');
    return retorno(200, 'Resposta de validação do google obtida com sucesso.', $ret);
}


function validarToken($post)
{
 
    $soma = $post['valida1'] + $post['valida2'];
    $captchaResponse = $post["valida"];

// Verifica se a resposta do reCAPTCHA numérico está correta
    if ($soma == $captchaResponse) {
        contato($post);
    } else {

    $code = 203;
    $message = "Resultado incorreto!";

    header('Content-Type: application/json');
    echo json_encode(['code' => $code, 'message' => $message]);
    exit;

    }

}


function contato($post)
{
 
    $header = array(
        "Content-Type:application/x-www-form-urlencoded",
        "token_app:pidcVHuKvq2DHJWwvN9hs"
    );

    $post = http_build_query($post);

    $to      = 'teste@mais10comunicacaovisual.com.br';
    $subject = 'the subject';
    $message = 'hello';
    $headers = 'From: rodrigo.sousa@rensoftware.com.br' . "\r\n" .
        'Reply-To: rodrigo.sousa@rensoftware.com.br' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    
    mail($to, $subject, $message, $headers);

}



function gerarLead($post)
{

    $header = array(
        "Content-Type:application/x-www-form-urlencoded",
        "token_app:pidcVHuKvq2DHJWwvN9hs"
    );

    $post["cidade_codigo"] = "8";
    
    if (empty($post["cnpj_cpf"])) {
        $post["cnpj_cpf"] = "000.000.000-00";
    }

    $post = http_build_query($post);

    //$url = URL_API_SERVICO . "/leadSite/gravarMensagem";
    // $url = 'http://222.222.1.23:8081/leadSite/gravarMensagem';
    $url = 'http://222.222.1.23:8081/easyRen/cadastrarIndicacaoProspect';
    $ret = json_decode(solicitaApi($url, $post, $header, 'POST'), false);

    return retorno($ret->status, $ret->msg);

}

function recuperarToken($post)
{
    $header = array(
        "Content-Type:application/x-www-form-urlencoded",
        "token_app:pidcVHuKvq2DHJWwvN9hs"
    );

    $post = http_build_query($post);

    //$url = URL_API_SERVICO . "/leadSite/gravarMensagem";
    // $url = 'http://222.222.1.23:8081/leadSite/gravarMensagem';
    $url = 'http://222.222.1.23:8081/easyRen/validarToken';
    $ret = json_decode(solicitaApi($url, $post, $header, 'POST'), false);
    
    return retorno($ret->status, $ret->msg);

}

// function solicitaProposta($post)
// {
//     $info = explode('&', $post['search']);
//     $infoData = null;

//     foreach ($info as $k => $v) {
//         $i = explode('=', $v);
//         $infoData[$i[0]] = str_replace(['%2F', '%40', '+'], ['/', '@', ' '], $i[1]);
//     }

//     $dados = [
//         "cidade_codigo" => $infoData['cidade'],
//         "telefone" => $infoData['telefone'],
//         "email" => $infoData['email'],
//         "nome_cliente" => $infoData['nome'],
//         "observacao" => $infoData['mensagem'],
//         "origem_indicacao" => 4,
//     ];
//     $data = http_build_query($dados);

//     $url = URL_API_SERVICO . "/leadSite/gravarMensagem";
//     $ret = json_decode(solicitaApi($url, $data, [TOKEN_API_AMBIENTE], 'POST'));
//     return retorno($ret->status, $ret->msg);
// }

function solicitaApi($url, $data, $header = null, $tipoRequisicao = 'POST')
{
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $tipoRequisicao,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => $header,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    return ($err) ? $err : $response;
}

function retorno($code = 200, $message = '', $data = [], $paginacao = null)
{
    header('Content-Type: application/json');
    echo json_encode(['code' => $code, 'message' => $message, 'data' => $data, 'paginacao' => $paginacao]);
    exit;
}

function formataStringEmail($str, $data)
{
    $arr = [
        "{REN_DISTRICT}" => $data["departamento"],
        "{REN_TYPE}" => $data["tipo_contato"],
        "{REN_NAME}" => $data["nome"],
        "{REN_EMAIL}" => $data["email"],
        "{REN_TELEFONE}" => $data["telefone"],
        "{REN_MENSAGEM}" => $data["mensagem"],
        "{REN_CIDADE}" => $data["cidade"],
        "{REN_ESTADO}" => $data["estado"],
        "{REN_YEAR}" => date("Y")
    ];

    return str_replace(array_keys($arr), array_values($arr), $str);
}
